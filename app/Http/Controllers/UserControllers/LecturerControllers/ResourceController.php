<?php
namespace App\Http\Controllers\UserControllers\LecturerControllers;

use App\Http\Controllers\Controller;
use App\Services\EncryptionService;
use App\Services\MongoService;
use Illuminate\Http\Request;
use MongoDB\BSON\ObjectId;

class ResourceController extends Controller
{
    private $mongo;

    public function __construct()
    {
        $this->mongo = new MongoService();
    }

    /**
     * Check Google Drive connection status
     */
    public function checkDriveStatus(Request $request)
    {
        try {
            $token = $request->header('Authorization');
            if (! $token) {
                return response()->json(['status' => 'error', 'message' => 'Authentication token not provided'], 401);
            }
            $token = str_replace('Bearer ', '', $token);

            $session = $this->mongo->findSessionByToken($token);
            if (! $session) {
                return response()->json(['status' => 'error', 'message' => 'Invalid session'], 401);
            }

            $driveSettings = $this->mongo->findOne('system_settings', ['setting_key' => 'google_drive']);

            if (! $driveSettings || ! isset($driveSettings['drive_config'])) {
                return response()->json([
                    'status'    => 'success',
                    'connected' => false,
                    'message'   => 'Google Drive not connected by admin',
                ], 200);
            }

            $config = $driveSettings['drive_config'];

            return response()->json([
                'status'         => 'success',
                'connected'      => $config['is_active'] ?? false,
                'root_folder_id' => $config['root_folder_id'] ?? null,
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Internal server error', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get resources for lecturer
     */
    public function getResources(Request $request)
    {
        try {
            $token = $request->header('Authorization');
            if (! $token) {
                return response()->json(['status' => 'error', 'message' => 'Authentication token not provided'], 401);
            }
            $token = str_replace('Bearer ', '', $token);

            $session = $this->mongo->findSessionByToken($token);
            if (! $session) {
                return response()->json(['status' => 'error', 'message' => 'Invalid session'], 401);
            }

            $user = $this->mongo->findOne('users', ['_id' => new ObjectId($session['user_id'])]);
            if (! $user || $user['role'] !== 'assigned_lecturer') {
                return response()->json(['status' => 'error', 'message' => 'Access denied'], 403);
            }

            $courseId = $request->query('course_id');
            $filter   = ['uploaded_by' => $session['user_id']];

            if ($courseId) {
                $filter['course_id'] = $courseId;
            }

            $resources = $this->mongo->find('resources', $filter, ['sort' => ['uploaded_at' => -1]]);

            $resourceList = [];
            foreach ($resources as $resource) {
                $resourceArray = is_array($resource) ? $resource : (array) $resource;

                $resourceList[] = [
                    'id'             => (string) ($resourceArray['_id'] ?? ''),
                    'encrypted_id'   => EncryptionService::encryptId((string) ($resourceArray['_id'] ?? '')),
                    'title'          => $resourceArray['title'] ?? 'Untitled',
                    'description'    => $resourceArray['description'] ?? '',
                    'course_id'      => $resourceArray['course_id'] ?? '',
                    'course_name'    => $resourceArray['course_name'] ?? 'Unknown Course',
                    'drive_file_id'  => $resourceArray['drive_file_id'] ?? '',
                    'drive_file_url' => $resourceArray['drive_file_url'] ?? '',
                    'file_name'      => $resourceArray['file_name'] ?? '',
                    'file_size'      => $resourceArray['file_size'] ?? 0,
                    'file_type'      => $resourceArray['file_type'] ?? '',
                    'uploaded_at'    => $resourceArray['uploaded_at'] ?? '',
                ];
            }

            return response()->json([
                'status'    => 'success',
                'resources' => $resourceList,
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Internal server error', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Upload resources - REWRITTEN: Creates course subfolders
     */
    public function uploadResources(Request $request)
    {
        try {
            // Step 1: Authenticate lecturer
            $token = $request->header('Authorization');
            if (! $token) {
                return response()->json(['status' => 'error', 'message' => 'Authentication token not provided'], 401);
            }
            $token = str_replace('Bearer ', '', $token);

            $session = $this->mongo->findSessionByToken($token);
            if (! $session) {
                return response()->json(['status' => 'error', 'message' => 'Invalid session'], 401);
            }

            $user = $this->mongo->findOne('users', ['_id' => new ObjectId($session['user_id'])]);
            if (! $user || $user['role'] !== 'assigned_lecturer') {
                return response()->json(['status' => 'error', 'message' => 'Access denied'], 403);
            }

            // Step 2: Check Google Drive connection
            $driveSettings = $this->mongo->findOne('system_settings', ['setting_key' => 'google_drive']);
            if (! $driveSettings || ! isset($driveSettings['drive_config']['access_token'])) {
                return response()->json(['status' => 'error', 'message' => 'Google Drive not connected. Contact admin.'], 500);
            }

            $driveConfig = $driveSettings['drive_config'];
            $accessToken = decrypt($driveConfig['access_token']);
            $rootFolderId = $driveConfig['root_folder_id'] ?? null;

            // Step 3: Validate request data
            if (! $request->has('title') || ! $request->has('course_id')) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Title and course_id are required',
                ], 422);
            }

            // Step 4: Get uploaded files
            $uploadedFiles = [];
            if ($request->hasFile('files')) {
                $files = $request->file('files');
                $uploadedFiles = is_array($files) ? $files : [$files];
            }
            elseif ($request->hasFile('file')) {
                $uploadedFiles = [$request->file('file')];
            }

            if (empty($uploadedFiles)) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'No files uploaded. Please select at least one file.',
                ], 422);
            }

            // Step 5: Verify course assignment
            $courseId    = $request->input('course_id');
            $title       = $request->input('title');
            $description = $request->input('description', '');

            $lecturerCourses = [];
            $courseName      = 'Unknown Course';

            if (isset($user['courses']) && is_iterable($user['courses'])) {
                foreach ($user['courses'] as $course) {
                    $courseArray = is_array($course) ? $course : (array) $course;
                    if (isset($courseArray['id'])) {
                        $lecturerCourses[] = $courseArray['id'];
                        if ($courseArray['id'] === $courseId) {
                            $courseName = $courseArray['title'] ?? 'Unknown Course';
                        }
                    }
                }
            }

            if (! in_array($courseId, $lecturerCourses)) {
                return response()->json(['status' => 'error', 'message' => 'You are not assigned to this course'], 403);
            }

            // Step 6: Get or create course subfolder
            $courseFolderId = $this->getOrCreateCourseFolder($accessToken, $rootFolderId, $courseName, $courseId);

            if (!$courseFolderId) {
                return response()->json(['status' => 'error', 'message' => 'Failed to create course folder'], 500);
            }

            // Step 7: Upload each file to course subfolder
            $uploadedResources = [];
            $sriLankaTimezone  = new \DateTimeZone('Asia/Colombo');
            $currentTime       = new \DateTime('now', $sriLankaTimezone);

            foreach ($uploadedFiles as $file) {
                if (! $file->isValid()) {
                    \Log::warning('Invalid file upload', ['file' => $file->getClientOriginalName()]);
                    continue;
                }

                if ($file->getSize() > 52428800) { // 50MB
                    \Log::warning('File too large', ['file' => $file->getClientOriginalName(), 'size' => $file->getSize()]);
                    continue;
                }

                // Upload to course subfolder
                $driveFileId = $this->uploadFileToDrive($accessToken, $file, $courseFolderId, $courseName);

                if (! $driveFileId) {
                    \Log::error('Failed to upload file to Drive', ['file' => $file->getClientOriginalName()]);
                    continue;
                }

                $shareableLink = $this->getShareableLink($accessToken, $driveFileId);

                // Save metadata to MongoDB
                $resourceData = [
                    'title'           => $title,
                    'description'     => $description,
                    'course_id'       => $courseId,
                    'course_name'     => $courseName,
                    'uploaded_by'     => $session['user_id'],
                    'lecturer_name'   => $user['name'] ?? 'Unknown Lecturer',
                    'drive_file_id'   => $driveFileId,
                    'drive_file_url'  => $shareableLink,
                    'drive_folder_id' => $courseFolderId, // Course subfolder ID
                    'file_name'       => $file->getClientOriginalName(),
                    'file_size'       => $file->getSize(),
                    'file_type'       => $file->getClientOriginalExtension(),
                    'mime_type'       => $file->getMimeType(),
                    'uploaded_at'     => $currentTime->format('Y-m-d H:i:s'),
                    'updated_at'      => $currentTime->format('Y-m-d H:i:s'),
                ];

                $result = $this->mongo->insertOne('resources', $resourceData);

                if ($result->getInsertedId()) {
                    $uploadedResources[] = [
                        'id'        => (string) $result->getInsertedId(),
                        'file_name' => $file->getClientOriginalName(),
                        'drive_url' => $shareableLink,
                    ];
                }
            }

            if (count($uploadedResources) > 0) {
                return response()->json([
                    'status'    => 'success',
                    'message'   => count($uploadedResources) . ' file(s) uploaded to ' . $courseName,
                    'resources' => $uploadedResources,
                ], 201);
            }

            return response()->json([
                'status'  => 'error',
                'message' => 'No files were uploaded. Please check file size and format.',
            ], 400);

        } catch (\Exception $e) {
            \Log::error('Upload error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['status' => 'error', 'message' => 'Internal server error', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get or create course subfolder in Google Drive
     */
    private function getOrCreateCourseFolder($accessToken, $rootFolderId, $courseName, $courseId)
    {
        try {
            // Step 1: Check if course folder already exists in database
            $existingMapping = $this->mongo->findOne('course_folders', ['course_id' => $courseId]);

            if ($existingMapping && isset($existingMapping['folder_id'])) {
                // Verify folder still exists in Drive
                $verifyResponse = \Http::withToken($accessToken)
                    ->get("https://www.googleapis.com/drive/v3/files/{$existingMapping['folder_id']}", [
                        'fields' => 'id,name,trashed'
                    ]);

                if ($verifyResponse->successful()) {
                    $folderData = $verifyResponse->json();
                    if (!($folderData['trashed'] ?? false)) {
                        \Log::info('Using existing course folder', [
                            'course_name' => $courseName,
                            'folder_id' => $existingMapping['folder_id']
                        ]);
                        return $existingMapping['folder_id'];
                    }
                }
            }

            // Step 2: Search for folder by name in root folder
            $searchResponse = \Http::withToken($accessToken)
                ->get('https://www.googleapis.com/drive/v3/files', [
                    'q' => "name = '{$courseName}' and mimeType = 'application/vnd.google-apps.folder' and '{$rootFolderId}' in parents and trashed = false",
                    'fields' => 'files(id, name)',
                    'pageSize' => 1
                ]);

            if ($searchResponse->successful()) {
                $files = $searchResponse->json()['files'] ?? [];
                if (!empty($files)) {
                    $folderId = $files[0]['id'];

                    // Save mapping to database
                    $this->saveCourseFolderMapping($courseId, $courseName, $folderId);

                    \Log::info('Found existing course folder', [
                        'course_name' => $courseName,
                        'folder_id' => $folderId
                    ]);
                    return $folderId;
                }
            }

            // Step 3: Create new course subfolder
            $createResponse = \Http::withToken($accessToken)
                ->asJson()
                ->post('https://www.googleapis.com/drive/v3/files', [
                    'name' => $courseName,
                    'mimeType' => 'application/vnd.google-apps.folder',
                    'parents' => [$rootFolderId]
                ]);

            if ($createResponse->successful()) {
                $folderData = $createResponse->json();
                $folderId = $folderData['id'];

                // Save mapping to database
                $this->saveCourseFolderMapping($courseId, $courseName, $folderId);

                \Log::info('Created new course folder', [
                    'course_name' => $courseName,
                    'folder_id' => $folderId
                ]);

                return $folderId;
            }

            \Log::error('Failed to create course folder', [
                'status' => $createResponse->status(),
                'body' => $createResponse->body()
            ]);
            return null;

        } catch (\Exception $e) {
            \Log::error('Error in getOrCreateCourseFolder', [
                'error' => $e->getMessage(),
                'course_name' => $courseName
            ]);
            return null;
        }
    }

    /**
     * Save course folder mapping to database
     */
    private function saveCourseFolderMapping($courseId, $courseName, $folderId)
    {
        try {
            $sriLankaTimezone = new \DateTimeZone('Asia/Colombo');
            $currentTime = new \DateTime('now', $sriLankaTimezone);

            $this->mongo->updateOne(
                'course_folders',
                ['course_id' => $courseId],
                ['$set' => [
                    'course_id' => $courseId,
                    'course_name' => $courseName,
                    'folder_id' => $folderId,
                    'created_at' => $currentTime->format('Y-m-d H:i:s'),
                    'updated_at' => $currentTime->format('Y-m-d H:i:s')
                ]],
                ['upsert' => true]
            );

            \Log::info('Saved course folder mapping', [
                'course_id' => $courseId,
                'folder_id' => $folderId
            ]);

        } catch (\Exception $e) {
            \Log::error('Error saving course folder mapping', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Upload file to Google Drive - REWRITTEN for course subfolders
     */
    private function uploadFileToDrive($accessToken, $file, $courseFolderId, $courseName)
    {
        try {
            $fileName = $file->getClientOriginalName();
            $mimeType = $file->getMimeType();
            $filePath = $file->getRealPath();

            \Log::info('Starting file upload', [
                'file_name' => $fileName,
                'course_folder_id' => $courseFolderId,
                'course_name' => $courseName,
                'file_size' => filesize($filePath)
            ]);

            // Use resumable upload (cURL) for reliability
            $fileId = $this->uploadFileWithCurl($accessToken, $filePath, $fileName, $mimeType, $courseFolderId);

            if ($fileId) {
                // Verify file is in correct location
                $this->verifyUploadedFile($accessToken, $fileId, $courseFolderId, $fileName);
            }

            return $fileId;

        } catch (\Exception $e) {
            \Log::error('Error in uploadFileToDrive', [
                'error' => $e->getMessage(),
                'file_name' => $fileName ?? 'unknown'
            ]);
            return null;
        }
    }

    /**
     * Upload file using cURL - REWRITTEN for proper metadata handling
     */
    private function uploadFileWithCurl($accessToken, $filePath, $fileName, $mimeType, $folderId)
    {
        try {
            // Step 1: Initiate resumable upload with proper metadata
            $metadata = [
                'name' => $fileName,
                'parents' => [$folderId]
            ];

            $metadataJson = json_encode($metadata);

            \Log::info('Initiating resumable upload', [
                'file_name' => $fileName,
                'target_folder' => $folderId,
                'file_size' => filesize($filePath)
            ]);

            $ch = curl_init('https://www.googleapis.com/upload/drive/v3/files?uploadType=resumable');
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $accessToken,
                    'Content-Type: application/json; charset=UTF-8',
                    'X-Upload-Content-Type: ' . $mimeType,
                    'X-Upload-Content-Length: ' . filesize($filePath),
                ],
                CURLOPT_POSTFIELDS => $metadataJson,
                CURLOPT_HEADER => true,
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $headers = substr($response, 0, $headerSize);
            curl_close($ch);

            if ($httpCode !== 200) {
                \Log::error('Failed to initiate upload', [
                    'http_code' => $httpCode,
                    'response' => substr($response, 0, 500)
                ]);
                return null;
            }

            // Extract upload URL
            $uploadUrl = null;
            if (preg_match('/Location:\s*(.+?)[\r\n]/i', $headers, $matches)) {
                $uploadUrl = trim($matches[1]);
            }

            if (!$uploadUrl) {
                \Log::error('Failed to extract upload URL');
                return null;
            }

            \Log::info('Upload session started', ['upload_url' => substr($uploadUrl, 0, 100)]);

            // Step 2: Upload file content
            $ch = curl_init($uploadUrl);
            $fileHandle = fopen($filePath, 'rb');

            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_PUT => true,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: ' . $mimeType,
                    'Content-Length: ' . filesize($filePath),
                ],
                CURLOPT_INFILE => $fileHandle,
                CURLOPT_INFILESIZE => filesize($filePath),
            ]);

            $uploadResponse = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            fclose($fileHandle);

            if ($httpCode !== 200 && $httpCode !== 201) {
                \Log::error('File upload failed', [
                    'http_code' => $httpCode,
                    'response' => substr($uploadResponse, 0, 500)
                ]);
                return null;
            }

            $fileData = json_decode($uploadResponse, true);
            $fileId = $fileData['id'] ?? null;

            if ($fileId) {
                \Log::info('File uploaded successfully', [
                    'file_id' => $fileId,
                    'file_name' => $fileName
                ]);
            }

            return $fileId;

        } catch (\Exception $e) {
            \Log::error('Error in uploadFileWithCurl', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    /**
     * Verify uploaded file and fix location/name if needed - REWRITTEN
     */
    private function verifyUploadedFile($accessToken, $fileId, $expectedFolderId, $expectedFileName)
    {
        try {
            // Get file metadata
            $verifyResponse = \Http::withToken($accessToken)
                ->get("https://www.googleapis.com/drive/v3/files/{$fileId}", [
                    'fields' => 'id,name,parents'
                ]);

            if (!$verifyResponse->successful()) {
                \Log::warning('Could not verify file', ['file_id' => $fileId]);
                return;
            }

            $fileInfo = $verifyResponse->json();
            $currentParents = $fileInfo['parents'] ?? [];
            $actualName = $fileInfo['name'] ?? 'unknown';

            \Log::info('File verification', [
                'file_id' => $fileId,
                'expected_name' => $expectedFileName,
                'actual_name' => $actualName,
                'expected_folder' => $expectedFolderId,
                'current_parents' => $currentParents
            ]);

            $needsUpdate = false;
            $updateData = [];

            // Check filename
            if ($actualName !== $expectedFileName && ($actualName === 'Untitled' || empty($actualName))) {
                $updateData['name'] = $expectedFileName;
                $needsUpdate = true;
                \Log::info('Filename needs correction', [
                    'from' => $actualName,
                    'to' => $expectedFileName
                ]);
            }

            // Check folder location
            if (!in_array($expectedFolderId, $currentParents)) {
                $this->moveFileToCorrectFolder($accessToken, $fileId, $currentParents, $expectedFolderId);
            }

            // Update filename if needed
            if ($needsUpdate) {
                $updateResponse = \Http::withToken($accessToken)
                    ->asJson()
                    ->patch("https://www.googleapis.com/drive/v3/files/{$fileId}", $updateData);

                if ($updateResponse->successful()) {
                    \Log::info('File metadata updated', ['file_id' => $fileId, 'updates' => $updateData]);
                } else {
                    \Log::error('Failed to update file metadata', [
                        'file_id' => $fileId,
                        'status' => $updateResponse->status()
                    ]);
                }
            }

        } catch (\Exception $e) {
            \Log::error('Error in verifyUploadedFile', [
                'file_id' => $fileId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Move file to correct folder - REWRITTEN
     */
    private function moveFileToCorrectFolder($accessToken, $fileId, $currentParents, $targetFolderId)
    {
        try {
            \Log::info('Moving file to correct folder', [
                'file_id' => $fileId,
                'from_folders' => $currentParents,
                'to_folder' => $targetFolderId
            ]);

            $params = ['addParents' => $targetFolderId];

            if (!empty($currentParents)) {
                $params['removeParents'] = implode(',', $currentParents);
            }

            $moveUrl = "https://www.googleapis.com/drive/v3/files/{$fileId}?" . http_build_query($params);

            $moveResponse = \Http::withToken($accessToken)->patch($moveUrl);

            if ($moveResponse->successful()) {
                \Log::info('File moved successfully', [
                    'file_id' => $fileId,
                    'new_folder' => $targetFolderId
                ]);
            } else {
                \Log::error('Failed to move file', [
                    'file_id' => $fileId,
                    'status' => $moveResponse->status(),
                    'response' => $moveResponse->body()
                ]);
            }

        } catch (\Exception $e) {
            \Log::error('Error in moveFileToCorrectFolder', [
                'file_id' => $fileId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Make file shareable and get link
     */
    private function getShareableLink($accessToken, $fileId)
    {
        try {
            \Http::withToken($accessToken)
                ->asJson()
                ->post("https://www.googleapis.com/drive/v3/files/{$fileId}/permissions", [
                    'role' => 'reader',
                    'type' => 'anyone',
                ]);

            return "https://drive.google.com/file/d/{$fileId}/view";

        } catch (\Exception $e) {
            \Log::error('Error making file shareable', ['error' => $e->getMessage()]);
            return "https://drive.google.com/file/d/{$fileId}/view";
        }
    }

    /**
     * Delete resource
     */
    public function deleteResource(Request $request, $encryptedId)
    {
        try {
            $token = $request->header('Authorization');
            if (! $token) {
                return response()->json(['status' => 'error', 'message' => 'Authentication token not provided'], 401);
            }
            $token = str_replace('Bearer ', '', $token);

            $session = $this->mongo->findSessionByToken($token);
            if (! $session) {
                return response()->json(['status' => 'error', 'message' => 'Invalid session'], 401);
            }

            $user = $this->mongo->findOne('users', ['_id' => new ObjectId($session['user_id'])]);
            if (! $user || $user['role'] !== 'assigned_lecturer') {
                return response()->json(['status' => 'error', 'message' => 'Access denied'], 403);
            }

            try {
                $resourceId = EncryptionService::decryptId($encryptedId);
                $objectId   = new ObjectId($resourceId);
            } catch (\Exception $e) {
                return response()->json(['status' => 'error', 'message' => 'Invalid resource ID'], 422);
            }

            $resource = $this->mongo->findOne('resources', [
                '_id'         => $objectId,
                'uploaded_by' => $session['user_id'],
            ]);

            if (! $resource) {
                return response()->json(['status' => 'error', 'message' => 'Resource not found or access denied'], 404);
            }

            if (isset($resource['drive_file_id'])) {
                $driveSettings = $this->mongo->findOne('system_settings', ['setting_key' => 'google_drive']);
                if ($driveSettings && isset($driveSettings['drive_config'])) {
                    $accessToken = decrypt($driveSettings['drive_config']['access_token']);
                    \Http::withToken($accessToken)
                        ->delete("https://www.googleapis.com/drive/v3/files/{$resource['drive_file_id']}");
                }
            }

            $deleteResult = $this->mongo->deleteOne('resources', ['_id' => $objectId]);

            if ($deleteResult->getDeletedCount() > 0) {
                return response()->json([
                    'status'  => 'success',
                    'message' => 'Resource deleted successfully',
                ], 200);
            }

            return response()->json(['status' => 'error', 'message' => 'Failed to delete resource'], 500);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Internal server error', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Download resource
     */
    public function downloadResource(Request $request, $encryptedId)
    {
        try {
            $token = $request->header('Authorization');
            if (! $token) {
                return response()->json(['status' => 'error', 'message' => 'Authentication token not provided'], 401);
            }
            $token = str_replace('Bearer ', '', $token);

            $session = $this->mongo->findSessionByToken($token);
            if (! $session) {
                return response()->json(['status' => 'error', 'message' => 'Invalid session'], 401);
            }

            try {
                $resourceId = EncryptionService::decryptId($encryptedId);
                $objectId   = new ObjectId($resourceId);
            } catch (\Exception $e) {
                return response()->json(['status' => 'error', 'message' => 'Invalid resource ID'], 422);
            }

            $resource = $this->mongo->findOne('resources', ['_id' => $objectId]);

            if (! $resource) {
                return response()->json(['status' => 'error', 'message' => 'Resource not found'], 404);
            }

            return response()->json([
                'status'    => 'success',
                'drive_url' => $resource['drive_file_url'],
                'file_name' => $resource['file_name'],
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Internal server error', 'error' => $e->getMessage()], 500);
        }
    }
}
