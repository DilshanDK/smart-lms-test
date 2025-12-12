<?php

namespace App\Http\Controllers\UserControllers\StudentControllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\MongoService;
use MongoDB\BSON\ObjectId;

class AssignmentController extends Controller
{
    private $mongo;

    public function __construct()
    {
        $this->mongo = new MongoService();
    }

    /**
     * Get all published quizzes/assignments for student's enrolled courses
     */
    public function getAssignments(Request $request)
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

            // Get student's enrolled courses
            $enrolledCoursesRaw = $user['enrolled_courses'] ?? [];
            $enrolledCourses = [];

            // Handle different MongoDB document types
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

            $enrolledCourseIds = array_column($enrolledCourses, 'id');

            if (empty($enrolledCourseIds)) {
                return response()->json([
                    'status' => 'success',
                    'assignments' => [],
                    'enrolled_courses' => [],
                    'message' => 'No enrolled courses found'
                ], 200);
            }

            // Fetch published assignments/quizzes for enrolled courses
            $assignments = $this->mongo->find('quizes', [
                'course_id' => ['$in' => $enrolledCourseIds],
                'status' => 'published'
            ], [
                'sort' => ['created_at' => -1]
            ]);

            $assignmentList = [];
            foreach ($assignments as $assignment) {
                $assignmentArray = [];

                if ($assignment instanceof \MongoDB\Model\BSONDocument) {
                    foreach ($assignment as $key => $value) {
                        $assignmentArray[$key] = $value;
                    }
                } elseif (is_array($assignment)) {
                    $assignmentArray = $assignment;
                } else {
                    $assignmentArray = (array)$assignment;
                }

                // Count questions
                $questionsCount = 0;
                if (isset($assignmentArray['questions_encrypted'])) {
                    if (is_array($assignmentArray['questions_encrypted']) || is_countable($assignmentArray['questions_encrypted'])) {
                        $questionsCount = count($assignmentArray['questions_encrypted']);
                    }
                } elseif (isset($assignmentArray['total_questions'])) {
                    $questionsCount = (int)$assignmentArray['total_questions'];
                }

                // Get course name
                $courseName = 'Unknown Course';
                foreach ($enrolledCourses as $course) {
                    if (isset($course['id']) && $course['id'] === ($assignmentArray['course_id'] ?? '')) {
                        $courseName = $course['title'] ?? 'Unknown Course';
                        break;
                    }
                }

                // Check if student has already submitted this assignment
                $hasSubmitted = false;
                $submissionScore = null;
                $existingSubmission = $this->mongo->findOne('assignment_marks', [
                    'assignment_id' => (string)($assignmentArray['_id'] ?? ''),
                    'student_id' => $session['user_id']
                ]);

                if ($existingSubmission) {
                    $hasSubmitted = true;
                    $submissionScore = $existingSubmission['score_percentage'] ?? 0;
                }

                $assignmentList[] = [
                    'id' => (string)($assignmentArray['_id'] ?? ''),
                    'title' => $assignmentArray['title'] ?? 'Untitled Assignment',
                    'description' => $assignmentArray['description'] ?? '',
                    'course_id' => $assignmentArray['course_id'] ?? '',
                    'course_name' => $courseName,
                    'lecturer_name' => $assignmentArray['lecturer_name'] ?? 'Unknown Lecturer',
                    'total_questions' => $questionsCount,
                    'due_date' => $assignmentArray['due_date'] ?? null,
                    'created_at' => $assignmentArray['created_at'] ?? '',
                    'status' => $assignmentArray['status'] ?? 'published',
                    'has_submitted' => $hasSubmitted,
                    'submission_score' => $submissionScore
                ];
            }

