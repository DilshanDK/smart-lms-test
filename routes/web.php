<?php

use Illuminate\Support\Facades\Route;

// Home page route
Route::view('/', 'home')->name('home');

// Role selection page (after Google sign-in for new users)
Route::view('/complete-registration', 'users.role-selection')->name('complete-registration');

// Admin dashboard and management routes
Route::prefix('admin')->name('admin.')->group(function () {

    Route::view('/home', 'users.admin.home')->name('home');
    Route::view('/dashboard', 'users.admin.dashboard')->name('dashboard');
    Route::view('/user-management', 'users.admin.user-management')->name('user-management');
    Route::view('/course-management', 'users.admin.course-management')->name('course-management');
    Route::view('/video-stream', 'users.admin.video-stream')->name('video-stream');
    Route::view('/video-stream-management', 'users.admin.video-stream-management')->name('video-stream-management');
    Route::view('/resource-management', 'users.admin.resource-management')->name('resource-management');
    Route::view('/system-management', 'users.admin.system-management')->name('system-management'); // NEW
    // Add this route for the OAuth callback (GET request)
    Route::get('/google-drive/callback', [\App\Http\Controllers\UserControllers\AdminControllers\ResourceController::class, 'handleGoogleDriveCallback'])->name('google-drive.callback');

});

// Lecturer routes
Route::prefix('lecturer')->name('lecturer.')->group(function () {

    Route::view('/dashboard', 'users.lecturer.dashboard')->name('dashboard');
    Route::view('/requested-dashboard', 'users.lecturer.requested-dashboard')->name('requested-dashboard');
    Route::view('/course-management', 'users.lecturer.course-management')->name('course-management');
    Route::view('/student-management', 'users.lecturer.student-management')->name('student-management');
    Route::view('/assignment-management', 'users.lecturer.assignment-management')->name('assignment-management');
    Route::view('/resource-management', 'users.lecturer.resource-management')->name('resource-management');
    Route::view('/gradebook-management', 'users.lecturer.gradebook-management')->name('gradebook-management');
    Route::view('/videos-management', 'users.lecturer.videos-management')->name('videos-management');

});

// Student routes
Route::prefix('student')->name('student.')->group(function () {

    Route::view('/dashboard', 'users.student.dashboard')->name('dashboard');
    Route::view('/requested-dashboard', 'users.student.requested-dashboard')->name('requested-dashboard');
    Route::view('/course-management', 'users.student.course-management')->name('course-management');
    Route::view('/assignment-management', 'users.student.assignment-management')->name('assignment-management');
    Route::view('/take-quiz', 'users.student.take-quiz')->name('take-quiz');
    Route::view('/progress-management', 'users.student.progress-management')->name('progress-management');
    Route::view('/video-management', 'users.student.video-management')->name('video-management');
    Route::view('/resource-management', 'users.student.resource-management')->name('resource-management');

});
