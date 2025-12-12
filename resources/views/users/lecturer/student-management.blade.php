@extends('layouts.app')

@section('title', 'Student Management - Smart LMS')

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
            --success: #10b981;
            --success-light: #dcfce7;
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
            max-width: 1400px;
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

        .filters-section {
            background: var(--surface);
            padding: 1.5rem;
            border-radius: var(--radius);
            margin-bottom: 2rem;
            box-shadow: var(--shadow-sm);
        }

        .filters-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .filters-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--foreground);
            margin: 0;
        }

        .filter-controls {
            display: flex;
            gap: 1rem;
            align-items: center;
            flex-wrap: wrap;
        }

        .filter-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .filter-label {
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--muted-foreground);
        }

        .filter-select {
            padding: 0.5rem 1rem;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            background: var(--surface);
            color: var(--foreground);
            font-size: 0.875rem;
            min-width: 200px;
        }

        .filter-select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.1);
        }

        .btn-filter {
            padding: 0.5rem 1rem;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: var(--radius);
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-filter:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
        }

        .btn-clear {
            padding: 0.5rem 1rem;
            background: var(--muted);
            color: var(--muted-foreground);
            border: none;
            border-radius: var(--radius);
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-clear:hover {
            background: var(--border);
            color: var(--foreground);
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

        .students-section {
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

        .course-group {
            margin-bottom: 2rem;
        }

        .course-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 1.5rem;
            background: var(--muted);
            border-radius: var(--radius);
            margin-bottom: 1rem;
        }

        .course-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--foreground);
            margin: 0;
        }

        .student-count {
            background: var(--primary);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 600;
        }

        .students-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 1rem;
        }

        .student-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 1.5rem;
            transition: all 0.3s ease;
            position: relative;
        }

        .student-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
            border-color: var(--primary);
        }

        .student-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .student-avatar {
            width: 50px;
            height: 50px;
            background: var(--primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.25rem;
            font-weight: 600;
        }

        .student-info h3 {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--foreground);
            margin: 0 0 0.25rem 0;
        }

        .student-info p {
            font-size: 0.875rem;
            color: var(--muted-foreground);
            margin: 0;
        }

        .student-details {
            font-size: 0.875rem;
            color: var(--muted-foreground);
            line-height: 1.6;
        }

        .student-details div {
            margin-bottom: 0.5rem;
        }

        .student-details strong {
            color: var(--foreground);
        }

        .student-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid var(--border);
        }

        .btn-action {
            flex: 1;
            padding: 0.5rem;
            border: none;
            border-radius: var(--radius);
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            text-align: center;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            color: white;
            text-decoration: none;
        }

        .btn-secondary {
            background: var(--muted);
            color: var(--muted-foreground);
        }

        .btn-secondary:hover {
            background: var(--border);
            color: var(--foreground);
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

            .filter-controls {
                flex-direction: column;
                align-items: stretch;
            }

            .filter-group {
                flex-direction: column;
                align-items: stretch;
            }

            .filter-select {
                min-width: auto;
            }

            .students-grid {
                grid-template-columns: 1fr;
            }

            .students-section {
                padding: 1rem;
            }
        }
    </style>
@endsection

