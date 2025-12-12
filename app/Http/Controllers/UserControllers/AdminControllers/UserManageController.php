<?php

namespace App\Http\Controllers\UserControllers\AdminControllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Services\MongoService;
use App\Models\User;
use App\Models\Course;
use MongoDB\BSON\ObjectId;

class UserManageController extends Controller
{
    private $mongo;

    public function __construct()
    {
        $this->mongo = new MongoService();
    }

    public function assignStudent(Request $request)
    {
        try {
            // Validate the request - now accepts array of request IDs
            $validator = Validator::make($request->all(), [
                'request_ids' => 'required|array|min:1',
                'request_ids.*' => 'required|string',
            ], [
                'request_ids.required' => 'At least one request ID is required',
                'request_ids.array' => 'Request IDs must be an array',
                'request_ids.min' => 'At least one request must be selected',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()->all(),
                ], 422);
            }

            // Fetch the admin ID using the getAdminId function
            $adminIdResponse = $this->getAdminId($request);
            if ($adminIdResponse->getStatusCode() !== 200) {
                return $adminIdResponse;
            }
            $adminId = $adminIdResponse->getData()->admin_id;

            $successCount = 0;
            $failedRequests = [];
            $sriLankaTimezone = new \DateTimeZone('Asia/Colombo');
            $currentTime = new \DateTime('now', $sriLankaTimezone);

            foreach ($request->request_ids as $requestId) {
                try {
                    // Fetch the registration request
                    $registrationRequest = $this->mongo->findOne('register_request', [
                        '_id' => new ObjectId($requestId),
                        'status' => 'pending',
                    ]);

                    if (!$registrationRequest) {
                        $failedRequests[] = "Request ID $requestId not found or already processed";
                        continue;
                    }

                    // Fetch the user associated with the registration request
                    $user = $this->mongo->findOne('users', [
                        'email' => $registrationRequest['user_email'],
                    ]);

                    if (!$user) {
                        $failedRequests[] = "User not found for request ID $requestId";
                        continue;
                    }

                    // Update the user with the registration request details and set the role to assigned_student
                    $updateResult = $this->mongo->updateOne(
                        'users',
                        ['_id' => new ObjectId($user['_id'])],
                        [
                            '$set' => [
                                'role' => 'assigned_student',
                                'dob' => $registrationRequest['request_data']['dob'] ?? null,
                                'nic' => $registrationRequest['request_data']['nic'] ?? null,
                                'gender' => $registrationRequest['request_data']['gender'] ?? null,
                                'phone' => $registrationRequest['request_data']['phoneNo'] ?? null,
                                'address' => $registrationRequest['request_data']['address'] ?? null,
                                'emergencyContact' => [
                                    'relation' => $registrationRequest['request_data']['emergency_relation'] ?? null,
                                    'contactNo' => $registrationRequest['request_data']['emergency_contact'] ?? null,
                                ],
                                'assigned_admin_id' => $adminId,
                                'assigned_at' => $currentTime->format('Y-m-d H:i:s'),
                                'updated_at' => $currentTime->format('Y-m-d H:i:s'),
                            ],
                        ]
                    );

                    if ($updateResult->getModifiedCount() === 0) {
                        $failedRequests[] = "Failed to update user for request ID $requestId";
                        continue;
                    }

                    // Update the registration request status to 'assigned'
                    $this->mongo->updateOne(
                        'register_request',
                        ['_id' => new ObjectId($requestId)],
                        [
                            '$set' => [
                                'status' => 'assigned',
                                'updated_at' => $currentTime->format('Y-m-d H:i:s'),
                            ],
                        ]
                    );

                    $successCount++;

                } catch (\Exception $e) {
                    $failedRequests[] = "Error processing request ID $requestId: " . $e->getMessage();
                }
            }

            $totalRequests = count($request->request_ids);
            $message = "Successfully assigned $successCount out of $totalRequests student requests.";

            if (!empty($failedRequests)) {
                $message .= " Failed: " . implode(', ', $failedRequests);
            }

            return response()->json([
                'status' => 'success',
                'message' => $message,
                'success_count' => $successCount,
                'failed_count' => count($failedRequests),
                'failed_requests' => $failedRequests
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Internal server error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function assignLecturer(Request $request)
    {
        try {
            // Validate the request - now accepts array of request IDs
            $validator = Validator::make($request->all(), [
                'request_ids' => 'required|array|min:1',
                'request_ids.*' => 'required|string',
            ], [
                'request_ids.required' => 'At least one request ID is required',
                'request_ids.array' => 'Request IDs must be an array',
                'request_ids.min' => 'At least one request must be selected',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()->all(),
                ], 422);
            }

            // Fetch the admin ID using the getAdminId function
            $adminIdResponse = $this->getAdminId($request);
            if ($adminIdResponse->getStatusCode() !== 200) {
                return $adminIdResponse;
            }
            $adminId = $adminIdResponse->getData()->admin_id;

            $successCount = 0;
            $failedRequests = [];
            $sriLankaTimezone = new \DateTimeZone('Asia/Colombo');
            $currentTime = new \DateTime('now', $sriLankaTimezone);

            foreach ($request->request_ids as $requestId) {
                try {
                    // Fetch the registration request
                    $registrationRequest = $this->mongo->findOne('register_request', [
                        '_id' => new ObjectId($requestId),
                        'status' => 'pending',
                    ]);

                    if (!$registrationRequest) {
                        $failedRequests[] = "Request ID $requestId not found or already processed";
                        continue;
                    }

                    // Fetch the user associated with the registration request
                    $user = $this->mongo->findOne('users', [
                        'email' => $registrationRequest['user_email'],
                    ]);

                    if (!$user) {
                        $failedRequests[] = "User not found for request ID $requestId";
                        continue;
                    }

                    // Update the user with the registration request details and set the role to assigned_lecturer
                    $updateResult = $this->mongo->updateOne(
                        'users',
                        ['_id' => new ObjectId($user['_id'])],
                        [
                            '$set' => [
                                'role' => 'assigned_lecturer',
                                'phone' => $registrationRequest['request_data']['phoneNo'] ?? null,
                                'nic' => $registrationRequest['request_data']['nic'] ?? null,
                                'address' => $registrationRequest['request_data']['address'] ?? null,
                                'assigned_admin_id' => $adminId,
                                'assigned_at' => $currentTime->format('Y-m-d H:i:s'),
                                'updated_at' => $currentTime->format('Y-m-d H:i:s'),
                            ],
                        ]
                    );

                    if ($updateResult->getModifiedCount() === 0) {
                        $failedRequests[] = "Failed to update user for request ID $requestId";
                        continue;
                    }

                    // Update the registration request status to 'assigned'
                    $this->mongo->updateOne(
                        'register_request',
                        ['_id' => new ObjectId($requestId)],
                        [
                            '$set' => [
                                'status' => 'assigned',
                                'updated_at' => $currentTime->format('Y-m-d H:i:s'),
                            ],
                        ]
                    );

                    $successCount++;

                } catch (\Exception $e) {
                    $failedRequests[] = "Error processing request ID $requestId: " . $e->getMessage();
                }
            }

            $totalRequests = count($request->request_ids);
            $message = "Successfully assigned $successCount out of $totalRequests lecturer requests.";

            if (!empty($failedRequests)) {
                $message .= " Failed: " . implode(', ', $failedRequests);
            }

            return response()->json([
                'status' => 'success',
                'message' => $message,
                'success_count' => $successCount,
                'failed_count' => count($failedRequests),
                'failed_requests' => $failedRequests
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Internal server error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Add new function for declining requests
    public function declineRequests(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'request_ids' => 'required|array|min:1',
                'request_ids.*' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()->all(),
                ], 422);
            }

            $successCount = 0;
            $failedRequests = [];
            $sriLankaTimezone = new \DateTimeZone('Asia/Colombo');
            $currentTime = new \DateTime('now', $sriLankaTimezone);

            foreach ($request->request_ids as $requestId) {
                try {
                    $result = $this->mongo->updateOne(
                        'register_request',
                        [
                            '_id' => new ObjectId($requestId),
                            'status' => 'pending'
                        ],
                        [
                            '$set' => [
                                'status' => 'declined',
                                'updated_at' => $currentTime->format('Y-m-d H:i:s'),
                            ]
                        ]
                    );

                    if ($result->getModifiedCount() > 0) {
                        $successCount++;
                    } else {
                        $failedRequests[] = "Request ID $requestId not found or already processed";
                    }
                } catch (\Exception $e) {
                    $failedRequests[] = "Error declining request ID $requestId: " . $e->getMessage();
                }
            }

            $totalRequests = count($request->request_ids);
            $message = "Successfully declined $successCount out of $totalRequests requests.";

            if (!empty($failedRequests)) {
                $message .= " Failed: " . implode(', ', $failedRequests);
            }

            return response()->json([
                'status' => 'success',
                'message' => $message,
                'success_count' => $successCount,
                'failed_count' => count($failedRequests)
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Internal server error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Decline enrollment requests (bulk operation)
     */
    public function declineEnrollmentRequests(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'enrollment_ids' => 'required|array|min:1',
                'enrollment_ids.*' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()->all(),
                ], 422);
            }

            $adminIdResponse = $this->getAdminId($request);
            if ($adminIdResponse->getStatusCode() !== 200) {
                return $adminIdResponse;
            }

            $successCount = 0;
            $failedRequests = [];
            $sriLankaTimezone = new \DateTimeZone('Asia/Colombo');
            $currentTime = new \DateTime('now', $sriLankaTimezone);

            foreach ($request->enrollment_ids as $enrollmentId) {
                try {
                    $result = $this->mongo->updateOne(
                        'enrollments',
                        [
                            '_id' => new ObjectId($enrollmentId),
                            'status' => 'pending'
                        ],
                        [
                            '$set' => [
                                'status' => 'declined',
                                'processed_at' => $currentTime->format('Y-m-d H:i:s'),
                                'updated_at' => $currentTime->format('Y-m-d H:i:s'),
                            ]
                        ]
                    );

                    if ($result->getModifiedCount() > 0) {
                        $successCount++;
                    } else {
                        $failedRequests[] = "Enrollment ID $enrollmentId not found or already processed";
                    }
                } catch (\Exception $e) {
                    $failedRequests[] = "Error declining enrollment ID $enrollmentId: " . $e->getMessage();
                }
            }

            $totalRequests = count($request->enrollment_ids);
            $message = "Successfully declined $successCount out of $totalRequests enrollment requests.";

            if (!empty($failedRequests)) {
                $message .= " Failed: " . implode(', ', $failedRequests);
            }

            return response()->json([
                'status' => 'success',
                'message' => $message,
                'success_count' => $successCount,
                'failed_count' => count($failedRequests)
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Internal server error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getRegistrationRequests(Request $request)
    {
        try {
            // Default to fetching only pending requests
            $statusFilter = $request->query('status', 'pending'); // Default to 'pending'

            $filter = ['status' => $statusFilter];

            // Fetch registration requests from the database
            $requests = $this->mongo->find('register_request', $filter, [
                'sort' => ['submitted_at' => -1], // Sort by most recent
            ]);

            $requestsArray = [];
            foreach ($requests as $req) {
                $requestsArray[] = [
                    'id' => (string) $req['_id'],
                    'user_name' => $req['user_name'],
                    'user_email' => $req['user_email'],
                    'role' => $req['request_role'],
                    'status' => $req['status'] ?? 'pending', // Default to 'pending' if not set
                    'submitted_at' => $req['submitted_at'],
                    'request_data' => $req['request_data'],
                    // No department field for lecturer requests anymore
                ];
            }

            return response()->json([
                'status' => 'success',
                'requests' => $requestsArray,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch registration requests',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getAdminId(Request $request)
    {
        try {
            // 1. Get token from Authorization header
            $token = $request->header('Authorization');

            if (!$token) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Token not provided'
                ], 401);
            }

            // Remove "Bearer " prefix if present
            $token = str_replace('Bearer ', '', $token);

            // 2. Find active session to get user
            $session = $this->mongo->findSessionByToken($token);

            if (!$session) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid or expired session'
                ], 401);
            }

            // 3. Fetch the user using the user_id from the session
            $user = $this->mongo->findOne('users', ['_id' => new ObjectId($session['user_id'])]);

            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not found'
                ], 404);
            }

            // 4. Check if the user's role is admin
            if ($user['role'] !== 'admin') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid admin privileges'
                ], 403);
            }

