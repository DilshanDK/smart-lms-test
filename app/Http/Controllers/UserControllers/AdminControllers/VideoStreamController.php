<?php

namespace App\Http\Controllers\UserControllers\AdminControllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\MongoService;
use MongoDB\BSON\ObjectId;
use Illuminate\Support\Facades\Http;

class VideoStreamController extends Controller
{
    private $mongo;

    public function __construct()
    {
        $this->mongo = new MongoService();
    }

    /**
     * Get YouTube channel connection status
     */
    public function getYouTubeStatus(Request $request)
    {
        try {
            // Authenticate admin
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
            if (!$user || $user['role'] !== 'admin') {
                return response()->json(['status' => 'error', 'message' => 'Access denied. Admins only.'], 403);
            }

            // Get YouTube channel settings from system_settings collection
            $youtubeSettings = $this->mongo->findOne('system_settings', ['setting_key' => 'youtube_channel']);

            if (!$youtubeSettings || !isset($youtubeSettings['youtube_config'])) {
                return response()->json([
                    'status' => 'success',
                    'connected' => false,
                    'message' => 'YouTube channel not connected'
                ], 200);
            }

            $config = $youtubeSettings['youtube_config'];

            return response()->json([
                'status' => 'success',
                'connected' => $config['is_active'] ?? false,
                'channel_name' => $config['channel_name'] ?? 'Unknown',
                'channel_id' => $config['channel_id'] ?? '',
                'connected_at' => $config['connected_at'] ?? '',
                'token_expires_at' => $config['token_expires_at'] ?? ''
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Internal server error', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get all video streams (across all lecturers/courses)
     */
    public function getAllStreams(Request $request)
    {
        try {
            // Authenticate admin
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
            if (!$user || $user['role'] !== 'admin') {
                return response()->json(['status' => 'error', 'message' => 'Access denied. Admins only.'], 403);
            }

            // Get filter parameters
            $status = $request->query('status'); // 'scheduled', 'live', 'ended'
            $courseId = $request->query('course_id');
            $lecturerId = $request->query('lecturer_id');

            $filter = [];
            if ($status) {
                $filter['status'] = $status;
            }
            if ($courseId) {
                $filter['course_id'] = $courseId;
            }
            if ($lecturerId) {
                $filter['lecturer_id'] = $lecturerId;
            }

            // Fetch all video streams
            $streams = $this->mongo->find('live_streams', $filter, ['sort' => ['created_at' => -1]]);

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
                    'youtube_video_id' => $streamArray['youtube_video_id'] ?? '',
                    'scheduled_start' => $streamArray['scheduled_start'] ?? '',
                    'scheduled_end' => $streamArray['scheduled_end'] ?? '',
                    'actual_start' => $streamArray['actual_start'] ?? null,
                    'actual_end' => $streamArray['actual_end'] ?? null,
                    'peak_viewers' => $streamArray['peak_viewers'] ?? 0,
                    'total_views' => $streamArray['total_views'] ?? 0,
                    'is_recorded' => $streamArray['is_recorded'] ?? false,
                    'recording_url' => $streamArray['recording_url'] ?? null,
                    'created_at' => $streamArray['created_at'] ?? ''
                ];
            }

            return response()->json([
                'status' => 'success',
                'streams' => $streamList,
                'total_streams' => count($streamList)
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Internal server error', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get streaming statistics/analytics
     */
    public function getStreamAnalytics(Request $request)
    {
        try {
            // Authenticate admin
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
            if (!$user || $user['role'] !== 'admin') {
                return response()->json(['status' => 'error', 'message' => 'Access denied. Admins only.'], 403);
            }

            // Get all streams
            $allStreams = $this->mongo->find('live_streams', []);

            $totalStreams = 0;
            $liveStreams = 0;
            $scheduledStreams = 0;
            $endedStreams = 0;
            $totalViews = 0;
            $totalPeakViewers = 0;

            foreach ($allStreams as $stream) {
                $streamArray = is_array($stream) ? $stream : (array)$stream;
                $totalStreams++;

                $status = $streamArray['status'] ?? '';
                if ($status === 'live') $liveStreams++;
                if ($status === 'scheduled') $scheduledStreams++;
                if ($status === 'ended') $endedStreams++;

                $totalViews += $streamArray['total_views'] ?? 0;
                $totalPeakViewers += $streamArray['peak_viewers'] ?? 0;
            }

            $averagePeakViewers = $endedStreams > 0 ? round($totalPeakViewers / $endedStreams, 2) : 0;

            return response()->json([
                'status' => 'success',
                'analytics' => [
                    'total_streams' => $totalStreams,
                    'live_now' => $liveStreams,
                    'scheduled' => $scheduledStreams,
                    'ended' => $endedStreams,
                    'total_views' => $totalViews,
                    'average_peak_viewers' => $averagePeakViewers
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Internal server error', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete a stream (admin override)
     */
    public function deleteStream(Request $request, $streamId)
    {
        try {
            // Authenticate admin
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
            if (!$user || $user['role'] !== 'admin') {
                return response()->json(['status' => 'error', 'message' => 'Access denied. Admins only.'], 403);
            }

            // Validate stream ID
            try {
                $objectId = new ObjectId($streamId);
            } catch (\Exception $e) {
                return response()->json(['status' => 'error', 'message' => 'Invalid stream ID'], 422);
            }

            // Delete stream
            $deleteResult = $this->mongo->deleteOne('live_streams', ['_id' => $objectId]);

            if ($deleteResult->getDeletedCount() > 0) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Stream deleted successfully'
                ], 200);
            }

            return response()->json(['status' => 'error', 'message' => 'Stream not found'], 404);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Internal server error', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Initiate YouTube OAuth connection - FIX redirect URI
     */
    public function initiateYouTubeConnection(Request $request)
    {
        try {
            // Authenticate admin
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
            if (!$user || $user['role'] !== 'admin') {
                return response()->json(['status' => 'error', 'message' => 'Access denied. Admins only.'], 403);
            }

            // Store admin user ID in session for callback
            $stateToken = bin2hex(random_bytes(32));

            // Store state token temporarily
            $this->mongo->insertOne('oauth_states', [
                'state_token' => $stateToken,
                'admin_id' => $session['user_id'],
                'created_at' => new \DateTime(),
                'expires_at' => new \DateTime('+1 hour')
            ]);

            // Use EXISTING Google OAuth credentials
            $clientId = env('GOOGLE_CLIENT_ID');

            // FIX: Use consistent redirect URI (localhost, not 127.0.0.1)
            $redirectUri = 'http://localhost:8000/api/admin/youtube/callback';

            \Log::info('YouTube OAuth Init', [
                'client_id' => $clientId,
                'redirect_uri' => $redirectUri
            ]);

            // YouTube-specific scopes
            $scope = 'https://www.googleapis.com/auth/youtube ' .
                     'https://www.googleapis.com/auth/youtube.force-ssl ' .
                     'https://www.googleapis.com/auth/youtube.upload';

            $authUrl = "https://accounts.google.com/o/oauth2/v2/auth?" . http_build_query([
                'client_id' => $clientId,
                'redirect_uri' => $redirectUri,
                'response_type' => 'code',
                'scope' => $scope,
                'access_type' => 'offline',
                'state' => $stateToken,
                'prompt' => 'consent'
            ]);

            return response()->json([
                'status' => 'success',
                'auth_url' => $authUrl
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Internal server error', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Handle YouTube OAuth callback - FIX token exchange
     */
    public function handleYouTubeCallback(Request $request)
    {
        try {
            $code = $request->query('code');
            $state = $request->query('state');
            $error = $request->query('error');

            // Log all incoming params for debugging
            \Log::info('YouTube OAuth Callback', [
                'has_code' => !empty($code),
                'has_state' => !empty($state),
                'error' => $error,
                'all_params' => $request->all()
            ]);

            // Handle user denying access
            if ($error) {
                \Log::warning('YouTube OAuth error', ['error' => $error]);
                return redirect('/admin/video-stream-management?error=' . urlencode($error));
            }

            if (!$code || !$state) {
                \Log::error('Missing OAuth parameters', [
                    'has_code' => !empty($code),
                    'has_state' => !empty($state)
                ]);
                return redirect('/admin/video-stream-management?error=missing_code');
            }

            // Verify state token
            $oauthState = $this->mongo->findOne('oauth_states', ['state_token' => $state]);
            if (!$oauthState) {
                \Log::error('Invalid OAuth state token', ['state' => $state]);
                return redirect('/admin/video-stream-management?error=invalid_state');
            }

            // Exchange code for tokens - FIX: Use consistent redirect URI
            $clientId = env('GOOGLE_CLIENT_ID');
            $clientSecret = env('GOOGLE_CLIENT_SECRET');

            // IMPORTANT: Must match exactly what was used in initiateYouTubeConnection
            $redirectUri = 'http://localhost:8000/api/admin/youtube/callback';

            \Log::info('Token exchange attempt', [
                'redirect_uri' => $redirectUri,
                'client_id' => $clientId,
                'has_code' => !empty($code),
                'has_secret' => !empty($clientSecret)
            ]);

            // Use asForm() for correct content type
            $tokenResponse = \Http::timeout(30)->asForm()->post('https://oauth2.googleapis.com/token', [
                'code' => $code,
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'redirect_uri' => $redirectUri,
                'grant_type' => 'authorization_code'
            ]);

            if (!$tokenResponse->successful()) {
                \Log::error('Token exchange failed', [
                    'status' => $tokenResponse->status(),
                    'body' => $tokenResponse->body(),
                    'redirect_uri' => $redirectUri
                ]);

                $errorDetails = $tokenResponse->json();
                $errorMessage = $errorDetails['error_description'] ?? $errorDetails['error'] ?? 'Unknown error';

                return redirect('/admin/video-stream-management?error=token_exchange_failed&details=' . urlencode($errorMessage));
            }

            $tokens = $tokenResponse->json();

            \Log::info('Token exchange success', [
                'has_access_token' => isset($tokens['access_token']),
                'has_refresh_token' => isset($tokens['refresh_token']),
                'expires_in' => $tokens['expires_in'] ?? null
            ]);

            $accessToken = $tokens['access_token'] ?? null;
            $refreshToken = $tokens['refresh_token'] ?? null;
            $expiresIn = $tokens['expires_in'] ?? 3600;

            if (!$accessToken) {
                \Log::error('No access token in response', ['tokens' => $tokens]);
                return redirect('/admin/video-stream-management?error=no_access_token');
            }

            // Get YouTube channel info
            $channelResponse = \Http::timeout(30)->withToken($accessToken)->get('https://www.googleapis.com/youtube/v3/channels', [
                'part' => 'snippet,contentDetails',
                'mine' => 'true'
            ]);

            if (!$channelResponse->successful()) {
                \Log::error('Failed to fetch YouTube channel', [
                    'status' => $channelResponse->status(),
                    'body' => $channelResponse->body()
                ]);
                return redirect('/admin/video-stream-management?error=channel_fetch_failed');
            }

            $channelData = $channelResponse->json();
            $channel = $channelData['items'][0] ?? null;

            if (!$channel) {
                \Log::error('No YouTube channel found', ['response' => $channelData]);
                return redirect('/admin/video-stream-management?error=no_channel');
            }

            // Store YouTube credentials
            $sriLankaTimezone = new \DateTimeZone('Asia/Colombo');
            $currentTime = new \DateTime('now', $sriLankaTimezone);
            $expiresAt = clone $currentTime;
            $expiresAt->modify("+{$expiresIn} seconds");

            $youtubeConfig = [
                'channel_id' => $channel['id'],
                'channel_name' => $channel['snippet']['title'],
                'channel_url' => 'https://youtube.com/channel/' . $channel['id'],
                'access_token' => encrypt($accessToken),
                'refresh_token' => $refreshToken ? encrypt($refreshToken) : null,
                'token_expires_at' => $expiresAt->format('Y-m-d H:i:s'),
                'connected_at' => $currentTime->format('Y-m-d H:i:s'),
                'connected_by' => $oauthState['admin_id'],
                'is_active' => true
            ];

            // Update or insert system settings
            $this->mongo->updateOne(
                'system_settings',
                ['setting_key' => 'youtube_channel'],
                ['$set' => [
                    'setting_key' => 'youtube_channel',
                    'youtube_config' => $youtubeConfig,
                    'updated_at' => $currentTime->format('Y-m-d H:i:s')
                ]],
                ['upsert' => true]
            );

            // NEW: Sync existing YouTube playlists from the connected channel
            $this->syncYouTubePlaylists($accessToken, $currentTime);

            // Clean up oauth state
            $this->mongo->deleteOne('oauth_states', ['state_token' => $state]);

            \Log::info('YouTube connection successful', [
                'channel_id' => $channel['id'],
                'channel_name' => $channel['snippet']['title']
            ]);

            return redirect('/admin/video-stream-management?success=connected');

        } catch (\Exception $e) {
            \Log::error('YouTube callback exception', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect('/admin/video-stream-management?error=exception&message=' . urlencode($e->getMessage()));
        }
    }

    /**
     * Sync existing YouTube playlists from connected channel
     */
    private function syncYouTubePlaylists($accessToken, $currentTime)
    {
        try {
            // Fetch all playlists from YouTube channel
            $playlistsResponse = \Http::withToken($accessToken)->get('https://www.googleapis.com/youtube/v3/playlists', [
                'part' => 'snippet,contentDetails',
                'mine' => 'true',
                'maxResults' => 50
            ]);

            if (!$playlistsResponse->successful()) {
                \Log::warning('Failed to fetch YouTube playlists', [
                    'status' => $playlistsResponse->status(),
                    'body' => $playlistsResponse->body()
                ]);
                return;
            }

            $playlists = $playlistsResponse->json()['items'] ?? [];

            \Log::info('Syncing YouTube playlists', ['count' => count($playlists)]);

            foreach ($playlists as $playlist) {
                $playlistId = $playlist['id'];
                $playlistTitle = $playlist['snippet']['title'] ?? 'Untitled Playlist';
                $playlistUrl = 'https://www.youtube.com/playlist?list=' . $playlistId;

                // Try to match playlist title with course name
                $courses = $this->mongo->find('courses', []);
                $matchedCourse = null;

                foreach ($courses as $course) {
                    $courseArray = is_array($course) ? $course : (array)$course;
                    $courseTitle = $courseArray['title'] ?? '';

                    // Check if playlist title contains course title
                    if (stripos($playlistTitle, $courseTitle) !== false) {
                        $matchedCourse = $courseArray;
                        break;
                    }
                }

                // If no match, create generic entry
                $courseId = $matchedCourse ? (string)$matchedCourse['_id'] : 'youtube_' . $playlistId;
                $courseName = $matchedCourse ? $matchedCourse['title'] : $playlistTitle;

                // Fetch video count for this playlist
                $videoCount = $playlist['contentDetails']['itemCount'] ?? 0;

                // Store or update playlist in database
                $this->mongo->updateOne(
                    'course_playlists',
                    ['youtube_playlist_id' => $playlistId],
                    ['$set' => [
                        'course_id' => $courseId,
                        'course_name' => $courseName,
                        'youtube_playlist_id' => $playlistId,
                        'playlist_url' => $playlistUrl,
                        'video_count' => $videoCount,
                        'synced_from_youtube' => true,
                        'created_at' => $currentTime->format('Y-m-d H:i:s'),
                        'updated_at' => $currentTime->format('Y-m-d H:i:s')
                    ]],
                    ['upsert' => true]
                );

                \Log::info('Synced playlist', [
                    'playlist_id' => $playlistId,
                    'course_name' => $courseName,
                    'video_count' => $videoCount
                ]);
            }

            \Log::info('YouTube playlist sync completed', ['synced' => count($playlists)]);

        } catch (\Exception $e) {
            \Log::error('Error syncing YouTube playlists', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Disconnect YouTube channel and revoke all permissions
     * Also clear all related playlist and video data
     */
    public function disconnectYouTube(Request $request)
    {
        try {
            // 1. Authenticate admin
            $token = $request->header('Authorization');
            if (!$token) {
                return response()->json(['status' => 'error', 'message' => 'Authentication token not provided'], 401);
            }
            $token = str_replace('Bearer ', '', $token);

            $this->mongo = new MongoService();
            $session = $this->mongo->findSessionByToken($token);
            if (!$session) {
                return response()->json(['status' => 'error', 'message' => 'Invalid session'], 401);
            }

            $user = $this->mongo->findOne('users', ['_id' => new ObjectId($session['user_id'])]);
            if (!$user || ($user['role'] ?? '') !== 'admin') {
                return response()->json(['status' => 'error', 'message' => 'Access denied'], 403);
            }

            // 2. Read existing YouTube settings
            $youtubeSettings = $this->mongo->findOne('system_settings', ['setting_key' => 'youtube_channel']);
            if (!$youtubeSettings || !isset($youtubeSettings['youtube_config'])) {
                return response()->json(['status' => 'success', 'message' => 'YouTube already disconnected or not configured'], 200);
            }

            $config = $youtubeSettings['youtube_config'];

            // 3. Try to revoke access token (if present)
            if (!empty($config['access_token'])) {
                try {
                    $accessToken = decrypt($config['access_token']);
                    \Http::asForm()->post('https://oauth2.googleapis.com/revoke', [
                        'token' => $accessToken
                    ]);
                    \Log::info('YouTube access token revoked by admin', ['admin_id' => $session['user_id']]);
                } catch (\Exception $e) {
                    \Log::warning('Failed to revoke YouTube access token', ['error' => $e->getMessage()]);
                }
            }

            // 4. Mark YouTube as disconnected but DO NOT delete videos/playlists/live_streams
            $sriLankaTimezone = new \DateTimeZone('Asia/Colombo');
            $currentTime = new \DateTime('now', $sriLankaTimezone);

            $update = [
                '$set' => [
                    'youtube_config.is_active' => false,
                    'youtube_config.disconnected_at' => $currentTime->format('Y-m-d H:i:s'),
                    'youtube_config.updated_at' => $currentTime->format('Y-m-d H:i:s')
                ],
                // remove only the live access token so it cannot be used; keep refresh_token/ids for reconnection
                '$unset' => [
                    'youtube_config.access_token' => ''
                ]
            ];

            $this->mongo->updateOne('system_settings', ['setting_key' => 'youtube_channel'], $update);

            \Log::info('YouTube disconnected (left videos/playlists/livestreams intact)', ['admin_id' => $session['user_id']]);

            return response()->json([
                'status' => 'success',
                'message' => 'YouTube disconnected. Existing videos, playlists and live streams are preserved and will be available after reconnection.'
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error in disconnectYouTube', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['status' => 'error', 'message' => 'Internal server error', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get playlist analytics for admin dashboard
     */
    public function getPlaylistAnalytics(Request $request)
    {
        try {
            // Authenticate admin
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
            if (!$user || $user['role'] !== 'admin') {
                return response()->json(['status' => 'error', 'message' => 'Access denied. Admins only.'], 403);
            }

            // Fetch all course playlists
            $playlists = $this->mongo->find('course_playlists', [], ['sort' => ['created_at' => -1]]);

            $playlistAnalytics = [];
            $totalPlaylists = 0;
            $totalVideosInPlaylists = 0;
            $totalPlaylistViews = 0;

            foreach ($playlists as $playlist) {
                $playlistArray = is_array($playlist) ? $playlist : (array)$playlist;
                $totalPlaylists++;

                // Count videos in this playlist
                $videosInPlaylist = $this->mongo->find('videos', [
                    'youtube_playlist_id' => $playlistArray['youtube_playlist_id'] ?? '',
                    'status' => ['$ne' => 'deleted']
                ]);

                $videoCount = 0;
                $playlistViews = 0;
                $latestVideo = null;
                $videoTitles = [];

                foreach ($videosInPlaylist as $video) {
                    $videoArray = is_array($video) ? $video : (array)$video;
                    $videoCount++;
                    $playlistViews += $videoArray['total_views'] ?? 0;
                    $videoTitles[] = $videoArray['title'] ?? 'Untitled';

                    if (!$latestVideo || ($videoArray['created_at'] ?? '') > ($latestVideo['created_at'] ?? '')) {
                        $latestVideo = $videoArray;
                    }
                }

                $totalVideosInPlaylists += $videoCount;
                $totalPlaylistViews += $playlistViews;

                $playlistAnalytics[] = [
                    'playlist_id' => $playlistArray['youtube_playlist_id'] ?? '',
                    'course_id' => $playlistArray['course_id'] ?? '',
                    'course_name' => $playlistArray['course_name'] ?? 'Unknown Course',
                    'playlist_url' => $playlistArray['playlist_url'] ?? '',
                    'video_count' => $videoCount,
                    'total_views' => $playlistViews,
                    'average_views_per_video' => $videoCount > 0 ? round($playlistViews / $videoCount, 2) : 0,
                    'created_at' => $playlistArray['created_at'] ?? '',
                    'latest_video' => $latestVideo ? [
                        'title' => $latestVideo['title'] ?? 'Untitled',
                        'uploaded_at' => $latestVideo['created_at'] ?? '',
                        'views' => $latestVideo['total_views'] ?? 0
                    ] : null,
                    'sample_videos' => array_slice($videoTitles, 0, 3) // First 3 video titles
                ];
            }

            // Calculate overall statistics
            $averageVideosPerPlaylist = $totalPlaylists > 0 ? round($totalVideosInPlaylists / $totalPlaylists, 2) : 0;
            $averageViewsPerPlaylist = $totalPlaylists > 0 ? round($totalPlaylistViews / $totalPlaylists, 2) : 0;

            return response()->json([
                'status' => 'success',
                'summary' => [
                    'total_playlists' => $totalPlaylists,
                    'total_videos' => $totalVideosInPlaylists,
                    'total_views' => $totalPlaylistViews,
                    'average_videos_per_playlist' => $averageVideosPerPlaylist,
                    'average_views_per_playlist' => $averageViewsPerPlaylist
                ],
                'playlists' => $playlistAnalytics
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Internal server error', 'error' => $e->getMessage()], 500);
        }
    }
}
