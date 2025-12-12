<?php

use App\Http\Controllers\UserControllers\AdminController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthControllers\AuthController;
use App\Http\Controllers\UserControllers\UserController;
use App\Http\Controllers\UserControllers\AdminControllers\UserManageController;
use App\Http\Controllers\UserControllers\LecturerControllers\CourseController as LecturerCourseController;
use App\Http\Controllers\UserControllers\LecturerControllers\AssignmentController as LecturerAssignmentController;
use App\Http\Controllers\UserControllers\LecturerControllers\StudentController;
use App\Http\Controllers\UserControllers\LecturerControllers\GradeBookController;
use App\Http\Controllers\UserControllers\LecturerControllers\VideosController;
use App\Http\Controllers\UserControllers\LecturerControllers\ResourceController as LecturerResourceController;

use App\Http\Controllers\UserControllers\StudentControllers\CourseController as StudentCourseController;
use App\Http\Controllers\UserControllers\StudentControllers\AssignmentController as StudentAssignmentController;
use App\Http\Controllers\UserControllers\StudentControllers\ProgressController;
use App\Http\Controllers\UserControllers\AdminControllers\VideoStreamController as AdminVideoStreamController;
use App\Http\Controllers\UserControllers\StudentControllers\VideosController as StudentVideosController;
use App\Http\Controllers\UserControllers\AdminControllers\ResourceController as AdminResourceController;
use App\Http\Controllers\UserControllers\StudentControllers\ResourceController as StudentResourceController;


// Authentication routes
Route::post('/google-signin', [AuthController::class, 'googleSignin']);
Route::post('/signout', [AuthController::class, 'signout']);
Route::get('/validate-session', [AuthController::class, 'validateSession']);

// User routes
Route::prefix('user')->name('user.')->group(function () {
    Route::get('/profile', [UserController::class, 'getProfile']);
    Route::put('/profile', [UserController::class, 'updateProfile']);
    Route::post('/student-registration-request', [UserController::class, 'storeStudentRegistrationRequest']);
    Route::post('/lecturer-registration-request', [UserController::class, 'storeLecturerRegistrationRequest']);
    Route::get('/registration-status', [UserController::class, 'getRegistrationStatus']);
    Route::get('/submitted-date', [UserController::class, 'getSubmittedDate']);
    Route::get('/dropdown/institutes', [UserController::class, 'getInstitutesDropdown']);
    Route::get('/dropdown/departments', [UserController::class, 'getDepartmentsDropdown']);
    Route::get('/dropdown/courses', [UserController::class, 'getCoursesDropdown']);
});

