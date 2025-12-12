<?php

namespace App\Http\Controllers\UserControllers\StudentControllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\MongoService;
use App\Models\Enrollment;
use MongoDB\BSON\ObjectId;

class CourseController extends Controller
{
    private $mongo;

    public function __construct()
    {
        $this->mongo = new MongoService();
    }

    /**
     * Get available courses for student enrollment
     */
    public function getAvailableCourses(Request $request)
    {
        try {
            // Authenticate student
            $token = $request->header('Authorization');
            if (!$token) {
                return response()->json(['status' => 'error', 'message' => 'Authentication token not provided'], 401);
            }
            $token = str_replace('Bearer ', '', $token);

            $session = $this->mongo->findSessionByToken($token);
            if (!$session) {
                return response()->json(['status' => 'error', 'message' => 'Invalid or expired session'], 401);
            }

            $user = $this->mongo->findOne('users', ['_id' => new ObjectId($session['user_id'])]);
            if (!$user || $user['role'] !== 'assigned_student') {
                return response()->json(['status' => 'error', 'message' => 'Access denied. Students only.'], 403);
            }

            // Get all active courses
            $courses = $this->mongo->find('courses', ['status' => 'active'], [
                'sort' => ['department' => 1, 'title' => 1]
            ]);

            // Get student's current enrollments
            $enrollments = $this->mongo->find('enrollments', ['user_id' => $session['user_id']]);
            $enrolledCourseIds = [];
            $pendingCourseIds = [];

            foreach ($enrollments as $enrollment) {
                if ($enrollment['status'] === 'enrolled') {
                    $enrolledCourseIds[] = $enrollment['course_id'];
                } elseif ($enrollment['status'] === 'pending') {
                    $pendingCourseIds[] = $enrollment['course_id'];
                }
                // Note: cancelled enrollments are ignored, allowing re-enrollment
            }

            // Format courses with enrollment status
            $courseList = [];
            foreach ($courses as $course) {
                $courseId = (string)$course['_id'];
                $enrollmentStatus = 'available';

                if (in_array($courseId, $enrolledCourseIds)) {
                    $enrollmentStatus = 'enrolled';
                } elseif (in_array($courseId, $pendingCourseIds)) {
                    $enrollmentStatus = 'pending';
                }

                $courseList[] = [
                    'id' => $courseId,
                    'title' => $course['title'] ?? 'Untitled Course',
                    'description' => $course['description'] ?? 'No description available',
                    'department' => $course['department'] ?? 'General',
                    'enrollment_status' => $enrollmentStatus,
                    'created_at' => $course['created_at'] ?? null
                ];
            }

            return response()->json([
                'status' => 'success',
                'courses' => $courseList,
                'student' => [
                    'id' => $session['user_id'],
                    'name' => $user['name'] ?? 'Unknown Student'
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Internal server error', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Request enrollment in a course
     */
    public function requestEnrollment(Request $request)
    {
        try {
            // Authenticate student
            $token = $request->header('Authorization');
            if (!$token) {
                return response()->json(['status' => 'error', 'message' => 'Authentication token not provided'], 401);
            }
            $token = str_replace('Bearer ', '', $token);

            $session = $this->mongo->findSessionByToken($token);
            if (!$session) {
                return response()->json(['status' => 'error', 'message' => 'Invalid or expired session'], 401);
            }

            $user = $this->mongo->findOne('users', ['_id' => new ObjectId($session['user_id'])]);
            if (!$user || $user['role'] !== 'assigned_student') {
                return response()->json(['status' => 'error', 'message' => 'Access denied. Students only.'], 403);
            }

            // Validate input
            $validator = \Validator::make($request->all(), [
                'course_id' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => 'error', 'message' => 'Validation failed', 'errors' => $validator->errors()->all()], 422);
            }

            $courseId = $request->course_id;

            // Check if course exists and is active
            try {
                $course = $this->mongo->findOne('courses', [
                    '_id' => new ObjectId($courseId),
                    'status' => 'active'
                ]);
            } catch (\Exception $e) {
                return response()->json(['status' => 'error', 'message' => 'Invalid course ID'], 422);
            }

            if (!$course) {
                return response()->json(['status' => 'error', 'message' => 'Course not found or not active'], 404);
            }

            // Check if student already has an enrollment request for this course
            $existingEnrollment = $this->mongo->findOne('enrollments', [
                'user_id' => $session['user_id'],
                'course_id' => $courseId
            ]);

            if ($existingEnrollment) {
                $status = $existingEnrollment['status'];
                if ($status === 'enrolled') {
                    return response()->json(['status' => 'error', 'message' => 'You are already enrolled in this course'], 409);
                } elseif ($status === 'pending') {
                    return response()->json(['status' => 'error', 'message' => 'You already have a pending enrollment request for this course'], 409);
                }
            }

            // Create enrollment request
            $enrollment = Enrollment::fromRequest([
                'user_id' => $session['user_id'],
                'course_id' => $courseId
            ]);

            $result = $this->mongo->insertOne('enrollments', $enrollment->toArray());

            if ($result->getInsertedId()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Enrollment request submitted successfully',
                    'enrollment_id' => (string)$result->getInsertedId()
                ], 201);
            }

            return response()->json(['status' => 'error', 'message' => 'Failed to submit enrollment request'], 500);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Internal server error', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Request unenrollment from a course
     */
    public function requestUnenrollment(Request $request)
    {
        try {
            // Authenticate student
            $token = $request->header('Authorization');
            if (!$token) {
                return response()->json(['status' => 'error', 'message' => 'Authentication token not provided'], 401);
            }
            $token = str_replace('Bearer ', '', $token);

            $session = $this->mongo->findSessionByToken($token);
            if (!$session) {
                return response()->json(['status' => 'error', 'message' => 'Invalid or expired session'], 401);
            }

            $user = $this->mongo->findOne('users', ['_id' => new ObjectId($session['user_id'])]);
            if (!$user || $user['role'] !== 'assigned_student') {
                return response()->json(['status' => 'error', 'message' => 'Access denied. Students only.'], 403);
            }

            // Validate input
            $validator = \Validator::make($request->all(), [
                'course_id' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => 'error', 'message' => 'Validation failed', 'errors' => $validator->errors()->all()], 422);
            }

            $courseId = $request->course_id;

            // Check if course exists and is active
            try {
                $course = $this->mongo->findOne('courses', [
                    '_id' => new ObjectId($courseId),
                    'status' => 'active'
                ]);
            } catch (\Exception $e) {
                return response()->json(['status' => 'error', 'message' => 'Invalid course ID'], 422);
            }

            if (!$course) {
                return response()->json(['status' => 'error', 'message' => 'Course not found or not active'], 404);
            }

            // Check if student is currently enrolled in the course
            $enrolledCoursesRaw = $user['enrolled_courses'] ?? [];
            $enrolledCourses = [];

            // Handle different MongoDB document types
            if ($enrolledCoursesRaw instanceof \MongoDB\Model\BSONArray) {
                foreach ($enrolledCoursesRaw as $courseDoc) {
                    if ($courseDoc instanceof \MongoDB\Model\BSONDocument) {
                        $courseArray = [];
                        foreach ($courseDoc as $key => $value) {
                            $courseArray[$key] = $value;
                        }
                        $enrolledCourses[] = $courseArray;
                    } else {
                        $enrolledCourses[] = (array)$courseDoc;
                    }
                }
            } elseif (is_array($enrolledCoursesRaw)) {
                $enrolledCourses = $enrolledCoursesRaw;
            } elseif (is_iterable($enrolledCoursesRaw)) {
                foreach ($enrolledCoursesRaw as $course) {
                    if ($course instanceof \MongoDB\Model\BSONDocument) {
                        $courseArray = [];
                        foreach ($course as $key => $value) {
                            $courseArray[$key] = $value;
                        }
                        $enrolledCourses[] = $courseArray;
                    } else {
                        $enrolledCourses[] = (array)$course;
                    }
                }
            }

            // Check if student is enrolled in this course
            $isEnrolled = false;
            foreach ($enrolledCourses as $enrolledCourse) {
                if (isset($enrolledCourse['id']) && $enrolledCourse['id'] === $courseId) {
                    $isEnrolled = true;
                    break;
                }
            }

            if (!$isEnrolled) {
                return response()->json(['status' => 'error', 'message' => 'You are not enrolled in this course'], 409);
            }

            // Check if there's already a pending unenrollment request
            $existingEnrollment = $this->mongo->findOne('enrollments', [
                'user_id' => $session['user_id'],
                'course_id' => $courseId,
                'status' => 'unenrollment_requested'
            ]);

            if ($existingEnrollment) {
                return response()->json(['status' => 'error', 'message' => 'You already have a pending unenrollment request for this course'], 409);
            }

            // Create unenrollment request by updating existing enrollment record
            $sriLankaTimezone = new \DateTimeZone('Asia/Colombo');
            $currentTime = new \DateTime('now', $sriLankaTimezone);

            // Find the enrolled enrollment record and update it to unenrollment_requested
            $result = $this->mongo->updateOne('enrollments',
                [
                    'user_id' => $session['user_id'],
                    'course_id' => $courseId,
                    'status' => 'enrolled'
                ],
                [
                    '$set' => [
                        'status' => 'unenrollment_requested',
                        'unenrollment_requested_at' => $currentTime->format('Y-m-d H:i:s'),
                        'updated_at' => $currentTime->format('Y-m-d H:i:s')
                    ]
                ]
            );

            if ($result->getModifiedCount() > 0) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Unenrollment request submitted successfully. Your request will be reviewed by administrators.',
                    'course_title' => $course['title'] ?? 'Unknown Course'
                ], 200);
            }

            return response()->json(['status' => 'error', 'message' => 'Failed to submit unenrollment request'], 500);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Internal server error', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Cancel enrollment request
     */
    public function cancelEnrollmentRequest(Request $request)
    {
        try {
            // Authenticate student
            $token = $request->header('Authorization');
            if (!$token) {
                return response()->json(['status' => 'error', 'message' => 'Authentication token not provided'], 401);
            }
            $token = str_replace('Bearer ', '', $token);

            $session = $this->mongo->findSessionByToken($token);
            if (!$session) {
                return response()->json(['status' => 'error', 'message' => 'Invalid or expired session'], 401);
            }

            $user = $this->mongo->findOne('users', ['_id' => new ObjectId($session['user_id'])]);
            if (!$user || $user['role'] !== 'assigned_student') {
                return response()->json(['status' => 'error', 'message' => 'Access denied. Students only.'], 403);
            }

            // Validate input
            $validator = \Validator::make($request->all(), [
                'course_id' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => 'error', 'message' => 'Validation failed', 'errors' => $validator->errors()->all()], 422);
            }

            $courseId = $request->course_id;

            // Find the pending enrollment request
            $enrollment = $this->mongo->findOne('enrollments', [
                'user_id' => $session['user_id'],
                'course_id' => $courseId,
                'status' => 'pending'
            ]);

            if (!$enrollment) {
                return response()->json(['status' => 'error', 'message' => 'No pending enrollment request found for this course'], 404);
            }

            // Update enrollment status to cancelled
            $sriLankaTimezone = new \DateTimeZone('Asia/Colombo');
            $currentTime = new \DateTime('now', $sriLankaTimezone);

            $result = $this->mongo->updateOne('enrollments',
                [
                    '_id' => $enrollment['_id'],
                    'status' => 'pending'
                ],
                [
                    '$set' => [
                        'status' => 'cancelled',
                        'cancelled_at' => $currentTime->format('Y-m-d H:i:s'),
                        'updated_at' => $currentTime->format('Y-m-d H:i:s')
                    ]
                ]
            );

            if ($result->getModifiedCount() > 0) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Enrollment request cancelled successfully'
                ], 200);
            }

