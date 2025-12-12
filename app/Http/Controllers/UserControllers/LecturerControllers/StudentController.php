<?php

namespace App\Http\Controllers\UserControllers\LecturerControllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\MongoService;
use MongoDB\BSON\ObjectId;

class StudentController extends Controller
{
    private $mongo;

    public function __construct()
    {
        $this->mongo = new MongoService();
    }

    /**
     * Get students assigned to courses that the lecturer teaches
     * REWRITTEN: Now correctly queries enrolled_courses array
     */
    public function getStudents(Request $request)
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
                    'students_by_course' => [],
                    'total_students' => 0,
                    'available_courses' => []
                ], 200);
            }

            // Extract course IDs
            $lecturerCourseIds = [];
            $courseMap = [];
            foreach ($lecturerCourses as $course) {
                if (isset($course['id'])) {
                    $lecturerCourseIds[] = $course['id'];
                    $courseMap[$course['id']] = $course['title'] ?? 'Unknown Course';
                }
            }

            // Get filters from request
            $courseFilter = $request->query('course_id');
            $studentNameFilter = $request->query('student_name');

            // FIX: Query students with enrolled_courses array containing lecturer's courses
            $studentFilter = [
                'role' => 'assigned_student',
                'enrolled_courses' => [
                    '$elemMatch' => [
                        'id' => ['$in' => $lecturerCourseIds]
                    ]
                ]
            ];

            // Apply course filter if provided
            if ($courseFilter && in_array($courseFilter, $lecturerCourseIds)) {
                $studentFilter['enrolled_courses']['$elemMatch']['id'] = $courseFilter;
            }

            // Apply student name filter if provided
            if ($studentNameFilter && !empty(trim($studentNameFilter))) {
                $studentFilter['name'] = [
                    '$regex' => preg_quote(trim($studentNameFilter), '/'),
                    '$options' => 'i'
                ];
            }

            // Fetch students
            $students = $this->mongo->find('users', $studentFilter, [
                'sort' => ['name' => 1]
            ]);

            // Process students and group by course
            $studentsByCourse = [];
            $processedStudents = [];

            foreach ($students as $student) {
                $studentArray = is_array($student) ? $student : (array)$student;

                // Get institute name
                $instituteName = 'Unknown Institute';
                if (isset($studentArray['institute_id'])) {
                    try {
                        $institute = $this->mongo->findOne('institutes', ['_id' => new ObjectId($studentArray['institute_id'])]);
                        $instituteName = $institute['institute'] ?? 'Unknown Institute';
                    } catch (\Exception $e) {
                        // Keep default
                    }
                }

                // Extract enrolled courses for this student
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

                // Add student to each of their enrolled courses that the lecturer teaches
                foreach ($enrolledCourses as $enrolledCourse) {
                    $courseId = $enrolledCourse['id'];

                    // Only include if lecturer teaches this course
                    if (!in_array($courseId, $lecturerCourseIds)) {
                        continue;
                    }

                    // Initialize course group if not exists
                    if (!isset($studentsByCourse[$courseId])) {
                        $studentsByCourse[$courseId] = [
                            'course_id' => $courseId,
                            'course_name' => $courseMap[$courseId] ?? 'Unknown Course',
                            'students' => [],
                            'student_count' => 0
                        ];
                    }

                    // Prepare student data
                    $studentData = [
                        'id' => (string)($studentArray['_id'] ?? ''),
                        'name' => $studentArray['name'] ?? 'Unknown Student',
                        'email' => $studentArray['email'] ?? '',
                        'phone' => $studentArray['phone'] ?? '',
                        'course_id' => $courseId,
                        'course_name' => $courseMap[$courseId] ?? 'Unknown Course',
                        'institute_id' => $studentArray['institute_id'] ?? null,
                        'institute_name' => $instituteName,
                        'gender' => $studentArray['gender'] ?? '',
                        'nic' => $studentArray['nic'] ?? '',
                        'address' => $studentArray['address'] ?? '',
                        'emergency_contact' => $studentArray['emergencyContact'] ?? [
                            'relation' => '',
                            'contactNo' => ''
                        ],
                        'assigned_at' => $studentArray['assigned_at'] ?? null,
                        'dob' => $studentArray['dob'] ?? null
                    ];

                    // Add to course group
                    $studentsByCourse[$courseId]['students'][] = $studentData;
                    $studentsByCourse[$courseId]['student_count']++;
                }
            }

            // Prepare course list for filtering
            $availableCourses = [];
            foreach ($lecturerCourses as $course) {
                $availableCourses[] = [
                    'id' => $course['id'],
                    'title' => $course['title'] ?? 'Unknown Course'
                ];
            }

            // Calculate total unique students
            $uniqueStudentIds = [];
            foreach ($studentsByCourse as $courseGroup) {
                foreach ($courseGroup['students'] as $student) {
                    $uniqueStudentIds[$student['id']] = true;
                }
            }
            $totalStudents = count($uniqueStudentIds);

            return response()->json([
                'status' => 'success',
                'message' => $totalStudents > 0 ? 'Students retrieved successfully' : 'No students found matching your criteria',
                'lecturer' => [
                    'id' => (string)$user['_id'],
                    'name' => $user['name'] ?? 'Unknown Lecturer',
                    'email' => $user['email'] ?? '',
                    'department' => $user['department'] ?? 'No Department'
                ],
                'students_by_course' => array_values($studentsByCourse),
                'total_students' => $totalStudents,
                'available_courses' => $availableCourses,
                'applied_filters' => [
                    'course_id' => $courseFilter,
                    'student_name' => $studentNameFilter
                ]
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Lecturer getStudents error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Internal server error while fetching students',
                'error' => config('app.debug') ? $e->getMessage() : 'Please try again later'
            ], 500);
        }
    }
}
