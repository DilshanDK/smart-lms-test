<?php

namespace App\Http\Controllers\UserControllers\StudentControllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\MongoService;
use MongoDB\BSON\ObjectId;

class ProgressController extends Controller
{
    private $mongo;

    public function __construct()
    {
        $this->mongo = new MongoService();
    }

    /**
     * Get student's overall progress and analytics
     */
    public function getProgress(Request $request)
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
                return response()->json(['status' => 'error', 'message' => 'Access denied'], 403);
            }

            // Get enrolled courses
            $enrolledCoursesRaw = $user['enrolled_courses'] ?? [];
            $enrolledCourses = [];

            if (is_iterable($enrolledCoursesRaw)) {
                foreach ($enrolledCoursesRaw as $courseDoc) {
                    if ($courseDoc instanceof \MongoDB\Model\BSONDocument) {
                        $courseArray = [];
                        foreach ($courseDoc as $key => $value) {
                            $courseArray[$key] = $value;
                        }
                        $enrolledCourses[] = $courseArray;
                    } else {
                        $enrolledCourses[] = is_array($courseDoc) ? $courseDoc : (array)$courseDoc;
                    }
                }
            }

            $enrolledCourseIds = array_column($enrolledCourses, 'id');

            if (empty($enrolledCourseIds)) {
                return response()->json([
                    'status' => 'success',
                    'overall_stats' => [
                        'total_assignments' => 0,
                        'completed_assignments' => 0,
                        'pending_assignments' => 0,
                        'average_score' => 0,
                        'completion_rate' => 0
                    ],
                    'course_progress' => [],
                    'recent_submissions' => [],
                    'performance_trend' => []
                ], 200);
            }

            // Get all published assignments
            $allAssignments = $this->mongo->find('quizes', [
                'course_id' => ['$in' => $enrolledCourseIds],
                'status' => 'published'
            ]);

            $totalAssignments = 0;
            foreach ($allAssignments as $assignment) {
                $totalAssignments++;
            }

            // Get student submissions
            $submissions = $this->mongo->find('assignment_marks', [
                'student_id' => $session['user_id']
            ], ['sort' => ['submitted_at' => -1]]);

            $submissionsArray = [];
            foreach ($submissions as $submission) {
                $submissionsArray[] = is_array($submission) ? $submission : (array)$submission;
            }

            $completedAssignments = count($submissionsArray);
            $pendingAssignments = $totalAssignments - $completedAssignments;

            // Calculate average score
            $totalScore = 0;
            foreach ($submissionsArray as $submission) {
                $totalScore += $submission['score_percentage'] ?? 0;
            }
            $averageScore = $completedAssignments > 0 ? round($totalScore / $completedAssignments, 2) : 0;
            $completionRate = $totalAssignments > 0 ? round(($completedAssignments / $totalAssignments) * 100, 2) : 0;

            // Group progress by course
            $submissionsByCourse = [];
            foreach ($submissionsArray as $submission) {
                $courseId = $submission['course_id'] ?? 'unknown';
                if (!isset($submissionsByCourse[$courseId])) {
                    $submissionsByCourse[$courseId] = [];
                }
                $submissionsByCourse[$courseId][] = $submission;
            }

            $courseProgress = [];
            foreach ($enrolledCourses as $course) {
                $courseId = $course['id'];
                $courseName = $course['title'] ?? 'Unknown Course';

                // Count assignments for this course
                $courseAssignments = 0;
                $allAssignments->rewind();
                foreach ($allAssignments as $assignment) {
                    $assignmentArray = is_array($assignment) ? $assignment : (array)$assignment;
                    if (($assignmentArray['course_id'] ?? '') === $courseId) {
                        $courseAssignments++;
                    }
                }

                $courseSubmissions = $submissionsByCourse[$courseId] ?? [];
                $courseCompleted = count($courseSubmissions);
                $coursePending = $courseAssignments - $courseCompleted;

                $courseTotal = 0;
                foreach ($courseSubmissions as $sub) {
                    $courseTotal += $sub['score_percentage'] ?? 0;
                }
                $courseAverage = $courseCompleted > 0 ? round($courseTotal / $courseCompleted, 2) : 0;
                $courseCompletionRate = $courseAssignments > 0 ? round(($courseCompleted / $courseAssignments) * 100, 2) : 0;

                $courseProgress[] = [
                    'course_id' => $courseId,
                    'course_name' => $courseName,
                    'total_assignments' => $courseAssignments,
                    'completed' => $courseCompleted,
                    'pending' => $coursePending,
                    'average_score' => $courseAverage,
                    'completion_rate' => $courseCompletionRate
                ];
            }

            // Recent submissions
            $recentSubmissions = array_slice($submissionsArray, 0, 10);
            $recentList = [];
            foreach ($recentSubmissions as $submission) {
                $recentList[] = [
                    'assignment_title' => $submission['assignment_title'] ?? 'Untitled',
                    'course_id' => $submission['course_id'] ?? '',
                    'score_percentage' => $submission['score_percentage'] ?? 0,
                    'correct_answers' => $submission['correct_answers'] ?? 0,
                    'total_questions' => $submission['total_questions'] ?? 0,
                    'submitted_at' => $submission['submitted_at'] ?? ''
                ];
            }

            // Performance trend
            $performanceTrend = [];
            $trendData = array_slice($submissionsArray, 0, 10);
            foreach ($trendData as $submission) {
                $performanceTrend[] = [
                    'date' => $submission['submitted_at'] ?? '',
                    'score' => $submission['score_percentage'] ?? 0,
                    'assignment' => $submission['assignment_title'] ?? 'Untitled'
                ];
            }
            $performanceTrend = array_reverse($performanceTrend);

            return response()->json([
                'status' => 'success',
                'overall_stats' => [
                    'total_assignments' => $totalAssignments,
                    'completed_assignments' => $completedAssignments,
                    'pending_assignments' => $pendingAssignments,
                    'average_score' => $averageScore,
                    'completion_rate' => $completionRate
                ],
                'course_progress' => $courseProgress,
                'recent_submissions' => $recentList,
                'performance_trend' => $performanceTrend
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
     * Get student's average score only
     */
    public function getAverageScore(Request $request)
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
                return response()->json(['status' => 'error', 'message' => 'Access denied'], 403);
            }

            // Get all submissions
            $submissions = $this->mongo->find('assignment_marks', [
                'student_id' => $session['user_id']
            ]);

            $completedAssignments = 0;
            $totalScore = 0;
            foreach ($submissions as $submission) {
                $completedAssignments++;
                $totalScore += $submission['score_percentage'] ?? 0;
            }
            $averageScore = $completedAssignments > 0 ? round($totalScore / $completedAssignments, 2) : 0;

            return response()->json([
                'status' => 'success',
                'average_score' => $averageScore
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
