@extends('layouts.app')

@section('title', 'My Courses - Smart LMS')

@section('styles')
    <style>
        /* Modern Typography System - Poppins Font */
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap');

        :root {
            --primary: #7c3aed;
            --primary-dark: #6d28d9;
            --secondary: #64748b;
            --background: #f8fafc;
            --surface: #ffffff;
            --foreground: #0f172a;
            --muted: #f1f5f9;
            --muted-foreground: #64748b;
            --accent: #8b5cf6;
            --border: #e2e8f0;
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
            margin-bottom: 1rem;
        }

        .lecturer-info {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-top: 1rem;
        }

        .lecturer-avatar {
            width: 60px;
            height: 60px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .lecturer-details h3 {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 600;
        }

        .lecturer-details p {
            margin: 0;
            opacity: 0.8;
        }

        .stats-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--surface);
            padding: 1.5rem;
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
            text-align: center;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .stat-icon {
            width: 3rem;
            height: 3rem;
            margin: 0 auto 1rem;
            background: var(--primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.25rem;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: var(--muted-foreground);
            font-weight: 500;
        }

        .courses-section {
            background: var(--surface);
            border-radius: var(--radius);
            padding: 2rem;
            box-shadow: var(--shadow-sm);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--muted);
        }

        .section-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--foreground);
            margin: 0;
        }

        .courses-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 1.5rem;
        }

        .course-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            overflow: hidden;
            transition: all 0.3s ease;
            position: relative;
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .course-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, var(--primary), var(--accent));
        }

        .course-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
            border-color: var(--primary);
        }

        .course-banner {
            background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
            padding: 1.5rem;
            color: white;
        }

        .course-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 0.75rem;
        }

        .course-title {
            font-size: 1.35rem;
            font-weight: 700;
            color: white;
            margin: 0;
            line-height: 1.3;
            flex: 1;
        }

        .course-status-badge {
            padding: 0.35rem 0.75rem;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-left: 0.5rem;
        }

        .status-active {
            background: rgba(255, 255, 255, 0.25);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.5);
        }

        .status-inactive {
            background: rgba(0, 0, 0, 0.2);
            color: rgba(255, 255, 255, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .course-department {
            color: rgba(255, 255, 255, 0.9);
            font-size: 0.875rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .course-body {
            padding: 1.5rem;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .course-description {
            color: var(--muted-foreground);
            line-height: 1.6;
            margin-bottom: 1.5rem;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            font-size: 0.9rem;
            flex: 1;
        }

        .course-stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1.5rem;
            padding: 1rem;
            background: var(--muted);
            border-radius: var(--radius);
        }

        .stat-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .stat-icon {
            width: 2.5rem;
            height: 2.5rem;
            background: white;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            font-size: 1rem;
            box-shadow: var(--shadow-sm);
        }

        .stat-content {
            flex: 1;
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--foreground);
            line-height: 1;
            margin-bottom: 0.25rem;
        }

        .stat-label {
            font-size: 0.75rem;
            color: var(--muted-foreground);
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .course-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.75rem;
        }

        .btn-action {
            padding: 0.85rem 1rem;
            border: none;
            border-radius: var(--radius);
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
            color: white;
            text-decoration: none;
        }

        .btn-secondary {
            background: white;
            color: var(--primary);
            border: 2px solid var(--primary);
        }

        .btn-secondary:hover {
            background: var(--primary);
            color: white;
            text-decoration: none;
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

        .empty-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--foreground);
        }

        .empty-description {
            font-size: 1rem;
            margin-bottom: 2rem;
        }

        /* Loading State */
        .loading {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 4rem;
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

        /* Responsive Design */
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

            .lecturer-info {
                flex-direction: column;
                text-align: center;
            }

            .courses-grid {
                grid-template-columns: 1fr;
            }

            .stat-card {
                padding: 1rem;
            }

            .courses-section {
                padding: 1rem;
            }
        }
    </style>
@endsection

@section('content')
    <div class="main-container">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">My Courses</h1>
            <p class="page-subtitle">Manage and track your assigned courses</p>

            <div class="lecturer-info" id="lecturerInfo">
                <div class="lecturer-avatar">
                    <i class="fas fa-chalkboard-teacher"></i>
                </div>
                <div class="lecturer-details">
                    <h3 id="lecturerName">Loading...</h3>
                    <p id="lecturerDepartment">Department</p>
                </div>
            </div>
        </div>

        <!-- Stats Section -->
        <div class="stats-section">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-book"></i>
                </div>
                <div class="stat-number" id="totalCourses">0</div>
                <div class="stat-label">Total Courses</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-number" id="totalStudents">0</div>
                <div class="stat-label">Total Students</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-number" id="activeCourses">0</div>
                <div class="stat-label">Active Courses</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-number" id="pendingTasks">0</div>
                <div class="stat-label">Pending Tasks</div>
            </div>
        </div>

        <!-- Courses Section -->
        <div class="courses-section">
            <div class="section-header">
                <h2 class="section-title">My Assigned Courses</h2>
            </div>

            <div id="coursesContainer">
                <div class="loading">
                    <div class="spinner"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        // API Configuration
        const API_BASE = '{{ url('') }}/api';

        // DOM Elements
        const coursesContainer = document.getElementById('coursesContainer');
        const lecturerNameEl = document.getElementById('lecturerName');
        const lecturerDepartmentEl = document.getElementById('lecturerDepartment');
        const totalCoursesEl = document.getElementById('totalCourses');
        const activeCoursesEl = document.getElementById('activeCourses');
        const totalStudentsEl = document.getElementById('totalStudents'); // Add this element

        // Load lecturer courses with student counts
        async function loadLecturerData() {
            try {
                const token = localStorage.getItem('auth_token');

                if (!token) {
                    showError('Authentication required. Please login again.');
                    return;
                }

                // Fetch both course data and student counts in parallel
                const [coursesResponse, studentCountResponse] = await Promise.all([
                    fetch(`${API_BASE}/lecturer/my-courses`, {
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Accept': 'application/json'
                        }
                    }),
                    fetch(`${API_BASE}/lecturer/student-count-by-course`, {
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Accept': 'application/json'
                        }
                    })
                ]);

                const coursesResult = await coursesResponse.json();
                const studentCountResult = await studentCountResponse.json();

                if (coursesResponse.ok && coursesResult.status === 'success' &&
                    studentCountResponse.ok && studentCountResult.status === 'success') {

                    // Create a map of course_id to student_count
                    const studentCountMap = {};
                    studentCountResult.courses.forEach(course => {
                        studentCountMap[course.id] = course.student_count;
                    });

                    // Merge student counts with course data
                    const coursesWithCounts = coursesResult.courses.map(course => ({
                        ...course,
                        student_count: studentCountMap[course.id] || 0
                    }));

                    displayLecturerInfo(coursesResult.lecturer);
                    displayCourses(coursesWithCounts);
                    updateStats(coursesWithCounts, coursesResult.total_courses, studentCountResult.total_students);
                } else {
                    console.error('Error loading data:', { coursesResult, studentCountResult });
                    showError(coursesResult.message || studentCountResult.message || 'Failed to load data');
                }
            } catch (error) {
                console.error('Network error:', error);
                showError('Network error. Please check your connection.');
            }
        }

        // Display lecturer information
        function displayLecturerInfo(lecturer) {
            lecturerNameEl.textContent = lecturer.name || 'Unknown Lecturer';
            lecturerDepartmentEl.textContent = lecturer.department || 'No Department';
        }

        // Display courses in card view with student counts
        function displayCourses(courses) {
            if (!courses || courses.length === 0) {
                coursesContainer.innerHTML = `
                    <div class="empty-state">
                        <div class="empty-icon">📚</div>
                        <h3>No Courses Assigned</h3>
                        <p>You haven't been assigned to any courses yet. Contact your administrator.</p>
                    </div>
                `;
                return;
            }

            let html = '<div class="courses-grid">';

            courses.forEach(course => {
                const statusClass = course.status === 'active' ? 'status-active' : 'status-inactive';
                const statusText = course.status === 'active' ? 'Active' : 'Inactive';

                html += `
                    <div class="course-card">
                        <div class="course-banner">
                            <div class="course-header">
                                <h3 class="course-title">${course.title}</h3>
                                <span class="course-status-badge ${statusClass}">${statusText}</span>
                            </div>
                            <div class="course-department">
                                <i class="fas fa-building"></i>
                                ${course.department}
                            </div>
                        </div>

                        <div class="course-body">
                            <p class="course-description">
                                ${course.description || 'No description available'}
                            </p>

                            <div class="course-stats">
                                <div class="stat-item">
                                    <div class="stat-icon">
                                        <i class="fas fa-users"></i>
                                    </div>
                                    <div class="stat-content">
                                        <div class="stat-value">${course.student_count || 0}</div>
                                        <div class="stat-label">Students</div>
                                    </div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-icon">
                                        <i class="fas fa-clipboard-list"></i>
                                    </div>
                                    <div class="stat-content">
                                        <div class="stat-value">${course.assignment_count || 0}</div>
                                        <div class="stat-label">Assignments</div>
                                    </div>
                                </div>
                            </div>

                            <div class="course-actions">
                                <button class="btn-action btn-primary" onclick="viewStudents('${course.id}', '${course.title.replace(/'/g, "\\'")}')">
                                    <i class="fas fa-users"></i>
                                    Students
                                </button>
                                <button class="btn-action btn-secondary" onclick="manageCourse('${course.id}')">
                                    <i class="fas fa-cog"></i>
                                    Manage
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            });

            html += '</div>';
            coursesContainer.innerHTML = html;
        }

        // View students for a specific course
        function viewStudents(courseId, courseTitle) {
            // Store course filter in localStorage
            localStorage.setItem('lecturerStudentFilterCourse', courseId);
            localStorage.setItem('lecturerStudentFilterCourseTitle', courseTitle);

            // Redirect to student management page
            window.location.href = '{{ route("lecturer.student-management") }}';
        }

        // Update statistics with real student counts
        function updateStats(courses, totalCourses, totalStudents) {
            totalCoursesEl.textContent = totalCourses || 0;
            totalStudentsEl.textContent = totalStudents || 0; // Update total students stat

            if (courses && courses.length > 0) {
                const activeCourses = courses.filter(course => course.status === 'active').length;
                activeCoursesEl.textContent = activeCourses;
            } else {
                activeCoursesEl.textContent = '0';
            }

            // You can update pending tasks later
            document.getElementById('pendingTasks').textContent = '0';
        }

        // Show error message
        function showError(message) {
            coursesContainer.innerHTML = `
                <div class="empty-state">
                    <div class="empty-icon" style="color: #ef4444;">⚠️</div>
                    <h3 class="empty-title">Error Loading Courses</h3>
                    <p class="empty-description">${message}</p>
                    <button onclick="loadLecturerData()" class="btn-action btn-primary" style="max-width: 200px; margin: 0 auto;">
                        <i class="fas fa-refresh"></i> Try Again
                    </button>
                </div>
            `;
        }

        // Course management functions (to be implemented)
        function manageCourse(courseId) {
            console.log('Managing course:', courseId);
            alert(`Course management for course ID: ${courseId} - Feature coming soon!`);
        }

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Lecturer Course Management page initialized');
            loadLecturerData();
        });
    </script>
@endsection
