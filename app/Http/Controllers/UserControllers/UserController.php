<?php

namespace App\Http\Controllers\UserControllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Services\MongoService;
use App\Models\User;
use App\Models\Institute;
use MongoDB\BSON\ObjectId;

class UserController extends Controller
{
    private $mongo;

    public function __construct()
    {
        $this->mongo = new MongoService();
    }

    public function updateProfile(Request $request)
    {
        try {
            // 1. Get token from Authorization header
            $token = $request->header('Authorization');

            if (!$token) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Token not provided'
                ], 401);
            }

            // Remove "Bearer " prefix if present
            $token = str_replace('Bearer ', '', $token);

            // 2. Find active session to get user
            $session = $this->mongo->findSessionByToken($token);

            if (!$session) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid or expired session'
                ], 401);
            }

            // 3. Get current user data
            $userData = $this->mongo->findOne('users', ['_id' => new ObjectId($session['user_id'])]);

            if (!$userData) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not found'
                ], 404);
            }

            $currentUser = new User((array) $userData);

            // 4. Validate update input
            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|string|min:2|max:255',
                'email' => 'sometimes|string|email|max:255',
                'phone' => 'sometimes|digits:10',
                'current_password' => 'required_with:new_password|string',
                'new_password' => 'sometimes|string|min:8|confirmed'
            ], [
                'name.min' => 'Name must be at least 2 characters long',
                'email.email' => 'Please provide a valid email address',
                'phone.digits' => 'Phone number must be exactly 10 digits',
                'current_password.required_with' => 'Current password is required when changing password',
                'new_password.min' => 'New password must be at least 8 characters long',
                'new_password.confirmed' => 'New password confirmation does not match'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()->all(),
                    'field_errors' => $validator->errors()
                ], 422);
            }

            $validated = $validator->validated();

            // 5. Check if email is being changed and if it's already taken
            if (isset($validated['email']) && $validated['email'] !== $currentUser->email) {
                $existingUser = $this->mongo->findOne('users', [
                    'email' => $validated['email'],
                    '_id' => ['$ne' => new ObjectId($currentUser->id)]
                ]);

                if ($existingUser) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Email is already taken by another user'
                    ], 409);
                }
            }

            // 6. Check if phone is being changed and if it's already taken
            if (isset($validated['phone']) && $validated['phone'] !== $currentUser->phone) {
                $existingPhone = $this->mongo->findOne('users', [
                    'phone' => $validated['phone'],
                    '_id' => ['$ne' => new ObjectId($currentUser->id)]
                ]);

                if ($existingPhone) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Phone number is already taken by another user'
                    ], 409);
                }
            }

            // 7. Verify current password if changing password
            if (isset($validated['new_password'])) {
                if (!Hash::check($validated['current_password'], $currentUser->password)) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Current password is incorrect'
                    ], 400);
                }
            }

            // 8. Prepare update data
            $updateData = [];
            if (isset($validated['name'])) {
                $updateData['name'] = $validated['name'];
            }
            if (isset($validated['email'])) {
                $updateData['email'] = $validated['email'];
            }
            if (isset($validated['phone'])) {
                $updateData['phone'] = $validated['phone'];
            }
            if (isset($validated['new_password'])) {
                $updateData['password'] = Hash::make($validated['new_password']);
            }

            // Add updated timestamp
            $sriLankaTimezone = new \DateTimeZone('Asia/Colombo');
            $updateData['updated_at'] = (new \DateTime('now', $sriLankaTimezone))->format('Y-m-d H:i:s');

            // 9. Update user in database
            $result = $this->mongo->updateOne('users',
                ['_id' => new ObjectId($currentUser->id)],
                ['$set' => $updateData]
            );

            if ($result->getModifiedCount() > 0) {
                // Get updated user data
                $updatedUserData = $this->mongo->findOne('users', ['_id' => new ObjectId($currentUser->id)]);
                $updatedUser = new User((array) $updatedUserData);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Profile updated successfully',
                    'user' => [
                        'id' => $updatedUser->id,
                        'name' => $updatedUser->name,
                        'email' => $updatedUser->email,
                        'phone' => $updatedUser->phone,
                        'role' => $updatedUser->role,
                        'updated_at' => $updatedUser->updated_at
                    ]
                ], 200);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'No changes were made'
            ], 400);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Internal server error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getProfile(Request $request)
    {
        try {
            // 1. Get token from Authorization header
            $token = $request->header('Authorization');

            if (!$token) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Token not provided'
                ], 401);
            }

            // Remove "Bearer " prefix if present
            $token = str_replace('Bearer ', '', $token);

            // 2. Find active session to get user
            $session = $this->mongo->findSessionByToken($token);

            if (!$session) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid or expired session'
                ], 401);
            }

            // 3. Get current user data
            $userData = $this->mongo->findOne('users', ['_id' => new ObjectId($session['user_id'])]);

            if (!$userData) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not found'
                ], 404);
            }

            $user = new User((array) $userData);

            return response()->json([
                'status' => 'success',
                'message' => 'Profile retrieved successfully',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'role' => $user->role,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at
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
     * Store registration completion request in MongoDB
     */
    public function storeStudentRegistrationRequest(Request $request)
    {
        try {
            // 1. Get token from Authorization header
            $token = $request->header('Authorization');

            if (!$token) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Token not provided'
                ], 401);
            }

            // Remove "Bearer " prefix if present
            $token = str_replace('Bearer ', '', $token);

            // 2. Find active session to get user
            $session = $this->mongo->findSessionByToken($token);

            if (!$session) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid or expired session'
                ], 401);
            }

            // 3. Get current user data
            $userData = $this->mongo->findOne('users', ['_id' => new ObjectId($session['user_id'])]);

            if (!$userData) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not found'
                ], 404);
            }

            $user = new User((array) $userData);

            // 1. Validate student-specific data - remove course_id and institute_id
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'dob' => 'required|date',
                'nic' => 'required|string|max:12|min:10',
                'gender' => 'required|string|in:male,female,other',
                'phoneNo' => 'required|regex:/^0[0-9]{9}$/',
                'address' => 'required|string',
                'emergency_relation' => 'required|string|max:100',
                'emergency_contact' => 'required|regex:/^0[0-9]{9}$/'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid data for student registration',
                    'errors' => $validator->errors()->all()
                ], 422);
            }

            $validatedData = $validator->validated();

            // 2. Remove course/institute validation

            // 3. Check if user already has a pending request
            $existingRequest = $this->mongo->findOne('register_request', [
                'user_id' => $session['user_id'],
                'status' => 'pending'
            ]);

            if ($existingRequest) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You already have a pending registration request'
                ], 409);
            }

            // 4. Prepare and store the registration request - no course/institute
            $sriLankaTimezone = new \DateTimeZone('Asia/Colombo');
            $currentTime = new \DateTime('now', $sriLankaTimezone);
            $timestamp = $currentTime->format('Y-m-d H:i:s');

            $registrationRequest = [
                'user_id' => $session['user_id'],
                'user_email' => $user->email,
                'user_name' => $user->name,
                'request_role' => 'student',
                'request_data' => $validatedData,
                'status' => 'pending',
                'submitted_at' => $timestamp,
            ];

            $result = $this->mongo->insertOne('register_request', $registrationRequest);

            if ($result->getInsertedId()) {
                // 5. Update user role to 'requested_student'
                $this->mongo->updateOne('users',
                    ['_id' => new ObjectId($session['user_id'])],
                    ['$set' => [
                        'role' => 'requested_student',
                        'updated_at' => $timestamp
                    ]]
                );

                return response()->json([
                    'status' => 'success',
                    'message' => 'Student registration request submitted successfully',
                    'request_id' => (string) $result->getInsertedId(),
                    'redirect_url' => '/student/requested-dashboard'
                ], 201);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to store student registration request'
            ], 500);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Internal server error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store registration completion request in MongoDB
     */
    public function storeLecturerRegistrationRequest(Request $request)
    {
        try {
            // 1. Get token from Authorization header
            $token = $request->header('Authorization');

            if (!$token) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Token not provided'
                ], 401);
            }

            // Remove "Bearer " prefix if present
            $token = str_replace('Bearer ', '', $token);

            // 2. Find active session to get user
            $session = $this->mongo->findSessionByToken($token);

            if (!$session) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid or expired session'
                ], 401);
            }

            // 3. Get current user data
            $userData = $this->mongo->findOne('users', ['_id' => new ObjectId($session['user_id'])]);

            if (!$userData) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not found'
                ], 404);
            }

            $user = new User((array) $userData);

            // 1. Validate lecturer-specific data - simplified validation
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'phoneNo' => 'required|regex:/^0[0-9]{9}$/',
                'nic' => 'required|string|max:12|min:10',
                'address' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid data for lecturer registration',
                    'errors' => $validator->errors()->all()
                ], 422);
            }

            $validatedData = $validator->validated();

            // 2. Check if user already has a pending request
            $existingRequest = $this->mongo->findOne('register_request', [
                'user_id' => $session['user_id'],
                'status' => 'pending'
            ]);

            if ($existingRequest) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You already have a pending registration request'
                ], 409);
            }

            // 3. Prepare and store the registration request
            $sriLankaTimezone = new \DateTimeZone('Asia/Colombo');
            $currentTime = new \DateTime('now', $sriLankaTimezone);
            $timestamp = $currentTime->format('Y-m-d H:i:s');

            $registrationRequest = [
                'user_id' => $session['user_id'],
                'user_email' => $user->email,
                'user_name' => $user->name,
                'request_role' => 'lecturer',
                'request_data' => $validatedData,
                'status' => 'pending',
                'submitted_at' => $timestamp,
            ];

            $result = $this->mongo->insertOne('register_request', $registrationRequest);

            if ($result->getInsertedId()) {
                // 4. Update user role to 'requested_lecturer'
                $this->mongo->updateOne('users',
                    ['_id' => new ObjectId($session['user_id'])],
                    ['$set' => [
                        'role' => 'requested_lecturer',
                        'updated_at' => $timestamp
                    ]]
                );

                return response()->json([
                    'status' => 'success',
                    'message' => 'Lecturer registration request submitted successfully',
                    'request_id' => (string) $result->getInsertedId(),
                    'redirect_url' => '/lecturer/requested-dashboard'
                ], 201);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to store lecturer registration request'
            ], 500);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Internal server error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's registration requests
     */
    public function getRegistrationRequests(Request $request)
    {
        try {
            // 1. Get token from Authorization header
            $token = $request->header('Authorization');

            if (!$token) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Token not provided'
                ], 401);
            }

            // Remove "Bearer " prefix if present
            $token = str_replace('Bearer ', '', $token);

            // 2. Find active session to get user
            $session = $this->mongo->findSessionByToken($token);

            if (!$session) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid or expired session'
                ], 401);
            }

            // 3. Get user's registration requests
            $requests = $this->mongo->find('register_request', [
                'user_id' => $session['user_id']
            ], [
                'sort' => ['created_at' => -1]
            ]);

            $requestsArray = [];
            foreach ($requests as $req) {
                $requestsArray[] = [
                    'id' => (string) $req['_id'],
                    'role' => $req['request_role'],
                    'status' => $req['status'],
                    'submitted_at' => $req['submitted_at'],
                    'processed_at' => $req['processed_at'] ?? null,
                    'admin_notes' => $req['admin_notes'] ?? null,
                    'data' => $req['request_data']
                ];
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Registration requests retrieved',
                'requests' => $requestsArray,
                'total' => count($requestsArray)
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
     * Get the submitted date of the latest registration request
     */
    public function getSubmittedDate(Request $request)
    {
        try {
            // 1. Get token from Authorization header
            $token = $request->header('Authorization');

            if (!$token) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Token not provided'
                ], 401);
            }

            // Remove "Bearer " prefix if present
            $token = str_replace('Bearer ', '', $token);

            // 2. Find active session to get user
            $session = $this->mongo->findSessionByToken($token);

            if (!$session) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid or expired session'
                ], 401);
            }

            // 3. Fetch the latest registration request for the user
            $requestRecord = $this->mongo->findOne('register_request', [
                'user_id' => $session['user_id']
            ], [
                'sort' => ['submitted_at' => -1]
            ]);

            if (!$requestRecord) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No registration request found'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'submitted_at' => $requestRecord['submitted_at'] // Includes both date and time
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
     * Add a new institute to the database
     */
    // public function addInstitute(Request $request)
    // {
    //     try {
    //         // Validate input
    //         $validator = \Validator::make($request->all(), [
    //             'institute' => 'required|string|max:255',
    //             'description' => 'nullable|string|max:1000',
    //             'status' => 'nullable|string|in:active,inactive'
    //         ]);

    //         if ($validator->fails()) {
    //             return response()->json([
    //                 'status' => 'error',
    //                 'message' => 'Validation failed',
    //                 'errors' => $validator->errors()->all(),
    //             ], 422);
    //         }

    //         $institute = Institute::fromRequest($validator->validated());

    //         $result = $this->mongo->insertOne('institutes', $institute->toArray());

    //         if (!$result->getInsertedId()) {
    //             return response()->json([
    //                 'status' => 'error',
    //                 'message' => 'Failed to add institute',
    //             ], 500);
    //         }

    //         return response()->json([
    //             'status' => 'success',
    //             'message' => 'Institute added successfully',
    //             'institute_id' => (string)$result->getInsertedId(),
    //         ], 201);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Internal server error',
    //             'error' => $e->getMessage(),
    //         ], 500);
    //     }
    // }



    // /**
    //  * Get all institutes for dropdowns (id and name only)
    //  */
    // public function getInstitutesDropdown(Request $request)
    // {
    //     try {
    //         $institutes = $this->mongo->find('institutes', ['status' => 'active'], [
    //             'projection' => ['institute' => 1],
    //             'sort' => ['institute' => 1]
    //         ]);

    //         $result = [];
    //         foreach ($institutes as $inst) {
    //             $result[] = [
    //                 'id' => (string)($inst['_id'] ?? ''),
    //                 'institute' => $inst['institute'] ?? '',
    //             ];
    //         }

    //         return response()->json([
    //             'status' => 'success',
    //             'institutes' => $result,
    //         ], 200);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Internal server error',
    //             'error' => $e->getMessage(),
    //         ], 500);
    //     }
    // }

    /**
     * Get all unique departments for dropdowns (with id)
     */
    public function getDepartmentsDropdown(Request $request)
    {
        try {
            $courses = $this->mongo->find('courses', [], [
                'projection' => ['department' => 1]
            ]);
            $departments = [];
            $departmentMap = [];
            foreach ($courses as $course) {
                $dept = $course['department'] ?? null;
                if ($dept && !isset($departmentMap[$dept])) {
                    // Use the first course _id as department id (not ideal, but MongoDB has no department collection)
                    $departmentMap[$dept] = (string)($course['_id'] ?? '');
                }
            }
            foreach ($departmentMap as $name => $id) {
                $departments[] = [
                    'id' => $id,
                    'department' => $name
                ];
            }
            usort($departments, fn($a, $b) => strcmp($a['department'], $b['department']));

            return response()->json([
                'status' => 'success',
                'departments' => $departments,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Internal server error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get all unique courses for dropdowns (with id), filtered by department in request body (JSON)
     */
    public function getCoursesDropdown(Request $request)
    {
        try {
            // Accept department from JSON body or query string for backward compatibility
            $department = $request->input('department', $request->query('department'));
            $filter = [];
            if ($department) {
                $filter['department'] = $department;
            }
            $courses = $this->mongo->find('courses', $filter, [
                'projection' => ['title' => 1],
                'sort' => ['title' => 1]
            ]);
            $titles = [];
            $titleMap = [];
            foreach ($courses as $course) {
                $title = $course['title'] ?? null;
                if ($title && !isset($titleMap[$title])) {
                    $titleMap[$title] = (string)($course['_id'] ?? '');
                }
            }
            foreach ($titleMap as $name => $id) {
                $titles[] = [
                    'id' => $id,
                    'title' => $name
                ];
            }

            return response()->json([
                'status' => 'success',
                'courses' => $titles,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Internal server error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get the current status of user's registration request
     */
    public function getRegistrationStatus(Request $request)
    {
        try {
            // Get token from Authorization header
            $token = $request->header('Authorization');
            if (!$token) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Token not provided'
                ], 401);
            }

            // Remove "Bearer " prefix if present
            $token = str_replace('Bearer ', '', $token);

            // Find active session to get user
            $session = $this->mongo->findSessionByToken($token);
            if (!$session) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid or expired session'
                ], 401);
            }

            // Get current user data
            $userData = $this->mongo->findOne('users', ['_id' => new ObjectId($session['user_id'])]);
            if (!$userData) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not found'
                ], 404);
            }

            // Get the latest registration request for this user
            $requestRecord = $this->mongo->findOne('register_request', [
                'user_id' => $session['user_id']
            ], [
                'sort' => ['submitted_at' => -1]
            ]);

            if (!$requestRecord) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No registration request found'
                ], 404);
            }

            // Determine redirect based on current role and request status
            $redirectUrl = null;
            $currentRole = $userData['role'] ?? 'pending';

            // If user is already assigned, redirect to their dashboard
            if ($currentRole === 'assigned_student') {
                $redirectUrl = '/student/dashboard';
            } elseif ($currentRole === 'assigned_lecturer') {
                $redirectUrl = '/lecturer/dashboard';
            }

            return response()->json([
                'status' => 'success',
                'request_status' => $requestRecord['status'] ?? 'pending',
                'request_role' => $requestRecord['request_role'] ?? 'unknown',
                'submitted_at' => $requestRecord['submitted_at'] ?? null,
                'processed_at' => $requestRecord['processed_at'] ?? null,
                'admin_notes' => $requestRecord['admin_notes'] ?? null,
                'current_role' => $currentRole,
                'redirect_url' => $redirectUrl,
                'user_name' => $userData['name'] ?? 'Unknown User'
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
