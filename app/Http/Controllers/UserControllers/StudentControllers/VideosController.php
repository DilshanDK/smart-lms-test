<?php

namespace App\Http\Controllers\UserControllers\StudentControllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\MongoService;
use MongoDB\BSON\ObjectId;

class VideosController extends Controller
{
    private $mongo;

    public function __construct()
    {
        $this->mongo = new MongoService();
    }

    /**
     * Get all videos for student's enrolled courses
     */
    public function getVideos(Request $request)
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
                return response()->json(['status' => 'error', 'message' => 'Invalid session'], 401);
            }

            $user = $this->mongo->findOne('users', ['_id' => new ObjectId($session['user_id'])]);
            if (!$user || $user['role'] !== 'assigned_student') {
                return response()->json(['status' => 'error', 'message' => 'Access denied'], 403);
            }

            // Get student's enrolled courses
            $enrolledCoursesRaw = $user['enrolled_courses'] ?? [];
            $enrolledCourses = [];
            $enrolledCourseIds = [];

            if (is_iterable($enrolledCoursesRaw)) {
                foreach ($enrolledCoursesRaw as $courseDoc) {
                    $courseArray = is_array($courseDoc) ? $courseDoc : (array)$courseDoc;
                    if (isset($courseArray['id'])) {
                        $enrolledCourses[] = [
                            'id' => $courseArray['id'],
                            'title' => $courseArray['title'] ?? 'Unknown Course'
                        ];
                        $enrolledCourseIds[] = $courseArray['id'];
                    }
                }
            }

            if (empty($enrolledCourseIds)) {
                return response()->json([
                    'status' => 'success',
                    'videos' => [],
                    'enrolled_courses' => [],
                    'message' => 'No enrolled courses found'
                ], 200);
            }

            // Fetch videos for enrolled courses (only unlisted or public)
            $videos = $this->mongo->find('videos', [
                'course_id' => ['$in' => $enrolledCourseIds],
                'status' => ['$in' => ['unlisted', 'public']],
            ], ['sort' => ['created_at' => -1]]);

            $videoList = [];
            foreach ($videos as $video) {
                $videoArray = is_array($video) ? $video : (array)$video;

                $videoList[] = [
                    'id' => (string)($videoArray['_id'] ?? ''),
                    'title' => $videoArray['title'] ?? 'Untitled Video',
                    'description' => $videoArray['description'] ?? '',
                    'course_id' => $videoArray['course_id'] ?? '',
                    'course_name' => $videoArray['course_name'] ?? 'Unknown Course',
                    'lecturer_name' => $videoArray['lecturer_name'] ?? 'Unknown Lecturer',
                    'youtube_video_id' => $videoArray['youtube_video_id'] ?? '',
                    'youtube_url' => $videoArray['youtube_url'] ?? '',
                    'thumbnail_url' => $videoArray['thumbnail_url'] ?? '',
                    'playlist_url' => $videoArray['playlist_url'] ?? '',
                    'video_type' => $videoArray['video_type'] ?? 'upload',
                    'duration_seconds' => $videoArray['duration_seconds'] ?? 0,
                    'total_views' => $videoArray['total_views'] ?? 0,
                    'created_at' => $videoArray['created_at'] ?? ''
                ];
            }

            return response()->json([
                'status' => 'success',
                'videos' => $videoList,
                'enrolled_courses' => $enrolledCourses,
                'total_videos' => count($videoList)
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Internal server error', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Record video view (increment view count)
     */
    public function recordView(Request $request, $videoId)
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
                return response()->json(['status' => 'error', 'message' => 'Invalid session'], 401);
            }

            $user = $this->mongo->findOne('users', ['_id' => new ObjectId($session['user_id'])]);
            if (!$user || $user['role'] !== 'assigned_student') {
                return response()->json(['status' => 'error', 'message' => 'Access denied'], 403);
            }

            // Validate video ID
            try {
                $objectId = new ObjectId($videoId);
            } catch (\Exception $e) {
                return response()->json(['status' => 'error', 'message' => 'Invalid video ID'], 422);
            }

            // Increment view count
            $updateResult = $this->mongo->updateOne(
                'videos',
                ['_id' => $objectId],
                ['$inc' => ['total_views' => 1]]
            );

            if ($updateResult->getModifiedCount() > 0) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'View recorded'
                ], 200);
            }

            return response()->json(['status' => 'success', 'message' => 'Video found'], 200);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Internal server error', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get live streams for student's enrolled courses
     */
    public function getLiveStreams(Request $request)
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
                return response()->json(['status' => 'error', 'message' => 'Invalid session'], 401);
            }

            $user = $this->mongo->findOne('users', ['_id' => new ObjectId($session['user_id'])]);
            if (!$user || $user['role'] !== 'assigned_student') {
                return response()->json(['status' => 'error', 'message' => 'Access denied'], 403);
            }

            // Get student's enrolled courses
            $enrolledCoursesRaw = $user['enrolled_courses'] ?? [];
            $enrolledCourseIds = [];

            if (is_iterable($enrolledCoursesRaw)) {
                foreach ($enrolledCoursesRaw as $courseDoc) {
                    $courseArray = is_array($courseDoc) ? $courseDoc : (array)$courseDoc;
                    if (isset($courseArray['id'])) {
                        $enrolledCourseIds[] = $courseArray['id'];
                    }
                }
            }

            if (empty($enrolledCourseIds)) {
                return response()->json([
                    'status' => 'success',
                    'streams' => [],
                    'message' => 'No enrolled courses found'
                ], 200);
            }

            // Fetch live streams for enrolled courses (only live or scheduled, not ended)
            $streams = $this->mongo->find('live_streams', [
                'course_id' => ['$in' => $enrolledCourseIds],
                'status' => ['$in' => ['live', 'scheduled']]
            ], ['sort' => ['scheduled_start' => -1]]);

            $streamList = [];
            foreach ($streams as $stream) {
                $streamArray = is_array($stream) ? $stream : (array)$stream;

                $streamList[] = [
                    'id' => (string)($streamArray['_id'] ?? ''),
                    'title' => $streamArray['title'] ?? 'Untitled Stream',
                    'description' => $streamArray['description'] ?? '',
                    'course_id' => $streamArray['course_id'] ?? '',
                    'course_name' => $streamArray['course_name'] ?? 'Unknown Course',
                    'lecturer_id' => $streamArray['lecturer_id'] ?? '',
                    'lecturer_name' => $streamArray['lecturer_name'] ?? 'Unknown Lecturer',
                    'status' => $streamArray['status'] ?? 'scheduled',
                    'youtube_broadcast_id' => $streamArray['youtube_broadcast_id'] ?? '',
                    'stream_url' => $streamArray['stream_url'] ?? '',
                    'stream_key' => $streamArray['stream_key'] ?? '',
                    'watch_url' => $streamArray['watch_url'] ?? '',
                    'thumbnail_url' => $streamArray['thumbnail_url'] ?? '',
                    'scheduled_start' => $streamArray['scheduled_start'] ?? '',
                    'actual_start_time' => $streamArray['actual_start_time'] ?? null,
                    'actual_end_time' => $streamArray['actual_end_time'] ?? null,
                    'peak_viewers' => $streamArray['peak_viewers'] ?? 0,
                    'total_views' => $streamArray['total_views'] ?? 0,
                    'created_at' => $streamArray['created_at'] ?? ''
                ];
            }

            return response()->json([
                'status' => 'success',
                'streams' => $streamList,
                'total_streams' => count($streamList),
                'live_count' => count(array_filter($streamList, fn($s) => $s['status'] === 'live')),
                'scheduled_count' => count(array_filter($streamList, fn($s) => $s['status'] === 'scheduled'))
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Internal server error', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Check if YouTube is connected (for student view) - FIXED
     */
    public function checkYouTubeConnection(Request $request)
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
                return response()->json(['status' => 'error', 'message' => 'Invalid session'], 401);
            }

            $user = $this->mongo->findOne('users', ['_id' => new ObjectId($session['user_id'])]);
            if (!$user || $user['role'] !== 'assigned_student') {
                return response()->json(['status' => 'error', 'message' => 'Access denied'], 403);
            }

            // FIXED: Check system_settings (same as lecturer)
            $youtubeSettings = $this->mongo->findOne('system_settings', ['setting_key' => 'youtube_channel']);

            \Log::info('Student YouTube check', [
                'has_settings' => !empty($youtubeSettings),
                'has_config' => isset($youtubeSettings['youtube_config']),
                'is_active' => isset($youtubeSettings['youtube_config']['is_active']) ? $youtubeSettings['youtube_config']['is_active'] : null
            ]);

            if (!$youtubeSettings || !isset($youtubeSettings['youtube_config'])) {
                return response()->json([
                    'status' => 'success',
                    'connected' => false,
                    'message' => 'YouTube channel not connected by administrator'
                ], 200);
            }

            $config = $youtubeSettings['youtube_config'];

            // Check if token is expired
            $tokenExpiresAt = $config['token_expires_at'] ?? null;
            $isExpired = false;

            if ($tokenExpiresAt) {
                $expiryTime = new \DateTime($tokenExpiresAt, new \DateTimeZone('Asia/Colombo'));
                $now = new \DateTime('now', new \DateTimeZone('Asia/Colombo'));
                $isExpired = $now > $expiryTime;
            }

            $isConnected = ($config['is_active'] ?? false) && !$isExpired;

            return response()->json([
                'status' => 'success',
                'connected' => $isConnected,
                'channel_name' => $config['channel_name'] ?? 'Unknown Channel',
                'token_expired' => $isExpired
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Student YouTube check error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Internal server error',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
