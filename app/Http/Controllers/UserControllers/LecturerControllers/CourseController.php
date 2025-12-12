<?php

namespace App\Http\Controllers\UserControllers\LecturerControllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\MongoService;
use MongoDB\BSON\ObjectId;

class CourseController extends Controller
{
    private $mongo;

    public function __construct()
    {
        $this->mongo = new MongoService();
    }

    /**
     * Get lecturer's assigned courses from their courses array
     * Only accessible by authenticated lecturer
     */
    public function getMyCourses(Request $request)
    {
        try {
            // Get token from Authorization header
            $token = $request->header('Authorization');

            if (!$token) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Authentication token not provided'
                ], 401);
            }

            // Remove "Bearer " prefix if present
            $token = str_replace('Bearer ', '', $token);

            // Find active session to get user
            $session = $this->mongo->findSessionByToken($token);

            if (!$session) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid or expired session. Please login again.'
                ], 401);
            }

            // Fetch the user using the user_id from the session
            $user = $this->mongo->findOne('users', ['_id' => new ObjectId($session['user_id'])]);

            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not found'
                ], 404);
            }

            // Check if the user's role is assigned_lecturer
            if ($user['role'] !== 'assigned_lecturer') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Access denied. Only assigned lecturers can access this resource.'
                ], 403);
            }

            // Get lecturer's courses array from user document
            $lecturerCourses = $user['courses'] ?? [];

            if (empty($lecturerCourses)) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'No courses assigned yet',
                    'lecturer' => [
                        'id' => (string)$user['_id'],
                        'name' => $user['name'] ?? 'Unknown Lecturer',
                        'department' => $user['department'] ?? 'No Department'
                    ],
                    'courses' => [],
                    'total_courses' => 0
                ], 200);
            }

            // Extract course IDs from lecturer's courses array
            $courseIds = [];
            $courseIdMap = []; // Map to keep track of course ID to title mapping from user's courses array

            foreach ($lecturerCourses as $course) {
                if (isset($course['id'])) {
                    try {
                        $objectId = new ObjectId($course['id']);
                        $courseIds[] = $objectId;
                        $courseIdMap[$course['id']] = $course['title'] ?? 'Unknown Course';
                    } catch (\Exception $e) {
                        // Skip invalid course IDs
                        continue;
                    }
                }
            }

            if (empty($courseIds)) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'No valid courses found',
                    'lecturer' => [
                        'id' => (string)$user['_id'],
                        'name' => $user['name'] ?? 'Unknown Lecturer',
                        'department' => $user['department'] ?? 'No Department'
                    ],
                    'courses' => [],
                    'total_courses' => 0
                ], 200);
            }

            // Fetch full course details from courses collection
            $fullCourses = $this->mongo->find('courses', [
                '_id' => ['$in' => $courseIds]
            ], [
                'sort' => ['department' => 1, 'title' => 1] // Sort by department, then title
            ]);

            $courseDetails = [];
            foreach ($fullCourses as $course) {
                $courseId = (string)$course['_id'];
                $courseDetails[] = [
                    'id' => $courseId,
                    'title' => $course['title'] ?? ($courseIdMap[$courseId] ?? 'Untitled Course'),
                    'description' => $course['description'] ?? 'No description available for this course.',
                    'department' => $course['department'] ?? 'General',
                    'status' => $course['status'] ?? 'inactive',
                    'created_at' => $course['created_at'] ?? null,
                    'updated_at' => $course['updated_at'] ?? null,
                    // Additional course metadata
                    'enrolled_students' => 0, // Placeholder - you can implement student counting later
                    'assignments' => 0, // Placeholder - you can implement assignment counting later
                    'completion_rate' => '0%' // Placeholder - you can implement completion rate calculation later
                ];
            }

            // Return successful response with lecturer info and course details
            return response()->json([
                'status' => 'success',
                'message' => count($courseDetails) > 0 ? 'Courses retrieved successfully' : 'Courses assigned but no details found',
                'lecturer' => [
                    'id' => (string)$user['_id'],
                    'name' => $user['name'] ?? 'Unknown Lecturer',
                    'email' => $user['email'] ?? '',
                    'department' => $user['department'] ?? 'No Department',
                    'phone' => $user['phone'] ?? '',
                    'experience_years' => $user['experience_years'] ?? 0
                ],
                'courses' => $courseDetails,
                'total_courses' => count($courseDetails),
                'summary' => [
                    'active_courses' => count(array_filter($courseDetails, fn($c) => $c['status'] === 'active')),
                    'inactive_courses' => count(array_filter($courseDetails, fn($c) => $c['status'] === 'inactive')),
                    'departments' => array_unique(array_column($courseDetails, 'department'))
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Internal server error while fetching courses',
                'error' => config('app.debug') ? $e->getMessage() : 'Please try again later'
            ], 500);
        }
    }

    /**
     * Get lecturer profile information
     */
    public function getProfile(Request $request)
    {
        try {
            // Get token from Authorization header
            $token = $request->header('Authorization');

            if (!$token) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Token not provided'
                ], 401);
            }

            // Remove "Bearer " prefix if present
            $token = str_replace('Bearer ', '', $token);

            // Find active session to get user
            $session = $this->mongo->findSessionByToken($token);

            if (!$session) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid or expired session'
                ], 401);
            }

            // Fetch the user using the user_id from the session
            $user = $this->mongo->findOne('users', ['_id' => new ObjectId($session['user_id'])]);

            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not found'
                ], 404);
            }

            // Check if the user's role is assigned_lecturer
            if ($user['role'] !== 'assigned_lecturer') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Access denied. Only lecturers can access this resource.'
                ], 403);
            }

            return response()->json([
                'status' => 'success',
                'lecturer' => [
                    'id' => (string)$user['_id'],
                    'name' => $user['name'] ?? '',
                    'email' => $user['email'] ?? '',
                    'department' => $user['department'] ?? '',
                    'phone' => $user['phone'] ?? '',
                    'address' => $user['address'] ?? '',
                    'experience_years' => $user['experience_years'] ?? 0,
                    'institute' => $user['institute'] ?? '',
                    'total_courses' => count($user['courses'] ?? [])
                ]
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
     * Get student count for each course taught by the lecturer
     * NEW FUNCTION: Returns student enrollment statistics
     */
    public function getStudentCountByCourse(Request $request)
    {
        try {
            // Authenticate lecturer
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
            if (!$user || $user['role'] !== 'assigned_lecturer') {
                return response()->json(['status' => 'error', 'message' => 'Access denied. Only assigned lecturers can access this resource.'], 403);
            }

            // Get lecturer's assigned courses
            $lecturerCourses = $user['courses'] ?? [];

            if (empty($lecturerCourses)) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'No courses assigned to lecturer',
                    'courses' => [],
                    'total_courses' => 0,
                    'total_students' => 0
                ], 200);
            }

            // Extract course IDs and prepare course map
            $lecturerCourseIds = [];
            $courseMap = [];
            foreach ($lecturerCourses as $course) {
                if (isset($course['id'])) {
                    $lecturerCourseIds[] = $course['id'];
                    $courseMap[$course['id']] = [
                        'id' => $course['id'],
                        'title' => $course['title'] ?? 'Unknown Course',
                        'department' => $course['department'] ?? 'No Department',
                        'student_count' => 0
                    ];
                }
            }

            // Query students with enrolled_courses containing lecturer's courses
            $students = $this->mongo->find('users', [
                'role' => 'assigned_student',
                'enrolled_courses' => [
                    '$elemMatch' => [
                        'id' => ['$in' => $lecturerCourseIds]
                    ]
                ]
            ]);

            // Count students per course
            $totalStudents = 0;
            $studentIdsByCourse = [];

            foreach ($students as $student) {
                $studentArray = is_array($student) ? $student : (array)$student;
                $enrolledCoursesRaw = $studentArray['enrolled_courses'] ?? [];
                $enrolledCourses = [];

                if (is_iterable($enrolledCoursesRaw)) {
                    foreach ($enrolledCoursesRaw as $courseDoc) {
                        $courseArray = is_array($courseDoc) ? $courseDoc : (array)$courseDoc;
                        if (isset($courseArray['id'])) {
                            $enrolledCourses[] = $courseArray;
                        }
                    }
                }

                // Track unique student per course
                $studentId = (string)($studentArray['_id'] ?? '');

                foreach ($enrolledCourses as $enrolledCourse) {
                    $courseId = $enrolledCourse['id'];

                    if (in_array($courseId, $lecturerCourseIds)) {
                        if (!isset($studentIdsByCourse[$courseId])) {
                            $studentIdsByCourse[$courseId] = [];
                        }
                        $studentIdsByCourse[$courseId][$studentId] = true;
                    }
                }
            }

            // Calculate counts
            foreach ($courseMap as $courseId => $courseData) {
                $count = isset($studentIdsByCourse[$courseId]) ? count($studentIdsByCourse[$courseId]) : 0;
                $courseMap[$courseId]['student_count'] = $count;
                $totalStudents += $count;
            }

            // Prepare response
            $coursesWithCount = array_values($courseMap);

            // Sort by student count (descending)
            usort($coursesWithCount, function($a, $b) {
                return $b['student_count'] - $a['student_count'];
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Student count retrieved successfully',
                'lecturer' => [
                    'id' => (string)$user['_id'],
                    'name' => $user['name'] ?? 'Unknown Lecturer',
                    'email' => $user['email'] ?? '',
                    'department' => $user['department'] ?? 'No Department'
                ],
                'courses' => $coursesWithCount,
                'total_courses' => count($coursesWithCount),
                'total_students' => $totalStudents,
                'average_students_per_course' => count($coursesWithCount) > 0 ? round($totalStudents / count($coursesWithCount), 2) : 0
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Lecturer getStudentCountByCourse error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Internal server error while fetching student counts',
                'error' => config('app.debug') ? $e->getMessage() : 'Please try again later'
            ], 500);
        }
    }
}