            return response()->json([
                'status' => 'success',
                'assignments' => $assignmentList,
                'enrolled_courses' => $enrolledCourses,
                'total_assignments' => count($assignmentList)
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
     * Get a specific assignment/quiz with questions (decrypted for student - NO correct answers)
     */
    public function getAssignment(Request $request, $assignmentId)
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

            // Validate ObjectId
            try {
                $objectId = new ObjectId($assignmentId);
            } catch (\Exception $e) {
                return response()->json(['status' => 'error', 'message' => 'Invalid assignment ID'], 422);
            }

            // Get assignment
            $assignment = $this->mongo->findOne('quizes', [
                '_id' => $objectId,
                'status' => 'published'
            ]);

            if (!$assignment) {
                return response()->json(['status' => 'error', 'message' => 'Assignment not found or not published'], 404);
            }

            // Verify student is enrolled in the course
            $enrolledCoursesRaw = $user['enrolled_courses'] ?? [];
            $enrolledCourseIds = [];

            if (is_iterable($enrolledCoursesRaw)) {
                foreach ($enrolledCoursesRaw as $course) {
                    $courseArray = is_array($course) ? $course : (array)$course;
                    if (isset($courseArray['id'])) {
                        $enrolledCourseIds[] = $courseArray['id'];
                    }
                }
            }

            if (!in_array($assignment['course_id'], $enrolledCourseIds)) {
                return response()->json(['status' => 'error', 'message' => 'You are not enrolled in this course'], 403);
            }

            // Validate time window
            $sriLankaTimezone = new \DateTimeZone('Asia/Colombo');
            $currentTime = new \DateTime('now', $sriLankaTimezone);
            $startDateTime = new \DateTime($assignment['start_date'] . ' ' . $assignment['start_time'], $sriLankaTimezone);
            $endDateTime = new \DateTime($assignment['end_date'] . ' ' . $assignment['end_time'], $sriLankaTimezone);

            if ($currentTime < $startDateTime) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Assignment not yet available',
                    'starts_at' => $startDateTime->format('Y-m-d H:i:s')
                ], 403);
            }

            if ($currentTime > $endDateTime) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Assignment has expired',
                    'ended_at' => $endDateTime->format('Y-m-d H:i:s')
                ], 403);
            }

            // Check if already submitted
            $existingSubmission = $this->mongo->findOne('assignment_marks', [
                'assignment_id' => (string)$assignment['_id'],
                'student_id' => $session['user_id']
            ]);

            if ($existingSubmission) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You have already submitted this assignment',
                    'score' => $existingSubmission['score_percentage'] ?? 0
                ], 403);
            }

            // Decrypt questions for student (WITHOUT correct answers)
            $questions = [];
            if (isset($assignment['questions_encrypted'])) {
                try {
                    $questionsEncrypted = $assignment['questions_encrypted'];
                    if ($questionsEncrypted instanceof \MongoDB\Model\BSONArray) {
                        $questionsEncrypted = iterator_to_array($questionsEncrypted, false);
                    }
                    // Use decryptQuizForStudent - no correct answers revealed
                    $questions = \App\Services\EncryptionService::decryptQuizForStudent($questionsEncrypted);
                } catch (\Exception $e) {
                    return response()->json(['status' => 'error', 'message' => 'Failed to load quiz questions'], 500);
                }
            }

            return response()->json([
                'status' => 'success',
                'assignment' => [
                    'id' => (string)$assignment['_id'],
                    'title' => $assignment['title'] ?? 'Untitled Assignment',
                    'description' => $assignment['description'] ?? '',
                    'course_id' => $assignment['course_id'] ?? '',
                    'start_date' => $assignment['start_date'],
                    'end_date' => $assignment['end_date'],
                    'start_time' => $assignment['start_time'],
                    'end_time' => $assignment['end_time'],
                    'total_questions' => count($questions),
                    'questions' => $questions,
                    'time_remaining' => $endDateTime->getTimestamp() - $currentTime->getTimestamp()
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
     * Submit assignment answers and calculate marks
     */
    public function submitAssignment(Request $request, $assignmentId)
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
                'answers' => 'required|array'
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => 'error', 'message' => 'Validation failed', 'errors' => $validator->errors()->all()], 422);
            }

            // Get assignment
            try {
                $objectId = new ObjectId($assignmentId);
            } catch (\Exception $e) {
                return response()->json(['status' => 'error', 'message' => 'Invalid assignment ID'], 422);
            }

            $assignment = $this->mongo->findOne('quizes', [
                '_id' => $objectId,
                'status' => 'published'
            ]);

            if (!$assignment) {
                return response()->json(['status' => 'error', 'message' => 'Assignment not found'], 404);
            }

            // Validate time window
            $sriLankaTimezone = new \DateTimeZone('Asia/Colombo');
            $currentTime = new \DateTime('now', $sriLankaTimezone);
            $endDateTime = new \DateTime($assignment['end_date'] . ' ' . $assignment['end_time'], $sriLankaTimezone);

            if ($currentTime > $endDateTime) {
                return response()->json(['status' => 'error', 'message' => 'Assignment submission time has expired'], 403);
            }

            // Check if already submitted
            $existingSubmission = $this->mongo->findOne('assignment_marks', [
                'assignment_id' => (string)$assignment['_id'],
                'student_id' => $session['user_id']
            ]);

            if ($existingSubmission) {
                return response()->json(['status' => 'error', 'message' => 'You have already submitted this assignment'], 403);
            }

            // Get encrypted questions
            $questionsEncrypted = $assignment['questions_encrypted'];
            if ($questionsEncrypted instanceof \MongoDB\Model\BSONArray) {
                $questionsEncrypted = iterator_to_array($questionsEncrypted, false);
            }

            // Validate answers using EncryptionService
            $results = \App\Services\EncryptionService::validateStudentAnswers($questionsEncrypted, $request->answers);

            // Store marks in database
            $marksData = [
                'assignment_id' => (string)$assignment['_id'],
                'assignment_title' => $assignment['title'] ?? 'Untitled Assignment',
                'student_id' => $session['user_id'],
                'student_name' => $user['name'] ?? 'Unknown Student',
                'student_email' => $user['email'] ?? '',
                'course_id' => $assignment['course_id'] ?? '',
                'lecturer_id' => $assignment['created_by'] ?? '',
                'total_questions' => $results['total_questions'],
                'correct_answers' => $results['correct_answers'],
                'score_percentage' => $results['score_percentage'],
                'submitted_at' => $currentTime->format('Y-m-d H:i:s'),
                'time_taken' => $request->input('time_taken', 0), // Optional: track time taken
                'results' => $results['results']
            ];

            $result = $this->mongo->insertOne('assignment_marks', $marksData);

            if ($result->getInsertedId()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Assignment submitted successfully',
                    'score' => [
                        'total_questions' => $results['total_questions'],
                        'correct_answers' => $results['correct_answers'],
                        'score_percentage' => $results['score_percentage']
                    ]
                ], 201);
            }

            return response()->json(['status' => 'error', 'message' => 'Failed to submit assignment'], 500);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Internal server error',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
