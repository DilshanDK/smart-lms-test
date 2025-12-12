@extends('layouts.app')

@section('title', 'Course Management - Smart LMS')

@section('styles')
    <style>
        /* Modern Typography System - Poppins Font */
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap');

        :root {
            --primary: #3b82f6;
            --primary-dark: #2563eb;
            --secondary: #6b7280;
            --background: #f9fafb;
            --surface: #ffffff;
            --foreground: #1f2937;
            --muted: #e5e7eb;
            --muted-foreground: #4b5563;
            --accent: #f97316;
            --border: #d1d5db;
            --radius: 8px;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
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
            background: var(--surface);
            border-radius: var(--radius);
            box-shadow: var(--shadow-md);
        }

        .page-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .page-title {
            color: var(--primary);
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .page-subtitle {
            color: var(--muted-foreground);
            font-size: 1.1rem;
        }

        .add-course-section {
            margin-bottom: 3rem;
            text-align: center;
        }

        .add-course-btn {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: var(--radius);
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: var(--shadow-sm);
        }

        .add-course-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .add-course-btn:active {
            transform: translateY(0);
        }

        /* Modal Styles */
        .modal-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0, 0, 0, 0.6) !important; /* Override Bootstrap backdrop */
            --bs-backdrop-opacity: 1 !important; /* Override Bootstrap CSS variable */
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 10000;
            backdrop-filter: none;
        }

        .modal-backdrop.show {
            display: flex;
        }

        .modal-container {
            background: var(--surface);
            border-radius: 12px;
            padding: 2rem;
            width: 90%;
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
            transform: scale(0.9);
            transition: transform 0.3s ease;
            scrollbar-width: none; /* Firefox */
            -ms-overflow-style: none; /* IE and Edge */
        }
        .modal-container::-webkit-scrollbar {
            display: none; /* Chrome, Safari, Opera */
        }

        .modal-backdrop.show .modal-container {
            transform: scale(1);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--muted);
        }

        .modal-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--foreground);
            margin: 0;
        }

        .close-btn {
            background: none;
            border: none;
            font-size: 2rem;
            color: var(--muted-foreground);
            cursor: pointer;
            padding: 0;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.3s ease;
        }

        .close-btn:hover {
            background: var(--muted);
            color: var(--foreground);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            font-weight: 600;
            color: var(--foreground);
            margin-bottom: 0.5rem;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid var(--border);
            border-radius: var(--radius);
            font-size: 1rem;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .btn-submit {
            width: 100%;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            border: none;
            padding: 1rem;
            border-radius: var(--radius);
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 1rem;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .btn-submit:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        /* Courses Display */
        .courses-section {
            margin-top: 2rem;
        }

        .department-block {
            margin-bottom: 2rem;
            background: var(--background);
            border-radius: var(--radius);
            padding: 1.5rem;
            box-shadow: var(--shadow-sm);
        }

        .department-header {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }

        .department-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--foreground);
            margin: 0;
        }

        .course-count {
            background: var(--primary);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 600;
            margin-left: 1rem;
        }

        .courses-grid {
            display: flex;
            gap: 1rem;
            overflow-x: auto;
            padding: 0.5rem 0;
            scrollbar-width: none; /* Firefox */
            -ms-overflow-style: none;  /* IE and Edge */
        }
        .courses-grid::-webkit-scrollbar {
            display: none; /* Chrome, Safari, Opera */
        }
        .courses-grid {
            cursor: grab;
        }
        .courses-grid:active {
            cursor: grabbing;
        }

        .course-card {
            flex: 0 0 280px;
            background: var(--surface);
            border-radius: var(--radius);
            padding: 1.5rem;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border);
            transition: all 0.3s ease;
        }

        .course-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
        }

        .course-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 0.5rem;
        }

        .course-description {
            color: var(--muted-foreground);
            margin-bottom: 1rem;
            line-height: 1.5;
        }

        .course-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .course-status {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 600;
        }

        .status-active {
            background: #dcfce7;
            color: #166534;
        }

        .status-inactive {
            background: #fef2f2;
            color: #991b1b;
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
            color: var(--muted-foreground);
        }

        .empty-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        /* Loading States */
        .loading {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .spinner {
            width: 40px;
            height: 40px;
            border: 3px solid var(--muted);
            border-top: 3px solid var(--primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .main-container {
                margin: 1rem;
                padding: 1rem;
            }

            .page-title {
                font-size: 2rem;
            }

            .course-card {
                flex: 0 0 250px;
            }
        }

        .btn-toggle-status {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: var(--radius);
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-toggle-status.active {
            background: #dcfce7;
            color: #166534;
        }

        .btn-toggle-status.inactive {
            background: #fee2e2;
            color: #991b1b;
        }

        .btn-toggle-status:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .btn-toggle-status:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }
    </style>
@endsection

@section('content')
    <div class="main-container">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">Course Management</h1>
            <p class="page-subtitle">Manage courses and organize them by department</p>
        </div>

        <!-- Add Course Section -->
        <div class="add-course-section">
            <button class="add-course-btn" id="openModalBtn">
                <i class="fas fa-plus"></i> Add New Course
            </button>
        </div>

        <!-- Courses Display -->
        <div class="courses-section">
            <div id="coursesContainer">
                <div class="loading">
                    <div class="spinner"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal-backdrop" id="modalBackdrop">
        <div class="modal-container" id="modalContainer">
            <div class="modal-header">
                <h2 class="modal-title">Add New Course</h2>
                <button class="close-btn" id="closeModalBtn">&times;</button>
            </div>

            <form id="courseForm">
                <div class="form-group">
                    <label for="courseTitle" class="form-label">Course Title *</label>
                    <input type="text" id="courseTitle" name="title" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="courseDescription" class="form-label">Course Description *</label>
                    <textarea id="courseDescription" name="description" class="form-control" rows="4" required></textarea>
                </div>

                <div class="form-group">
                    <label for="courseDepartment" class="form-label">Department</label>
                    <input type="text" id="courseDepartment" name="department" class="form-control" placeholder="e.g., Computer Science">
                </div>

                <div class="form-group">
                    <label for="courseStatus" class="form-label">Status</label>
                    <select id="courseStatus" name="status" class="form-control">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

                <button type="submit" class="btn-submit" id="submitBtn">
                    Add Course
                </button>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // DOM Elements
        const openModalBtn = document.getElementById('openModalBtn');
        const closeModalBtn = document.getElementById('closeModalBtn');
        const modalBackdrop = document.getElementById('modalBackdrop');
        const modalContainer = document.getElementById('modalContainer');
        const courseForm = document.getElementById('courseForm');
        const submitBtn = document.getElementById('submitBtn');
        const coursesContainer = document.getElementById('coursesContainer');

        // API Configuration - FIXED: Added /admin prefix
        const API_BASE = '{{ url('') }}/api/admin';

        // Modal Functions
        function openModal() {
            console.log('Opening modal...');
            modalBackdrop.classList.add('show');
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            console.log('Closing modal...');
            modalBackdrop.classList.remove('show');
            document.body.style.overflow = 'auto';
            courseForm.reset();
        }

        // Event Listeners
        openModalBtn.addEventListener('click', openModal);
        closeModalBtn.addEventListener('click', closeModal);

        // Close modal when clicking backdrop
        modalBackdrop.addEventListener('click', function(e) {
            if (e.target === modalBackdrop) {
                closeModal();
            }
        });

        // Prevent modal from closing when clicking inside
        modalContainer.addEventListener('click', function(e) {
            e.stopPropagation();
        });

        // Form Submission
        courseForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const courseData = Object.fromEntries(formData.entries());

            console.log('Submitting course data:', courseData);

            // Disable submit button
            submitBtn.disabled = true;
            submitBtn.textContent = 'Adding Course...';

            try {
                // FIXED: Now uses /api/admin/add-course
                const response = await fetch(`${API_BASE}/add-course`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(courseData)
                });

                const result = await response.json();

                if (response.ok && result.status === 'success') {
                    alert('Course added successfully!');
                    closeModal();
                    loadCourses();
                } else {
                    console.error('Error:', result);
                    alert('Error: ' + (result.message || 'Failed to add course'));
                }
            } catch (error) {
                console.error('Network error:', error);
                alert('Network error. Please check your connection and try again.');
            } finally {
                // Re-enable submit button
                submitBtn.disabled = false;
                submitBtn.textContent = 'Add Course';
            }
        });

        // Load Courses Function
        async function loadCourses() {
            console.log('Loading courses...');

            try {
                // FIXED: Now uses /api/admin/get-courses
                const response = await fetch(`${API_BASE}/get-courses`, {
                    headers: {
                        'Accept': 'application/json',
                        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
                    }
                });

                const result = await response.json();

                if (response.ok && result.status === 'success') {
                    displayCourses(result.courses);
                } else {
                    console.error('Error loading courses:', result);
                    showError('Failed to load courses');
                }
            } catch (error) {
                console.error('Network error loading courses:', error);
                showError('Network error loading courses');
            }
        }

        // Enable drag-to-scroll and wheel-to-horizontal-scroll for .courses-grid
        function enableHorizontalScroll(grid) {
            let isDown = false;
            let startX;
            let scrollLeft;

            grid.addEventListener('mousedown', (e) => {
                isDown = true;
                grid.classList.add('active');
                startX = e.pageX - grid.offsetLeft;
                scrollLeft = grid.scrollLeft;
            });
            grid.addEventListener('mouseleave', () => {
                isDown = false;
                grid.classList.remove('active');
            });
            grid.addEventListener('mouseup', () => {
                isDown = false;
                grid.classList.remove('active');
            });
            grid.addEventListener('mousemove', (e) => {
                if (!isDown) return;
                e.preventDefault();
                const x = e.pageX - grid.offsetLeft;
                const walk = (x - startX) * 2; // scroll-fast
                grid.scrollLeft = scrollLeft - walk;
            });
            // Wheel to horizontal scroll
            grid.addEventListener('wheel', (e) => {
                if (e.deltaY === 0) return;
                e.preventDefault();
                grid.scrollLeft += e.deltaY;
            }, { passive: false });
        }

        // Display Courses Function
        function displayCourses(coursesByDepartment) {
            console.log('Displaying courses:', coursesByDepartment);

            if (!coursesByDepartment || Object.keys(coursesByDepartment).length === 0) {
                coursesContainer.innerHTML = `
                    <div class="empty-state">
                        <div class="empty-icon">📚</div>
                        <h3>No courses available</h3>
                        <p>Click "Add New Course" to create your first course!</p>
                    </div>
                `;
                return;
            }

            let html = '';

            Object.keys(coursesByDepartment).forEach(department => {
                const courses = coursesByDepartment[department];

                html += `
                    <div class="department-block">
                        <div class="department-header">
                            <h2 class="department-title">${department}</h2>
                            <span class="course-count">${courses.length} course${courses.length !== 1 ? 's' : ''}</span>
                        </div>
                        <div class="courses-grid">
                `;

                courses.forEach(course => {
                    const isActive = course.status === 'active';
                    const statusBadgeClass = isActive ? 'active' : 'inactive';
                    const statusText = isActive ? 'Active' : 'Inactive';
                    const statusIcon = isActive ? 'fa-check-circle' : 'fa-times-circle';
                    const toggleButtonClass = isActive ? 'active' : 'inactive';
                    const toggleButtonText = isActive ? 'Inactivate' : 'Activate';
                    const toggleButtonIcon = isActive ? 'fa-ban' : 'fa-check';

                    html += `
                        <div class="course-card">
                            <h3 class="course-title">${course.title}</h3>
                            <p class="course-description">${course.description}</p>
                            <div class="course-meta">
                                <span class="course-status status-${course.status}">
                                    ${course.status.charAt(0).toUpperCase() + course.status.slice(1)}
                                </span>
                                <button
                                    class="btn-toggle-status ${toggleButtonClass}"
                                    onclick="toggleCourseStatus('${course.id}', '${isActive ? 'inactive' : 'active'}', '${course.title.replace(/'/g, "\\'")}')">
                                    <i class="fas ${toggleButtonIcon}"></i>
                                    ${toggleButtonText}
                                </button>
                            </div>
                        </div>
                    `;
                });

                html += `
                        </div>
                    </div>
                `;
            });

            coursesContainer.innerHTML = html;

            // Enable horizontal scroll for all .courses-grid
            document.querySelectorAll('.courses-grid').forEach(enableHorizontalScroll);
        }

        // Toggle course status with SweetAlert confirmation
        async function toggleCourseStatus(courseId, newStatus, courseTitle) {
            const isActivating = newStatus === 'active';

            const result = await Swal.fire({
                title: isActivating ? 'Activate Course?' : 'Inactivate Course?',
                html: `
                    <p>Are you sure you want to <strong>${isActivating ? 'activate' : 'inactivate'}</strong> this course?</p>
                    <p style="color: #6b7280; font-size: 0.875rem; margin-top: 0.5rem;">
                        <strong>Course:</strong> ${courseTitle}
                    </p>
                    ${!isActivating ? '<p style="color: #ef4444; font-size: 0.875rem; margin-top: 0.5rem;"><strong>⚠️ Warning:</strong> Students will not be able to enroll in this course when it is inactive.</p>' : ''}
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: isActivating ? '#3b82f6' : '#ef4444',
                cancelButtonColor: '#6b7280',
                confirmButtonText: isActivating ? '<i class="fas fa-check"></i> Yes, Activate' : '<i class="fas fa-ban"></i> Yes, Inactivate',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            });

            if (!result.isConfirmed) {
                return;
            }

            // Show loading state
            Swal.fire({
                title: 'Updating...',
                text: 'Please wait while we update the course status',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            try {
                const token = localStorage.getItem('auth_token');
                const response = await fetch(`${API_BASE}/update-course-status`, {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        course_id: courseId,
                        status: newStatus
                    })
                });

                const updateResult = await response.json();

                if (response.ok && updateResult.status === 'success') {
                    await Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: `Course ${isActivating ? 'activated' : 'inactivated'} successfully!`,
                        confirmButtonColor: '#3b82f6',
                        timer: 2000,
                        showConfirmButton: false
                    });

                    // Reload courses to reflect changes
                    loadCourses();
                } else {
                    await Swal.fire({
                        icon: 'error',
                        title: 'Update Failed',
                        text: updateResult.message || 'Failed to update course status',
                        confirmButtonColor: '#3b82f6'
                    });
                }
            } catch (error) {
                console.error('Error updating course status:', error);
                await Swal.fire({
                    icon: 'error',
                    title: 'Network Error',
                    text: 'Failed to update course status. Please try again.',
                    confirmButtonColor: '#3b82f6'
                });
            }
        }

        // Show Error Function
        function showError(message) {
            coursesContainer.innerHTML = `
                <div class="empty-state">
                    <div class="empty-icon" style="color: #ef4444;">⚠️</div>
                    <h3>Error Loading Courses</h3>
                    <p>${message}</p>
                    <button onclick="loadCourses()" class="add-course-btn" style="margin-top: 1rem;">
                        <i class="fas fa-refresh"></i> Try Again
                    </button>
                </div>
            `;
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Course Management page initialized');
            loadCourses();
        });

        // Handle Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && modalBackdrop.classList.contains('show')) {
                closeModal();
            }
        });
    </script>
@endsection
