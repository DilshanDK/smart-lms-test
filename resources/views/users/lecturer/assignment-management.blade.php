@extends('layouts.app')

@section('title', 'Assignment Management - Smart LMS')

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

        .action-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            background: var(--surface);
            padding: 1.5rem;
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
        }

        .btn-create {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: var(--radius);
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: var(--shadow-sm);
        }

        .btn-create:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .assignments-section {
            background: var(--surface);
            border-radius: var(--radius);
            padding: 2rem;
            box-shadow: var(--shadow-sm);
        }

        .assignments-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 1.5rem;
        }

        .assignment-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 1.5rem;
            transition: all 0.3s ease;
            position: relative;
        }

        .assignment-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
            border-color: var(--primary);
        }

        .assignment-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }

        .assignment-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--foreground);
            margin: 0;
        }

        .assignment-status {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-draft {
            background: #fef3c7;
            color: #92400e;
        }

        .status-published {
            background: var(--success-light);
            color: #166534;
        }

        .status-upcoming {
            background: #dbeafe;
            color: #1e40af;
        }

        .status-ongoing {
            background: #dcfce7;
            color: #166534;
        }

        .status-expired {
            background: #fee2e2;
            color: #dc2626;
        }

        .assignment-meta {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
            font-size: 0.875rem;
            color: var(--muted-foreground);
        }

        .assignment-description {
            color: var(--muted-foreground);
            line-height: 1.5;
            margin-bottom: 1.5rem;
        }

        .assignment-actions {
            display: flex;
            gap: 0.5rem;
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

        .btn-secondary {
            background: var(--muted);
            color: var(--muted-foreground);
        }

        .btn-action:hover {
            transform: translateY(-1px);
        }

        /* Modal Styles - Override Bootstrap backdrop */
        .modal-backdrop {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            bottom: 0 !important;
            width: 100vw !important;
            height: 100vh !important;
            background: rgba(0, 0, 0, 0.7) !important;
            backdrop-filter: blur(5px) !important;
            display: none !important;
            justify-content: center !important;
            align-items: center !important;
            z-index: 10000 !important;
            /* Override Bootstrap CSS variables */
            --bs-backdrop-opacity: 1 !important;
            --bs-backdrop-zindex: 10000 !important;
            --bs-backdrop-bg: rgba(0, 0, 0, 0.7) !important;
        }

        .modal-backdrop.show {
            display: flex !important;
            opacity: 1 !important;
        }

        /* Ensure no Bootstrap modal classes interfere */
        .modal-backdrop:not(.show) {
            display: none !important;
        }

        .modal-container {
            background: var(--surface);
            border-radius: 12px;
            padding: 0;
            width: 90%;
            max-width: 800px;
            max-height: 90vh;
            overflow: hidden;
            box-shadow: var(--shadow-lg);
            animation: modalSlideIn 0.3s ease-out;
            position: relative;
            z-index: 10001;
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: scale(0.9) translateY(-20px);
            }
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        .modal-header {
            padding: 2rem 2rem 1rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--foreground);
            margin: 0;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--muted-foreground);
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 50%;
            transition: all 0.3s ease;
        }

        .modal-close:hover {
            background: var(--muted);
            color: var(--foreground);
        }

        .modal-body {
            padding: 2rem;
            max-height: calc(90vh - 200px);
            overflow-y: auto;
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
            background: var(--surface);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.1);
        }

        .questions-container {
            margin-top: 2rem;
        }

        .question-card {
            background: var(--muted);
            border-radius: var(--radius);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border: 1px solid var(--border);
        }

        .question-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .question-number {
            font-weight: 700;
            color: var(--primary);
        }

        .btn-remove {
            background: #ef4444;
            color: white;
            border: none;
            padding: 0.25rem 0.5rem;
            border-radius: var(--radius);
            font-size: 0.75rem;
            cursor: pointer;
        }

        .options-container {
            margin-top: 1rem;
        }

        .option-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
        }

        .option-radio {
            margin: 0;
        }

        .option-input {
            flex: 1;
        }

        .btn-add {
            background: var(--success);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: var(--radius);
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            margin-top: 1rem;
        }

        .modal-footer {
            padding: 1rem 2rem 2rem;
            border-top: 1px solid var(--border);
            display: flex;
            gap: 1rem;
        }

        .btn-submit {
            flex: 1;
            background: var(--primary);
            color: white;
            border: none;
            padding: 1rem;
            border-radius: var(--radius);
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-cancel {
            flex: 1;
            background: var(--muted);
            color: var(--muted-foreground);
            border: none;
            padding: 1rem;
            border-radius: var(--radius);
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
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

            .modal-container {
                width: 95%;
                margin: 1rem;
            }

            .assignments-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Additional Bootstrap Override - Force backdrop styles */
        body.modal-open .modal-backdrop {
            background: rgba(0, 0, 0, 0.7) !important;
            opacity: 1 !important;
        }

        /* Remove any Bootstrap modal fade effects that might interfere */
        .modal-backdrop.fade {
            opacity: 1 !important;
        }

        .modal-backdrop.fade.show {
            opacity: 1 !important;
        }
    </style>
@endsection

@section('content')
    <div class="main-container">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">Assignment Management</h1>
            <p class="page-subtitle">Create and manage quizzes and assignments for your courses</p>
        </div>

        <!-- Action Bar -->
        <div class="action-bar">
            <div>
                <h3 style="margin: 0; color: var(--foreground);">My Assignments</h3>
                <p style="margin: 0; color: var(--muted-foreground); font-size: 0.875rem;">Manage your course assignments and quizzes</p>
            </div>
            <button class="btn-create" id="createAssignmentBtn">
                <i class="fas fa-plus"></i> Create New Assignment
            </button>
        </div>

        <!-- Assignments Section -->
        <div class="assignments-section">
            <div id="assignmentsContainer">
                <div class="loading">
                    <div class="spinner"></div>
                </div>
            </div>
        </div>

        <!-- Create Assignment Modal -->
        <div class="modal-backdrop" id="assignmentModal">
            <div class="modal-container" id="assignmentModalContainer">
                <div class="modal-header">
                    <h2 class="modal-title" id="modalTitle">Create New Assignment</h2>
                    <button class="modal-close" id="closeModalBtn" type="button">&times;</button>
                </div>

                <div class="modal-body">
                    <form id="assignmentForm">
                        <input type="hidden" id="assignmentId" name="assignment_id">

                        <!-- Basic Information -->
                        <div class="form-group">
                            <label class="form-label" for="assignmentTitle">Assignment Title</label>
                            <input type="text" id="assignmentTitle" name="title" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="assignmentCourse">Course</label>
                            <select id="assignmentCourse" name="course_id" class="form-control" required>
                                <option value="">Select Course</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="assignmentDescription">Description</label>
                            <textarea id="assignmentDescription" name="description" class="form-control" rows="3"></textarea>
                        </div>

                        <!-- Date and Time Settings -->
                        <div class="form-group">
                            <label class="form-label">Assignment Duration</label>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                                <div>
                                    <label class="form-label" for="startDate">Start Date</label>
                                    <input type="date" id="startDate" name="start_date" class="form-control" required>
                                </div>
                                <div>
                                    <label class="form-label" for="endDate">End Date</label>
                                    <input type="date" id="endDate" name="end_date" class="form-control" required>
                                </div>
                            </div>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                <div>
                                    <label class="form-label" for="startTime">Start Time</label>
                                    <input type="time" id="startTime" name="start_time" class="form-control" required>
                                </div>
                                <div>
                                    <label class="form-label" for="endTime">End Time</label>
                                    <input type="time" id="endTime" name="end_time" class="form-control" required>
                                </div>
                            </div>
                        </div>

                        <!-- Questions Section -->
                        <div class="form-group">
                            <label class="form-label">Questions</label>
                            <div class="questions-container" id="questionsContainer">
                                <!-- Questions will be added here -->
                            </div>
                            <button type="button" class="btn-add" id="addQuestionBtn">
                                <i class="fas fa-plus"></i> Add Question
                            </button>
                        </div>
                    </form>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-cancel" id="cancelBtn">Cancel</button>
                    <button type="button" class="btn-submit" id="saveDraftBtn">Save as Draft</button>
                    <button type="button" class="btn-submit" id="publishBtn">Publish Assignment</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // API Configuration
        const API_BASE = '{{ url('') }}/api';

        // Global variables
        let lecturerCourses = [];
        let questionCount = 0;
        let assignmentsData = [];
        let isEditMode = false;
        let currentAssignmentId = null;

        // DOM Elements
        const assignmentsContainer = document.getElementById('assignmentsContainer');
        const createAssignmentBtn = document.getElementById('createAssignmentBtn');
        const assignmentModal = document.getElementById('assignmentModal');
        const closeModalBtn = document.getElementById('closeModalBtn');
        const assignmentForm = document.getElementById('assignmentForm');
        const questionsContainer = document.getElementById('questionsContainer');
        const addQuestionBtn = document.getElementById('addQuestionBtn');
        const saveDraftBtn = document.getElementById('saveDraftBtn');
        const publishBtn = document.getElementById('publishBtn');
        const cancelBtn = document.getElementById('cancelBtn');

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            initializeEventListeners();
            loadLecturerCourses();
            loadAssignments();
        });

        // Event Listeners
        function initializeEventListeners() {
            createAssignmentBtn?.addEventListener('click', openCreateModal);
            closeModalBtn?.addEventListener('click', closeModal);
            cancelBtn?.addEventListener('click', closeModal);
            addQuestionBtn?.addEventListener('click', addQuestion);
            saveDraftBtn?.addEventListener('click', () => saveAssignment('draft'));
            publishBtn?.addEventListener('click', () => saveAssignment('published'));

            // Close modal when clicking backdrop
            assignmentModal?.addEventListener('click', function(e) {
                if (e.target === assignmentModal) {
                    closeModal();
                }
            });

            // Date validation
            document.getElementById('startDate')?.addEventListener('change', validateDates);
            document.getElementById('endDate')?.addEventListener('change', validateDates);
        }

        // Load lecturer courses
        async function loadLecturerCourses() {
            try {
                const token = localStorage.getItem('auth_token');
                if (!token) return;

                const response = await fetch(`${API_BASE}/lecturer/my-courses`, {
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Accept': 'application/json'
                    }
                });

                const result = await response.json();
                if (response.ok && result.status === 'success') {
                    lecturerCourses = result.courses || [];
                    populateCourseOptions(lecturerCourses);
                }
            } catch (error) {
                console.error('Error loading courses:', error);
            }
        }

        // Load assignments
        async function loadAssignments() {
            try {
                const token = localStorage.getItem('auth_token');
                if (!token) {
                    showError('Authentication required. Please login again.');
                    return;
                }

                // FIX: Use the correct API route
                const response = await fetch(`${API_BASE}/lecturer/get-assignments`, {
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Accept': 'application/json'
                    }
                });

                const result = await response.json();
                if (response.ok && result.status === 'success') {
                    assignmentsData = result.assignments || [];
                    displayAssignments(assignmentsData);
                } else {
                    showError(result.message || 'Failed to load assignments');
                }
            } catch (error) {
                console.error('Network error:', error);
                showError('Network error. Please check your connection.');
            }
        }

        // Display assignments with enhanced status logic - GROUPED BY COURSE
        function displayAssignments(assignments) {
            if (!assignments || assignments.length === 0) {
                assignmentsContainer.innerHTML = `
                    <div class="empty-state">
                        <div class="empty-icon">📝</div>
                        <h3>No Assignments Created</h3>
                        <p>Create your first assignment to get started.</p>
                    </div>
                `;
                return;
            }

            // Group assignments by course
            const groupedAssignments = {};
            assignments.forEach(assignment => {
                const courseId = assignment.course_id || 'unknown';
                const courseName = assignment.course_name || 'Unknown Course';

                if (!groupedAssignments[courseId]) {
                    groupedAssignments[courseId] = {
                        courseName: courseName,
                        assignments: []
                    };
                }
                groupedAssignments[courseId].assignments.push(assignment);
            });

            let html = '';

            // Display each course group
            Object.keys(groupedAssignments).forEach(courseId => {
                const group = groupedAssignments[courseId];

                html += `
                    <div style="margin-bottom: 3rem;">
                        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 2px solid var(--border);">
                            <i class="fas fa-book" style="color: var(--primary); font-size: 1.5rem;"></i>
                            <h3 style="margin: 0; color: var(--foreground); font-size: 1.5rem; font-weight: 600;">
                                ${group.courseName}
                            </h3>
                            <span style="color: var(--muted-foreground); font-size: 0.875rem;">
                                (${group.assignments.length} assignment${group.assignments.length !== 1 ? 's' : ''})
                            </span>
                        </div>

                        <div class="assignments-grid">
                `;

                group.assignments.forEach(assignment => {
                    const status = getAssignmentDisplayStatus(assignment);
                    const statusClass = `status-${status.toLowerCase()}`;

                    html += `
                        <div class="assignment-card">
                            <div class="assignment-header">
                                <h3 class="assignment-title">${assignment.title}</h3>
                                <span class="assignment-status ${statusClass}">
                                    ${status}
                                </span>
                            </div>

                            <div class="assignment-meta">
                                <div class="meta-item">
                                    <i class="fas fa-question-circle"></i>
                                    ${assignment.total_questions} questions
                                </div>
                                ${assignment.start_date ? `
                                    <div class="meta-item">
                                        <i class="fas fa-calendar"></i>
                                        ${formatDateRange(assignment)}
                                    </div>
                                ` : ''}
                            </div>

                            ${assignment.description ? `
                                <p class="assignment-description">${assignment.description}</p>
                            ` : ''}

                            <div class="assignment-actions">
                                <button class="btn-action btn-secondary" onclick="editAssignment('${assignment.encrypted_id}')">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button class="btn-action btn-primary" onclick="toggleStatus('${assignment.encrypted_id}', '${assignment.status}')">
                                    <i class="fas fa-${assignment.status === 'published' ? 'eye-slash' : 'eye'}"></i>
                                    ${assignment.status === 'published' ? 'Unpublish' : 'Publish'}
                                </button>
                                <button class="btn-action btn-secondary" onclick="deleteAssignment('${assignment.encrypted_id}')">
                                    <i class="fas fa-trash"></i>
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

            assignmentsContainer.innerHTML = html;
        }

        // Get assignment display status based on dates and current status
        function getAssignmentDisplayStatus(assignment) {
            if (assignment.status === 'draft') {
                return 'Draft';
            }

            if (!assignment.start_date || !assignment.end_date) {
                return assignment.status === 'published' ? 'Published' : 'Draft';
            }

            const now = new Date();
            const startDateTime = new Date(`${assignment.start_date} ${assignment.start_time || '00:00:00'}`);
            const endDateTime = new Date(`${assignment.end_date} ${assignment.end_time || '23:59:59'}`);

            if (now < startDateTime) {
                return 'Upcoming';
            } else if (now >= startDateTime && now <= endDateTime) {
                return 'Ongoing';
            } else {
                return 'Expired';
            }
        }

        // Format date range for display
        function formatDateRange(assignment) {
            if (!assignment.start_date) return 'No date set';

            const startDate = new Date(assignment.start_date).toLocaleDateString();
            const endDate = new Date(assignment.end_date).toLocaleDateString();

            if (startDate === endDate) {
                return `${startDate} (${assignment.start_time || '00:00'} - ${assignment.end_time || '23:59'})`;
            } else {
                return `${startDate} - ${endDate}`;
            }
        }

        // Populate course options
        function populateCourseOptions(courses) {
            const courseSelect = document.getElementById('assignmentCourse');
            if (!courseSelect) return;

            courseSelect.innerHTML = '<option value="">Select Course</option>';
            courses.forEach(course => {
                courseSelect.innerHTML += `<option value="${course.id}">${course.title}</option>`;
            });
        }

        // Modal functions
        function openCreateModal() {
            isEditMode = false;
            currentAssignmentId = null;
            document.getElementById('modalTitle').textContent = 'Create New Assignment';
            resetForm();
            assignmentModal.classList.add('show');
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            assignmentModal.classList.remove('show');
            document.body.style.overflow = 'auto';
            resetForm();
        }

        function resetForm() {
            assignmentForm.reset();
            document.getElementById('assignmentId').value = '';
            questionsContainer.innerHTML = '';
            questionCount = 0;
            addQuestion(); // Add one default question
        }

        // Date validation
        function validateDates() {
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;

            if (startDate && endDate && new Date(startDate) > new Date(endDate)) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Invalid Dates',
                    text: 'End date cannot be earlier than start date'
                });
                document.getElementById('endDate').value = startDate;
            }
        }

        // Question management
        function addQuestion() {
            questionCount++;
            const questionHtml = `
                <div class="question-card" id="question-${questionCount}">
                    <div class="question-header">
                        <span class="question-number">Question ${questionCount}</span>
                        <button type="button" class="btn-remove" onclick="removeQuestion(${questionCount})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Question Text</label>
                        <textarea class="form-control question-text" placeholder="Enter your question here..." required></textarea>
                    </div>
                    <div class="options-container">
                        <label class="form-label">Options</label>
                        <div class="option-group">
                            <input type="radio" name="correct-${questionCount}" value="0" class="option-radio">
                            <input type="text" class="form-control option-input" placeholder="Option 1" required>
                        </div>
                        <div class="option-group">
                            <input type="radio" name="correct-${questionCount}" value="1" class="option-radio">
                            <input type="text" class="form-control option-input" placeholder="Option 2" required>
                        </div>
                        <div class="option-group">
                            <input type="radio" name="correct-${questionCount}" value="2" class="option-radio">
                            <input type="text" class="form-control option-input" placeholder="Option 3">
                        </div>
                        <div class="option-group">
                            <input type="radio" name="correct-${questionCount}" value="3" class="option-radio">
                            <input type="text" class="form-control option-input" placeholder="Option 4">
                        </div>
                    </div>
                </div>
            `;
            questionsContainer.insertAdjacentHTML('beforeend', questionHtml);
        }

        function removeQuestion(questionId) {
            if (questionsContainer.children.length <= 1) {
                Swal.fire({
                    icon: 'warning',
                    title: 'At least one question is required'
                });
                return;
            }
            document.getElementById(`question-${questionId}`).remove();
        }

        // Edit assignment
        async function editAssignment(assignmentId) {
            try {
                const token = localStorage.getItem('auth_token');
                if (!token) return;

                // Use the correct GET endpoint for fetching assignment details
                const response = await fetch(`${API_BASE}/lecturer/get-assignment/${assignmentId}`, {
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Accept': 'application/json'
                    }
                });

                const result = await response.json();
                if (response.ok && result.status === 'success' && result.assignment) {
                    const assignment = result.assignment;

                    // Set edit mode
                    isEditMode = true;
                    currentAssignmentId = assignmentId;
                    document.getElementById('modalTitle').textContent = 'Edit Assignment';

                    // Populate form
                    document.getElementById('assignmentId').value = assignmentId;
                    document.getElementById('assignmentTitle').value = assignment.title;
                    document.getElementById('assignmentCourse').value = assignment.course_id;
                    document.getElementById('assignmentDescription').value = assignment.description || '';
                    document.getElementById('startDate').value = assignment.start_date || '';
                    document.getElementById('endDate').value = assignment.end_date || '';
                    document.getElementById('startTime').value = assignment.start_time || '';
                    document.getElementById('endTime').value = assignment.end_time || '';

                    // Populate questions
                    questionsContainer.innerHTML = '';
                    questionCount = 0;

                    // Fix: Ensure assignment.questions is an array before forEach
                    if (Array.isArray(assignment.questions) && assignment.questions.length > 0) {
                        assignment.questions.forEach((question, index) => {
                            addQuestion();
                            const questionCard = document.getElementById(`question-${questionCount}`);
                            questionCard.querySelector('.question-text').value = question.question;

                            const optionInputs = questionCard.querySelectorAll('.option-input');
                            const radioInputs = questionCard.querySelectorAll('.option-radio');

                            if (Array.isArray(question.options)) {
                                question.options.forEach((option, optIndex) => {
                                    if (optionInputs[optIndex]) {
                                        optionInputs[optIndex].value = option;
                                    }
                                });
                            }

                            if (
                                typeof question.correct_answer === 'number' &&
                                radioInputs[question.correct_answer]
                            ) {
                                radioInputs[question.correct_answer].checked = true;
                            }
                        });
                    } else {
                        addQuestion();
                    }

                    // Show modal
                    assignmentModal.classList.add('show');
                    document.body.style.overflow = 'hidden';
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Failed to load assignment details',
                        text: result.message || 'Assignment not found or you do not have permission.'
                    });
                }
            } catch (error) {
                console.error('Error loading assignment:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Failed to load assignment details',
                    text: error.message || 'Network or server error.'
                });
            }
        }

        // Save assignment
        async function saveAssignment(status) {
            const formData = new FormData(assignmentForm);
            const questions = [];

            // Collect questions
            document.querySelectorAll('.question-card').forEach((questionCard, index) => {
                const questionText = questionCard.querySelector('.question-text').value.trim();
                const options = Array.from(questionCard.querySelectorAll('.option-input'))
                    .map(input => input.value.trim())
                    .filter(value => value !== '');
                const correctAnswer = questionCard.querySelector('input[type="radio"]:checked')?.value;

                if (questionText && options.length >= 2 && correctAnswer !== undefined) {
                    questions.push({
                        question: questionText,
                        options: options,
                        correct_answer: parseInt(correctAnswer)
                    });
                }
            });

            if (questions.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Please add at least one valid question with at least 2 options'
                });
                return;
            }

            const assignmentData = {
                title: formData.get('title'),
                description: formData.get('description'),
                course_id: formData.get('course_id'),
                start_date: formData.get('start_date'),
                end_date: formData.get('end_date'),
                start_time: formData.get('start_time'),
                end_time: formData.get('end_time'),
                questions: questions,
                status: status
            };

            // Validate required fields
            if (!assignmentData.title || !assignmentData.course_id || !assignmentData.start_date || !assignmentData.end_date) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Please fill in all required fields'
                });
                return;
            }

            try {
                const token = localStorage.getItem('auth_token');
                const url = isEditMode
                    ? `${API_BASE}/lecturer/edit-assignment/${currentAssignmentId}`
                    : `${API_BASE}/lecturer/create-assignment`;
                const method = isEditMode ? 'PUT' : 'POST';

                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(assignmentData)
                });

                const result = await response.json();

                if (response.ok && result.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: isEditMode ? 'Assignment updated successfully!' : 'Assignment created successfully!'
                    });
                    closeModal();
                    loadAssignments();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: result.message || 'Failed to save assignment'
                    });
                }
            } catch (error) {
                console.error('Error saving assignment:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Network error',
                    text: 'Please try again.'
                });
            }
        }

        // Toggle assignment status
        async function toggleStatus(assignmentId, currentStatus) {
            const newStatus = currentStatus === 'published' ? 'draft' : 'published';

            try {
                const token = localStorage.getItem('auth_token');
                // Update to use the new route
                const response = await fetch(`${API_BASE}/lecturer/publication-assignment/${assignmentId}`, {
                    method: 'PUT',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ status: newStatus })
                });

                const result = await response.json();

                if (response.ok && result.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Assignment status updated'
                    });
                    loadAssignments();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: result.message || 'Failed to update status'
                    });
                }
            } catch (error) {
                console.error('Error updating status:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Network error',
                    text: 'Please try again.'
                });
            }
        }

        // Delete assignment (soft delete)
        async function deleteAssignment(assignmentId) {
            const confirm = await Swal.fire({
                title: 'Delete Assignment?',
                text: 'This will permanently remove the assignment. This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Yes, Delete',
                cancelButtonText: 'Cancel'
            });

            if (!confirm.isConfirmed) return;

            try {
                const token = localStorage.getItem('auth_token');
                const response = await fetch(`${API_BASE}/lecturer/delete-assignment/${assignmentId}`, {
                    method: 'DELETE',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Accept': 'application/json'
                    }
                });

                const result = await response.json();

                if (response.ok && result.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Assignment deleted successfully',
                        timer: 2000,
                        showConfirmButton: false
                    });
                    loadAssignments();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: result.message || 'Failed to delete assignment'
                    });
                }
            } catch (error) {
                console.error('Error deleting assignment:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Network error',
                    text: 'Please try again.'
                });
            }
        }

        // Show error
        function showError(message) {
            assignmentsContainer.innerHTML = `
                <div class="empty-state">
                    <div class="empty-icon" style="color: #ef4444;">⚠️</div>
                    <h3>Error Loading Assignments</h3>
                    <p>${message}</p>
                    <button onclick="loadAssignments()" class="btn-primary">
                        <i class="fas fa-refresh"></i> Try Again
                    </button>
                </div>
            `;
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: message
            });
        }
    </script>
@endsection
