<?php

namespace App\Http\Controllers\UserControllers\LecturerControllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\MongoService;
use MongoDB\BSON\ObjectId;
use Illuminate\Support\Facades\Http;

class VideosController extends Controller
{
    private $mongo;

    public function __construct()
    {
        $this->mongo = new MongoService();
    }

    /**
     * Get all videos for the lecturer
     */
    public function getVideos(Request $request)
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

            // Fetch videos
            $videos = $this->mongo->find('videos', [
                'lecturer_id' => $session['user_id'],
                'status' => ['$ne' => 'deleted']
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
                    'youtube_video_id' => $videoArray['youtube_video_id'] ?? '',
                    'youtube_url' => $videoArray['youtube_url'] ?? '',
                    'thumbnail_url' => $videoArray['thumbnail_url'] ?? '',
                    'video_type' => $videoArray['video_type'] ?? 'upload',
                    'status' => $videoArray['status'] ?? 'unlisted',
                    'duration_seconds' => $videoArray['duration_seconds'] ?? 0,
                    'total_views' => $videoArray['total_views'] ?? 0,
                    'created_at' => $videoArray['created_at'] ?? ''
                ];
            }

            return response()->json([
                'status' => 'success',
                'videos' => $videoList,
                'lecturer_courses' => $lecturerCourses
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Internal server error', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Check YouTube connection status - FIXED
     */
    public function checkYouTubeStatus(Request $request)
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

            // FIXED: Check YouTube settings correctly
            $youtubeSettings = $this->mongo->findOne('system_settings', ['setting_key' => 'youtube_channel']);

            \Log::info('YouTube status check for lecturer', [
                'has_settings' => !empty($youtubeSettings),
                'has_config' => isset($youtubeSettings['youtube_config']),
                'is_active' => isset($youtubeSettings['youtube_config']['is_active']) ? $youtubeSettings['youtube_config']['is_active'] : null
            ]);

            if (!$youtubeSettings || !isset($youtubeSettings['youtube_config'])) {
                return response()->json([
                    'status' => 'success',
                    'connected' => false,
                    'message' => 'YouTube channel not connected by admin'
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
                'channel_name' => $config['channel_name'] ?? 'Unknown',
                'channel_id' => $config['channel_id'] ?? '',
                'token_expired' => $isExpired,
                'connected_at' => $config['connected_at'] ?? ''
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error checking YouTube status', [
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

    /**
     * Delete a video
     */
    public function deleteVideo(Request $request, $videoId)
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

            // Validate video ID
            try {
                $objectId = new ObjectId($videoId);
            } catch (\Exception $e) {
                return response()->json(['status' => 'error', 'message' => 'Invalid video ID'], 422);
            }

            // Soft delete video
            $sriLankaTimezone = new \DateTimeZone('Asia/Colombo');
            $currentTime = new \DateTime('now', $sriLankaTimezone);

            $updateResult = $this->mongo->updateOne(
                'videos',
                [
                    '_id' => $objectId,
                    'lecturer_id' => $session['user_id']
                ],
                [
                    '$set' => [
                        'status' => 'deleted',
                        'deleted_at' => $currentTime->format('Y-m-d H:i:s')
                    ]
                ]
            );

            if ($updateResult->getModifiedCount() > 0) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Video deleted successfully'
                ], 200);
            }

            return response()->json(['status' => 'error', 'message' => 'Video not found or access denied'], 404);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Internal server error', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Upload video to YouTube with course playlist - COMPLETELY REWRITTEN
     */
    public function uploadVideo(Request $request)
    {
        try {
            // Step 1: Authenticate lecturer
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

            // Step 2: Validate input
            $validator = \Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'description' => 'nullable|string|max:5000',
                'course_id' => 'required|string',
                'video' => 'required|file|mimes:mp4,mov,avi,wmv|max:524288' // Max 512MB
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()->all()
                ], 422);
            }

            // Step 3: Verify course belongs to lecturer
            $lecturerCourses = [];
            $courseName = 'Unknown Course';
            if (isset($user['courses']) && is_iterable($user['courses'])) {
                foreach ($user['courses'] as $course) {
                    $courseArray = is_array($course) ? $course : (array)$course;
                    if (isset($courseArray['id'])) {
                        $lecturerCourses[] = $courseArray['id'];
                        if ($courseArray['id'] === $request->course_id) {
                            $courseName = $courseArray['title'] ?? 'Unknown Course';
                        }
                    }
                }
            }

            if (!in_array($request->course_id, $lecturerCourses)) {
                return response()->json(['status' => 'error', 'message' => 'You are not assigned to this course'], 403);
            }

            // Step 4: Get YouTube credentials
            $youtubeSettings = $this->mongo->findOne('system_settings', ['setting_key' => 'youtube_channel']);
            if (!$youtubeSettings || !isset($youtubeSettings['youtube_config'])) {
                return response()->json(['status' => 'error', 'message' => 'YouTube not connected. Contact admin.'], 500);
            }

            $youtubeConfig = $youtubeSettings['youtube_config'];
            $accessToken = decrypt($youtubeConfig['access_token']);

            // Step 5: Get video file details
            $videoFile = $request->file('video');
            $videoPath = $videoFile->getRealPath();
            $videoSize = $videoFile->getSize();
            $mimeType = $videoFile->getMimeType();

            \Log::info('Starting video upload', [
                'file_name' => $videoFile->getClientOriginalName(),
                'file_size' => $videoSize,
                'mime_type' => $mimeType,
                'title' => $request->title
            ]);

            // Step 6: Upload video using cURL (more reliable than Http facade)
            $youtubeVideoId = $this->uploadVideoWithCurl(
                $accessToken,
                $videoPath,
                $videoSize,
                $mimeType,
                $request->title,
                $request->description ?? ''
            );

            if (!$youtubeVideoId) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to upload video to YouTube. Check logs for details.'
                ], 500);
            }

            // Step 7: Get or create course playlist
            $playlistId = $this->getOrCreateCoursePlaylist($accessToken, $request->course_id, $courseName);

            // Step 8: Add video to playlist if playlist exists
            $addedToPlaylist = false;
            if ($playlistId) {
                $addedToPlaylist = $this->addVideoToPlaylist($accessToken, $playlistId, $youtubeVideoId);
            }

            // Step 9: Generate URLs
            $youtubeUrl = 'https://www.youtube.com/watch?v=' . $youtubeVideoId;
            $thumbnailUrl = 'https://i.ytimg.com/vi/' . $youtubeVideoId . '/hqdefault.jpg';
            $playlistUrl = $playlistId ? 'https://www.youtube.com/playlist?list=' . $playlistId : null;

            // Step 10: Save to database
            $sriLankaTimezone = new \DateTimeZone('Asia/Colombo');
            $currentTime = new \DateTime('now', $sriLankaTimezone);

            $videoData = [
                'title' => $request->title,
                'description' => $request->description ?? '',
                'course_id' => $request->course_id,
                'course_name' => $courseName,
                'lecturer_id' => $session['user_id'],
                'lecturer_name' => $user['name'] ?? 'Unknown Lecturer',
                'youtube_video_id' => $youtubeVideoId,
                'youtube_url' => $youtubeUrl,
                'youtube_playlist_id' => $playlistId,
                'playlist_url' => $playlistUrl,
                'thumbnail_url' => $thumbnailUrl,
                'video_type' => 'upload',
                'status' => 'unlisted',
                'duration_seconds' => 0,
                'total_views' => 0,
                'created_at' => $currentTime->format('Y-m-d H:i:s'),
                'updated_at' => $currentTime->format('Y-m-d H:i:s')
            ];

            $result = $this->mongo->insertOne('videos', $videoData);

            if ($result->getInsertedId()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Video uploaded to YouTube successfully',
                    'video' => [
                        'id' => (string)$result->getInsertedId(),
                        'youtube_video_id' => $youtubeVideoId,
                        'youtube_url' => $youtubeUrl,
                        'thumbnail_url' => $thumbnailUrl,
                        'playlist_id' => $playlistId,
                        'added_to_playlist' => $addedToPlaylist
                    ]
                ], 201);
            }

            return response()->json(['status' => 'error', 'message' => 'Failed to save video metadata'], 500);

        } catch (\Exception $e) {
            \Log::error('Upload exception', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Internal server error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload video to YouTube using cURL - COMPLETELY REWRITTEN
     */
    private function uploadVideoWithCurl($accessToken, $videoPath, $videoSize, $mimeType, $title, $description)
    {
        try {
            // Step 1: Prepare metadata (JSON format)
            $metadata = json_encode([
                'snippet' => [
                    'title' => $title,
                    'description' => $description,
                    'categoryId' => '27' // Education category
                ],
                'status' => [
                    'privacyStatus' => 'unlisted',
                    'selfDeclaredMadeForKids' => false
                ]
            ]);

            \Log::info('Initializing resumable upload', [
                'video_size' => $videoSize,
                'mime_type' => $mimeType,
                'title' => $title
            ]);

            // Step 2: Initialize resumable upload session
            $ch = curl_init('https://www.googleapis.com/upload/youtube/v3/videos?uploadType=resumable&part=snippet,status');
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $accessToken,
                    'Content-Type: application/json; charset=UTF-8',
                    'X-Upload-Content-Length: ' . $videoSize,
                    'X-Upload-Content-Type: ' . $mimeType
                ],
                CURLOPT_POSTFIELDS => $metadata,
                CURLOPT_HEADER => true,
                CURLOPT_NOBODY => false
            ]);

            $initResponse = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $headers = substr($initResponse, 0, $headerSize);
            curl_close($ch);

            \Log::info('Init response received', [
                'http_code' => $httpCode,
                'headers_preview' => substr($headers, 0, 500)
            ]);

            if ($httpCode !== 200) {
                \Log::error('Failed to initialize upload', [
                    'http_code' => $httpCode,
                    'response' => substr($initResponse, $headerSize, 1000)
                ]);
                return null;
            }

            // Step 3: Extract upload URL from Location header
            $uploadUrl = null;
            if (preg_match('/Location:\s*(.+?)[\r\n]/i', $headers, $matches)) {
                $uploadUrl = trim($matches[1]);
            }

            if (!$uploadUrl) {
                \Log::error('No upload URL in response', ['headers' => $headers]);
                return null;
            }

            \Log::info('Upload URL obtained', ['url' => substr($uploadUrl, 0, 100)]);

            // Step 4: Upload video file in chunks
            $chunkSize = 5 * 1024 * 1024; // 5MB chunks
            $fileHandle = fopen($videoPath, 'rb');
            $uploadedBytes = 0;
            $youtubeVideoId = null;

            while (!feof($fileHandle)) {
                $chunkData = fread($fileHandle, $chunkSize);
                $chunkLength = strlen($chunkData);
                $chunkStart = $uploadedBytes;
                $chunkEnd = $uploadedBytes + $chunkLength - 1;

                \Log::info('Uploading chunk', [
                    'start' => $chunkStart,
                    'end' => $chunkEnd,
                    'total' => $videoSize,
                    'progress' => round(($uploadedBytes / $videoSize) * 100, 2) . '%'
                ]);

                $ch = curl_init($uploadUrl);
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_CUSTOMREQUEST => 'PUT',
                    CURLOPT_HTTPHEADER => [
                        'Content-Length: ' . $chunkLength,
                        'Content-Range: bytes ' . $chunkStart . '-' . $chunkEnd . '/' . $videoSize,
                        'Content-Type' => $mimeType
                    ],
                    CURLOPT_POSTFIELDS => $chunkData
                ]);

                $chunkResponse = curl_exec($ch);
                $chunkHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                $uploadedBytes += $chunkLength;

                \Log::info('Chunk response', [
                    'http_code' => $chunkHttpCode,
                    'uploaded_bytes' => $uploadedBytes
                ]);

                // Check if upload is complete (200 or 201)
                if ($chunkHttpCode === 200 || $chunkHttpCode === 201) {
                    $responseData = json_decode($chunkResponse, true);
                    $youtubeVideoId = $responseData['id'] ?? null;

                    \Log::info('Upload complete', [
                        'video_id' => $youtubeVideoId,
                        'response' => substr($chunkResponse, 0, 500)
                    ]);
                    break;
                }
                // 308 means "Resume Incomplete" - continue uploading
                elseif ($chunkHttpCode === 308) {
                    continue;
                }
                // Any other code is an error
                else {
                    fclose($fileHandle);
                    \Log::error('Chunk upload failed', [
                        'http_code' => $chunkHttpCode,
                        'response' => $chunkResponse
                    ]);
                    return null;
                }
            }

            fclose($fileHandle);

            return $youtubeVideoId;

        } catch (\Exception $e) {
            \Log::error('Error in uploadVideoWithCurl', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    /**
     * Get existing course playlist or create new one
     */
    private function getOrCreateCoursePlaylist($accessToken, $courseId, $courseName)
    {
        try {
            // Check if playlist already exists in database
            $existingPlaylist = $this->mongo->findOne('course_playlists', [
                'course_id' => $courseId
            ]);

            if ($existingPlaylist && isset($existingPlaylist['youtube_playlist_id'])) {
                // Verify playlist still exists on YouTube
                $verifyResponse = Http::withToken($accessToken)->get('https://www.googleapis.com/youtube/v3/playlists', [
                    'part' => 'snippet',
                    'id' => $existingPlaylist['youtube_playlist_id']
                ]);

                if ($verifyResponse->successful()) {
                    $data = $verifyResponse->json();
                    if (isset($data['items']) && count($data['items']) > 0) {
                        \Log::info('Using existing playlist', ['playlist_id' => $existingPlaylist['youtube_playlist_id']]);
                        return $existingPlaylist['youtube_playlist_id'];
                    }
                }
            }

            // Create new playlist - FIXED: Use asJson()
            $createResponse = Http::withToken($accessToken)
                ->asJson()
                ->post('https://www.googleapis.com/youtube/v3/playlists?part=snippet,status', [
                    'snippet' => [
                        'title' => $courseName,
                        'description' => "Educational videos for {$courseName} course",
                        'tags' => ['education', 'course'],
                        'defaultLanguage' => 'en'
                    ],
                    'status' => [
                        'privacyStatus' => 'unlisted'
                    ]
                ]);

            if (!$createResponse->successful()) {
                \Log::error('Failed to create playlist', [
                    'status' => $createResponse->status(),
                    'body' => $createResponse->body(),
                    'course_id' => $courseId,
                    'course_name' => $courseName
                ]);
                return null;
            }

            $playlistData = $createResponse->json();
            $playlistId = $playlistData['id'] ?? null;

            if (!$playlistId) {
                \Log::error('No playlist ID in response', ['response' => $playlistData]);
                return null;
            }

            // Store playlist in database
            $sriLankaTimezone = new \DateTimeZone('Asia/Colombo');
            $currentTime = new \DateTime('now', $sriLankaTimezone);

            $this->mongo->updateOne(
                'course_playlists',
                ['course_id' => $courseId],
                ['$set' => [
                    'course_id' => $courseId,
                    'course_name' => $courseName,
                    'youtube_playlist_id' => $playlistId,
                    'playlist_url' => 'https://www.youtube.com/playlist?list=' . $playlistId,
                    'created_at' => $currentTime->format('Y-m-d H:i:s'),
                    'updated_at' => $currentTime->format('Y-m-d H:i:s')
                ]],
                ['upsert' => true]
            );

            \Log::info('Created new playlist', [
                'playlist_id' => $playlistId,
                'course_id' => $courseId
            ]);

            return $playlistId;

        } catch (\Exception $e) {
            \Log::error('Error in getOrCreateCoursePlaylist', [
                'message' => $e->getMessage(),
                'course_id' => $courseId
            ]);
            return null;
        }
    }

    /**
     * Add video to playlist
     */
    private function addVideoToPlaylist($accessToken, $playlistId, $videoId)
    {
        try {
            $response = Http::withToken($accessToken)
                ->asJson()
                ->post('https://www.googleapis.com/youtube/v3/playlistItems?part=snippet', [
                    'snippet' => [
                        'playlistId' => $playlistId,
                        'resourceId' => [
                            'kind' => 'youtube#video',
                            'videoId' => $videoId
                        ]
                    ]
                ]);

            if ($response->successful()) {
                return true;
            }

            \Log::error('Failed to add video to playlist', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            return false;

        } catch (\Exception $e) {
            \Log::error('Error adding video to playlist', [
                'message' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Create a YouTube Live Broadcast and start streaming (Go Live)
     *
     * This creates a real YouTube live stream that the lecturer can broadcast to
     */
    public function goLive(Request $request)
    {
        try {
            // Step 1: Authenticate lecturer
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

            // Step 2: Validate input
            $validator = \Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'course_id' => 'required|string',
                'start_now' => 'nullable|boolean',
                'scheduled_start' => 'nullable|date'
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => 'error', 'message' => 'Validation failed', 'errors' => $validator->errors()->all()], 422);
            }

            $title = $request->input('title');
            $description = $request->input('description', '');
            $courseId = $request->input('course_id');
            $startNow = (bool) $request->input('start_now', true); // Default to start now
            $scheduledStart = $request->input('scheduled_start', null);

            // Step 3: Verify lecturer assigned to course
            $courseName = 'Unknown Course';
            $assigned = false;
            if (isset($user['courses']) && is_iterable($user['courses'])) {
                foreach ($user['courses'] as $c) {
                    $cArr = is_array($c) ? $c : (array)$c;
                    if (isset($cArr['id']) && $cArr['id'] === $courseId) {
                        $assigned = true;
                        $courseName = $cArr['title'] ?? $courseName;
                        break;
                    }
                }
            }
            if (!$assigned) {
                return response()->json(['status' => 'error', 'message' => 'You are not assigned to this course'], 403);
            }

            // Step 4: Get YouTube credentials
            $youtubeSettings = $this->mongo->findOne('system_settings', ['setting_key' => 'youtube_channel']);
            if (!$youtubeSettings || !isset($youtubeSettings['youtube_config'])) {
                return response()->json(['status' => 'error', 'message' => 'YouTube not connected. Contact admin.'], 500);
            }

            $youtubeConfig = $youtubeSettings['youtube_config'];
            $accessToken = decrypt($youtubeConfig['access_token']);

            // Step 5: Create YouTube Live Broadcast
            $broadcast = $this->createYouTubeLiveBroadcast($accessToken, $title, $description, $scheduledStart);

            if (!$broadcast) {
                return response()->json(['status' => 'error', 'message' => 'Failed to create YouTube live broadcast'], 500);
            }

            // Step 6: Create YouTube Live Stream (technical configuration)
            $stream = $this->createYouTubeLiveStream($accessToken, $title);

            if (!$stream) {
                return response()->json(['status' => 'error', 'message' => 'Failed to create YouTube live stream'], 500);
            }

            // Step 7: Bind broadcast to stream
            $bindSuccess = $this->bindBroadcastToStream($accessToken, $broadcast['id'], $stream['id']);

            if (!$bindSuccess) {
                return response()->json(['status' => 'error', 'message' => 'Failed to bind broadcast to stream'], 500);
            }

            // Step 8: Transition broadcast to "live" status if starting now
            if ($startNow) {
                $this->transitionBroadcastToLive($accessToken, $broadcast['id']);
            }

            // Step 9: Save to database
            $sriLankaTimezone = new \DateTimeZone('Asia/Colombo');
            $currentTime = new \DateTime('now', $sriLankaTimezone);

            $liveData = [
                'title' => $title,
                'description' => $description,
                'course_id' => $courseId,
                'course_name' => $courseName,
                'lecturer_id' => $session['user_id'],
                'lecturer_name' => $user['name'] ?? 'Unknown Lecturer',
                'youtube_broadcast_id' => $broadcast['id'],
                'youtube_stream_id' => $stream['id'],
                'youtube_video_id' => $broadcast['id'], // Broadcast ID is also the video ID
                'live_chat_id' => $broadcast['snippet']['liveChatId'] ?? null,
                'watch_url' => 'https://www.youtube.com/watch?v=' . $broadcast['id'],
                'embed_url' => 'https://www.youtube.com/embed/' . $broadcast['id'],
                'stream_url' => $stream['cdn']['ingestionInfo']['ingestionAddress'] ?? null,
                'stream_key' => $stream['cdn']['ingestionInfo']['streamName'] ?? null,
                'status' => $startNow ? 'live' : 'scheduled',
                'scheduled_start' => $scheduledStart ? (new \DateTime($scheduledStart, $sriLankaTimezone))->format('Y-m-d H:i:s') : null,
                'actual_start' => $startNow ? $currentTime->format('Y-m-d H:i:s') : null,
                'created_at' => $currentTime->format('Y-m-d H:i:s'),
                'updated_at' => $currentTime->format('Y-m-d H:i:s'),
            ];

            $insertResult = $this->mongo->insertOne('live_streams', $liveData);

            if ($insertResult->getInsertedId()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Live stream created successfully! Use the stream key in your broadcasting software.',
                    'live_stream' => [
                        'id' => (string)$insertResult->getInsertedId(),
                        'broadcast_id' => $broadcast['id'],
                        'watch_url' => $liveData['watch_url'],
                        'embed_url' => $liveData['embed_url'],
                        'stream_url' => $liveData['stream_url'],
                        'stream_key' => $liveData['stream_key'],
                        'status' => $liveData['status']
                    ]
                ], 201);
            }

            return response()->json(['status' => 'error', 'message' => 'Failed to save live stream data'], 500);

        } catch (\Exception $e) {
            \Log::error('goLive exception', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['status' => 'error', 'message' => 'Internal server error', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Create YouTube Live Broadcast
     */
    private function createYouTubeLiveBroadcast($accessToken, $title, $description, $scheduledStart)
    {
        try {
            $sriLankaTimezone = new \DateTimeZone('Asia/Colombo');
            $startTime = $scheduledStart ? new \DateTime($scheduledStart, $sriLankaTimezone) : new \DateTime('now', $sriLankaTimezone);

            $broadcastData = [
                'snippet' => [
                    'title' => $title,
                    'description' => $description,
                    'scheduledStartTime' => $startTime->format('c') // ISO 8601 format
                ],
                'status' => [
                    'privacyStatus' => 'unlisted', // Can be 'public', 'private', or 'unlisted'
                    'selfDeclaredMadeForKids' => false
                ],
                'contentDetails' => [
                    'enableAutoStart' => true,
                    'enableAutoStop' => true,
                    'enableDvr' => true,
                    'enableContentEncryption' => false,
                    'enableEmbed' => true,
                    'recordFromStart' => true
                ]
            ];

            $response = \Http::withToken($accessToken)
                ->asJson()
                ->post('https://www.googleapis.com/youtube/v3/liveBroadcasts?part=snippet,status,contentDetails', $broadcastData);

            if ($response->successful()) {
                \Log::info('YouTube broadcast created', ['broadcast_id' => $response->json()['id']]);
                return $response->json();
            }

            \Log::error('Failed to create YouTube broadcast', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            return null;

        } catch (\Exception $e) {
            \Log::error('Error creating YouTube broadcast', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Create YouTube Live Stream (technical configuration for streaming)
     */
    private function createYouTubeLiveStream($accessToken, $title)
    {
        try {
            $streamData = [
                'snippet' => [
                    'title' => $title . ' - Stream'
                ],
                'cdn' => [
                    'frameRate' => '30fps',
                    'ingestionType' => 'rtmp',
                    'resolution' => '1080p'
                ]
            ];

            $response = \Http::withToken($accessToken)
                ->asJson()
                ->post('https://www.googleapis.com/youtube/v3/liveStreams?part=snippet,cdn,status', $streamData);

            if ($response->successful()) {
                \Log::info('YouTube stream created', ['stream_id' => $response->json()['id']]);
                return $response->json();
            }

            \Log::error('Failed to create YouTube stream', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            return null;

        } catch (\Exception $e) {
            \Log::error('Error creating YouTube stream', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Bind broadcast to stream - FIXED: Use query parameters
     */
    private function bindBroadcastToStream($accessToken, $broadcastId, $streamId)
    {
        try {
            // FIXED: YouTube API requires parameters in URL query string
            $url = 'https://www.googleapis.com/youtube/v3/liveBroadcasts/bind?' . http_build_query([
                'id' => $broadcastId,
                'streamId' => $streamId,
                'part' => 'id,snippet,status'
            ]);

            $response = \Http::withToken($accessToken)->post($url);

            if ($response->successful()) {
                \Log::info('Broadcast bound to stream', [
                    'broadcast_id' => $broadcastId,
                    'stream_id' => $streamId
                ]);
                return true;
            }

            \Log::error('Failed to bind broadcast to stream', [
                'status' => $response->status(),
                'body' => $response->body(),
                'broadcast_id' => $broadcastId,
                'stream_id' => $streamId
            ]);
            return false;

        } catch (\Exception $e) {
            \Log::error('Error binding broadcast to stream', [
                'error' => $e->getMessage(),
                'broadcast_id' => $broadcastId,
                'stream_id' => $streamId
            ]);
            return false;
        }
    }

    /**
     * Transition broadcast to live status - FIXED: Use query parameters
     */
    private function transitionBroadcastToLive($accessToken, $broadcastId)
    {
        try {
            // FIXED: YouTube API requires parameters in URL query string
            $url = 'https://www.googleapis.com/youtube/v3/liveBroadcasts/transition?' . http_build_query([
                'broadcastStatus' => 'live',
                'id' => $broadcastId,
                'part' => 'status'
            ]);

            $response = \Http::withToken($accessToken)->post($url);

            if ($response->successful()) {
                \Log::info('Broadcast transitioned to live', ['broadcast_id' => $broadcastId]);
                return true;
            }

            \Log::warning('Failed to transition broadcast to live', [
                'status' => $response->status(),
                'body' => $response->body(),
                'broadcast_id' => $broadcastId
            ]);
            return false;

        } catch (\Exception $e) {
            \Log::error('Error transitioning broadcast to live', [
                'error' => $e->getMessage(),
                'broadcast_id' => $broadcastId
            ]);
            return false;
        }
    }

    /**
     * Get lecturer's live streams with RTMP credentials (for OBS streaming)
     */
    public function getMyLiveStreams(Request $request)
    {
        try {
            // Step 1: Authenticate lecturer
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

            // Step 2: Fetch live streams for this lecturer (only active/scheduled, not ended)
            $streams = $this->mongo->find('live_streams', [
                'lecturer_id' => $session['user_id'],
                'status' => ['$in' => ['scheduled', 'live']] // Exclude ended streams
            ], ['sort' => ['created_at' => -1]]);

            $streamList = [];
            foreach ($streams as $stream) {
                $streamArray = is_array($stream) ? $stream : (array)$stream;

                $streamList[] = [
                    'id' => (string)($streamArray['_id'] ?? ''),
                    'title' => $streamArray['title'] ?? 'Untitled Stream',
                    'description' => $streamArray['description'] ?? '',
                    'course_id' => $streamArray['course_id'] ?? '',
                    'course_name' => $streamArray['course_name'] ?? 'Unknown Course',
                    'status' => $streamArray['status'] ?? 'scheduled',
                    'watch_url' => $streamArray['watch_url'] ?? '',
                    'embed_url' => $streamArray['embed_url'] ?? '',
                    'stream_url' => $streamArray['stream_url'] ?? null,
                    'stream_key' => $streamArray['stream_key'] ?? null,
                    'scheduled_start' => $streamArray['scheduled_start'] ?? null,
                    'actual_start' => $streamArray['actual_start'] ?? null,
                    'created_at' => $streamArray['created_at'] ?? ''
                ];
            }

            \Log::info('Lecturer fetching live streams', [
                'lecturer_id' => $session['user_id'],
                'stream_count' => count($streamList)
            ]);

            return response()->json([
                'status' => 'success',
                'streams' => $streamList
            ], 200);

        } catch (\Exception $e) {
            \Log::error('getMyLiveStreams error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['status' => 'error', 'message' => 'Internal server error', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete a live stream
     */
    public function deleteLiveStream(Request $request, $streamId)
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

            // Validate stream ID
            try {
                $objectId = new ObjectId($streamId);
            } catch (\Exception $e) {
                return response()->json(['status' => 'error', 'message' => 'Invalid stream ID'], 422);
            }

            // Delete stream (only if it belongs to this lecturer)
            $deleteResult = $this->mongo->deleteOne('live_streams', [
                '_id' => $objectId,
                'lecturer_id' => $session['user_id']
            ]);

            if ($deleteResult->getDeletedCount() > 0) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Live stream deleted successfully'
                ], 200);
            }

            return response()->json(['status' => 'error', 'message' => 'Stream not found or access denied'], 404);

        } catch (\Exception $e) {
            \Log::error('Delete stream error', [
                'error' => $e->getMessage(),
                'stream_id' => $streamId
            ]);
            return response()->json(['status' => 'error', 'message' => 'Internal server error', 'error' => $e->getMessage()], 500);
        }
    }
}