            // 5. Return the admin ID
            return response()->json([
                'status' => 'success',
                'admin_id' => (string) $user['_id']
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Internal server error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function addCourse(Request $request)
    {
        try {
            // Check if the current user is an admin
            $adminIdResponse = $this->getAdminId($request);
            if ($adminIdResponse->getStatusCode() !== 200) {
                return $adminIdResponse; // Return the error response if the user is not an admin
            }

            // Validate the request
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'description' => 'required|string|max:1000',
                'department' => 'nullable|string|max:255', // New validation rule
                'status' => 'nullable|string|in:active,inactive', // New validation rule
            ], [
                'title.required' => 'Course title is required',
                'description.required' => 'Course description is required',
                'status.in' => 'Status must be either active or inactive',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()->all(),
                ], 422);
            }

            // Create the course object using the Course model
            $course = Course::fromRequest($validator->validated());

            // Insert the course into the MongoDB 'courses' collection
            $insertResult = $this->mongo->insertOne('courses', $course->toArray());

            if (!$insertResult->getInsertedId()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to add course',
                ], 500);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Course added successfully',
                'course_id' => (string) $insertResult->getInsertedId(),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Internal server error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getCourses(Request $request)
    {
        try {
            // Fetch all courses from the MongoDB 'courses' collection
            $courses = $this->mongo->find('courses', [], [
                'sort' => ['department' => 1, 'title' => 1] // Sort by department and title
            ]);

            $coursesArray = [];
            foreach ($courses as $course) {
                $coursesArray[] = [
                    'id' => (string) $course['_id'],
                    'title' => $course['title'],
                    'description' => $course['description'],
                    'department' => $course['department'] ?? 'General',
                    'status' => $course['status'] ?? 'inactive',
                ];
            }

            // Group courses by department
            $groupedCourses = [];
            foreach ($coursesArray as $course) {
                $department = $course['department'];
                if (!isset($groupedCourses[$department])) {
                    $groupedCourses[$department] = [];
                }
                $groupedCourses[$department][] = $course;
            }

            return response()->json([
                'status' => 'success',
                'courses' => $groupedCourses,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch courses',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Get all users with role 'assigned_lecturer' (id, name, department, courses)
     * Only accessible by admin.
     */
    public function getAssignedLecturers(Request $request)
    {
        // Verify admin privileges
        $adminIdResponse = $this->getAdminId($request);
        if ($adminIdResponse->getStatusCode() !== 200) {
            return $adminIdResponse;
        }

        try {
            $courseTitle = $request->query('course_title');
            $courseId = $request->query('course_id');

            $filter = ['role' => 'assigned_lecturer'];

            // Fetch all lecturers
            $lecturers = $this->mongo->find('users', $filter, [
                'projection' => ['name' => 1, 'department' => 1, 'course' => 1, 'courses' => 1],
                'sort' => ['name' => 1]
            ]);

            $result = [];
            foreach ($lecturers as $lecturer) {
                // Filter by course title or course id if provided
                $courses = $lecturer['courses'] ?? [];
                $match = true;
                if ($courseTitle) {
                    $match = false;
                    foreach ($courses as $c) {
                        if (isset($c['title']) && stripos($c['title'], $courseTitle) !== false) {
                            $match = true;
                            break;
                        }
                    }
                }
                if ($courseId) {
                    $match = false;
                    foreach ($courses as $c) {
                        if (isset($c['id']) && $c['id'] == $courseId) {
                            $match = true;
                            break;
                        }
                    }
                }
                if ($match) {
                    $result[] = [
                        'id' => (string)($lecturer['_id'] ?? ''),
                        'name' => $lecturer['name'] ?? '',
                        'department' => $lecturer['department'] ?? '',
                        'course' => $lecturer['course'] ?? '',
                        'courses' => $courses,
                    ];
                }
            }
            return response()->json([
                'status' => 'success',
                'lecturers' => $result,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Internal server error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Change the courses assigned to a lecturer (role: assigned_lecturer)
     * Request: { "lecturer_id": "...", "courses": ["courseId1", "courseId2", ...], "action": "add|remove" }
     * Only admin can perform this action.
     */
    public function changeLecturerCourses(Request $request)
    {
        try {
            // Validate admin privileges
            $adminIdResponse = $this->getAdminId($request);
            if ($adminIdResponse->getStatusCode() !== 200) {
                return $adminIdResponse;
            }

            // Validate input - expecting a single course ID
            $validator = \Validator::make($request->all(), [
                'lecturer_id' => 'required|string',
                'course_id' => 'required|string',
                'action' => 'required|string|in:add,remove'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()->all(),
                ], 422);
            }

            $lecturerId = $request->lecturer_id;
            $courseId = $request->course_id;
            $action = $request->action;

            // Validate course ID and fetch course details
            try {
                $objectId = new \MongoDB\BSON\ObjectId($courseId);
            } catch (\Exception $e) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Invalid course ID: $courseId"
                ], 422);
            }

            // Find the course
            $course = $this->mongo->findOne('courses', ['_id' => $objectId]);

            if (!$course) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Course not found'
                ], 422);
            }

            $courseData = [
                'id' => (string)$course['_id'],
                'title' => $course['title'] ?? ''
            ];

            // Find the lecturer
            $lecturer = $this->mongo->findOne('users', [
                '_id' => new \MongoDB\BSON\ObjectId($lecturerId),
                'role' => 'assigned_lecturer'
            ]);

            if (!$lecturer) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Lecturer not found or not assigned_lecturer',
                ], 404);
            }

