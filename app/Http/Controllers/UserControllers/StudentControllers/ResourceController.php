<?php

namespace App\Http\Controllers\UserControllers\StudentControllers;

use App\Http\Controllers\Controller;
use App\Services\MongoService;
use Illuminate\Http\Request;
use MongoDB\BSON\ObjectId;
use Illuminate\Support\Facades\Http;

class ResourceController extends Controller
{
    private $mongo;

    public function __construct()
    {
        $this->mongo = new MongoService();
    }

    /**
     * Get resources grouped by enrolled courses - REWRITTEN based on VideosController pattern
     */
    public function getResources(Request $request)
    {
        try {
            // Step 1: Authenticate student (same as VideosController)
            $token = $request->header('Authorization');
            if (!$token) {
                return response()->json(['status' => 'error', 'message' => 'Authentication token not provided'], 401);
            }
            $token = str_replace('Bearer ', '', $token);

            $session = $this->mongo->findSessionByToken($token);
            if (!$session) {
                return response()->json(['status' => 'error', 'message' => 'Invalid session'], 401);
            }

            // Step 2: Verify student role (same as VideosController)
            $user = $this->mongo->findOne('users', ['_id' => new ObjectId($session['user_id'])]);
            if (!$user || $user['role'] !== 'assigned_student') {
                return response()->json(['status' => 'error', 'message' => 'Access denied'], 403);
            }

            // FIX: Use enrolled_courses instead of courses
            $enrolledCoursesRaw = $user['enrolled_courses'] ?? [];
            $enrolledCourses = [];
            $enrolledCourseIds = [];

            \Log::info('Student fetching resources', [
                'user_id' => $session['user_id'],
                'has_courses' => isset($user['courses']),
                'courses_raw' => $enrolledCoursesRaw
            ]);

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

            \Log::info('Enrolled courses extracted', [
                'count' => count($enrolledCourseIds),
                'course_ids' => $enrolledCourseIds
            ]);

            if (empty($enrolledCourseIds)) {
                return response()->json([
                    'status' => 'success',
                    'courses' => [],
                    'enrolled_courses' => [],
                    'message' => 'No enrolled courses found'
                ], 200);
            }

            // Step 4: Fetch resources for enrolled courses (same query pattern as VideosController)
            $resources = $this->mongo->find('resources', [
                'course_id' => ['$in' => $enrolledCourseIds]
            ], ['sort' => ['uploaded_at' => -1]]);

            \Log::info('Resources query executed', [
                'query_course_ids' => $enrolledCourseIds
            ]);

            // Step 5: Group resources by course (same as video grouping logic)
            $groupedByCourse = [];
            $totalResourceCount = 0;

            foreach ($resources as $resource) {
                $resourceArray = is_array($resource) ? $resource : (array)$resource;

                $courseId = $resourceArray['course_id'] ?? null;

                \Log::info('Processing resource', [
                    'resource_id' => (string)($resourceArray['_id'] ?? ''),
                    'course_id' => $courseId,
                    'title' => $resourceArray['title'] ?? '',
                    'file_name' => $resourceArray['file_name'] ?? ''
                ]);

                if (!$courseId || !in_array($courseId, $enrolledCourseIds)) {
                    \Log::warning('Resource not in enrolled courses', [
                        'resource_course_id' => $courseId,
                        'enrolled_ids' => $enrolledCourseIds
                    ]);
                    continue;
                }

                // Initialize course group if not exists
                if (!isset($groupedByCourse[$courseId])) {
                    // Find course title from enrolled courses
                    $courseTitle = 'Unknown Course';
                    foreach ($enrolledCourses as $ec) {
                        if ($ec['id'] === $courseId) {
                            $courseTitle = $ec['title'];
                            break;
                        }
                    }

                    $groupedByCourse[$courseId] = [
                        'id' => $courseId,
                        'title' => $courseTitle,
                        'resources' => []
                    ];
                }

                // Add resource to group
                $groupedByCourse[$courseId]['resources'][] = [
                    'id' => (string)($resourceArray['_id'] ?? ''),
                    'title' => $resourceArray['title'] ?? 'Untitled',
                    'description' => $resourceArray['description'] ?? '',
                    'course_id' => $courseId,
                    'course_name' => $resourceArray['course_name'] ?? $groupedByCourse[$courseId]['title'],
                    'lecturer_name' => $resourceArray['lecturer_name'] ?? 'Unknown Lecturer',
                    'drive_file_id' => $resourceArray['drive_file_id'] ?? '',
                    'drive_file_url' => $resourceArray['drive_file_url'] ?? '',
                    'drive_folder_id' => $resourceArray['drive_folder_id'] ?? '', // Course subfolder
                    'file_name' => $resourceArray['file_name'] ?? 'unknown',
                    'file_size' => $resourceArray['file_size'] ?? 0,
                    'file_type' => $resourceArray['file_type'] ?? '',
                    'mime_type' => $resourceArray['mime_type'] ?? '',
                    'uploaded_at' => $resourceArray['uploaded_at'] ?? '',
                    'updated_at' => $resourceArray['updated_at'] ?? ''
                ];

                $totalResourceCount++;
            }

            \Log::info('Resources grouped successfully', [
                'total_courses_with_resources' => count($groupedByCourse),
                'total_resources' => $totalResourceCount,
                'courses' => array_keys($groupedByCourse)
            ]);

            // Step 6: Convert to indexed array (same as VideosController)
            $coursesWithResources = array_values($groupedByCourse);

            return response()->json([
                'status' => 'success',
                'courses' => $coursesWithResources,
                'enrolled_courses' => $enrolledCourses,
                'total_resources' => $totalResourceCount,
                'summary' => [
                    'total_courses' => count($coursesWithResources),
                    'total_resources' => $totalResourceCount,
                    'enrolled_in' => count($enrolledCourseIds)
                ]
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Student getResources error', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Internal server error',
                'error' => config('app.debug') ? $e->getMessage() : 'Failed to load resources'
            ], 500);
        }
    }

