<?php

namespace App\Http\Controllers\UserControllers\LecturerControllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\MongoService;
use App\Services\EncryptionService;
use MongoDB\BSON\ObjectId;

class AssignmentController extends Controller
{
    private $mongo;

    public function __construct()
    {
        $this->mongo = new MongoService();
    }


    public function getAssignments(Request $request)
    {
        try {
            // Get token from Authorization header
            $token = $request->header('Authorization');
            if (!$token) {
                return response()->json(['status' => 'error', 'message' => 'Authentication token not provided'], 401);
            }
            $token = str_replace('Bearer ', '', $token);

            // Find active session and verify lecturer
            $session = $this->mongo->findSessionByToken($token);
            if (!$session) {
                return response()->json(['status' => 'error', 'message' => 'Invalid or expired session'], 401);
            }

            $user = $this->mongo->findOne('users', ['_id' => new ObjectId($session['user_id'])]);
            if (!$user || $user['role'] !== 'assigned_lecturer') {
                return response()->json(['status' => 'error', 'message' => 'Access denied'], 403);
            }

            // Get lecturer's courses using the same logic as getMyCourses
            $lecturerCourses = [];
            $courseDetails = [];
            if (isset($user['courses']) && is_iterable($user['courses'])) {
                // Extract course IDs from lecturer's courses array
                $courseIds = [];
                $courseIdMap = [];

                foreach ($user['courses'] as $course) {
                    $courseArray = is_array($course) ? $course : (array)$course;
                    if (isset($courseArray['id'])) {
                        $lecturerCourses[] = [
                            'id' => $courseArray['id'],
                            'title' => $courseArray['title'] ?? 'Unknown Course'
                        ];
                        try {
                            $objectId = new ObjectId($courseArray['id']);
                            $courseIds[] = $objectId;
                            $courseIdMap[$courseArray['id']] = $courseArray['title'] ?? 'Unknown Course';
                        } catch (\Exception $e) {
                            continue;
                        }
                    }
                }

                // Fetch full course details from courses collection if we have valid IDs
                if (!empty($courseIds)) {
                    $fullCourses = $this->mongo->find('courses', [
                        '_id' => ['$in' => $courseIds]
                    ], [
                        'sort' => ['department' => 1, 'title' => 1]
                    ]);

                    foreach ($fullCourses as $course) {
                        $courseId = (string)$course['_id'];
                        $courseDetails[] = [
                            'id' => $courseId,
                            'title' => $course['title'] ?? ($courseIdMap[$courseId] ?? 'Untitled Course'),
                            'description' => $course['description'] ?? 'No description available',
                            'department' => $course['department'] ?? 'General',
                            'status' => $course['status'] ?? 'inactive'
                        ];
                    }
                }
            }

            $courseIds = array_column($lecturerCourses, 'id');

            // If no courses assigned, return empty result
            if (empty($courseIds)) {
                return response()->json([
                    'status' => 'success',
                    'assignments' => [],
                    'lecturer_courses' => $lecturerCourses,
                    'course_details' => $courseDetails
                ], 200);
            }

            // Fetch assignments
            $assignments = $this->mongo->find('quizes', [
                'created_by' => $session['user_id'],
                'course_id' => ['$in' => $courseIds],
                'status' => ['$ne' => 'deleted'] // Exclude deleted assignments
            ], ['sort' => ['created_at' => -1]]);

            $assignmentList = [];
            foreach ($assignments as $assignment) {
                // Convert MongoDB document to PHP array properly
                $assignmentArray = [];

                // Handle different MongoDB document types
                if ($assignment instanceof \MongoDB\Model\BSONDocument) {
                    foreach ($assignment as $key => $value) {
                        $assignmentArray[$key] = $value;
                    }
                } elseif (is_array($assignment)) {
                    $assignmentArray = $assignment;
                } else {
                    $assignmentArray = (array)$assignment;
                }

                // Count questions from encrypted data
                $questionsCount = 0;
                if (isset($assignmentArray['questions_encrypted'])) {
                    if (is_array($assignmentArray['questions_encrypted'])) {
                        $questionsCount = count($assignmentArray['questions_encrypted']);
                    } elseif (is_countable($assignmentArray['questions_encrypted'])) {
                        $questionsCount = count($assignmentArray['questions_encrypted']);
                    }
                } elseif (isset($assignmentArray['total_questions'])) {
                    $questionsCount = (int)$assignmentArray['total_questions'];
                }

                // Get course name from course details
                $courseName = $this->getCourseNameFromDetails($assignmentArray['course_id'] ?? '', $courseDetails);

                $assignmentList[] = [
                    'id' => (string)($assignmentArray['_id'] ?? ''),
                    'encrypted_id' => EncryptionService::encryptId((string)($assignmentArray['_id'] ?? '')), // Add encrypted ID
                    'title' => $assignmentArray['title'] ?? 'Untitled Assignment',
                    'description' => $assignmentArray['description'] ?? '',
                    'course_id' => $assignmentArray['course_id'] ?? '',
                    'course_name' => $courseName,
                    'total_questions' => $questionsCount,
                    'status' => $assignmentArray['status'] ?? 'draft',
                    'created_at' => $assignmentArray['created_at'] ?? '',
                    'due_date' => $assignmentArray['due_date'] ?? null,
                    'is_encrypted' => isset($assignmentArray['questions_encrypted'])
                ];
            }

            return response()->json([
                'status' => 'success',
                'assignments' => $assignmentList,
                'lecturer_courses' => $lecturerCourses,
                'course_details' => $courseDetails
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Internal server error', 'error' => $e->getMessage()], 500);
        }
    }


    public function createAssignment(Request $request)
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
                return response()->json(['status' => 'error', 'message' => 'Invalid session'], 401);
            }

            $user = $this->mongo->findOne('users', ['_id' => new ObjectId($session['user_id'])]);
            if (!$user || $user['role'] !== 'assigned_lecturer') {
                return response()->json(['status' => 'error', 'message' => 'Access denied'], 403);
            }

            // Validate input
            $validator = \Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'course_id' => 'required|string',
                'questions' => 'required|array|min:1',
                'questions.*.question' => 'required|string',
                'questions.*.options' => 'required|array|min:2',
                'questions.*.correct_answer' => 'required|integer',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'start_time' => 'required|string',
                'end_time' => 'required|string',
                'status' => 'nullable|string|in:draft,published'
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => 'error', 'message' => 'Validation failed', 'errors' => $validator->errors()->all()], 422);
            }

            // Verify course belongs to lecturer
            $lecturerCourses = [];
            if (isset($user['courses']) && is_iterable($user['courses'])) {
                foreach ($user['courses'] as $course) {
                    $courseArray = is_array($course) ? $course : (array)$course;
                    $lecturerCourses[] = $courseArray['id'] ?? '';
                }
            }

            if (!in_array($request->course_id, $lecturerCourses)) {
                return response()->json(['status' => 'error', 'message' => 'You are not assigned to this course'], 403);
            }

            // Encrypt questions before storing
            try {
                $encryptedQuestions = EncryptionService::encryptQuizData($request->questions);
            } catch (\Exception $e) {
                return response()->json(['status' => 'error', 'message' => 'Failed to secure quiz data: ' . $e->getMessage()], 500);
            }

            // Create assignment with encrypted data
            $sriLankaTimezone = new \DateTimeZone('Asia/Colombo');
            $currentTime = new \DateTime('now', $sriLankaTimezone);

            $assignmentData = [
                'title' => $request->title,
                'description' => $request->description ?? '',
                'course_id' => $request->course_id,
                'created_by' => $session['user_id'],
                'lecturer_name' => $user['name'] ?? 'Unknown Lecturer',
                'questions_encrypted' => $encryptedQuestions,
                'total_questions' => count($encryptedQuestions),
                'status' => $request->status ?? 'draft',
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'created_at' => $currentTime->format('Y-m-d H:i:s'),
                'updated_at' => $currentTime->format('Y-m-d H:i:s'),
                'security_hash' => hash('sha256', json_encode($encryptedQuestions))
            ];

            $result = $this->mongo->insertOne('quizes', $assignmentData);

            if ($result->getInsertedId()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Assignment created and secured successfully',
                    'assignment_id' => (string)$result->getInsertedId()
                ], 201);
            }

            return response()->json(['status' => 'error', 'message' => 'Failed to create assignment'], 500);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Internal server error', 'error' => $e->getMessage()], 500);
        }
    }


    public function getAssignment(Request $request, $encryptedId)
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
                return response()->json(['status' => 'error', 'message' => 'Invalid session'], 401);
            }

            $user = $this->mongo->findOne('users', ['_id' => new ObjectId($session['user_id'])]);
            if (!$user || $user['role'] !== 'assigned_lecturer') {
                return response()->json(['status' => 'error', 'message' => 'Access denied'], 403);
            }

            // Decrypt the assignment ID first
            try {
                $assignmentId = EncryptionService::decryptId($encryptedId);
            } catch (\Exception $e) {
                return response()->json(['status' => 'error', 'message' => 'Invalid assignment ID'], 422);
            }

            // Validate assignment ID
            try {
                $objectId = new ObjectId($assignmentId);
            } catch (\Exception $e) {
                return response()->json(['status' => 'error', 'message' => 'Invalid assignment ID format'], 422);
            }

            // Get assignment
            $assignment = $this->mongo->findOne('quizes', [
                '_id' => $objectId,
                'created_by' => $session['user_id']
            ]);

            if (!$assignment) {
                return response()->json(['status' => 'error', 'message' => 'Assignment not found or access denied'], 404);
            }

            // Decrypt questions
            $questions = [];
            if (isset($assignment['questions_encrypted'])) {
                try {
                    // Convert MongoDB BSONArray to PHP array first
                    $questionsEncrypted = $assignment['questions_encrypted'];
                    if ($questionsEncrypted instanceof \MongoDB\Model\BSONArray) {
                        $questionsEncrypted = iterator_to_array($questionsEncrypted, false);
                    } elseif (!is_array($questionsEncrypted)) {
                        $questionsEncrypted = (array)$questionsEncrypted;
                    }

                    // Use decryptQuizForLecturer instead of decryptQuizData
                    $questions = EncryptionService::decryptQuizForLecturer($questionsEncrypted);
                    // Ensure $questions is always an array of question objects with options
                    if (!is_array($questions)) {
                        $questions = [];
                    }
                    // Fix: Ensure each question has 'question', 'options', 'correct_answer'
                    foreach ($questions as &$q) {
                        if (!isset($q['question'])) $q['question'] = '';
                        if (!isset($q['options']) || !is_array($q['options'])) $q['options'] = [];
                        if (!isset($q['correct_answer'])) $q['correct_answer'] = 0;
                    }
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
                    'start_date' => $assignment['start_date'] ?? null,
                    'end_date' => $assignment['end_date'] ?? null,
                    'start_time' => $assignment['start_time'] ?? null,
                    'end_time' => $assignment['end_time'] ?? null,
                    'status' => $assignment['status'] ?? 'draft',
                    'questions' => $questions
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


    public function editAssignment(Request $request, $encryptedId)
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
                return response()->json(['status' => 'error', 'message' => 'Invalid session'], 401);
            }

            $user = $this->mongo->findOne('users', ['_id' => new ObjectId($session['user_id'])]);
            if (!$user || $user['role'] !== 'assigned_lecturer') {
                return response()->json(['status' => 'error', 'message' => 'Access denied'], 403);
            }

            // Decrypt the assignment ID first
            try {
                $assignmentId = EncryptionService::decryptId($encryptedId);
            } catch (\Exception $e) {
                return response()->json(['status' => 'error', 'message' => 'Invalid assignment ID'], 422);
            }

            // Validate assignment ID
            try {
                $objectId = new ObjectId($assignmentId);
            } catch (\Exception $e) {
                return response()->json(['status' => 'error', 'message' => 'Invalid assignment ID format'], 422);
            }

            // Check if assignment exists and belongs to the lecturer
            $assignment = $this->mongo->findOne('quizes', [
                '_id' => $objectId,
                'created_by' => $session['user_id']
            ]);

            if (!$assignment) {
                return response()->json(['status' => 'error', 'message' => 'Assignment not found or access denied'], 404);
            }

            // Optionally: Decrypt existing questions for validation or merging
            $existingQuestions = [];
            if (isset($assignment['questions_encrypted'])) {
                try {
                    $existingQuestions = EncryptionService::decodeQuizData($assignment['questions_encrypted']);
                } catch (\Exception $e) {
                    $existingQuestions = [];
                }
            }

            // Verify course belongs to lecturer
            $lecturerCourses = [];
            if (isset($user['courses']) && is_iterable($user['courses'])) {
                foreach ($user['courses'] as $course) {
                    $courseArray = is_array($course) ? $course : (array)$course;
                    $lecturerCourses[] = $courseArray['id'] ?? '';
                }
            }

            if (!in_array($request->course_id, $lecturerCourses)) {
                return response()->json(['status' => 'error', 'message' => 'You are not assigned to this course'], 403);
            }

            // Encrypt new questions before storing
            try {
                $encryptedQuestions = EncryptionService::encryptQuizData($request->questions);
            } catch (\Exception $e) {
                return response()->json(['status' => 'error', 'message' => 'Failed to secure quiz data: ' . $e->getMessage()], 500);
            }

            // Update assignment
            $sriLankaTimezone = new \DateTimeZone('Asia/Colombo');
            $currentTime = new \DateTime('now', $sriLankaTimezone);

            $updateResult = $this->mongo->updateOne(
                'quizes',
                ['_id' => $objectId],
                [
                    '$set' => [
                        'title' => $request->title,
                        'description' => $request->description ?? '',
                        'course_id' => $request->course_id,
                        'questions_encrypted' => $encryptedQuestions,
                        'total_questions' => count($encryptedQuestions),
                        'status' => $request->status ?? 'draft',
                        'start_date' => $request->start_date,
                        'end_date' => $request->end_date,
                        'start_time' => $request->start_time,
                        'end_time' => $request->end_time,
                        'updated_at' => $currentTime->format('Y-m-d H:i:s'),
                        'security_hash' => hash('sha256', json_encode($encryptedQuestions))
                    ]
                ]
            );

            if ($updateResult->getModifiedCount() > 0) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Assignment updated successfully'
                ], 200);
            }

            return response()->json(['status' => 'error', 'message' => 'Failed to update assignment'], 500);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Internal server error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function assignmentPublication(Request $request, $encryptedId)
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
                return response()->json(['status' => 'error', 'message' => 'Invalid session'], 401);
            }

            $user = $this->mongo->findOne('users', ['_id' => new ObjectId($session['user_id'])]);
            if (!$user || $user['role'] !== 'assigned_lecturer') {
                return response()->json(['status' => 'error', 'message' => 'Access denied'], 403);
            }

            // Validate input
            $validator = \Validator::make($request->all(), [
                'status' => 'required|string|in:draft,published'
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()->all()
                ], 422);
            }

            // Decrypt the assignment ID first
            try {
                $assignmentId = EncryptionService::decryptId($encryptedId);
            } catch (\Exception $e) {
                return response()->json(['status' => 'error', 'message' => 'Invalid assignment ID'], 422);
            }

            // Validate assignment ID
            try {
                $objectId = new ObjectId($assignmentId);
            } catch (\Exception $e) {
                return response()->json(['status' => 'error', 'message' => 'Invalid assignment ID format'], 422);
            }

            // Check if assignment exists and belongs to the lecturer
            $assignment = $this->mongo->findOne('quizes', [
                '_id' => $objectId,
                'created_by' => $session['user_id']
            ]);
            if (!$assignment) {
                return response()->json(['status' => 'error', 'message' => 'Assignment not found or access denied'], 404);
            }

            // Update assignment status
            $sriLankaTimezone = new \DateTimeZone('Asia/Colombo');
            $currentTime = new \DateTime('now', $sriLankaTimezone);

            $updateResult = $this->mongo->updateOne(
                'quizes',
                ['_id' => $objectId],
                [
                    '$set' => [
                        'status' => $request->status,
                        'updated_at' => $currentTime->format('Y-m-d H:i:s')
                    ]
                ]
            );

            if ($updateResult->getModifiedCount() > 0) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Assignment status updated successfully',
                    'new_status' => $request->status
                ], 200);
            }

            return response()->json(['status' => 'error', 'message' => 'Failed to update assignment status'], 500);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Internal server error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Soft delete assignment by changing status to 'deleted'
     */
    public function deleteAssignment(Request $request, $encryptedId)
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
                return response()->json(['status' => 'error', 'message' => 'Invalid session'], 401);
            }

            $user = $this->mongo->findOne('users', ['_id' => new ObjectId($session['user_id'])]);
            if (!$user || $user['role'] !== 'assigned_lecturer') {
                return response()->json(['status' => 'error', 'message' => 'Access denied'], 403);
            }

            // Decrypt the assignment ID
            try {
                $assignmentId = EncryptionService::decryptId($encryptedId);
            } catch (\Exception $e) {
                return response()->json(['status' => 'error', 'message' => 'Invalid assignment ID'], 422);
            }

            // Validate assignment ID
            try {
                $objectId = new ObjectId($assignmentId);
            } catch (\Exception $e) {
                return response()->json(['status' => 'error', 'message' => 'Invalid assignment ID format'], 422);
            }

            // Check if assignment exists and belongs to the lecturer
            $assignment = $this->mongo->findOne('quizes', [
                '_id' => $objectId,
                'created_by' => $session['user_id']
            ]);

            if (!$assignment) {
                return response()->json(['status' => 'error', 'message' => 'Assignment not found or access denied'], 404);
            }

            // Soft delete: Update status to 'deleted'
            $sriLankaTimezone = new \DateTimeZone('Asia/Colombo');
            $currentTime = new \DateTime('now', $sriLankaTimezone);

            $updateResult = $this->mongo->updateOne(
                'quizes',
                ['_id' => $objectId],
                [
                    '$set' => [
                        'status' => 'deleted',
                        'deleted_at' => $currentTime->format('Y-m-d H:i:s'),
                        'updated_at' => $currentTime->format('Y-m-d H:i:s')
                    ]
                ]
            );

            if ($updateResult->getModifiedCount() > 0) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Assignment deleted successfully'
                ], 200);
            }

            return response()->json(['status' => 'error', 'message' => 'Failed to delete assignment'], 500);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Internal server error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Add this helper method at the end of the class (before the last closing brace)
    private function getCourseNameFromDetails($courseId, $courseDetails)
    {
        foreach ($courseDetails as $course) {
            if ($course['id'] === $courseId) {
                return $course['title'];
            }
        }
        return 'Unknown Course';
    }


}