            return response()->json(['status' => 'error', 'message' => 'Failed to cancel enrollment request'], 500);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Internal server error', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Cancel unenrollment request
     */
    public function cancelUnenrollmentRequest(Request $request)
    {
        try {
            // Authenticate student
            $token = $request->header('Authorization');
            if (!$token) {
                return response()->json(['status' => 'error', 'message' => 'Authentication token not provided'], 401);
            }
            $token = str_replace('Bearer ', '', $token);

            $session = $this->mongo->findSessionByToken($token);
            if (!$session) {
                return response()->json(['status' => 'error', 'message' => 'Invalid or expired session'], 401);
            }

            $user = $this->mongo->findOne('users', ['_id' => new ObjectId($session['user_id'])]);
            if (!$user || $user['role'] !== 'assigned_student') {
                return response()->json(['status' => 'error', 'message' => 'Access denied. Students only.'], 403);
            }

            // Validate input
            $validator = \Validator::make($request->all(), [
                'course_id' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => 'error', 'message' => 'Validation failed', 'errors' => $validator->errors()->all()], 422);
            }

            $courseId = $request->course_id;

            // Find the unenrollment request and revert it back to enrolled
            $sriLankaTimezone = new \DateTimeZone('Asia/Colombo');
            $currentTime = new \DateTime('now', $sriLankaTimezone);

            $result = $this->mongo->updateOne('enrollments',
                [
                    'user_id' => $session['user_id'],
                    'course_id' => $courseId,
                    'status' => 'unenrollment_requested'
                ],
                [
                    '$set' => [
                        'status' => 'enrolled',
                        'updated_at' => $currentTime->format('Y-m-d H:i:s')
                    ],
                    '$unset' => [
                        'unenrollment_requested_at' => ''
                    ]
                ]
            );

            if ($result->getModifiedCount() > 0) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Unenrollment request cancelled successfully'
                ], 200);
            }

            return response()->json(['status' => 'error', 'message' => 'No pending unenrollment request found for this course'], 404);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Internal server error', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get student's enrolled courses (Updated to include unenrollment status)
     */
    public function getEnrolledCourses(Request $request)
    {
        try {
            // Authenticate student
            $token = $request->header('Authorization');
            if (!$token) {
                return response()->json(['status' => 'error', 'message' => 'Authentication token not provided'], 401);
            }
            $token = str_replace('Bearer ', '', $token);

            $session = $this->mongo->findSessionByToken($token);
            if (!$session) {
                return response()->json(['status' => 'error', 'message' => 'Invalid or expired session'], 401);
            }

            $user = $this->mongo->findOne('users', ['_id' => new ObjectId($session['user_id'])]);
            if (!$user || $user['role'] !== 'assigned_student') {
                return response()->json(['status' => 'error', 'message' => 'Access denied. Students only.'], 403);
            }

            // Get enrolled courses from user document and convert to array properly
            $enrolledCoursesRaw = $user['enrolled_courses'] ?? [];
            $enrolledCourses = [];

            // Handle different MongoDB document types
            if ($enrolledCoursesRaw instanceof \MongoDB\Model\BSONArray) {
                foreach ($enrolledCoursesRaw as $courseDoc) {
                    if ($courseDoc instanceof \MongoDB\Model\BSONDocument) {
                        $courseArray = [];
                        foreach ($courseDoc as $key => $value) {
                            $courseArray[$key] = $value;
                        }
                        $enrolledCourses[] = $courseArray;
                    } else {
                        $enrolledCourses[] = (array)$courseDoc;
                    }
                }
            } elseif (is_array($enrolledCoursesRaw)) {
                $enrolledCourses = $enrolledCoursesRaw;
            } elseif (is_iterable($enrolledCoursesRaw)) {
                foreach ($enrolledCoursesRaw as $course) {
                    if ($course instanceof \MongoDB\Model\BSONDocument) {
                        $courseArray = [];
                        foreach ($course as $key => $value) {
                            $courseArray[$key] = $value;
                        }
                        $enrolledCourses[] = $courseArray;
                    } else {
                        $enrolledCourses[] = (array)$course;
                    }
                }
            }

            if (empty($enrolledCourses)) {
                return response()->json([
                    'status' => 'success',
                    'courses' => [],
                    'total_courses' => 0
                ], 200);
            }

            // Extract course IDs safely
            $courseIds = [];
            foreach ($enrolledCourses as $course) {
                if (isset($course['id']) && is_string($course['id'])) {
                    try {
                        $courseIds[] = new ObjectId($course['id']);
                    } catch (\Exception $e) {
                        continue;
                    }
                }
            }

            if (empty($courseIds)) {
                return response()->json([
                    'status' => 'success',
                    'courses' => [],
                    'total_courses' => 0
                ], 200);
            }

            $courses = $this->mongo->find('courses', [
                '_id' => ['$in' => $courseIds]
            ], [
                'sort' => ['department' => 1, 'title' => 1]
            ]);

            // Get enrollment statuses for each course
            $enrollmentStatuses = [];
            $enrollments = $this->mongo->find('enrollments', [
                'user_id' => $session['user_id'],
                'course_id' => ['$in' => array_map(function($course) {
                    return $course['id'];
                }, $enrolledCourses)]
            ]);

            foreach ($enrollments as $enrollment) {
                $enrollmentStatuses[$enrollment['course_id']] = $enrollment['status'] ?? 'enrolled';
            }

            $courseDetails = [];
            foreach ($courses as $course) {
                $courseArray = [];
                if ($course instanceof \MongoDB\Model\BSONDocument) {
                    foreach ($course as $key => $value) {
                        $courseArray[$key] = $value;
                    }
                } else {
                    $courseArray = (array)$course;
                }

                $courseId = (string)($courseArray['_id'] ?? '');
                $enrollmentStatus = $enrollmentStatuses[$courseId] ?? 'enrolled';

                $courseDetails[] = [
                    'id' => $courseId,
                    'title' => $courseArray['title'] ?? 'Untitled Course',
                    'description' => $courseArray['description'] ?? 'No description available',
                    'department' => $courseArray['department'] ?? 'General',
                    'status' => $courseArray['status'] ?? 'inactive',
                    'enrollment_status' => $enrollmentStatus
                ];
            }

            return response()->json([
                'status' => 'success',
                'courses' => $courseDetails,
                'total_courses' => count($courseDetails)
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Internal server error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get count of enrolled courses for the student
     */
    public function getEnrolledCoursesCount(Request $request)
    {
        try {
            // Authenticate student
            $token = $request->header('Authorization');
            if (!$token) {
                return response()->json(['status' => 'error', 'message' => 'Authentication token not provided'], 401);
            }
            $token = str_replace('Bearer ', '', $token);

            $session = $this->mongo->findSessionByToken($token);
            if (!$session) {
                return response()->json(['status' => 'error', 'message' => 'Invalid or expired session'], 401);
            }

            $user = $this->mongo->findOne('users', ['_id' => new ObjectId($session['user_id'])]);
            if (!$user || $user['role'] !== 'assigned_student') {
                return response()->json(['status' => 'error', 'message' => 'Access denied. Students only.'], 403);
            }

            // Get enrolled_courses array and count
            $enrolledCoursesRaw = $user['enrolled_courses'] ?? [];
            $count = 0;

            if ($enrolledCoursesRaw instanceof \MongoDB\Model\BSONArray) {
                $count = count($enrolledCoursesRaw);
            } elseif (is_array($enrolledCoursesRaw)) {
                $count = count($enrolledCoursesRaw);
            } elseif (is_iterable($enrolledCoursesRaw)) {
                foreach ($enrolledCoursesRaw as $course) {
                    $count++;
                }
            }

            return response()->json([
                'status' => 'success',
                'enrolled_courses_count' => $count
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Internal server error',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