@section('content')
    <div class="main-container">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">Student Management</h1>
            <p class="page-subtitle">Manage students enrolled in your courses</p>
        </div>

        <!-- Filters Section -->
        <div class="filters-section">
            <div class="filters-header">
                <h3 class="filters-title">Filter Students</h3>
            </div>
            <div class="filter-controls">
                <div class="filter-group">
                    <label class="filter-label">Course:</label>
                    <select class="filter-select" id="courseFilter">
                        <option value="">All Courses</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label class="filter-label">Student Name:</label>
                    <input type="text" class="filter-select" id="studentNameFilter" placeholder="Search by student name..." style="min-width: 250px;">
                </div>
                <button class="btn-filter" id="applyFilter">
                    <i class="fas fa-filter"></i> Apply Filters
                </button>
                <button class="btn-clear" id="clearFilter">
                    <i class="fas fa-times"></i> Clear All
                </button>
            </div>
        </div>

        <!-- Stats Section -->
        <div class="stats-section">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-number" id="totalStudents">0</div>
                <div class="stat-label">Total Students</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-book"></i>
                </div>
                <div class="stat-number" id="totalCourses">0</div>
                <div class="stat-label">Courses Teaching</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="stat-number" id="avgStudents">0</div>
                <div class="stat-label">Avg Students/Course</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <div class="stat-number" id="activeStudents">0</div>
                <div class="stat-label">Active Students</div>
            </div>
        </div>

        <!-- Students Section -->
        <div class="students-section">
            <div class="section-header">
                <h2 class="section-title">My Students</h2>
            </div>

            <div id="studentsContainer">
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

        // Global data
        let studentsData = null;
        let availableCourses = [];
        let allStudentsByCourse = []; // Store original data

        // DOM Elements
        const studentsContainer = document.getElementById('studentsContainer');
        const courseFilter = document.getElementById('courseFilter');
        const studentNameFilter = document.getElementById('studentNameFilter');
        const applyFilterBtn = document.getElementById('applyFilter');
        const clearFilterBtn = document.getElementById('clearFilter');

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            const preSelectedCourse = localStorage.getItem('lecturerStudentFilterCourse');
            const preSelectedCourseTitle = localStorage.getItem('lecturerStudentFilterCourseTitle');

            if (preSelectedCourse) {
                loadStudentsData().then(() => {
                    courseFilter.value = preSelectedCourse;
                    applyLocalFilter();

                    if (preSelectedCourseTitle) {
                        const filtersHeader = document.querySelector('.filters-header h3');
                        filtersHeader.textContent = `Filtered by Course: ${preSelectedCourseTitle}`;
                        filtersHeader.style.color = 'var(--primary)';
                    }

                    localStorage.removeItem('lecturerStudentFilterCourse');
                    localStorage.removeItem('lecturerStudentFilterCourseTitle');
                });
            } else {
                loadStudentsData();
            }
        });

        // Load students data
        async function loadStudentsData() {
            try {
                const token = localStorage.getItem('auth_token');

                if (!token) {
                    showError('Authentication required. Please login again.');
                    return;
                }

                const response = await fetch(`${API_BASE}/lecturer/students`, {
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Accept': 'application/json'
                    }
                });

                const result = await response.json();

                if (response.ok && result.status === 'success') {
                    studentsData = result;
                    availableCourses = result.available_courses || [];
                    allStudentsByCourse = result.students_by_course || [];

                    populateCourseFilter();
                    displayStudents(allStudentsByCourse);
                    updateStats(result);
                } else {
                    console.error('Error loading students:', result);
                    showError(result.message || 'Failed to load students');
                }
            } catch (error) {
                console.error('Network error:', error);
                showError('Network error. Please check your connection.');
            }
        }

        // Populate course filter dropdown
        function populateCourseFilter() {
            courseFilter.innerHTML = '<option value="">All Courses</option>';

            availableCourses.forEach(course => {
                const option = document.createElement('option');
                option.value = course.id;
                option.textContent = course.title;
                courseFilter.appendChild(option);
            });
        }

        // Apply local filter (hide/show courses)
        function applyLocalFilter() {
            const courseId = courseFilter.value;
            const studentName = studentNameFilter.value.trim().toLowerCase();

            let filteredData = [...allStudentsByCourse];

            // Filter by course
            if (courseId) {
                filteredData = filteredData.filter(course => course.course_id === courseId);
            }

            // Filter by student name within courses
            if (studentName) {
                filteredData = filteredData.map(course => {
                    const filteredStudents = course.students.filter(student =>
                        student.name.toLowerCase().includes(studentName)
                    );

                    if (filteredStudents.length > 0) {
                        return {
                            ...course,
                            students: filteredStudents,
                            student_count: filteredStudents.length
                        };
                    }
                    return null;
                }).filter(course => course !== null);
            }

            displayStudents(filteredData);
            updateFilterFeedback(courseId, studentName, filteredData);
        }

        // Display students grouped by course
        function displayStudents(studentsByCourse) {
            if (!studentsByCourse || studentsByCourse.length === 0) {
                studentsContainer.innerHTML = `
                    <div class="empty-state">
                        <div class="empty-icon">👥</div>
                        <h3 class="empty-title">No Students Found</h3>
                        <p class="empty-description">
                            No students match your current filters.
                        </p>
                    </div>
                `;
                return;
            }

            let html = '';

            studentsByCourse.forEach(courseGroup => {
                html += `
                    <div class="course-group">
                        <div class="course-header">
                            <h3 class="course-title">${courseGroup.course_name}</h3>
                            <span class="student-count">${courseGroup.student_count} student${courseGroup.student_count !== 1 ? 's' : ''}</span>
                        </div>
                        <div class="students-grid">
                `;

                courseGroup.students.forEach(student => {
                    const initials = student.name.split(' ').map(n => n[0]).join('').toUpperCase();

                    html += `
                        <div class="student-card">
                            <div class="student-header">
                                <div class="student-avatar">${initials}</div>
                                <div class="student-info">
                                    <h3>${student.name}</h3>
                                    <p>${student.email}</p>
                                </div>
                            </div>
                            <div class="student-details">
                                <div><strong>Phone:</strong> ${student.phone || 'Not provided'}</div>
                                <div><strong>Institute:</strong> ${student.institute_name}</div>
                                <div><strong>Gender:</strong> ${student.gender || 'Not specified'}</div>
                                <div><strong>NIC:</strong> ${student.nic || 'Not provided'}</div>
                                ${student.emergency_contact.relation ?
                                    `<div><strong>Emergency:</strong> ${student.emergency_contact.relation} - ${student.emergency_contact.contactNo}</div>` :
                                    ''
                                }
                            </div>
                            <div class="student-actions">
                                <button class="btn-action btn-primary" onclick="viewProgress('${student.id}')">
                                    <i class="fas fa-chart-line"></i> Progress
                                </button>
                                <button class="btn-action btn-secondary" onclick="contactStudent('${student.id}', '${student.email}')">
                                    <i class="fas fa-envelope"></i> Contact
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

            studentsContainer.innerHTML = html;
        }

        // Update statistics based on filtered data
        function updateStats(data) {
            const courseId = courseFilter.value;
            const studentName = studentNameFilter.value.trim().toLowerCase();

            let totalStudents = 0;
            let activeCourses = 0;

            if (courseId || studentName) {
                // Calculate stats from filtered data
                let filteredCourses = [...allStudentsByCourse];

                if (courseId) {
                    filteredCourses = filteredCourses.filter(c => c.course_id === courseId);
                }

                activeCourses = filteredCourses.length;

                filteredCourses.forEach(course => {
                    if (studentName) {
                        const matchingStudents = course.students.filter(s =>
                            s.name.toLowerCase().includes(studentName)
                        );
                        totalStudents += matchingStudents.length;
                    } else {
                        totalStudents += course.student_count;
                    }
                });
            } else {
                // Use original data
                totalStudents = data.total_students || 0;
                activeCourses = data.available_courses?.length || 0;
            }

            document.getElementById('totalStudents').textContent = totalStudents;
            document.getElementById('totalCourses').textContent = data.available_courses?.length || 0;
            document.getElementById('activeStudents').textContent = totalStudents;

            const avgStudents = activeCourses > 0 ? Math.round(totalStudents / activeCourses) : 0;
            document.getElementById('avgStudents').textContent = avgStudents;
        }

        // Clear all filters
        function clearFilters() {
            courseFilter.value = '';
            studentNameFilter.value = '';
            displayStudents(allStudentsByCourse);
            updateStats(studentsData);
            clearFilterFeedback();
        }

        // Update filter feedback
        function updateFilterFeedback(courseId, studentName, filteredData) {
            const filtersHeader = document.querySelector('.filters-header h3');
            let feedbackText = 'Filter Students';

            const filters = [];
            if (courseId) {
                const course = availableCourses.find(c => c.id === courseId);
                filters.push(`Course: ${course ? course.title : 'Unknown'}`);
            }
            if (studentName) {
                filters.push(`Name: "${studentName}"`);
            }

            if (filters.length > 0) {
                const totalStudents = filteredData.reduce((sum, course) => sum + course.student_count, 0);
                feedbackText = `Filtered by ${filters.join(', ')} (${totalStudents} result${totalStudents !== 1 ? 's' : ''})`;
            }

            filtersHeader.textContent = feedbackText;
            filtersHeader.style.color = filters.length > 0 ? 'var(--primary)' : '';
        }

        // Clear filter feedback
        function clearFilterFeedback() {
            const filtersHeader = document.querySelector('.filters-header h3');
            filtersHeader.textContent = 'Filter Students';
            filtersHeader.style.color = '';
        }

        // Real-time filtering on input
        studentNameFilter.addEventListener('input', function() {
            clearTimeout(window.searchTimeout);
            window.searchTimeout = setTimeout(() => {
                if (this.value.trim().length >= 2 || this.value.trim().length === 0) {
                    applyLocalFilter();
                }
            }, 300);
        });

        // Event listeners
        applyFilterBtn.addEventListener('click', applyLocalFilter);
        clearFilterBtn.addEventListener('click', clearFilters);
        courseFilter.addEventListener('change', applyLocalFilter);

        // Placeholder functions
        function viewProgress(studentId) {
            alert(`Viewing progress for student: ${studentId}\n\nProgress tracking feature coming soon!`);
        }

        function contactStudent(studentId, email) {
            window.location.href = `mailto:${email}?subject=Message from Lecturer`;
        }

        // Show error message
        function showError(message) {
            studentsContainer.innerHTML = `
                <div class="empty-state">
                    <div class="empty-icon" style="color: #ef4444;">⚠️</div>
                    <h3 class="empty-title">Error Loading Students</h3>
                    <p class="empty-description">${message}</p>
                    <button onclick="loadStudentsData()" class="btn-action btn-primary" style="max-width: 200px; margin: 1rem auto 0;">
                        <i class="fas fa-refresh"></i> Try Again
                    </button>
                </div>
            `;
        }
    </script>
@endsection
