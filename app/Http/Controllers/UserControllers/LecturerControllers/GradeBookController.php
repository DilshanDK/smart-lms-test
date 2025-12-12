<?php

namespace App\Http\Controllers\UserControllers\LecturerControllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\MongoService;
use MongoDB\BSON\ObjectId;

class GradeBookController extends Controller
{
    private $mongo;

    public function __construct()
    {
        $this->mongo = new MongoService();
    }

    /**
     * Get all student submissions for lecturer's assignments
     */
    public function getGradebook(Request $request)
    {
        try {
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

            // Get lecturer's courses
            $lecturerCourses = [];
            if (isset($user['courses']) && is_iterable($user['courses'])) {
                foreach ($user['courses'] as $course) {
                    $courseArray = is_array($course) ? $course : (array)$course;
                    if (isset($courseArray['id'])) {
                        $lecturerCourses[] = [
                            'id' => $courseArray['id'],
                            'title' => $courseArray['title'] ?? 'Unknown Course'
                        ];
                    }
                }
            }

            $courseIds = array_column($lecturerCourses, 'id');

            // Get all submissions for lecturer's courses
            $submissions = $this->mongo->find('assignment_marks', [
                'lecturer_id' => $session['user_id']
            ], ['sort' => ['submitted_at' => -1]]);

            $submissionList = [];
            foreach ($submissions as $submission) {
                $submissionList[] = [
                    'student_name' => $submission['student_name'] ?? 'Unknown',
                    'student_email' => $submission['student_email'] ?? '',
                    'assignment_title' => $submission['assignment_title'] ?? '',
                    'course_id' => $submission['course_id'] ?? '',
                    'score_percentage' => $submission['score_percentage'] ?? 0,
                    'correct_answers' => $submission['correct_answers'] ?? 0,
                    'total_questions' => $submission['total_questions'] ?? 0,
                    'submitted_at' => $submission['submitted_at'] ?? ''
                ];
            }

            // Group by student
            $studentGrades = [];
            foreach ($submissionList as $submission) {
                $email = $submission['student_email'];
                if (!isset($studentGrades[$email])) {
                    $studentGrades[$email] = [
                        'student_name' => $submission['student_name'],
                        'student_email' => $email,
                        'submissions' => [],
                        'average_score' => 0,
                        'total_assignments' => 0
                    ];
                }
                $studentGrades[$email]['submissions'][] = $submission;
                $studentGrades[$email]['total_assignments']++;
            }

            // Calculate averages
            foreach ($studentGrades as &$student) {
                $totalScore = 0;
                foreach ($student['submissions'] as $sub) {
                    $totalScore += $sub['score_percentage'];
                }
                $student['average_score'] = $student['total_assignments'] > 0
                    ? round($totalScore / $student['total_assignments'], 2)
                    : 0;
            }

            return response()->json([
                'status' => 'success',
                'students' => array_values($studentGrades),
                'total_submissions' => count($submissionList),
                'courses' => $lecturerCourses
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