            $updateOperation = [];
            $message = '';

            switch ($action) {
                case 'add':
                    // Check if course already exists in the courses array
                    $existingCourses = $lecturer['courses'] ?? [];
                    $alreadyExists = false;

                    foreach ($existingCourses as $existingCourse) {
                        if (isset($existingCourse['id']) && $existingCourse['id'] === $courseId) {
                            $alreadyExists = true;
                            break;
                        }
                    }

                    if ($alreadyExists) {
                        return response()->json([
                            'status' => 'error',
                            'message' => 'Course is already assigned to this lecturer'
                        ], 422);
                    }

                    // Use MongoDB $push to add new course object to the courses array
                    $updateOperation = [
                        '$push' => [
                            'courses' => $courseData
                        ],
                        '$set' => [
                            'updated_at' => now('Asia/Colombo')->format('Y-m-d H:i:s')
                        ]
                    ];
                    $message = "Course '{$courseData['title']}' added to lecturer successfully";
                    break;

                case 'remove':
                    // Use MongoDB $pull to remove course object from the courses array
                    $updateOperation = [
                        '$pull' => [
                            'courses' => ['id' => $courseId]
                        ],
                        '$set' => [
                            'updated_at' => now('Asia/Colombo')->format('Y-m-d H:i:s')
                        ]
                    ];
                    $message = "Course '{$courseData['title']}' removed from lecturer successfully";
                    break;
            }