// Admin routes
Route::prefix('admin')->name('admin.')->group(function () {
    // Registration requests
    Route::get('/registration-requests', [UserManageController::class, 'getRegistrationRequests']);
    Route::post('/assign-student', [UserManageController::class, 'assignStudent']);
    Route::post('/assign-lecturer', [UserManageController::class, 'assignLecturer']);
    Route::post('/decline-requests', [UserManageController::class, 'declineRequests']);

    // Lecturer management
    Route::get('/assigned-lecturers', [UserManageController::class, 'getAssignedLecturers']);
    Route::get('/assigned-lecturers-count', [UserManageController::class, 'getAssignedLecturersCount']); // NEW
    Route::post('/change-lecturer-courses', [UserManageController::class, 'changeLecturerCourses']);

    // NEW: Dashboard statistics endpoints
    Route::get('/assigned-students-count', [UserManageController::class, 'getAssignedStudentsCount']);
    Route::get('/active-courses-count', [UserManageController::class, 'getActiveCoursesCount']);
    Route::get('/pending-requests-count', [UserManageController::class, 'getPendingRequestsCount']);

    // Course management
    Route::post('/add-course', [UserManageController::class, 'addCourse']);
    Route::get('/get-courses', [UserManageController::class, 'getCourses']);
    Route::post('/update-course-status', [UserManageController::class, 'updateCourseStatus']); // NEW

    // Enrollment management
    Route::get('/enrollment-requests', [UserManageController::class, 'getEnrollmentRequests']);
    Route::post('/enroll-students', [UserManageController::class, 'enrollStudents']);
    Route::post('/decline-enrollment-requests', [UserManageController::class, 'declineEnrollmentRequests']);
    Route::get('/enrolled-students', [UserManageController::class, 'getEnrolledStudents']);

    // Unenrollment management
    Route::get('/unenrollment-requests', [UserManageController::class, 'getUnenrollmentRequests']);
    Route::post('/process-unenrollment-requests', [UserManageController::class, 'processUnenrollmentRequests']);
    Route::post('/unenroll-students', [UserManageController::class, 'unenrollStudents']);

    Route::post('/get-admin-id', [UserManageController::class, 'getAdminId']);

    // Video Stream Management
    Route::get('/youtube/status', [AdminVideoStreamController::class, 'getYouTubeStatus']);
    Route::post('/youtube/connect', [AdminVideoStreamController::class, 'initiateYouTubeConnection']);
    Route::get('/youtube/callback', [AdminVideoStreamController::class, 'handleYouTubeCallback'])->name('youtube.callback');
    Route::delete('/youtube/disconnect', [AdminVideoStreamController::class, 'disconnectYouTube']);
    Route::get('/streams', [AdminVideoStreamController::class, 'getAllStreams']);
    Route::get('/streams/analytics', [AdminVideoStreamController::class, 'getStreamAnalytics']);
    Route::delete('/streams/{id}', [AdminVideoStreamController::class, 'deleteStream']);
    Route::get('/playlists/analytics', [AdminVideoStreamController::class, 'getPlaylistAnalytics']);

    // Admin Resource Management (Google Drive)
    Route::get('/google-drive/status', [AdminResourceController::class, 'getDriveStatus']);
    Route::get('/google-drive/account-info', [AdminResourceController::class, 'getDriveAccountInfo']);
    Route::post('/google-drive/connect', [AdminResourceController::class, 'initiateGoogleDriveConnection']);
    Route::post('/google-drive/disconnect', [AdminResourceController::class, 'disconnectGoogleDrive']);
    Route::get('/resources/analytics', [AdminResourceController::class, 'getResourceAnalytics']);
});

// Lecturer routes
Route::prefix('lecturer')->name('lecturer.')->group(function () {

    Route::get('/my-courses', [LecturerCourseController::class, 'getMyCourses']);
    Route::get('/profile', [LecturerCourseController::class, 'getProfile']);
    Route::get('/student-count-by-course', [LecturerCourseController::class, 'getStudentCountByCourse']); // new: get student count by course

    // Student Management
    Route::get('/students', [StudentController::class, 'getStudents']); // ✅ This should exist

    // Assignment routes
    Route::get('/get-assignments', [LecturerAssignmentController::class, 'getAssignments']);
    Route::post('/create-assignment', [LecturerAssignmentController::class, 'createAssignment']);
    Route::get('/get-assignment/{id}', [LecturerAssignmentController::class, 'getAssignment']);
    Route::put('/edit-assignment/{id}', [LecturerAssignmentController::class, 'editAssignment']);
    Route::put('/publication-assignment/{id}', [LecturerAssignmentController::class, 'assignmentPublication']);
    Route::delete('/delete-assignment/{id}', [LecturerAssignmentController::class, 'deleteAssignment']);

    // Resource routes
    Route::get('/resources', [LecturerResourceController::class, 'getResources']);
    Route::post('/upload-resources', [LecturerResourceController::class, 'uploadResources']);
    Route::get('/download-resource/{id}', [LecturerResourceController::class, 'downloadResource'])->name('download-resource');
    Route::delete('/delete-resource/{id}', [LecturerResourceController::class, 'deleteResource']);

    // Gradebook
    Route::get('/gradebook', [GradeBookController::class, 'getGradebook']);

    // Video routes (existing)
    Route::get('/videos', [App\Http\Controllers\UserControllers\LecturerControllers\VideosController::class, 'getVideos']);
    Route::post('/videos/upload', [App\Http\Controllers\UserControllers\LecturerControllers\VideosController::class, 'uploadVideo']);
    Route::delete('/videos/{videoId}', [App\Http\Controllers\UserControllers\LecturerControllers\VideosController::class, 'deleteVideo']);
    Route::get('/youtube-status', [App\Http\Controllers\UserControllers\LecturerControllers\VideosController::class, 'checkYouTubeStatus']);

    // Live streaming routes
    Route::post('/videos/go-live', [App\Http\Controllers\UserControllers\LecturerControllers\VideosController::class, 'goLive']);
    Route::get('/my-live-streams', [App\Http\Controllers\UserControllers\LecturerControllers\VideosController::class, 'getMyLiveStreams']); // ADD THIS LINE
    Route::post('/live-streams/{id}/end', [App\Http\Controllers\UserControllers\LecturerControllers\VideosController::class, 'endLiveStream']); // OPTIONAL

    // Lecturer Resource Management (Google Drive)
    Route::get('/resources/drive-status', [LecturerResourceController::class, 'checkDriveStatus']);
    Route::get('/resources', [LecturerResourceController::class, 'getResources']);
    Route::post('/upload-resources', [LecturerResourceController::class, 'uploadResources']);
    Route::delete('/delete-resource/{encryptedId}', [LecturerResourceController::class, 'deleteResource']);
    Route::get('/download-resource/{encryptedId}', [LecturerResourceController::class, 'downloadResource']);
});