    /**
     * Download resource - with enrollment verification (same pattern as VideosController::recordView)
     */
    public function downloadResource(Request $request, $resourceId)
    {
        try {
            // Step 1: Authenticate student
            $token = $request->header('Authorization');
            if (!$token) {
                return response()->json(['status' => 'error', 'message' => 'Authentication token not provided'], 401);
            }
            $token = str_replace('Bearer ', '', $token);

            $session = $this->mongo->findSessionByToken($token);
            if (!$session) {
                return response()->json(['status' => 'error', 'message' => 'Invalid session'], 401);
            }

            // Step 2: Verify student role
            $user = $this->mongo->findOne('users', ['_id' => new ObjectId($session['user_id'])]);
            if (!$user || $user['role'] !== 'assigned_student') {
                return response()->json(['status' => 'error', 'message' => 'Access denied'], 403);
            }

            // Step 3: Validate resource ID
            try {
                $objectId = new ObjectId($resourceId);
            } catch (\Exception $e) {
                return response()->json(['status' => 'error', 'message' => 'Invalid resource ID'], 422);
            }

            // Step 4: Get resource
            $resource = $this->mongo->findOne('resources', ['_id' => $objectId]);

            if (!$resource) {
                return response()->json(['status' => 'error', 'message' => 'Resource not found'], 404);
            }

            // Step 5: Extract student's enrolled course IDs (same as getResources)
            $enrolledCourseIds = [];
            $enrolledCoursesRaw = $user['enrolled_courses'] ?? []; // Changed to match VideosController

            if (is_iterable($enrolledCoursesRaw)) {
                foreach ($enrolledCoursesRaw as $courseDoc) {
                    $courseArray = is_array($courseDoc) ? $courseDoc : (array)$courseDoc;
                    if (isset($courseArray['id'])) {
                        $enrolledCourseIds[] = $courseArray['id'];
                    }
                }
            }

            // Step 6: Verify student is enrolled in resource's course
            $resourceCourseId = $resource['course_id'] ?? null;

            if (!$resourceCourseId) {
                \Log::error('Resource has no course_id', ['resource_id' => $resourceId]);
                return response()->json(['status' => 'error', 'message' => 'Resource has no associated course'], 500);
            }

            if (!in_array($resourceCourseId, $enrolledCourseIds)) {
                \Log::warning('Student attempted unauthorized resource download', [
                    'student_id' => $session['user_id'],
                    'resource_id' => $resourceId,
                    'resource_course_id' => $resourceCourseId,
                    'enrolled_courses' => $enrolledCourseIds
                ]);
                return response()->json(['status' => 'error', 'message' => 'You are not enrolled in this course'], 403);
            }

            // Step 7: Return download information
            \Log::info('Student downloading resource', [
                'student_id' => $session['user_id'],
                'resource_id' => $resourceId,
                'file_name' => $resource['file_name'] ?? 'unknown',
                'drive_folder_id' => $resource['drive_folder_id'] ?? 'none'
            ]);

            return response()->json([
                'status' => 'success',
                'drive_url' => $resource['drive_file_url'] ?? '',
                'file_name' => $resource['file_name'] ?? 'download',
                'drive_file_id' => $resource['drive_file_id'] ?? '',
                'drive_folder_id' => $resource['drive_folder_id'] ?? '' // Course subfolder ID
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Student downloadResource error', [
                'error' => $e->getMessage(),
                'resource_id' => $resourceId ?? 'unknown',
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to process download',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Check if student has connected their Google Drive
     */
    public function checkDriveStatus(Request $request)
    {
        try {
            $token = $this->getToken($request);
            $session = $this->mongo->findSessionByToken($token);
            if (!$session) return response()->json(['status' => 'error', 'message' => 'Invalid session'], 401);

            $user = $this->mongo->findOne('users', ['_id' => new ObjectId($session['user_id'])]);

            $connected = isset($user['drive_config']['access_token']);

            return response()->json([
                'status' => 'success',
                'connected' => $connected,
                'email' => $user['drive_config']['email'] ?? null
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Initiate Google Drive OAuth for student - FIXED redirect URI
     */
    public function initiateDriveConnection(Request $request)
    {
        try {
            $token = $this->getToken($request);
            $session = $this->mongo->findSessionByToken($token);
            if (!$session) return response()->json(['status' => 'error', 'message' => 'Invalid session'], 401);

            $stateToken = bin2hex(random_bytes(32));
            $this->mongo->insertOne('oauth_states', [
                'state_token' => $stateToken,
                'user_id' => $session['user_id'],
                'type' => 'student_drive',
                'created_at' => new \DateTime()
            ]);

            $clientId = env('GOOGLE_CLIENT_ID');

            // FIXED: Use consistent URI format matching Google Console
            // Use localhost (not 127.0.0.1) and ensure it matches exactly
            $redirectUri = 'http://localhost:8000/api/student/resources/drive/callback';

            \Log::info('Student Drive OAuth Init', [
                'redirect_uri' => $redirectUri,
                'state_token' => $stateToken
            ]);

            $scope = 'https://www.googleapis.com/auth/drive.file https://www.googleapis.com/auth/userinfo.email';

            $authUrl = "https://accounts.google.com/o/oauth2/v2/auth?" . http_build_query([
                'client_id' => $clientId,
                'redirect_uri' => $redirectUri,
                'response_type' => 'code',
                'scope' => $scope,
                'access_type' => 'offline',
                'state' => $stateToken,
                'prompt' => 'consent'
            ]);

            return response()->json(['status' => 'success', 'auth_url' => $authUrl]);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Handle OAuth callback - FIXED redirect URI
     */
    public function handleDriveCallback(Request $request)
    {
        try {
            $code = $request->code;
            $state = $request->state;

            $oauthState = $this->mongo->findOne('oauth_states', ['state_token' => $state, 'type' => 'student_drive']);
            if (!$oauthState) return redirect('/student/resource-management?error=invalid_state');

            // FIXED: Use exact same redirect URI as in initiateDriveConnection
            $redirectUri = 'http://localhost:8000/api/student/resources/drive/callback';

            \Log::info('Student Drive Token Exchange', [
                'redirect_uri' => $redirectUri,
                'has_code' => !empty($code)
            ]);

            $response = Http::timeout(30)->asForm()->post('https://oauth2.googleapis.com/token', [
                'code' => $code,
                'client_id' => env('GOOGLE_CLIENT_ID'),
                'client_secret' => env('GOOGLE_CLIENT_SECRET'),
                'redirect_uri' => $redirectUri,
                'grant_type' => 'authorization_code'
            ]);

            if (!$response->successful()) {
                \Log::error('Student Drive token exchange failed', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return redirect('/student/resource-management?error=token_failed');
            }

            $tokens = $response->json();
            if (!isset($tokens['access_token'])) return redirect('/student/resource-management?error=token_failed');

            // Get user email
            $userRes = Http::withToken($tokens['access_token'])->get('https://www.googleapis.com/oauth2/v2/userinfo');
            $email = $userRes->json()['email'] ?? 'Unknown';

            // Save to user profile
            $driveConfig = [
                'access_token' => encrypt($tokens['access_token']),
                'refresh_token' => isset($tokens['refresh_token']) ? encrypt($tokens['refresh_token']) : null,
                'email' => $email,
                'connected_at' => date('Y-m-d H:i:s')
            ];

            $this->mongo->updateOne('users',
                ['_id' => new ObjectId($oauthState['user_id'])],
                ['$set' => ['drive_config' => $driveConfig]]
            );

            $this->mongo->deleteOne('oauth_states', ['_id' => $oauthState['_id']]);

            return redirect('/student/resource-management?success=drive_connected');

        } catch (\Exception $e) {
            \Log::error('Student Drive callback error', [
                'error' => $e->getMessage()
            ]);
            return redirect('/student/resource-management?error=' . urlencode($e->getMessage()));
        }
    }

    /**
     * Save resource to student's Google Drive - FIXED: Download and re-upload
     */
    public function saveToDrive(Request $request, $id)
    {
        try {
            $token = $this->getToken($request);
            $session = $this->mongo->findSessionByToken($token);
            if (!$session) return response()->json(['status' => 'error', 'message' => 'Invalid session'], 401);

            // Get student user
            $user = $this->mongo->findOne('users', ['_id' => new ObjectId($session['user_id'])]);
            if (!$user || $user['role'] !== 'assigned_student') {
                return response()->json(['status' => 'error', 'message' => 'Access denied'], 403);
            }

            // Get resource
            $resource = $this->mongo->findOne('resources', ['_id' => new ObjectId($id)]);
            if (!$resource) return response()->json(['status' => 'error', 'message' => 'Resource not found'], 404);

            // Verify student is enrolled in the resource's course
            $resourceCourseId = $resource['course_id'] ?? null;
            if (!$resourceCourseId) {
                return response()->json(['status' => 'error', 'message' => 'Resource has no associated course'], 500);
            }

            $enrolledCourseIds = [];
            $enrolledCoursesRaw = $user['enrolled_courses'] ?? [];

            if (is_iterable($enrolledCoursesRaw)) {
                foreach ($enrolledCoursesRaw as $courseDoc) {
                    $courseArray = is_array($courseDoc) ? $courseDoc : (array)$courseDoc;
                    if (isset($courseArray['id'])) {
                        $enrolledCourseIds[] = $courseArray['id'];
                    }
                }
            }

            if (!in_array($resourceCourseId, $enrolledCourseIds)) {
                \Log::warning('Student attempted to save resource from unenrolled course', [
                    'student_id' => $session['user_id'],
                    'resource_id' => $id,
                    'resource_course_id' => $resourceCourseId
                ]);
                return response()->json(['status' => 'error', 'message' => 'You are not enrolled in this course'], 403);
            }

            // Get student Drive token
            if (!isset($user['drive_config']['access_token'])) {
                return response()->json(['status' => 'error', 'message' => 'Please connect your Google Drive first'], 400);
            }

            $studentAccessToken = decrypt($user['drive_config']['access_token']);

            // Check if token is expired and refresh if needed
            $tokenExpiresAt = $user['drive_config']['token_expires_at'] ?? null;
            if ($tokenExpiresAt) {
                $expiryTime = new \DateTime($tokenExpiresAt, new \DateTimeZone('Asia/Colombo'));
                $now = new \DateTime('now', new \DateTimeZone('Asia/Colombo'));

                if ($now > $expiryTime) {
                    $refreshToken = isset($user['drive_config']['refresh_token']) ? decrypt($user['drive_config']['refresh_token']) : null;

                    if ($refreshToken) {
                        $studentAccessToken = $this->refreshDriveToken($refreshToken, $session['user_id']);
                        if (!$studentAccessToken) {
                            return response()->json(['status' => 'error', 'message' => 'Drive token expired. Please reconnect your Google Drive.'], 401);
                        }
                    } else {
                        return response()->json(['status' => 'error', 'message' => 'Drive token expired. Please reconnect your Google Drive.'], 401);
                    }
                }
            }

            // Get admin Drive token to download the file
            $driveSettings = $this->mongo->findOne('system_settings', ['setting_key' => 'google_drive']);
            if (!$driveSettings || !isset($driveSettings['drive_config']['access_token'])) {
                return response()->json(['status' => 'error', 'message' => 'System Drive not configured, Contact the Admin'], 500);
            }

            $adminAccessToken = decrypt($driveSettings['drive_config']['access_token']);

            // Get/Create root folder "Smart LMS Resources" in student's Drive
            $rootId = $this->getOrCreateFolder($studentAccessToken, 'Smart LMS Resources');
            if (!$rootId) {
                return response()->json(['status' => 'error', 'message' => 'Failed to create root folder in your Drive'], 500);
            }

            // Get/Create course folder in student's Drive
            $courseName = $resource['course_name'] ?? 'General';
            $courseFolderId = $this->getOrCreateFolder($studentAccessToken, $courseName, $rootId);
            if (!$courseFolderId) {
                return response()->json(['status' => 'error', 'message' => 'Failed to create course folder in your Drive'], 500);
            }

            // FIXED: Download file from admin's Drive and re-upload to student's Drive
            $adminFileId = $resource['drive_file_id'];
            $fileName = $resource['file_name'];
            $mimeType = $resource['mime_type'] ?? 'application/octet-stream';

            \Log::info('Starting file copy process', [
                'admin_file_id' => $adminFileId,
                'file_name' => $fileName,
                'mime_type' => $mimeType,
                'student_folder_id' => $courseFolderId
            ]);

            // Step 1: Download file content from admin's Drive
            $downloadUrl = "https://www.googleapis.com/drive/v3/files/{$adminFileId}?alt=media";

            $downloadResponse = \Http::timeout(300)->withToken($adminAccessToken)->get($downloadUrl);

            if (!$downloadResponse->successful()) {
                \Log::error('Failed to download file from admin Drive', [
                    'status' => $downloadResponse->status(),
                    'admin_file_id' => $adminFileId
                ]);
                return response()->json(['status' => 'error', 'message' => 'Failed to access source file'], 500);
            }

            $fileContent = $downloadResponse->body();
            $fileSize = strlen($fileContent);

            \Log::info('File downloaded from admin Drive', [
                'file_size' => $fileSize,
                'file_name' => $fileName
            ]);

            // Step 2: Upload file to student's Drive using resumable upload
            $uploadedFileId = $this->uploadFileToStudentDrive(
                $studentAccessToken,
                $fileContent,
                $fileName,
                $mimeType,
                $courseFolderId
            );

            if (!$uploadedFileId) {
                return response()->json(['status' => 'error', 'message' => 'Failed to upload file to your Drive'], 500);
            }

            \Log::info('Student saved resource to Drive successfully', [
                'student_id' => $session['user_id'],
                'resource_id' => $id,
                'course_name' => $courseName,
                'new_file_id' => $uploadedFileId
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Saved to your Google Drive!',
                'file_id' => $uploadedFileId
            ]);

        } catch (\Exception $e) {
            \Log::error('saveToDrive exception', [
                'student_id' => $session['user_id'] ?? 'unknown',
                'resource_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['status' => 'error', 'message' => 'Internal server error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * ADDED: Upload file to student's Drive using resumable upload
     */
    private function uploadFileToStudentDrive($accessToken, $fileContent, $fileName, $mimeType, $parentFolderId)
    {
        try {
            $fileSize = strlen($fileContent);

            \Log::info('Starting resumable upload to student Drive', [
                'file_name' => $fileName,
                'file_size' => $fileSize,
                'mime_type' => $mimeType,
                'parent_folder' => $parentFolderId
            ]);

            // Step 1: Initiate resumable upload session
            $metadata = json_encode([
                'name' => $fileName,
                'parents' => [$parentFolderId],
                'mimeType' => $mimeType
            ]);

            $ch = curl_init('https://www.googleapis.com/upload/drive/v3/files?uploadType=resumable');
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $accessToken,
                    'Content-Type: application/json; charset=UTF-8',
                    'X-Upload-Content-Type: ' . $mimeType,
                    'X-Upload-Content-Length: ' . $fileSize
                ],
                CURLOPT_POSTFIELDS => $metadata,
                CURLOPT_HEADER => true,
                CURLOPT_NOBODY => false,
                CURLOPT_TIMEOUT => 60
            ]);

            $initResponse = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $headers = substr($initResponse, 0, $headerSize);
            curl_close($ch);

            \Log::info('Upload session init response', [
                'http_code' => $httpCode,
                'headers_preview' => substr($headers, 0, 300)
            ]);

            if ($httpCode !== 200) {
                \Log::error('Failed to initiate upload session', [
                    'http_code' => $httpCode,
                    'response' => substr($initResponse, $headerSize, 500)
                ]);
                return null;
            }

            // Extract upload URL from Location header
            $uploadUrl = null;
            if (preg_match('/Location:\s*(.+?)[\r\n]/i', $headers, $matches)) {
                $uploadUrl = trim($matches[1]);
            }

            if (!$uploadUrl) {
                \Log::error('No upload URL in init response', ['headers' => $headers]);
                return null;
            }

            \Log::info('Upload URL obtained', ['url' => substr($uploadUrl, 0, 100)]);

            // Step 2: Upload file content in one PUT request (for files < 5MB)
            $ch = curl_init($uploadUrl);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CUSTOMREQUEST => 'PUT',
                CURLOPT_HTTPHEADER => [
                    'Content-Length: ' . $fileSize,
                    'Content-Type: ' . $mimeType
                ],
                CURLOPT_POSTFIELDS => $fileContent,
                CURLOPT_TIMEOUT => 300
            ]);

            $uploadResponse = curl_exec($ch);
            $uploadHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            \Log::info('Upload response', [
                'http_code' => $uploadHttpCode,
                'response_preview' => substr($uploadResponse, 0, 200)
            ]);

            if ($uploadHttpCode === 200 || $uploadHttpCode === 201) {
                $responseData = json_decode($uploadResponse, true);
                $fileId = $responseData['id'] ?? null;

                \Log::info('File uploaded successfully to student Drive', [
                    'file_id' => $fileId,
                    'file_name' => $fileName
                ]);

                return $fileId;
            } else {
                \Log::error('Upload failed', [
                    'http_code' => $uploadHttpCode,
                    'response' => $uploadResponse
                ]);
                return null;
            }

        } catch (\Exception $e) {
            \Log::error('Error in uploadFileToStudentDrive', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    /**
     * ADDED: Refresh expired Google Drive token
     */
    private function refreshDriveToken($refreshToken, $userId)
    {
        try {
            \Log::info('Attempting to refresh Drive token', ['user_id' => $userId]);

            $response = \Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'client_id' => env('GOOGLE_CLIENT_ID'),
                'client_secret' => env('GOOGLE_CLIENT_SECRET'),
                'refresh_token' => $refreshToken,
                'grant_type' => 'refresh_token'
            ]);

            if (!$response->successful()) {
                \Log::error('Token refresh failed', [
                    'user_id' => $userId,
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return null;
            }

            $tokens = $response->json();
            $newAccessToken = $tokens['access_token'] ?? null;
            $expiresIn = $tokens['expires_in'] ?? 3600;

            if (!$newAccessToken) {
                \Log::error('No access token in refresh response', ['user_id' => $userId]);
                return null;
            }

            // Update user's Drive config with new token
            $sriLankaTimezone = new \DateTimeZone('Asia/Colombo');
            $currentTime = new \DateTime('now', $sriLankaTimezone);
            $expiresAt = clone $currentTime;
            $expiresAt->modify("+{$expiresIn} seconds");

            $this->mongo->updateOne(
                'users',
                ['_id' => new ObjectId($userId)],
                ['$set' => [
                    'drive_config.access_token' => encrypt($newAccessToken),
                    'drive_config.token_expires_at' => $expiresAt->format('Y-m-d H:i:s'),
                    'drive_config.updated_at' => $currentTime->format('Y-m-d H:i:s')
                ]]
            );

            \Log::info('Drive token refreshed successfully', ['user_id' => $userId]);

            return $newAccessToken;

        } catch (\Exception $e) {
            \Log::error('Error refreshing Drive token', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Disconnect student's Google Drive
     */
    public function disconnectDrive(Request $request)
    {
        try {
            $token = $this->getToken($request);
            $session = $this->mongo->findSessionByToken($token);
            if (!$session) return response()->json(['status' => 'error', 'message' => 'Invalid session'], 401);

            $user = $this->mongo->findOne('users', ['_id' => new ObjectId($session['user_id'])]);
            if (!$user || $user['role'] !== 'assigned_student') {
                return response()->json(['status' => 'error', 'message' => 'Access denied'], 403);
            }

            // Revoke token if exists
            if (isset($user['drive_config']['access_token'])) {
                try {
                    $accessToken = decrypt($user['drive_config']['access_token']);

                    // Revoke Google OAuth token
                    \Http::asForm()->post('https://oauth2.googleapis.com/revoke', [
                        'token' => $accessToken
                    ]);

                    \Log::info('Student Drive token revoked', ['user_id' => $session['user_id']]);
                } catch (\Exception $e) {
                    \Log::warning('Failed to revoke student Drive token', [
                        'user_id' => $session['user_id'],
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // Remove drive_config from user document
            $this->mongo->updateOne(
                'users',
                ['_id' => new ObjectId($session['user_id'])],
                ['$unset' => ['drive_config' => '']]
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Google Drive disconnected successfully'
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Student disconnectDrive error', [
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
     * Get or create folder in Google Drive
     */
    private function getOrCreateFolder($token, $name, $parentId = null)
    {
        try {
            // Search for existing folder
            $query = "mimeType='application/vnd.google-apps.folder' and name='" . addslashes($name) . "' and trashed=false";
            if ($parentId) {
                $query .= " and '" . addslashes($parentId) . "' in parents";
            }

            $searchResponse = Http::withToken($token)->get('https://www.googleapis.com/drive/v3/files', [
                'q' => $query,
                'fields' => 'files(id, name)',
                'pageSize' => 1
            ]);

            if ($searchResponse->successful()) {
                $files = $searchResponse->json()['files'] ?? [];
                if (!empty($files)) {
                    \Log::info('Found existing folder', ['name' => $name, 'id' => $files[0]['id']]);
                    return $files[0]['id'];
                }
            }

            // Create new folder
            $folderMetadata = [
                'name' => $name,
                'mimeType' => 'application/vnd.google-apps.folder'
            ];

            if ($parentId) {
                $folderMetadata['parents'] = [$parentId];
            }

            $createResponse = Http::withToken($token)
                ->asJson()
                ->post('https://www.googleapis.com/drive/v3/files', $folderMetadata);

            if ($createResponse->successful()) {
                $folderId = $createResponse->json()['id'] ?? null;
                \Log::info('Created new folder', ['name' => $name, 'id' => $folderId]);
                return $folderId;
            }

            \Log::error('Failed to create folder', [
                'name' => $name,
                'status' => $createResponse->status(),
                'body' => $createResponse->body()
            ]);
            return null;

        } catch (\Exception $e) {
            \Log::error('Error in getOrCreateFolder', [
                'name' => $name,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    private function getToken($request) {
        return str_replace('Bearer ', '', $request->header('Authorization'));
    }
}
