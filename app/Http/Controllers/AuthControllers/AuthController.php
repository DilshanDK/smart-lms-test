<?php

namespace App\Http\Controllers\AuthControllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Services\MongoService;
use App\Models\User;
use MongoDB\BSON\ObjectId;
use Google_Client;

class AuthController extends Controller
{

    private $mongo;    public function __construct()
    {
        $this->mongo = new MongoService();
        // SSL verification bypass removed - now using cacert.pem from php.ini
    }

    public function signout(Request $request)
    {
        try {
            $token = $request->header('Authorization');

            if (!$token) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Token not provided'
                ], 401);
            }

            // Remove "Bearer " prefix if present
            $token = str_replace('Bearer ', '', $token);

            // Invalidate the session
            $result = $this->mongo->invalidateSession($token);

            if ($result->getModifiedCount() > 0) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Logout successful'
                ], 200);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Invalid token or already logged out'
            ], 401);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Internal server error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function googleSignin(Request $request)
    {
        try {
            // 1. Validate Google signin input with ID token
            $validator = Validator::make($request->all(), [
                'id_token' => 'required|string',
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255'
            ], [
                'id_token.required' => 'Google ID token is required',
                'name.required' => 'Name is required',
                'name.max' => 'Name cannot exceed 255 characters',
                'email.required' => 'Email is required',
                'email.email' => 'Please provide a valid email address',
                'email.max' => 'Email cannot exceed 255 characters'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()->all(),
                    'field_errors' => $validator->errors()
                ], 422);
            }

            // 2. Verify Google ID token - now uses proper SSL certificates from php.ini
            $client = new Google_Client(['client_id' => config('services.google.client_id')]);

            // SSL verification bypass removed - Google_Client now uses system certificates
            $payload = $client->verifyIdToken($request->id_token);

            if (!$payload) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid Google ID token',
                    'field_errors' => [
                        'id_token' => ['Google ID token verification failed']
                    ]
                ], 401);
            }

            // 3. Extract verified user data from token
            $verifiedEmail = $payload['email'];
            $verifiedName = $payload['name'];

            // 4. Validate that the provided email matches the verified token
            if ($verifiedEmail !== $request->email) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Email mismatch with Google token',
                    'field_errors' => [
                        'email' => ['Email does not match Google account']
                    ]
                ], 422);
            }

            // 5. Check if user already exists
            $existingUser = $this->mongo->findUserByEmail($verifiedEmail);

            if ($existingUser) {
                // User exists, create session and login
                $user = new User((array) $existingUser);
                $token = bin2hex(random_bytes(32));

                // Create session with 1-day expiry
                $sessionResult = $this->mongo->createSession($user->id, $token, 86400);

                if (!$sessionResult) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Failed to create session'
                    ], 500);
                }

                // Calculate Sri Lanka time for session expiry display
                $sriLankaTimezone = new \DateTimeZone('Asia/Colombo');
                $expiryTime = new \DateTime('+86400 seconds', $sriLankaTimezone);

                // Role-based dashboard redirection for existing users
                $dashboards = [
                    'admin' => route('admin.dashboard'),
                    'assigned_lecturer' => route('lecturer.dashboard'),
                    'assigned_student' => route('student.dashboard'),
                    'requested_lecturer' => route('lecturer.requested-dashboard'),
                    'requested_student' => route('student.requested-dashboard'),
                    'pending' => route('complete-registration')
                ];

                return response()->json([
                    'status' => 'success',
                    'message' => 'Google login successful',
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role
                    ],
                    'token' => $token,
                    'session_expires' => $expiryTime->format('Y-m-d H:i:s'),
                    'redirect_url' => $dashboards[$user->role] ?? route('home'),
                    'is_new_user' => false
                ], 200);

            }

            // 6. Create new user with verified Google credentials
            $insertResult = $this->mongo->createGoogleUser($verifiedName, $verifiedEmail);
            $userId = (string)$insertResult->getInsertedId();

            // 7. Create user model and session
            $userData = $this->mongo->findUserByEmail($verifiedEmail);
            $user = new User((array) $userData);
            $token = bin2hex(random_bytes(32));

            // Create session with 1-day expiry
            $sessionResult = $this->mongo->createSession($user->id, $token, 86400);

            if (!$sessionResult) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to create session'
                ], 500);
            }

            // Calculate Sri Lanka time for session expiry display
            $sriLankaTimezone = new \DateTimeZone('Asia/Colombo');
            $expiryTime = new \DateTime('+86400 seconds', $sriLankaTimezone);

            return response()->json([
                'status' => 'success',
                'message' => 'Google account registered and logged in successfully',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role
                ],
                'token' => $token,
                'session_expires' => $expiryTime->format('Y-m-d H:i:s'),
                'redirect_url' => route('complete-registration'), // Fixed route function usage
                'is_new_user' => true
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Internal server error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function validateSession(Request $request)
    {
        try {
            $token = $request->header('Authorization');

            if (!$token) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Token not provided'
                ], 401);
            }

            // Remove "Bearer " prefix if present
            $token = str_replace('Bearer ', '', $token);

            // Find active session
            $session = $this->mongo->findSessionByToken($token);

            if (!$session) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid or expired session'
                ], 401);
            }            // Get user data
            $userData = $this->mongo->findOne('users', ['_id' => new ObjectId($session['user_id'])]);

            if (!$userData) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not found'
                ], 404);
            }

            $user = new User((array) $userData); // Convert BSONDocument to array

            return response()->json([
                'status' => 'success',
                'message' => 'Session is valid',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'role' => $user->role,
                    'role_status' => $user->role_status
                ],
                'session_expires' => $session['expires_at'] // Already a formatted string
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