// Student routes
Route::prefix('student')->name('student.')->group(function () {

    // Course routes
    Route::get('/courses/available',             [StudentCourseController::class, 'getAvailableCourses']);
    Route::get('/courses/enrolled',              [StudentCourseController::class, 'getEnrolledCourses']);
    Route::post('/courses/request-enrollment',   [StudentCourseController::class, 'requestEnrollment']);
    Route::post('/courses/cancel-enrollment',    [StudentCourseController::class, 'cancelEnrollmentRequest']);
    Route::post('/courses/request-unenrollment', [StudentCourseController::class, 'requestUnenrollment']);
    Route::post('/courses/cancel-unenrollment',  [StudentCourseController::class, 'cancelUnenrollmentRequest']);
    Route::get('/courses/enrolled-count',        [StudentCourseController::class, 'getEnrolledCoursesCount']);

    // Assignment routes
    Route::get('/assignments',              [StudentAssignmentController::class, 'getAssignments']);
    Route::get('/assignments/{id}',         [StudentAssignmentController::class, 'getAssignment']);
    Route::post('/assignments/{id}/submit', [StudentAssignmentController::class, 'submitAssignment']);

    // Progress routes
    Route::get('/progress',               [ProgressController::class, 'getProgress']);
    Route::get('/progress/average-score', [ProgressController::class, 'getAverageScore']);

    // Video routes
    Route::get('/videos',            [StudentVideosController::class, 'getVideos']);
    Route::post('/videos/{id}/view', [StudentVideosController::class, 'recordView']);

    // Live streams route
    Route::get('/live-streams', [StudentVideosController::class, 'getLiveStreams']);

    // NEW: YouTube connection status
    Route::get('/youtube-connection', [StudentVideosController::class, 'checkYouTubeConnection']);

    // Resource routes
    Route::get('/resources',                     [StudentResourceController::class, 'getResources']);
    Route::get('/resources/{id}/download',       [StudentResourceController::class, 'downloadResource']);

    // Student Google Drive Integration
    Route::get('/resources/drive-status',        [StudentResourceController::class, 'checkDriveStatus']);
    Route::post('/resources/drive/connect',      [StudentResourceController::class, 'initiateDriveConnection']);
    Route::get('/resources/drive/callback',      [StudentResourceController::class, 'handleDriveCallback']);
    Route::post('/resources/{id}/save-to-drive', [StudentResourceController::class, 'saveToDrive']);
    Route::post('/resources/disconnect-drive',   [StudentResourceController::class, 'disconnectDrive']);
});

// Dropdown routes (accessible without prefix for backward compatibility)
Route::get('/dropdown/institutes',  [UserController::class, 'getInstitutesDropdown']);
Route::get('/dropdown/departments', [UserController::class, 'getDepartmentsDropdown']);
Route::get('/dropdown/courses',     [UserController::class, 'getCoursesDropdown']);

// Lecturer Live Stream Routes
Route::delete('/lecturer/live-streams/{streamId}', [VideosController::class, 'deleteLiveStream']);

