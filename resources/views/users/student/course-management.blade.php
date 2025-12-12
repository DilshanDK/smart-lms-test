@extends('layouts.app')

@section('title', 'Course Enrollment - Smart LMS')

@section('styles')
    <style>
        /* Modern Typography System - Poppins Font */
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap');

        :root {
            --primary: #059669;
            --primary-dark: #047857;
            --secondary: #64748b;
            --background: #f8fafc;
            --surface: #ffffff;
            --foreground: #0f172a;
            --muted: #f1f5f9;
            --muted-foreground: #64748b;
            --accent: #10b981;
            --border: #e2e8f0;
            --success: #22c55e;
            --success-light: #dcfce7;
            --warning: #f59e0b;
            --warning-light: #fef3c7;
            --radius: 8px;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        * {
            font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        body {
            background: var(--background);
        }

        .main-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .page-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 2rem;
            border-radius: var(--radius);
            margin-bottom: 2rem;
            box-shadow: var(--shadow-md);
        }

        .page-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .page-subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
            margin: 0;
        }

        .tabs-container {
            background: var(--surface);
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
            margin-bottom: 2rem;
        }

        .tabs-nav {
            display: flex;
            border-bottom: 1px solid var(--border);
        }

        .tab-button {
            flex: 1;
            padding: 1rem 2rem;
            background: none;
            border: none;
            font-size: 1rem;
            font-weight: 500;
            color: var(--muted-foreground);
            cursor: pointer;
            transition: all 0.3s ease;
            border-bottom: 3px solid transparent;
        }

        .tab-button.active {
            color: var(--primary);
            border-bottom-color: var(--primary);
        }

        .tab-button:hover {
            color: var(--primary);
            background: var(--muted);
        }

        .tab-content {
            padding: 2rem;
        }

        .courses-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 1.5rem;
        }

        .course-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 1.5rem;
            transition: all 0.3s ease;
            position: relative;
        }

        .course-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
        }

        .course-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }

        .course-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--foreground);
            margin: 0 0 0.5rem 0;
        }

        .course-department {
            color: var(--primary);
            font-size: 0.875rem;
            font-weight: 500;
        }

        .enrollment-status {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-available {
            background: var(--muted);
            color: var(--muted-foreground);
        }

        .status-enrolled {
            background: var(--success-light);
            color: #166534;
        }

        .status-pending {
            background: var(--warning-light);
            color: #92400e;
        }

        .status-cancelled {
            background: #f3f4f6;
            color: #6b7280;
        }

        .status-unenrollment-requested {
            background: #fbbf24;
            color: #92400e;
        }

        .course-description {
            color: var(--muted-foreground);
            line-height: 1.6;
            margin-bottom: 1.5rem;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .course-actions {
            display: flex;
            gap: 0.5rem;
        }

        .btn-enroll {
            flex: 1;
            background: var(--primary);
            color: white;
            border: none;
            padding: 0.75rem;
            border-radius: var(--radius);
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-enroll:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        .btn-enroll:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }

        .btn-enrolled {
            background: var(--success);
            cursor: default;
        }

        .btn-pending {
            background: var(--warning);
            cursor: pointer;
        }

        .btn-pending:hover {
            background: #d97706;
        }

        /* Loading States */
        .loading {
            text-align: center;
            padding: 4rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            min-height: 300px;
        }

        .spinner {
            width: 40px;
            height: 40px;
            border: 3px solid var(--muted);
            border-top: 3px solid var(--primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--muted-foreground);
        }

        .empty-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .main-container {
                padding: 1rem;
            }

            .page-header {
                padding: 1.5rem;
            }

            .page-title {
                font-size: 2rem;
            }

            .courses-grid {
                grid-template-columns: 1fr;
            }

            .tabs-nav {
                flex-direction: column;
            }
        }
    </style>
@endsection

@section('content')
    <div class="main-container">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">Course Enrollment</h1>
            <p class="page-subtitle">Browse and enroll in available courses</p>
        </div>

        <!-- Tabs Container -->
        <div class="tabs-container">
            <div class="tabs-nav">
                <button class="tab-button active" onclick="switchTab('available')">
                    <i class="fas fa-search"></i> Available Courses
                </button>
                <button class="tab-button" onclick="switchTab('enrolled')">
                    <i class="fas fa-graduation-cap"></i> My Enrolled Courses
                </button>
            </div>

            <!-- Available Courses Tab -->
            <div class="tab-content" id="availableTab">
                <div id="availableCoursesContainer">
                    <div class="loading">
                        <div class="spinner"></div>
                    </div>
                </div>
            </div>

            <!-- Enrolled Courses Tab -->
            <div class="tab-content" id="enrolledTab" style="display: none;">
                <div id="enrolledCoursesContainer">
                    <div class="loading">
                        <div class="spinner"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <!-- Add SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // API Configuration
        const API_BASE = '{{ url('') }}/api/student/courses';

        let currentTab = 'available';

        // DOM Elements
        const availableCoursesContainer = document.getElementById('availableCoursesContainer');
        const enrolledCoursesContainer = document.getElementById('enrolledCoursesContainer');

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            // Check for tab in localStorage
            let tabParam = localStorage.getItem('studentCourseTab');
            if (tabParam === 'enrolled' || tabParam === 'available') {
                // Switch tab and load data immediately
                currentTab = tabParam;
                document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
                document.querySelector(`.tab-button[onclick="switchTab('${tabParam}')"]`).classList.add('active');
                document.getElementById('availableTab').style.display = tabParam === 'available' ? 'block' : 'none';
                document.getElementById('enrolledTab').style.display = tabParam === 'enrolled' ? 'block' : 'none';
                if (tabParam === 'available') {
                    loadAvailableCourses();
                } else {
                    loadEnrolledCourses();
                }
            } else {
                // If no tab in localStorage, check for last tab
                let lastTab = localStorage.getItem('studentCourseLastTab');
                if (lastTab === 'enrolled' || lastTab === 'available') {
                    currentTab = lastTab;
                    document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
                    document.querySelector(`.tab-button[onclick="switchTab('${lastTab}')"]`).classList.add('active');
                    document.getElementById('availableTab').style.display = lastTab === 'available' ? 'block' : 'none';
                    document.getElementById('enrolledTab').style.display = lastTab === 'enrolled' ? 'block' : 'none';
                    if (lastTab === 'available') {
                        loadAvailableCourses();
                    } else {
                        loadEnrolledCourses();
                    }
                } else {
                    switchTab('available');
                }
            }
            // Remove tab param after use
            localStorage.removeItem('studentCourseTab');
        });

        // Tab switching
        function switchTab(tab) {
            currentTab = tab;
            localStorage.setItem('studentCourseLastTab', tab);

            // Update tab buttons
            document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');

            // Show/hide tab content
            document.getElementById('availableTab').style.display = tab === 'available' ? 'block' : 'none';
            document.getElementById('enrolledTab').style.display = tab === 'enrolled' ? 'block' : 'none';

            // Load appropriate data
            if (tab === 'available') {
                loadAvailableCourses();
            } else {
                loadEnrolledCourses();
            }
        }

        // Load available courses
        async function loadAvailableCourses() {
            try {
                const token = localStorage.getItem('auth_token');
                if (!token) {
                    showError(availableCoursesContainer, 'Authentication required. Please login again.');
                    return;
                }

                const response = await fetch(`${API_BASE}/available`, {
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Accept': 'application/json'
                    }
                });

                const result = await response.json();

                if (response.ok && result.status === 'success') {
                    displayAvailableCourses(result.courses);
                } else {
                    showError(availableCoursesContainer, result.message || 'Failed to load courses');
                }
            } catch (error) {
                console.error('Network error:', error);
                showError(availableCoursesContainer, 'Network error. Please check your connection.');
            }
        }

        // Load enrolled courses
        async function loadEnrolledCourses() {
            try {
                const token = localStorage.getItem('auth_token');
                if (!token) {
                    showError(enrolledCoursesContainer, 'Authentication required. Please login again.');
                    return;
                }

                const response = await fetch(`${API_BASE}/enrolled`, {
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Accept': 'application/json'
                    }
                });

                const result = await response.json();

                if (response.ok && result.status === 'success') {
                    displayEnrolledCourses(result.courses);
                } else {
                    showError(enrolledCoursesContainer, result.message || 'Failed to load enrolled courses');
                }
            } catch (error) {
                console.error('Network error:', error);
                showError(enrolledCoursesContainer, 'Network error. Please check your connection.');
            }
        }

        // Display available courses
        function displayAvailableCourses(courses) {
            if (!courses || courses.length === 0) {
                availableCoursesContainer.innerHTML = `
                    <div class="empty-state">
                        <div class="empty-icon">📚</div>
                        <h3>No Courses Available</h3>
                        <p>There are currently no courses available for enrollment.</p>
                    </div>
                `;
                return;
            }

            let html = '<div class="courses-grid">';

            courses.forEach(course => {
                const statusClass = `status-${course.enrollment_status}`;
                let buttonContent = '';
                let buttonClass = 'btn-enroll';
                let buttonDisabled = '';
                let onClick = '';

                switch (course.enrollment_status) {
                    case 'available':
                        buttonContent = '<i class="fas fa-plus"></i> Enroll Now';
                        onClick = `onclick="enrollInCourse('${course.id}')"`;
                        break;
                    case 'pending':
                        buttonContent = '<i class="fas fa-times"></i> Cancel Request';
                        buttonClass = 'btn-enroll btn-pending';
                        onClick = `onclick="cancelEnrollmentRequest('${course.id}')"`;
                        break;
                    case 'enrolled':
                        buttonContent = '<i class="fas fa-check"></i> Enrolled';
                        buttonClass = 'btn-enroll btn-enrolled';
                        buttonDisabled = 'disabled';
                        break;
                }

                html += `
                    <div class="course-card">
                        <div class="course-header">
                            <div>
                                <h3 class="course-title">${course.title}</h3>
                                <div class="course-department">${course.department}</div>
                            </div>
                            <span class="enrollment-status ${statusClass}">
                                ${course.enrollment_status}
                            </span>
                        </div>

                        <p class="course-description">
                            ${course.description}
                        </p>

                        <div class="course-actions">
                            <button class="${buttonClass}" ${buttonDisabled} ${onClick}>
                                ${buttonContent}
                            </button>
                        </div>
                    </div>
                `;
            });

            html += '</div>';
            availableCoursesContainer.innerHTML = html;
        }

        // Display enrolled courses
        function displayEnrolledCourses(courses) {
            if (!courses || courses.length === 0) {
                enrolledCoursesContainer.innerHTML = `
                    <div class="empty-state">
                        <div class="empty-icon">🎓</div>
                        <h3>No Enrolled Courses</h3>
                        <p>You haven't enrolled in any courses yet. Browse available courses to get started!</p>
                    </div>
                `;
                return;
            }

            let html = '<div class="courses-grid">';

            courses.forEach(course => {
                let buttonContent = '';
                let buttonClass = 'btn-enroll';
                let buttonDisabled = '';
                let onClick = '';
                let statusClass = 'status-enrolled';
                let statusText = 'enrolled';

                // Handle different enrollment statuses
                if (course.enrollment_status === 'unenrollment_requested') {
                    statusClass = 'status-unenrollment-requested';
                    statusText = 'unenrollment requested';
                    buttonContent = '<i class="fas fa-undo"></i> Cancel Unenrollment';
                    buttonClass = 'btn-enroll btn-pending';
                    onClick = `onclick="cancelUnenrollmentRequest('${course.id}')"`;
                } else {
                    statusClass = 'status-enrolled';
                    statusText = 'enrolled';
                    buttonContent = '<i class="fas fa-sign-out-alt"></i> Request Unenrollment';
                    buttonClass = 'btn-enroll';
                    onClick = `onclick="requestUnenrollment('${course.id}')"`;
                }

                html += `
                    <div class="course-card">
                        <div class="course-header">
                            <div>
                                <h3 class="course-title">${course.title}</h3>
                                <div class="course-department">${course.department}</div>
                            </div>
                            <span class="enrollment-status ${statusClass}">
                                ${statusText}
                            </span>
                        </div>

                        <p class="course-description">
                            ${course.description}
                        </p>

                        <div class="course-actions">
                            <button class="${buttonClass}" ${buttonDisabled} ${onClick}>
                                ${buttonContent}
                            </button>
                        </div>
                    </div>
                `;
            });

            html += '</div>';
            enrolledCoursesContainer.innerHTML = html;
        }

        // Enroll in course
        async function enrollInCourse(courseId) {
            try {
                const token = localStorage.getItem('auth_token');
                if (!token) {
                    await Swal.fire({
                        icon: 'warning',
                        title: 'Authentication Required',
                        text: 'Please login again.',
                        confirmButtonColor: '#059669'
                    });
                    return;
                }

                // Disable the button temporarily
                const button = event.target;
                const originalContent = button.innerHTML;
                button.disabled = true;
                button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enrolling...';

                const response = await fetch(`${API_BASE}/request-enrollment`, {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ course_id: courseId })
                });

                const result = await response.json();

                if (response.ok && result.status === 'success') {
                    await Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Enrollment request submitted successfully!',
                        confirmButtonColor: '#059669'
                    });
                    loadAvailableCourses(); // Reload to show updated status
                } else {
                    await Swal.fire({
                        icon: 'error',
                        title: 'Enrollment Failed',
                        text: result.message || 'Failed to submit enrollment request',
                        confirmButtonColor: '#059669'
                    });
                    // Restore button if there was an error
                    button.disabled = false;
                    button.innerHTML = originalContent;
                }
            } catch (error) {
                console.error('Enrollment error:', error);
                await Swal.fire({
                    icon: 'error',
                    title: 'Network Error',
                    text: 'Please try again.',
                    confirmButtonColor: '#059669'
                });
                // Restore button
                const button = event.target;
                button.disabled = false;
                button.innerHTML = '<i class="fas fa-plus"></i> Enroll Now';
            }
        }

        // Cancel enrollment request
        async function cancelEnrollmentRequest(courseId) {
            const result = await Swal.fire({
                title: 'Are you sure?',
                text: 'Do you want to cancel your enrollment request for this course?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#f97316',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, cancel it!',
                cancelButtonText: 'No, keep it'
            });

            if (!result.isConfirmed) {
                return;
            }

            try {
                const token = localStorage.getItem('auth_token');
                if (!token) {
                    await Swal.fire({
                        icon: 'warning',
                        title: 'Authentication Required',
                        text: 'Please login again.',
                        confirmButtonColor: '#059669'
                    });
                    return;
                }

                // Disable the button temporarily
                const button = event.target;
                const originalContent = button.innerHTML;
                button.disabled = true;
                button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Cancelling...';

                const response = await fetch(`${API_BASE}/cancel-enrollment`, {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ course_id: courseId })
                });

                const responseResult = await response.json();

                if (response.ok && responseResult.status === 'success') {
                    await Swal.fire({
                        icon: 'success',
                        title: 'Cancelled!',
                        text: 'Enrollment request cancelled successfully!',
                        confirmButtonColor: '#059669'
                    });
                    loadAvailableCourses(); // Reload to show updated status
                } else {
                    await Swal.fire({
                        icon: 'error',
                        title: 'Cancellation Failed',
                        text: responseResult.message || 'Failed to cancel enrollment request',
                        confirmButtonColor: '#059669'
                    });
                    // Restore button if there was an error
                    button.disabled = false;
                    button.innerHTML = originalContent;
                }
            } catch (error) {
                console.error('Cancel enrollment error:', error);
                await Swal.fire({
                    icon: 'error',
                    title: 'Network Error',
                    text: 'Please try again.',
                    confirmButtonColor: '#059669'
                });
                // Restore button
                const button = event.target;
                button.disabled = false;
                button.innerHTML = '<i class="fas fa-times"></i> Cancel Request';
            }
        }

        // Request unenrollment
        async function requestUnenrollment(courseId) {
            const result = await Swal.fire({
                title: 'Request Unenrollment?',
                text: 'Are you sure you want to request unenrollment from this course? This action will need admin approval.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#f97316',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, request unenrollment',
                cancelButtonText: 'Cancel'
            });

            if (!result.isConfirmed) {
                return;
            }

            try {
                const token = localStorage.getItem('auth_token');
                if (!token) {
                    await Swal.fire({
                        icon: 'warning',
                        title: 'Authentication Required',
                        text: 'Please login again.',
                        confirmButtonColor: '#059669'
                    });
                    return;
                }

                // Disable the button temporarily
                const button = event.target;
                const originalContent = button.innerHTML;
                button.disabled = true;
                button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Requesting...';

                const response = await fetch(`${API_BASE}/request-unenrollment`, {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ course_id: courseId })
                });

                const responseResult = await response.json();

                if (response.ok && responseResult.status === 'success') {
                    await Swal.fire({
                        icon: 'success',
                        title: 'Request Submitted!',
                        text: 'Unenrollment request submitted successfully! Your request will be reviewed by administrators.',
                        confirmButtonColor: '#059669'
                    });
                    loadEnrolledCourses(); // Reload to show updated status
                } else {
                    await Swal.fire({
                        icon: 'error',
                        title: 'Request Failed',
                        text: responseResult.message || 'Failed to submit unenrollment request',
                        confirmButtonColor: '#059669'
                    });
                    // Restore button if there was an error
                    button.disabled = false;
                    button.innerHTML = originalContent;
                }
            } catch (error) {
                console.error('Unenrollment request error:', error);
                await Swal.fire({
                    icon: 'error',
                    title: 'Network Error',
                    text: 'Please try again.',
                    confirmButtonColor: '#059669'
                });
                // Restore button
                const button = event.target;
                button.disabled = false;
                button.innerHTML = '<i class="fas fa-sign-out-alt"></i> Request Unenrollment';
            }
        }

        // Cancel unenrollment request
        async function cancelUnenrollmentRequest(courseId) {
            const result = await Swal.fire({
                title: 'Cancel Unenrollment Request?',
                text: 'Are you sure you want to cancel your unenrollment request?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#059669',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, cancel request',
                cancelButtonText: 'No, keep it'
            });

            if (!result.isConfirmed) {
                return;
            }

            try {
                const token = localStorage.getItem('auth_token');
                if (!token) {
                    await Swal.fire({
                        icon: 'warning',
                        title: 'Authentication Required',
                        text: 'Please login again.',
                        confirmButtonColor: '#059669'
                    });
                    return;
                }

                // Disable the button temporarily
                const button = event.target;
                const originalContent = button.innerHTML;
                button.disabled = true;
                button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Cancelling...';

                const response = await fetch(`${API_BASE}/cancel-unenrollment`, {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ course_id: courseId })
                });

                const responseResult = await response.json();

                if (response.ok && responseResult.status === 'success') {
                    await Swal.fire({
                        icon: 'success',
                        title: 'Request Cancelled!',
                        text: 'Unenrollment request cancelled successfully!',
                        confirmButtonColor: '#059669'
                    });
                    loadEnrolledCourses(); // Reload to show updated status
                } else {
                    await Swal.fire({
                        icon: 'error',
                        title: 'Cancellation Failed',
                        text: responseResult.message || 'Failed to cancel unenrollment request',
                        confirmButtonColor: '#059669'
                    });
                    // Restore button if there was an error
                    button.disabled = false;
                    button.innerHTML = originalContent;
                }
            } catch (error) {
                console.error('Cancel unenrollment error:', error);
                await Swal.fire({
                    icon: 'error',
                    title: 'Network Error',
                    text: 'Please try again.',
                    confirmButtonColor: '#059669'
                });
                // Restore button
                const button = event.target;
                button.disabled = false;
                button.innerHTML = '<i class="fas fa-undo"></i> Cancel Unenrollment';
            }
        }

        // Show error message
        function showError(container, message) {
            container.innerHTML = `
                <div class="empty-state">
                    <div class="empty-icon" style="color: #ef4444;">⚠️</div>
                    <h3>Error Loading Courses</h3>
                    <p>${message}</p>
                    <button onclick="${container === availableCoursesContainer ? 'loadAvailableCourses()' : 'loadEnrolledCourses()'}" class="btn-enroll">
                        <i class="fas fa-refresh"></i> Try Again
                    </button>
                </div>
            `;
        }
    </script>
@endsection
