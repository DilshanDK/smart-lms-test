<?php

namespace App\Http\Controllers\UserControllers\AdminControllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\MongoService;
use MongoDB\BSON\ObjectId;

class ResourceController extends Controller
{
    private $mongo;

    public function __construct()
    {
        $this->mongo = new MongoService();
    }

    /**
     * Get Google Drive connection status
     */
    public function getDriveStatus(Request $request)
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
                return response()->json(['status' => 'error', 'message' => 'Access denied'], 403);
            }

            // Get Google Drive settings
            $driveSettings = $this->mongo->findOne('system_settings', ['setting_key' => 'google_drive']);

            if (!$driveSettings || !isset($driveSettings['drive_config'])) {
                return response()->json([
                    'status' => 'success',
                    'connected' => false,
                    'message' => 'Google Drive not connected'
                ], 200);
            }

            $config = $driveSettings['drive_config'];

            return response()->json([
                'status' => 'success',
                'connected' => $config['is_active'] ?? false,
                'root_folder_id' => $config['root_folder_id'] ?? '',
                'connected_at' => $config['connected_at'] ?? '',
                'token_expires_at' => $config['token_expires_at'] ?? ''
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Internal server error', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Initiate Google Drive OAuth connection
     */
    public function initiateGoogleDriveConnection(Request $request)
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
                return response()->json(['status' => 'error', 'message' => 'Access denied'], 403);
            }

            // Store state token
            $stateToken = bin2hex(random_bytes(32));

            $this->mongo->insertOne('oauth_states', [
                'state_token' => $stateToken,
                'admin_id' => $session['user_id'],
                'service' => 'google_drive',
                'created_at' => new \DateTime(),
                'expires_at' => new \DateTime('+1 hour')
            ]);

            // Use existing Google OAuth credentials
            $clientId = env('GOOGLE_CLIENT_ID');

            // --- DEBUG: Log the redirect URI being used ---
            // Use request()->getSchemeAndHttpHost() for absolute URI
            $redirectUri = request()->getSchemeAndHttpHost() . '/admin/google-drive/callback';
            \Log::info('Google OAuth redirect_uri used:', ['redirect_uri' => $redirectUri]);

            // Google Drive scope
            $scope = 'https://www.googleapis.com/auth/drive.file';

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
                'auth_url' => $authUrl,
                'redirect_uri' => $redirectUri // for debugging
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Internal server error', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Handle Google Drive OAuth callback
     */
    public function handleGoogleDriveCallback(Request $request)
    {
        try {
            $code = $request->query('code');
            $state = $request->query('state');
            $error = $request->query('error');

            if ($error) {
                return redirect()->route('admin.resource-management', ['error' => $error]);
            }

            if (!$code || !$state) {
                return redirect()->route('admin.resource-management', ['error' => 'missing_code']);
            }

            // Verify state token
            $oauthState = $this->mongo->findOne('oauth_states', [
                'state_token' => $state,
                'service' => 'google_drive'
            ]);

            if (!$oauthState) {
                return redirect()->route('admin.resource-management', ['error' => 'invalid_state']);
            }

            $clientId = env('GOOGLE_CLIENT_ID');
            $clientSecret = env('GOOGLE_CLIENT_SECRET');

            // --- DEBUG: Use the same redirect URI as above ---
            $redirectUri = request()->getSchemeAndHttpHost() . '/admin/google-drive/callback';
            \Log::info('Google OAuth callback redirect_uri used:', ['redirect_uri' => $redirectUri]);

            $tokenResponse = \Http::timeout(30)->asForm()->post('https://oauth2.googleapis.com/token', [
                'code' => $code,
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'redirect_uri' => $redirectUri,
                'grant_type' => 'authorization_code'
            ]);

            if (!$tokenResponse->successful()) {
                \Log::error('Drive token exchange failed', [
                    'status' => $tokenResponse->status(),
                    'body' => $tokenResponse->body()
                ]);
                return redirect()->route('admin.resource-management', ['error' => 'token_exchange_failed']);
            }

            $tokens = $tokenResponse->json();
            $accessToken = $tokens['access_token'] ?? null;
            $refreshToken = $tokens['refresh_token'] ?? null;
            $expiresIn = $tokens['expires_in'] ?? 3600;

            if (!$accessToken) {
                return redirect()->route('admin.resource-management', ['error' => 'no_access_token']);
            }

            // Create root folder in Google Drive
            $rootFolderId = $this->createRootFolder($accessToken);

            // Store Drive credentials
            $sriLankaTimezone = new \DateTimeZone('Asia/Colombo');
            $currentTime = new \DateTime('now', $sriLankaTimezone);
            $expiresAt = clone $currentTime;
            $expiresAt->modify("+{$expiresIn} seconds");

            $driveConfig = [
                'access_token' => encrypt($accessToken),
                'refresh_token' => $refreshToken ? encrypt($refreshToken) : null,
                'token_expires_at' => $expiresAt->format('Y-m-d H:i:s'),
                'connected_at' => $currentTime->format('Y-m-d H:i:s'),
                'connected_by' => $oauthState['admin_id'],
                'root_folder_id' => $rootFolderId,
                'is_active' => true
            ];

            $this->mongo->updateOne(
                'system_settings',
                ['setting_key' => 'google_drive'],
                ['$set' => [
                    'setting_key' => 'google_drive',
                    'drive_config' => $driveConfig,
                    'updated_at' => $currentTime->format('Y-m-d H:i:s')
                ]],
                ['upsert' => true]
            );

            // Clean up oauth state
            $this->mongo->deleteOne('oauth_states', ['state_token' => $state]);

            \Log::info('Google Drive connection successful');

            return redirect()->route('admin.resource-management', ['success' => 'connected']);

        } catch (\Exception $e) {
            \Log::error('Drive callback exception', [
                'message' => $e->getMessage()
            ]);
            return redirect()->route('admin.resource-management', ['error' => 'exception']);
        }
    }

    /**
     * Create or get root folder in Google Drive
     */
    private function createRootFolder($accessToken)
    {
        try {
            // Step 1: Search for existing folder named 'Smart LMS Resources'
            $searchResponse = \Http::withToken($accessToken)
                ->get('https://www.googleapis.com/drive/v3/files', [
                    'q' => "name = 'Smart LMS Resources' and mimeType = 'application/vnd.google-apps.folder' and trashed = false",
                    'fields' => 'files(id, name)',
                    'pageSize' => 1
                ]);

            if ($searchResponse->successful()) {
                $files = $searchResponse->json()['files'] ?? [];
                if (!empty($files)) {
                    // Folder exists, return its ID
                    return $files[0]['id'];
                }
            }

            // Step 2: Folder not found, create new one
            $createResponse = \Http::withToken($accessToken)
                ->asJson()
                ->post('https://www.googleapis.com/drive/v3/files', [
                    'name' => 'Smart LMS Resources',
                    'mimeType' => 'application/vnd.google-apps.folder'
                ]);

            if ($createResponse->successful()) {
                $folderData = $createResponse->json();
                return $folderData['id'] ?? null;
            }

            return null;

        } catch (\Exception $e) {
            \Log::error('Error creating/finding root folder', [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Disconnect Google Drive - Revoke permissions and clean up
     */
    public function disconnectGoogleDrive(Request $request)
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
                return response()->json(['status' => 'error', 'message' => 'Access denied'], 403);
            }

            // Get Drive settings
            $driveSettings = $this->mongo->findOne('system_settings', ['setting_key' => 'google_drive']);

            if ($driveSettings && isset($driveSettings['drive_config']['access_token'])) {
                try {
                    $accessToken = decrypt($driveSettings['drive_config']['access_token']);
                    $rootFolderId = $driveSettings['drive_config']['root_folder_id'] ?? null;

                    // Step 1: Revoke all file permissions (remove public access)
                    if ($rootFolderId) {
                        \Log::info('Revoking permissions for Google Drive resources');
                        $this->revokeAllDrivePermissions($accessToken, $rootFolderId);
                    }

                    // Step 2: Revoke OAuth token
                    $revokeResponse = \Http::asForm()->post('https://oauth2.googleapis.com/revoke', [
                        'token' => $accessToken
                    ]);

                    if ($revokeResponse->successful()) {
                        \Log::info('Google Drive OAuth token revoked successfully');
                    } else {
                        \Log::warning('Failed to revoke Drive token', [
                            'status' => $revokeResponse->status(),
                            'body' => $revokeResponse->body()
                        ]);
                    }

                } catch (\Exception $e) {
                    \Log::error('Error during Drive disconnect cleanup', [
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // Step 3: Delete Drive settings from database
            $this->mongo->deleteOne('system_settings', ['setting_key' => 'google_drive']);

            // Step 4: Clear course folder mappings (optional - keeps folder IDs for reconnection)
            // Uncomment if you want to completely remove folder mappings:
            // $this->mongo->deleteMany('course_folders', []);

            // Step 5: Update resource records (mark as disconnected but don't delete)
            $this->mongo->updateMany(
                'resources',
                [],
                ['$set' => [
                    'drive_status' => 'disconnected',
                    'disconnected_at' => (new \DateTime('now', new \DateTimeZone('Asia/Colombo')))->format('Y-m-d H:i:s')
                ]]
            );

            \Log::info('Google Drive fully disconnected and cleaned up', [
                'admin_id' => $session['user_id']
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Google Drive disconnected successfully. All permissions revoked.'
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Critical error in disconnectGoogleDrive', [
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
     * Revoke all permissions for files in Google Drive (recursively)
     */
    private function revokeAllDrivePermissions($accessToken, $folderId)
    {
        try {
            // Get all files in the root folder and subfolders
            $files = $this->listAllDriveFiles($accessToken, $folderId);

            \Log::info('Found ' . count($files) . ' files to revoke permissions');

            foreach ($files as $fileId) {
                try {
                    // Get all permissions for this file
                    $permissionsResponse = \Http::withToken($accessToken)
                        ->get("https://www.googleapis.com/drive/v3/files/{$fileId}/permissions");

                    if ($permissionsResponse->successful()) {
                        $permissions = $permissionsResponse->json()['permissions'] ?? [];

                        foreach ($permissions as $permission) {
                            $permissionId = $permission['id'];
                            $permissionType = $permission['type'];

                            // Only revoke 'anyone' permissions (public access)
                            // Keep 'user' and 'domain' permissions for the Drive owner
                            if ($permissionType === 'anyone') {
                                $deleteResponse = \Http::withToken($accessToken)
                                    ->delete("https://www.googleapis.com/drive/v3/files/{$fileId}/permissions/{$permissionId}");

                                if ($deleteResponse->successful()) {
                                    \Log::info("Revoked public permission for file: {$fileId}");
                                } else {
                                    \Log::warning("Failed to revoke permission", [
                                        'file_id' => $fileId,
                                        'permission_id' => $permissionId,
                                        'status' => $deleteResponse->status()
                                    ]);
                                }
                            }
                        }
                    }

                    // Small delay to avoid rate limiting
                    usleep(100000); // 0.1 seconds

                } catch (\Exception $e) {
                    \Log::error("Error revoking permissions for file {$fileId}", [
                        'error' => $e->getMessage()
                    ]);
                    continue;
                }
            }

        } catch (\Exception $e) {
            \Log::error('Error in revokeAllDrivePermissions', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * List all files in Google Drive folder (recursively)
     */
    private function listAllDriveFiles($accessToken, $folderId)
    {
        $fileIds = [];

        try {
            // Query to get all files in the folder and subfolders
            $query = "'{$folderId}' in parents and trashed = false";

            $pageToken = null;

            do {
                $params = [
                    'q' => $query,
                    'fields' => 'nextPageToken, files(id, mimeType)',
                    'pageSize' => 100
                ];

                if ($pageToken) {
                    $params['pageToken'] = $pageToken;
                }

                $response = \Http::withToken($accessToken)
                    ->get('https://www.googleapis.com/drive/v3/files', $params);

                if ($response->successful()) {
                    $data = $response->json();
                    $files = $data['files'] ?? [];

                    foreach ($files as $file) {
                        $fileId = $file['id'];
                        $mimeType = $file['mimeType'];

                        // If it's a folder, recursively get files inside
                        if ($mimeType === 'application/vnd.google-apps.folder') {
                            $subFiles = $this->listAllDriveFiles($accessToken, $fileId);
                            $fileIds = array_merge($fileIds, $subFiles);
                        } else {
                            // It's a file, add to list
                            $fileIds[] = $fileId;
                        }
                    }

                    $pageToken = $data['nextPageToken'] ?? null;
                } else {
                    \Log::error('Failed to list Drive files', [
                        'status' => $response->status(),
                        'body' => $response->body()
                    ]);
                    break;
                }

            } while ($pageToken);

        } catch (\Exception $e) {
            \Log::error('Error in listAllDriveFiles', [
                'error' => $e->getMessage()
            ]);
        }

        return $fileIds;
    }

    /**
     * Get resource analytics
     */
    public function getResourceAnalytics(Request $request)
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
                return response()->json(['status' => 'error', 'message' => 'Access denied'], 403);
            }

            // Fetch all resources
            $resources = $this->mongo->find('resources', []);

            $totalResources = 0;
            $totalSize = 0;
            $resourcesByCourse = [];
            $recentResources = [];

            foreach ($resources as $resource) {
                $resourceArray = is_array($resource) ? $resource : (array)$resource;
                $totalResources++;
                $totalSize += $resourceArray['file_size'] ?? 0;

                $courseId = $resourceArray['course_id'] ?? 'unknown';
                if (!isset($resourcesByCourse[$courseId])) {
                    $resourcesByCourse[$courseId] = [
                        'course_name' => $resourceArray['course_name'] ?? 'Unknown Course',
                        'count' => 0,
                        'size' => 0
                    ];
                }
                $resourcesByCourse[$courseId]['count']++;
                $resourcesByCourse[$courseId]['size'] += $resourceArray['file_size'] ?? 0;

                $recentResources[] = [
                    'title' => $resourceArray['title'] ?? 'Untitled',
                    'course_name' => $resourceArray['course_name'] ?? 'Unknown Course',
                    'lecturer_name' => $resourceArray['lecturer_name'] ?? 'Unknown Lecturer',
                    'file_size' => $resourceArray['file_size'] ?? 0,
                    'uploaded_at' => $resourceArray['uploaded_at'] ?? ''
                ];
            }

            // Sort recent resources by date
            usort($recentResources, function($a, $b) {
                return strtotime($b['uploaded_at']) - strtotime($a['uploaded_at']);
            });
            $recentResources = array_slice($recentResources, 0, 10);

            return response()->json([
                'status' => 'success',
                'analytics' => [
                    'total_resources' => $totalResources,
                    'total_size' => $totalSize,
                    'total_size_mb' => round($totalSize / (1024 * 1024), 2),
                    'resources_by_course' => array_values($resourcesByCourse),
                    'recent_resources' => $recentResources
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Internal server error', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get Google Drive account information (email)
     */
    public function getDriveAccountInfo(Request $request)
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
                return response()->json(['status' => 'error', 'message' => 'Access denied'], 403);
            }

            // Get Google Drive settings
            $driveSettings = $this->mongo->findOne('system_settings', ['setting_key' => 'google_drive']);

            if (!$driveSettings || !isset($driveSettings['drive_config'])) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Google Drive not connected'
                ], 404);
            }

            $config = $driveSettings['drive_config'];
            $accessToken = decrypt($config['access_token']);

            // Fetch user info from Google API
            $response = \Http::withToken($accessToken)
                ->get('https://www.googleapis.com/oauth2/v2/userinfo');

            if ($response->successful()) {
                $userInfo = $response->json();

                return response()->json([
                    'status' => 'success',
                    'email' => $userInfo['email'] ?? 'Unknown Email',
                    'name' => $userInfo['name'] ?? 'Unknown User',
                    'picture' => $userInfo['picture'] ?? null
                ], 200);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch account info'
            ], 500);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Internal server error',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