            // Update the lecturer's courses array
            $updateResult = $this->mongo->updateOne(
                'users',
                ['_id' => new \MongoDB\BSON\ObjectId($lecturerId)],
                $updateOperation
            );

            if ($updateResult->getModifiedCount() === 0) {
                if ($action === 'remove') {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Course was not assigned to this lecturer'
                    ], 422);
                } else {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Failed to update lecturer courses'
                    ], 500);
                }
            }

            // Fetch updated lecturer to return current courses
            $updatedLecturer = $this->mongo->findOne('users', [
                '_id' => new \MongoDB\BSON\ObjectId($lecturerId)
            ]);

            $finalCourses = $updatedLecturer['courses'] ?? [];

            return response()->json([
                'status' => 'success',
                'message' => $message,
                'action' => $action,
                'course' => $courseData,
                'courses' => $finalCourses,
                'total_courses' => count($finalCourses)
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Internal server error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get enrollment requests
     */
    public function getEnrollmentRequests(Request $request)
    {
        try {
            // Verify admin privileges
            $adminIdResponse = $this->getAdminId($request);
            if ($adminIdResponse->getStatusCode() !== 200) {
                return $adminIdResponse;
            }

            $statusFilter = $request->query('status', 'pending');
            $filter = ['status' => $statusFilter];

            $enrollments = $this->mongo->find('enrollments', $filter, [
                'sort' => ['requested_at' => -1]
            ]);

            $enrollmentRequests = [];
            foreach ($enrollments as $enrollment) {
                // Get user details
                $user = $this->mongo->findOne('users', ['_id' => new ObjectId($enrollment['user_id'])]);
                // Get course details
                $course = $this->mongo->findOne('courses', ['_id' => new ObjectId($enrollment['course_id'])]);

                $enrollmentRequests[] = [
                    'id' => (string)$enrollment['_id'],
                    'user_id' => $enrollment['user_id'],
                    'user_name' => $user['name'] ?? 'Unknown Student',
                    'user_email' => $user['email'] ?? '',
                    'course_id' => $enrollment['course_id'],
                    'course_title' => $course['title'] ?? 'Unknown Course',
                    'course_department' => $course['department'] ?? 'General',
                    'status' => $enrollment['status'],
                    'requested_at' => $enrollment['requested_at'],
                    'processed_at' => $enrollment['processed_at'] ?? null
                ];
            }

            return response()->json([
                'status' => 'success',
                'enrollments' => $enrollmentRequests
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
     * Enroll students (bulk operation)
     */
    public function enrollStudents(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'enrollment_ids' => 'required|array|min:1',
                'enrollment_ids.*' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()->all(),
                ], 422);
            }

            $adminIdResponse = $this->getAdminId($request);
            if ($adminIdResponse->getStatusCode() !== 200) {
                return $adminIdResponse;
            }

            $successCount = 0;
            $failedRequests = [];
            $sriLankaTimezone = new \DateTimeZone('Asia/Colombo');
            $currentTime = new \DateTime('now', $sriLankaTimezone);

            foreach ($request->enrollment_ids as $enrollmentId) {
                try {
                    // Get enrollment request
                    $enrollment = $this->mongo->findOne('enrollments', [
                        '_id' => new ObjectId($enrollmentId),
                        'status' => 'pending'
                    ]);

                    if (!$enrollment) {
                        $failedRequests[] = "Enrollment ID $enrollmentId not found or already processed";
                        continue;
                    }

                    // Get course details
                    $course = $this->mongo->findOne('courses', ['_id' => new ObjectId($enrollment['course_id'])]);
                    if (!$course) {
                        $failedRequests[] = "Course not found for enrollment ID $enrollmentId";
                        continue;
                    }

                    // Update enrollment status
                    $updateEnrollment = $this->mongo->updateOne(
                        'enrollments',
                        ['_id' => new ObjectId($enrollmentId)],
                        [
                            '$set' => [
                                'status' => 'enrolled',
                                'processed_at' => $currentTime->format('Y-m-d H:i:s'),
                                'updated_at' => $currentTime->format('Y-m-d H:i:s')
                            ]
                        ]
                    );

                    if ($updateEnrollment->getModifiedCount() === 0) {
                        $failedRequests[] = "Failed to update enrollment status for ID $enrollmentId";
                        continue;
                    }

                    // Add course to student's enrolled_courses array
                    $courseData = [
                        'id' => (string)$course['_id'],
                        'title' => $course['title'] ?? 'Unknown Course',
                        'enrolled_at' => $currentTime->format('Y-m-d H:i:s')
                    ];

                    $updateUser = $this->mongo->updateOne(
                        'users',
                        ['_id' => new ObjectId($enrollment['user_id'])],
                        [
                            '$push' => [
                                'enrolled_courses' => $courseData
                            ],
                            '$set' => [
                                'updated_at' => $currentTime->format('Y-m-d H:i:s')
                            ]
                        ]
                    );

                    if ($updateUser->getModifiedCount() > 0) {
                        $successCount++;
                    } else {
                        $failedRequests[] = "Failed to update user courses for enrollment ID $enrollmentId";
                    }

                } catch (\Exception $e) {
                    $failedRequests[] = "Error processing enrollment ID $enrollmentId: " . $e->getMessage();
                }
            }

            $totalRequests = count($request->enrollment_ids);
            $message = "Successfully enrolled $successCount out of $totalRequests students.";

            if (!empty($failedRequests)) {
                $message .= " Failed: " . implode(', ', $failedRequests);
            }

            return response()->json([
                'status' => 'success',
                'message' => $message,
                'success_count' => $successCount,
                'failed_count' => count($failedRequests)
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Internal server error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Unenroll students (bulk operation)
     */
    public function unenrollStudents(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'student_course_ids' => 'required|array|min:1',
                'student_course_ids.*' => 'required|array',
                'student_course_ids.*.student_id' => 'required|string',
                'student_course_ids.*.course_id' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()->all(),
                ], 422);
            }

            $adminIdResponse = $this->getAdminId($request);
            if ($adminIdResponse->getStatusCode() !== 200) {
                return $adminIdResponse;
            }

            $successCount = 0;
            $failedRequests = [];
            $sriLankaTimezone = new \DateTimeZone('Asia/Colombo');
            $currentTime = new \DateTime('now', $sriLankaTimezone);

            foreach ($request->student_course_ids as $item) {
                try {
                    $studentId = $item['student_id'];
                    $courseId = $item['course_id'];

                    // Remove course from student's enrolled_courses array
                    $updateUser = $this->mongo->updateOne(
                        'users',
                        ['_id' => new ObjectId($studentId)],
                        [
                            '$pull' => [
                                'enrolled_courses' => ['id' => $courseId]
                            ],
                            '$set' => [
                                'updated_at' => $currentTime->format('Y-m-d H:i:s')
                            ]
                        ]
                    );

                    if ($updateUser->getModifiedCount() > 0) {
                        // Update enrollment record if exists
                        $this->mongo->updateOne(
                            'enrollments',
                            [
                                'user_id' => $studentId,
                                'course_id' => $courseId,
                                'status' => 'enrolled'
                            ],
                            [
                                '$set' => [
                                    'status' => 'declined',
                                    'processed_at' => $currentTime->format('Y-m-d H:i:s'),
                                    'updated_at' => $currentTime->format('Y-m-d H:i:s')
                                ]
                            ]
                        );
                        $successCount++;
                    } else {
                        $failedRequests[] = "Student $studentId was not enrolled in course $courseId";
                    }

                } catch (\Exception $e) {
                    $failedRequests[] = "Error unenrolling student {$item['student_id']}: " . $e->getMessage();
                }
            }

            $totalRequests = count($request->student_course_ids);
            $message = "Successfully unenrolled $successCount out of $totalRequests students.";

            if (!empty($failedRequests)) {
                $message .= " Failed: " . implode(', ', $failedRequests);
            }

            return response()->json([
                'status' => 'success',
                'message' => $message,
                'success_count' => $successCount,
                'failed_count' => count($failedRequests)
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Internal server error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get enrolled students by course
     */
    public function getEnrolledStudents(Request $request)
    {
        try {
            $adminIdResponse = $this->getAdminId($request);
            if ($adminIdResponse->getStatusCode() !== 200) {
                return $adminIdResponse;
            }

            $courseFilter = $request->query('course_id');
            $studentNameFilter = $request->query('student_name');

            $filter = ['role' => 'assigned_student'];

            // Add filters
            if ($courseFilter) {
                $filter['enrolled_courses.id'] = $courseFilter;
            }
            if ($studentNameFilter && !empty(trim($studentNameFilter))) {
                $filter['name'] = [
                    '$regex' => preg_quote(trim($studentNameFilter), '/'),
                    '$options' => 'i'
                ];
            }

            $students = $this->mongo->find('users', $filter, [
                'sort' => ['name' => 1]
            ]);

            $enrolledStudents = [];
            foreach ($students as $student) {
                // Handle BSONArray to PHP array conversion properly
                $enrolledCoursesRaw = $student['enrolled_courses'] ?? [];
                $enrolledCourses = [];

                // Convert BSONArray/BSONDocument to regular PHP arrays
                if ($enrolledCoursesRaw instanceof \MongoDB\Model\BSONArray || is_iterable($enrolledCoursesRaw)) {
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
                }

                // If course filter is applied, only show that course
                if ($courseFilter) {
                    $enrolledCourses = array_filter($enrolledCourses, function($course) use ($courseFilter) {
                        return ($course['id'] ?? '') === $courseFilter;
                    });
                }

                if (!empty($enrolledCourses)) {
                    $enrolledStudents[] = [
                        'id' => (string)$student['_id'],
                        'name' => $student['name'] ?? 'Unknown Student',
                        'email' => $student['email'] ?? '',
                        'enrolled_courses' => array_values($enrolledCourses)
                    ];
                }
            }

            return response()->json([
                'status' => 'success',
                'students' => $enrolledStudents,
                'total_students' => count($enrolledStudents)
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
     * Get unenrollment requests
     */
    public function getUnenrollmentRequests(Request $request)
    {
        try {
            // Verify admin privileges
            $adminIdResponse = $this->getAdminId($request);
            if ($adminIdResponse->getStatusCode() !== 200) {
                return $adminIdResponse;
            }

            $statusFilter = $request->query('status', 'unenrollment_requested');
            $filter = ['status' => $statusFilter];

            $enrollments = $this->mongo->find('enrollments', $filter, [
                'sort' => ['unenrollment_requested_at' => -1]
            ]);

            $unenrollmentRequests = [];
            foreach ($enrollments as $enrollment) {
                // Get user details
                $user = $this->mongo->findOne('users', ['_id' => new ObjectId($enrollment['user_id'])]);
                // Get course details
                $course = $this->mongo->findOne('courses', ['_id' => new ObjectId($enrollment['course_id'])]);

                $unenrollmentRequests[] = [
                    'id' => (string)$enrollment['_id'],
                    'user_id' => $enrollment['user_id'],
                    'user_name' => $user['name'] ?? 'Unknown Student',
                    'user_email' => $user['email'] ?? '',
                    'course_id' => $enrollment['course_id'],
                    'course_title' => $course['title'] ?? 'Unknown Course',
                    'course_department' => $course['department'] ?? 'General',
                    'status' => $enrollment['status'],
                    'unenrollment_requested_at' => $enrollment['unenrollment_requested_at'] ?? null,
                    'enrolled_at' => $enrollment['processed_at'] ?? null
                ];
            }

            return response()->json([
                'status' => 'success',
                'unenrollment_requests' => $unenrollmentRequests
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
     * Process unenrollment requests (approve or decline)
     */
    public function processUnenrollmentRequests(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'enrollment_ids' => 'required|array|min:1',
                'enrollment_ids.*' => 'required|string',
                'action' => 'required|string|in:approve,decline'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()->all(),
                ], 422);
            }

            $adminIdResponse = $this->getAdminId($request);
            if ($adminIdResponse->getStatusCode() !== 200) {
                return $adminIdResponse;
            }

            $action = $request->action;
            $successCount = 0;
            $failedRequests = [];
            $sriLankaTimezone = new \DateTimeZone('Asia/Colombo');
            $currentTime = new \DateTime('now', $sriLankaTimezone);

            foreach ($request->enrollment_ids as $enrollmentId) {
                try {
                    // Get enrollment request
                    $enrollment = $this->mongo->findOne('enrollments', [
                        '_id' => new ObjectId($enrollmentId),
                        'status' => 'unenrollment_requested'
                    ]);

                    if (!$enrollment) {
                        $failedRequests[] = "Unenrollment request $enrollmentId not found or already processed";
                        continue;
                    }

                    if ($action === 'approve') {
                        // Update enrollment status to declined (unenrolled)
                        $updateEnrollment = $this->mongo->updateOne(
                            'enrollments',
                            ['_id' => new ObjectId($enrollmentId)],
                            [
                                '$set' => [
                                    'status' => 'declined',
                                    'unenrolled_at' => $currentTime->format('Y-m-d H:i:s'),
                                    'processed_at' => $currentTime->format('Y-m-d H:i:s'),
                                    'updated_at' => $currentTime->format('Y-m-d H:i:s')
                                ]
                            ]
                        );

                        if ($updateEnrollment->getModifiedCount() === 0) {
                            $failedRequests[] = "Failed to update enrollment status for ID $enrollmentId";
                            continue;
                        }

                        // Remove course from student's enrolled_courses array
                        $updateUser = $this->mongo->updateOne(
                            'users',
                            ['_id' => new ObjectId($enrollment['user_id'])],
                            [
                                '$pull' => [
                                    'enrolled_courses' => ['id' => $enrollment['course_id']]
                                ],
                                '$set' => [
                                    'updated_at' => $currentTime->format('Y-m-d H:i:s')
                                ]
                            ]
                        );

                        if ($updateUser->getModifiedCount() > 0) {
                            $successCount++;
                        } else {
                            $failedRequests[] = "Failed to remove course from student for enrollment ID $enrollmentId";
                        }

                    } else { // decline
                        // Revert status back to enrolled (decline unenrollment request)
                        $updateEnrollment = $this->mongo->updateOne(
                            'enrollments',
                            ['_id' => new ObjectId($enrollmentId)],
                            [
                                '$set' => [
                                    'status' => 'enrolled',
                                    'processed_at' => $currentTime->format('Y-m-d H:i:s'),
                                    'updated_at' => $currentTime->format('Y-m-d H:i:s')
                                ],
                                '$unset' => [
                                    'unenrollment_requested_at' => ''
                                ]
                            ]
                        );

                        if ($updateEnrollment->getModifiedCount() > 0) {
                            $successCount++;
                        } else {
                            $failedRequests[] = "Failed to decline unenrollment request for ID $enrollmentId";
                        }
                    }

                } catch (\Exception $e) {
                    $failedRequests[] = "Error processing unenrollment request ID $enrollmentId: " . $e->getMessage();
                }
            }

            $totalRequests = count($request->enrollment_ids);
            $actionText = $action === 'approve' ? 'approved (unenrolled)' : 'declined';
            $message = "Successfully $actionText $successCount out of $totalRequests unenrollment requests.";

            if (!empty($failedRequests)) {
                $message .= " Failed: " . implode(', ', $failedRequests);
            }

            return response()->json([
                'status' => 'success',
                'message' => $message,
                'success_count' => $successCount,
                'failed_count' => count($failedRequests)
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Internal server error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update course status (active/inactive)
     * NEW FUNCTION: Toggle course status with authentication
     */
    public function updateCourseStatus(Request $request)
    {
        try {
            // Step 1: Authenticate admin
            $token = $request->header('Authorization');
            if (!$token) {
                return response()->json(['status' => 'error', 'message' => 'Authentication token not provided'], 401);
            }
            $token = str_replace('Bearer ', '', $token);

            // Step 2: Verify session
            $session = $this->mongo->findSessionByToken($token);
            if (!$session) {
                return response()->json(['status' => 'error', 'message' => 'Invalid or expired session'], 401);
            }

            // Step 3: Verify admin role
            $user = $this->mongo->findOne('users', ['_id' => new ObjectId($session['user_id'])]);
            if (!$user || $user['role'] !== 'admin') {
                return response()->json(['status' => 'error', 'message' => 'Access denied. Admins only.'], 403);
            }

            // Step 4: Validate request
            $validator = \Validator::make($request->all(), [
                'course_id' => 'required|string',
                'status' => 'required|string|in:active,inactive'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()->all()
                ], 422);
            }

            $courseId = $request->input('course_id');
            $newStatus = $request->input('status');

            // Step 5: Validate course ID format
            try {
                $objectId = new ObjectId($courseId);
            } catch (\Exception $e) {
                return response()->json(['status' => 'error', 'message' => 'Invalid course ID format'], 422);
            }

            // Step 6: Check if course exists
            $course = $this->mongo->findOne('courses', ['_id' => $objectId]);
            if (!$course) {
                return response()->json(['status' => 'error', 'message' => 'Course not found'], 404);
            }

            // Step 7: Update course status
            $sriLankaTimezone = new \DateTimeZone('Asia/Colombo');
            $currentTime = new \DateTime('now', $sriLankaTimezone);

            $updateResult = $this->mongo->updateOne(
                'courses',
                ['_id' => $objectId],
                ['$set' => [
                    'status' => $newStatus,
                    'updated_at' => $currentTime->format('Y-m-d H:i:s'),
                    'updated_by' => $session['user_id']
                ]]
            );

            // Step 8: Log the action
            \Log::info('Course status updated', [
                'course_id' => $courseId,
                'course_title' => $course['title'] ?? 'Unknown',
                'old_status' => $course['status'] ?? 'unknown',
                'new_status' => $newStatus,
                'admin_id' => $session['user_id'],
                'admin_name' => $user['name'] ?? 'Unknown Admin'
            ]);

            if ($updateResult->getModifiedCount() > 0) {
                return response()->json([
                    'status' => 'success',
                    'message' => "Course status updated to '{$newStatus}' successfully",
                    'course' => [
                        'id' => $courseId,
                        'title' => $course['title'] ?? 'Unknown Course',
                        'department' => $course['department'] ?? 'Unknown',
                        'status' => $newStatus,
                        'updated_at' => $currentTime->format('Y-m-d H:i:s')
                    ]
                ], 200);
            }

            // No changes made (status was already the same)
            return response()->json([
                'status' => 'success',
                'message' => "Course status unchanged (already {$newStatus})",
                'course' => [
                    'id' => $courseId,
                    'title' => $course['title'] ?? 'Unknown Course',
                    'status' => $course['status'] ?? 'inactive'
                ]
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Update course status error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Internal server error',
                'error' => config('app.debug') ? $e->getMessage() : 'Failed to update course status'
            ], 500);
        }
    }

    /**
     * Get count of assigned lecturers
     * NEW FUNCTION: Returns the total count of users with role 'assigned_lecturer'
     */
    public function getAssignedLecturersCount(Request $request)
    {
        try {
            // Step 1: Authenticate admin
            $token = $request->header('Authorization');
            if (!$token) {
                return response()->json(['status' => 'error', 'message' => 'Authentication token not provided'], 401);
            }
            $token = str_replace('Bearer ', '', $token);

            // Step 2: Verify session
            $session = $this->mongo->findSessionByToken($token);
            if (!$session) {
                return response()->json(['status' => 'error', 'message' => 'Invalid or expired session'], 401);
            }

            // Step 3: Verify admin role
            $user = $this->mongo->findOne('users', ['_id' => new ObjectId($session['user_id'])]);
            if (!$user || $user['role'] !== 'admin') {
                return response()->json(['status' => 'error', 'message' => 'Access denied. Admins only.'], 403);
            }

            // Step 4: Count assigned lecturers
            $lecturers = $this->mongo->find('users', ['role' => 'assigned_lecturer']);

            $totalLecturers = 0;
            $lecturersWithCourses = 0;
            $lecturersWithoutCourses = 0;
            $totalCourses = 0;
            $departmentMap = [];

            foreach ($lecturers as $lecturer) {
                $lecturerArray = is_array($lecturer) ? $lecturer : (array)$lecturer;
                $totalLecturers++;

                $courses = $lecturerArray['courses'] ?? [];
                $courseCount = is_array($courses) ? count($courses) : 0;

                if ($courseCount > 0) {
                    $lecturersWithCourses++;
                    $totalCourses += $courseCount;
                } else {
                    $lecturersWithoutCourses++;
                }

                // Track lecturers by department
                $department = $lecturerArray['department'] ?? 'No Department';
                if (!isset($departmentMap[$department])) {
                    $departmentMap[$department] = 0;
                }
                $departmentMap[$department]++;
            }

            // Calculate average courses per lecturer
            $averageCoursesPerLecturer = $totalLecturers > 0 ? round($totalCourses / $totalLecturers, 2) : 0;

            // Prepare department breakdown
            $departmentBreakdown = [];
            foreach ($departmentMap as $dept => $count) {
                $departmentBreakdown[] = [
                    'department' => $dept,
                    'lecturer_count' => $count
                ];
            }

            // Sort by count (descending)
            usort($departmentBreakdown, function($a, $b) {
                return $b['lecturer_count'] - $a['lecturer_count'];
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Lecturer count retrieved successfully',
                'data' => [
                    'total_lecturers' => $totalLecturers,
                    'lecturers_with_courses' => $lecturersWithCourses,
                    'lecturers_without_courses' => $lecturersWithoutCourses,
                    'total_courses_assigned' => $totalCourses,
                    'average_courses_per_lecturer' => $averageCoursesPerLecturer,
                    'by_department' => $departmentBreakdown
                ]
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Get assigned lecturers count error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Internal server error',
                'error' => config('app.debug') ? $e->getMessage() : 'Failed to retrieve lecturer count'
            ], 500);
        }
    }

    /**
     * Get count of assigned students
     * NEW FUNCTION: Returns total count of users with role 'assigned_student'
     */
    public function getAssignedStudentsCount(Request $request)
    {
        try {
            // Step 1: Authenticate admin
            $token = $request->header('Authorization');
            if (!$token) {
                return response()->json(['status' => 'error', 'message' => 'Authentication token not provided'], 401);
            }
            $token = str_replace('Bearer ', '', $token);

            // Step 2: Verify session
            $session = $this->mongo->findSessionByToken($token);
            if (!$session) {
                return response()->json(['status' => 'error', 'message' => 'Invalid or expired session'], 401);
            }

            // Step 3: Verify admin role
            $user = $this->mongo->findOne('users', ['_id' => new ObjectId($session['user_id'])]);
            if (!$user || $user['role'] !== 'admin') {
                return response()->json(['status' => 'error', 'message' => 'Access denied. Admins only.'], 403);
            }

            // Step 4: Count assigned students
            $students = $this->mongo->find('users', ['role' => 'assigned_student']);

            $totalStudents = 0;
            $studentsWithCourses = 0;
            $studentsWithoutCourses = 0;
            $totalEnrolledCourses = 0;

            foreach ($students as $student) {
                $studentArray = is_array($student) ? $student : (array)$student;
                $totalStudents++;

                $enrolledCourses = $studentArray['enrolled_courses'] ?? [];
                $courseCount = is_array($enrolledCourses) ? count($enrolledCourses) : 0;

                if ($courseCount > 0) {
                    $studentsWithCourses++;
                    $totalEnrolledCourses += $courseCount;
                } else {
                    $studentsWithoutCourses++;
                }
            }

            // Calculate average courses per student
            $averageCoursesPerStudent = $totalStudents > 0 ? round($totalEnrolledCourses / $totalStudents, 2) : 0;

            return response()->json([
                'status' => 'success',
                'message' => 'Student count retrieved successfully',
                'data' => [
                    'total_students' => $totalStudents,
                    'students_with_courses' => $studentsWithCourses,
                    'students_without_courses' => $studentsWithoutCourses,
                    'total_enrolled_courses' => $totalEnrolledCourses,
                    'average_courses_per_student' => $averageCoursesPerStudent
                ]
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Get assigned students count error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Internal server error',
                'error' => config('app.debug') ? $e->getMessage() : 'Failed to retrieve student count'
            ], 500);
        }
    }

    /**
     * Get count of active courses
     * NEW FUNCTION: Returns total count of courses with status 'active'
     */
    public function getActiveCoursesCount(Request $request)
    {
        try {
            // Step 1: Authenticate admin
            $token = $request->header('Authorization');
            if (!$token) {
                return response()->json(['status' => 'error', 'message' => 'Authentication token not provided'], 401);
            }
            $token = str_replace('Bearer ', '', $token);

            // Step 2: Verify session
            $session = $this->mongo->findSessionByToken($token);
            if (!$session) {
                return response()->json(['status' => 'error', 'message' => 'Invalid or expired session'], 401);
            }

            // Step 3: Verify admin role
            $user = $this->mongo->findOne('users', ['_id' => new ObjectId($session['user_id'])]);
            if (!$user || $user['role'] !== 'admin') {
                return response()->json(['status' => 'error', 'message' => 'Access denied. Admins only.'], 403);
            }

            // Step 4: Count courses by status
            $allCourses = $this->mongo->find('courses', []);

            $totalCourses = 0;
            $activeCourses = 0;
            $inactiveCourses = 0;
            $departmentMap = [];

            foreach ($allCourses as $course) {
                $courseArray = is_array($course) ? $course : (array)$course;
                $totalCourses++;

                $status = $courseArray['status'] ?? 'inactive';
                if ($status === 'active') {
                    $activeCourses++;
                } else {
                    $inactiveCourses++;
                }

                // Track courses by department
                $department = $courseArray['department'] ?? 'General';
                if (!isset($departmentMap[$department])) {
                    $departmentMap[$department] = [
                        'total' => 0,
                        'active' => 0,
                        'inactive' => 0
                    ];
                }
                $departmentMap[$department]['total']++;
                if ($status === 'active') {
                    $departmentMap[$department]['active']++;
                } else {
                    $departmentMap[$department]['inactive']++;
                }
            }

            // Prepare department breakdown
            $departmentBreakdown = [];
            foreach ($departmentMap as $dept => $counts) {
                $departmentBreakdown[] = [
                    'department' => $dept,
                    'total_courses' => $counts['total'],
                    'active_courses' => $counts['active'],
                    'inactive_courses' => $counts['inactive']
                ];
            }

            // Sort by total courses (descending)
            usort($departmentBreakdown, function($a, $b) {
                return $b['total_courses'] - $a['total_courses'];
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Course count retrieved successfully',
                'data' => [
                    'total_courses' => $totalCourses,
                    'active_courses' => $activeCourses,
                    'inactive_courses' => $inactiveCourses,
                    'by_department' => $departmentBreakdown
                ]
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Get active courses count error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Internal server error',
                'error' => config('app.debug') ? $e->getMessage() : 'Failed to retrieve course count'
            ], 500);
        }
    }

    /**
     * Get count of pending registration requests
     * NEW FUNCTION: Returns count of pending requests (requested_student + requested_lecturer)
     */
    public function getPendingRequestsCount(Request $request)
    {
        try {
            // Step 1: Authenticate admin
            $token = $request->header('Authorization');
            if (!$token) {
                return response()->json(['status' => 'error', 'message' => 'Authentication token not provided'], 401);
            }
            $token = str_replace('Bearer ', '', $token);

            // Step 2: Verify session
            $session = $this->mongo->findSessionByToken($token);
            if (!$session) {
                return response()->json(['status' => 'error', 'message' => 'Invalid or expired session'], 401);
            }

            // Step 3: Verify admin role
            $user = $this->mongo->findOne('users', ['_id' => new ObjectId($session['user_id'])]);
            if (!$user || $user['role'] !== 'admin') {
                return response()->json(['status' => 'error', 'message' => 'Access denied. Admins only.'], 403);
            }

            // Step 4: Count pending registration requests
            $pendingRequests = $this->mongo->find('register_request', ['status' => 'pending']);

            $totalPending = 0;
            $pendingStudents = 0;
            $pendingLecturers = 0;

            foreach ($pendingRequests as $request) {
                $requestArray = is_array($request) ? $request : (array)$request;
                $totalPending++;

                $role = $requestArray['request_role'] ?? '';
                if ($role === 'student') {
                    $pendingStudents++;
                } elseif ($role === 'lecturer') {
                    $pendingLecturers++;
                }
            }

            // Step 5: Count pending enrollment requests
            $pendingEnrollments = $this->mongo->find('enrollments', ['status' => 'pending']);
            $enrollmentCount = 0;
            foreach ($pendingEnrollments as $enrollment) {
                $enrollmentCount++;
            }

            // Step 6: Count pending unenrollment requests
            $pendingUnenrollments = $this->mongo->find('enrollments', ['status' => 'unenrollment_requested']);
            $unenrollmentCount = 0;
            foreach ($pendingUnenrollments as $unenrollment) {
                $unenrollmentCount++;
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Pending requests count retrieved successfully',
                'data' => [
                    'total_pending_requests' => $totalPending,
                    'pending_student_requests' => $pendingStudents,
                    'pending_lecturer_requests' => $pendingLecturers,
                    'pending_enrollments' => $enrollmentCount,
                    'pending_unenrollments' => $unenrollmentCount,
                    'total_all_pending' => $totalPending + $enrollmentCount + $unenrollmentCount
                ]
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Get pending requests count error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Internal server error',
                'error' => config('app.debug') ? $e->getMessage() : 'Failed to retrieve pending requests count'
            ], 500);
        }
    }
}
