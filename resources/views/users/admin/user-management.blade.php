@extends('layouts.app')

@section('title', 'User Management - Smart LMS')

@section('styles')
    <style>
        /* Modern Typography System - Poppins Font */
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap');

         :root {
            --primary: #3b82f6; /* Light blue */
            --primary-dark: #2563eb; /* Darker blue */
            --secondary: #6b7280; /* Neutral gray */
            --background: #f9fafb; /* Light gray */
            --surface: #ffffff; /* White */
            --foreground: #1f2937; /* Dark gray */
            --muted: #e5e7eb; /* Muted light gray */
            --muted-foreground: #4b5563; /* Medium gray */
            --accent: #f97316; /* Orange for accents */
            --border: #d1d5db; /* Light border gray */
            --radius: 8px;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        * {
            font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        .container {
            background: var(--surface);
            border-radius: var(--radius);
            padding: 0 2rem;
            box-shadow: var(--shadow-md);
        }

        h1 {
            color: var(--primary);
            font-size: 2rem;
            font-weight: 700;
            text-align: center;
            margin-bottom: 2rem;
        }

        .table-container {
            margin-top: 2rem;
            background: var(--background);
            border-radius: var(--radius);
            padding: 1.5rem;
            box-shadow: var(--shadow-sm);
        }

        .table-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--foreground);
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th,
        .table td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid var(--border);
        }

        .table th {
            background: var(--muted);
            font-weight: 600;
            color: var(--foreground);
        }

        .table td {
            color: var(--muted-foreground);
        }

        .badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: var(--radius);
            font-size: 0.875rem;
            font-weight: 500;
        }

        .badge-student {
            background: var(--muted);
            color: var(--primary);
        }

        .badge-lecturer {
            background: var(--muted);
            color: var(--secondary);
        }

        .action-btn {
            background: var(--primary);
            color: var(--background);
            padding: 0.5rem 1rem;
            border-radius: var(--radius);
            font-size: 0.875rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .action-btn:hover {
            background: var(--primary-dark);
        }

        .action-btn-decline {
            background: var(--accent);
            color: var(--background);
        }

        .action-btn-decline:hover {
            background: #991b1b;
        }

        .action-icon {
            margin-right: 0.5rem;
        }

        .text-success {
            color: #16a34a;
        }

        .text-danger {
            color: #dc2626;
        }

        .empty-state {
            text-align: center;
            padding: 2rem;
            color: var(--secondary);
        }

        .empty-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        .loading {
            text-align: center;
            padding: 2rem;
            color: var(--primary);
        }

        /* Modal Styles */
        .modal-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }

        .modal-container {
            background: var(--surface);
            border-radius: var(--radius);
            padding: 2rem;
            box-shadow: var(--shadow-md);
            width: 90%;
            max-width: 500px;
            position: relative;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .modal-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--foreground);
        }

        .close-btn {
            background: transparent;
            border: none;
            color: var(--secondary);
            font-size: 1.5rem;
            cursor: pointer;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--foreground);
        }

        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            background: var(--muted);
            color: var(--foreground);
        }

        .btn-submit {
            background: var(--primary);
            color: var(--background);
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: var(--radius);
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .btn-submit:hover {
            background: var(--primary-dark);
        }

        .requests-container {
            max-height: 400px;
            overflow-y: auto;
            border: 1px solid var(--border);
            border-radius: var(--radius);
        }

        .bulk-actions {
            padding: 1rem;
            background: var(--muted);
            border-top: 1px solid var(--border);
            display: flex;
            gap: 1rem;
            align-items: center;
            flex-wrap: wrap;
        }

        .checkbox-column {
            width: 40px;
            text-align: center;
        }

        .select-all-checkbox {
            cursor: pointer;
        }

        .request-checkbox {
            cursor: pointer;
        }

        .bulk-btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: var(--radius);
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .bulk-approve {
            background: var(--primary);
            color: white;
        }

        .bulk-approve:hover {
            background: var(--primary-dark);
        }

        .bulk-decline {
            background: var(--accent);
            color: white;
        }

        .bulk-decline:hover {
            background: #991b1b;
        }

        .bulk-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* Additional styles for enrollment management */
        .enrollment-tabs {
            display: flex;
            margin-bottom: 1rem;
            border-bottom: 2px solid var(--border);
            overflow-x: auto;
        }

        .tab-btn {
            background: none;
            border: none;
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
            font-weight: 500;
            color: var(--muted-foreground);
            cursor: pointer;
            transition: all 0.3s ease;
            border-bottom: 3px solid transparent;
            white-space: nowrap;
            min-width: fit-content;
        }

        .tab-btn.active {
            color: var(--primary);
            border-bottom-color: var(--primary);
        }

        .tab-btn:hover {
            color: var(--primary);
            background: var(--muted);
        }

        .enrollment-section {
            margin-top: 1rem;
        }

        .badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: var(--radius);
            font-size: 0.75rem;
            font-weight: 500;
            margin-right: 0.25rem;
            margin-bottom: 0.25rem;
        }

        .badge-student {
            background: var(--muted);
            color: var(--primary);
        }

        /* Additional styles for course selection */
        .course-checkbox-container {
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 0.5rem;
            margin-top: 0.5rem;
        }

        .course-checkbox-item {
            display: flex;
            align-items: center;
            padding: 0.25rem;
            border-radius: 4px;
            margin-bottom: 0.25rem;
            transition: all 0.2s ease;
        }

        .course-checkbox-item:hover {
            background: var(--muted);
        }

        .course-checkbox-item input[type="checkbox"] {
            margin-right: 0.5rem;
        }

        .course-selection-summary {
            font-size: 0.75rem;
            color: var(--muted-foreground);
            margin-top: 0.25rem;
            padding: 0.25rem;
            background: var(--muted);
            border-radius: 4px;
        }

        .btn-action {
            background: var(--primary);
            color: var(--background);
            padding: 0.5rem 1rem;
            border-radius: var(--radius);
            font-size: 0.875rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            display: inline-block;
        }

        .btn-action:hover {
            background: var(--primary-dark);
            color: var(--background);
            text-decoration: none;
        }

        /* Additional styles for unenrollment requests */
        .status-unenrollment-requested {
            background: #fbbf24;
            color: #92400e;
        }

        .unenrollment-checkbox {
            cursor: pointer;
        }
    </style>
@endsection

@section('content')
    <div class="container">
        <h1>User Management</h1>

        <!-- Student Registration Requests -->
        <div class="table-container">
            <h2 class="table-title">Student Registration Requests</h2>
            <div class="requests-container">
                <table class="table">
                    <thead style="position: sticky; top: 0; background: var(--surface); z-index: 10;">
                        <tr>
                            <th class="checkbox-column">
                                <input type="checkbox" class="select-all-checkbox" id="selectAllStudents" onchange="toggleAllCheckboxes('student')">
                            </th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Submitted At</th>
                        </tr>
                    </thead>
                    <tbody id="student-requests">
                        <!-- Dynamic rows populated by JavaScript -->
                    </tbody>
                </table>
            </div>
            <div class="bulk-actions">
                <span class="selected-count" id="studentSelectedCount">0 selected</span>
                <button class="bulk-btn bulk-approve" id="bulkApproveStudents" onclick="bulkAction('student', 'approve')" disabled>
                    <i class="fas fa-check"></i> Approve Selected
                </button>
                <button class="bulk-btn bulk-decline" id="bulkDeclineStudents" onclick="bulkAction('student', 'decline')" disabled>
                    <i class="fas fa-times"></i> Decline Selected
                </button>
            </div>
        </div>

        <!-- Lecturer Registration Requests -->
        <div class="table-container">
            <h2 class="table-title">Lecturer Registration Requests</h2>
            <div class="requests-container">
                <table class="table">
                    <thead style="position: sticky; top: 0; background: var(--surface); z-index: 10;">
                        <tr>
                            <th class="checkbox-column">
                                <input type="checkbox" class="select-all-checkbox" id="selectAllLecturers" onchange="toggleAllCheckboxes('lecturer')">
                            </th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Submitted At</th>
                        </tr>
                    </thead>
                    <tbody id="lecturer-requests">
                        <!-- Dynamic rows populated by JavaScript -->
                    </tbody>
                </table>
            </div>
            <div class="bulk-actions">
                <span class="selected-count" id="lecturerSelectedCount">0 selected</span>
                <button class="bulk-btn bulk-approve" id="bulkApproveLecturers" onclick="bulkAction('lecturer', 'approve')" disabled>
                    <i class="fas fa-check"></i> Approve Selected
                </button>
                <button class="bulk-btn bulk-decline" id="bulkDeclineLecturers" onclick="bulkAction('lecturer', 'decline')" disabled>
                    <i class="fas fa-times"></i> Decline Selected
                </button>
            </div>
        </div>

        <!-- Assigned Lecturers by Department -->
        <div class="table-container" id="assigned-lecturers-section">
            <h2 class="table-title">Assigned Lecturers</h2>
            <div style="margin-bottom:1rem;display:flex;gap:1rem;align-items:center;">
                <input type="text" id="lecturerCourseFilter" class="form-control" style="max-width:300px;display:inline-block;" placeholder="Filter by course title...">
                <button class="btn-submit" id="lecturerCourseFilterBtn" style="padding:0.5rem 1.5rem;">Filter</button>
                <button class="btn-submit" id="lecturerCourseFilterClearBtn" style="padding:0.5rem 1.5rem;background:var(--muted);color:var(--foreground);">Clear</button>
            </div>
            <div id="assigned-lecturers-content">
                <div class="loading">Loading assigned lecturers...</div>
            </div>
        </div>

        <!-- Student Enrollment Management -->
        <div class="table-container" id="enrollment-management-section">
            <h2 class="table-title">Student Enrollments</h2>

            <!-- Enrollment Requests Tab -->
            <div class="enrollment-tabs">
                <button class="tab-btn active" onclick="switchEnrollmentTab('requests')" id="requestsTab">
                    Enrollment Requests
                </button>
                <button class="tab-btn" onclick="switchEnrollmentTab('enrolled')" id="enrolledTab">
                    Enrolled Students
                </button>
                <button class="tab-btn" onclick="switchEnrollmentTab('unenrollment')" id="unenrollmentTab">
                    Unenrollment Requests
                </button>
            </div>

            <!-- Enrollment Requests Section -->
            <div id="enrollmentRequestsSection" class="enrollment-section">
                <div class="requests-container">
                    <table class="table">
                        <thead style="position: sticky; top: 0; background: var(--surface); z-index: 10;">
                            <tr>
                                <th class="checkbox-column">
                                    <input type="checkbox" class="select-all-checkbox" id="selectAllEnrollments" onchange="toggleAllEnrollmentCheckboxes()">
                                </th>
                                <th>Student Name</th>
                                <th>Course</th>
                                <th>Department</th>
                                <th>Requested At</th>
                            </tr>
                        </thead>
                        <tbody id="enrollment-requests">
                            <!-- Dynamic rows populated by JavaScript -->
                        </tbody>
                    </table>
                </div>
                <div class="bulk-actions">
                    <span class="selected-count" id="enrollmentSelectedCount">0 selected</span>
                    <button class="bulk-btn bulk-approve" id="bulkEnrollStudents" onclick="bulkEnrollmentAction('enroll')" disabled>
                        <i class="fas fa-check"></i> Enroll Selected
                    </button>
                    <button class="bulk-btn bulk-decline" id="bulkDeclineEnrollments" onclick="bulkEnrollmentAction('decline')" disabled>
                        <i class="fas fa-times"></i> Decline Selected
                    </button>
                </div>
            </div>

            <!-- Enrolled Students Section -->
            <div id="enrolledStudentsSection" class="enrollment-section" style="display: none;">
                <div style="margin-bottom:1rem;display:flex;gap:1rem;align-items:center;">
                    <input type="text" id="enrolledStudentFilter" class="form-control" style="max-width:300px;display:inline-block;" placeholder="Filter by student name...">
                    <select id="enrolledCourseFilter" class="form-control" style="max-width:300px;display:inline-block;">
                        <option value="">All Courses</option>
                    </select>
                    <button class="btn-submit" id="enrolledStudentFilterBtn" style="padding:0.5rem 1.5rem;">Filter</button>
                    <button class="btn-submit" id="enrolledStudentFilterClearBtn" style="padding:0.5rem 1.5rem;background:var(--muted);color:var(--foreground);">Clear</button>
                </div>
                <div class="requests-container">
                    <table class="table">
                        <thead style="position: sticky; top: 0; background: var(--surface); z-index: 10;">
                            <tr>
                                <th class="checkbox-column">
                                    <!-- Hidden input checkbox for selection (for JS logic) -->
                                    <input type="checkbox" class="select-all-checkbox" id="selectAllEnrolledStudents" onchange="toggleAllEnrolledCheckboxes()" style="display:none;">
                                </th>
                                <th>Student Name</th>
                                <th>Enrolled Courses</th>
                                <th>Email</th>
                            </tr>
                        </thead>
                        <tbody id="enrolled-students">
                            <!-- Dynamic rows populated by JavaScript -->
                        </tbody>
                    </table>
                </div>
                <div class="bulk-actions">
                    <span class="selected-count" id="enrolledSelectedCount">0 selected</span>
                    <div class="selected-courses-info" id="selectedCoursesInfo" style="display: none;">
                        <i class="fas fa-info-circle"></i> <span id="coursesSelectionText">Select courses to unenroll</span>
                    </div>
                    <button class="bulk-btn bulk-decline" id="bulkUnenrollStudents" onclick="bulkUnenrollAction()" disabled>
                        <i class="fas fa-user-times"></i> Unenroll Selected
                    </button>
                </div>
            </div>

            <!-- Unenrollment Requests Section -->
            <div id="unenrollmentRequestsSection" class="enrollment-section" style="display: none;">
                <div class="requests-container">
                    <table class="table">
                        <thead style="position: sticky; top: 0; background: var(--surface); z-index: 10;">
                            <tr>
                                <th class="checkbox-column">
                                    <input type="checkbox" class="select-all-checkbox" id="selectAllUnenrollments" onchange="toggleAllUnenrollmentCheckboxes()">
                                </th>
                                <th>Student Name</th>
                                <th>Course</th>
                                <th>Department</th>
                                <th>Requested At</th>
                            </tr>
                        </thead>
                        <tbody id="unenrollment-requests">
                            <!-- Dynamic rows populated by JavaScript -->
                        </tbody>
                    </table>
                </div>
                <div class="bulk-actions">
                    <span class="selected-count" id="unenrollmentSelectedCount">0 selected</span>
                    <button class="bulk-btn bulk-approve" id="bulkApproveUnenrollments" onclick="bulkUnenrollmentAction('approve')" disabled>
                        <i class="fas fa-check"></i> Approve (Unenroll)
                    </button>
                    <button class="bulk-btn bulk-decline" id="bulkDeclineUnenrollments" onclick="bulkUnenrollmentAction('decline')" disabled>
                        <i class="fas fa-times"></i> Decline Request
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal for Assigning Courses to Lecturer -->
        <div class="modal-backdrop" id="assignCourseModalBackdrop" style="display:none;">
            <div class="modal-container" id="assignCourseModalContainer">
                <div class="modal-header">
                    <h2 class="modal-title">Manage Lecturer Courses</h2>
                    <button class="close-btn" id="closeAssignCourseModalBtn">&times;</button>
                </div>

                <!-- Current Courses Display -->
                <div class="form-group">
                    <label class="form-label">Current Assigned Courses</label>
                    <div id="currentCoursesList" style="max-height: 120px; overflow-y: auto; border: 1px solid var(--border); border-radius: var(--radius); padding: 0.5rem;">
                        <div id="noCourses" style="text-align: center; color: var(--muted-foreground); padding: 1rem;">
                            No courses assigned yet
                        </div>
                    </div>
                </div>

                <form id="assignCourseForm">
                    <div class="form-group">
                        <label class="form-label">Department</label>
                        <select class="form-control" id="assignDepartmentSelect" required>
                            <option value="">Select Department</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Available Courses</label>
                        <select class="form-control" id="assignCoursesSelect" multiple required style="min-height:120px;">
                            <!-- Options populated dynamically -->
                        </select>
                    </div>

                    <!-- Action Buttons -->
                    <div style="display: flex; gap: 0.5rem; margin-top: 1rem;">
                        <button type="button" class="btn-submit" id="addCoursesBtn" style="flex: 1; background: var(--primary);">
                            <i class="fas fa-plus"></i> Add Selected Courses
                        </button>
                        <button type="button" class="btn-submit" id="removeCoursesBtn" style="flex: 1; background: var(--accent);">
                            <i class="fas fa-minus"></i> Remove Selected Courses
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <!-- Add SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        const apiUrl = '{{ url('') }}/api/admin';  // ADD /admin prefix here

        // Bulk action handler
        async function bulkAction(role, action) {
            const checkboxes = document.querySelectorAll(`.request-checkbox[data-role="${role}"]:checked`);
            const requestIds = Array.from(checkboxes).map(cb => cb.dataset.requestId);

            if (requestIds.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'No Selection',
                    text: 'Please select at least one request.',
                    confirmButtonColor: '#3b82f6'
                });
                return;
            }

            const result = await Swal.fire({
                title: 'Are you sure?',
                text: `Do you want to ${action} ${requestIds.length} ${role} request${requestIds.length > 1 ? 's' : ''}?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: action === 'approve' ? '#3b82f6' : '#f97316',
                cancelButtonColor: '#6b7280',
                confirmButtonText: `Yes, ${action}!`,
                cancelButtonText: 'Cancel'
            });

            if (!result.isConfirmed) {
                return;
            }

            const button = document.getElementById(`bulk${action.charAt(0).toUpperCase() + action.slice(1)}${role.charAt(0).toUpperCase() + role.slice(1)}s`);
            const originalText = button.innerHTML;
            button.disabled = true;
            button.innerHTML = `<i class="fas fa-spinner fa-spin"></i> Processing...`;

            try {
                let endpoint = '';
                if (action === 'approve') {
                    endpoint = role === 'student' ? '/assign-student' : '/assign-lecturer';
                } else {
                    endpoint = '/decline-requests';
                }

                const response = await axios.post(`${apiUrl}${endpoint}`, {
                    request_ids: requestIds
                });

                if (response.data.status === 'success') {
                    await Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.data.message,
                        confirmButtonColor: '#3b82f6'
                    });
                    fetchRequests();
                    if (role === 'lecturer' && action === 'approve') {
                        fetchAssignedLecturers();
                    }
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Operation Failed',
                        text: response.data.message,
                        confirmButtonColor: '#3b82f6'
                    });
                }
            } catch (error) {
                console.error('Bulk action error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Operation failed. Please try again.',
                    confirmButtonColor: '#3b82f6'
                });
            } finally {
                button.disabled = false;
                button.innerHTML = originalText;
            }
        }

        // Toggle all checkboxes
        function toggleAllCheckboxes(role) {
            const selectAllCheckbox = document.getElementById(`selectAll${role.charAt(0).toUpperCase() + role.slice(1)}s`);
            const checkboxes = document.querySelectorAll(`.request-checkbox[data-role="${role}"]`);

            checkboxes.forEach(checkbox => {
                checkbox.checked = selectAllCheckbox.checked;
            });

            updateBulkActionButtons(role);
        }

        // Update bulk action buttons state
        function updateBulkActionButtons(role) {
            const checkboxes = document.querySelectorAll(`.request-checkbox[data-role="${role}"]:checked`);
            const count = checkboxes.length;

            const countElement = document.getElementById(`${role}SelectedCount`);
            const approveButton = document.getElementById(`bulkApprove${role.charAt(0).toUpperCase() + role.slice(1)}s`);
            const declineButton = document.getElementById(`bulkDecline${role.charAt(0).toUpperCase() + role.slice(1)}s`);

            countElement.textContent = `${count} selected`;
            approveButton.disabled = count === 0;
            declineButton.disabled = count === 0;
        }

        async function fetchRequests() {
            try {
                const response = await axios.get(`${apiUrl}/registration-requests`);
                const { requests } = response.data;

                const studentRequests = requests.filter(req => req.role === 'student').slice(0, 10); // Limit to 10
                const lecturerRequests = requests.filter(req => req.role === 'lecturer').slice(0, 10); // Limit to 10

                populateTable('student-requests', studentRequests, 'student');
                populateTable('lecturer-requests', lecturerRequests, 'lecturer');
            } catch (error) {
                console.error('Error fetching registration requests:', error);
            }
        }

        function populateTable(tableId, requests, role) {
            const tableBody = document.getElementById(tableId);
            tableBody.innerHTML = '';

            if (requests.length === 0) {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="4" class="text-center" style="padding: 2rem; color: var(--muted-foreground);">
                            No ${role} registration requests found
                        </td>
                    </tr>
                `;
                return;
            }

            requests.forEach(request => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td class="checkbox-column">
                        <input type="checkbox" class="request-checkbox"
                               data-role="${role}"
                               data-request-id="${request.id}"
                               onchange="updateBulkActionButtons('${role}')">
                    </td>
                    <td>${request.user_name}</td>
                    <td>${request.user_email}</td>
                    <td>${request.submitted_at}</td>
                `;
                tableBody.appendChild(row);
            });

            // Reset bulk action buttons
            updateBulkActionButtons(role);
        }

        let allDepartments = [];
        let allCoursesByDept = {};
        let currentLecturerId = null;
        let currentLecturerCourses = []; // Track current lecturer courses

        // --- Assigned Lecturers Section ---
        async function fetchAssignedLecturers(filter = {}) {
            const container = document.getElementById('assigned-lecturers-content');
            container.innerHTML = '<div class="loading">Loading assigned lecturers...</div>';
            try {
                let url = `${apiUrl}/assigned-lecturers`;  // Now correctly: /api/admin/assigned-lecturers
                const params = [];
                if (filter.course_title) params.push(`course_title=${encodeURIComponent(filter.course_title)}`);
                if (filter.course_id) params.push(`course_id=${encodeURIComponent(filter.course_id)}`);
                if (params.length > 0) url += '?' + params.join('&');

                // Fetch lecturers first
                const lectRes = await axios.get(url);
                if (lectRes.data.status !== 'success') {
                    container.innerHTML = `<div class="empty-state"><div class="empty-icon" style="color:#ef4444;">⚠️</div><h3>Error loading lecturers</h3></div>`;
                    return;
                }
                const lecturers = lectRes.data.lecturers || [];

                // Fetch dropdowns and courses for modal
                const [deptRes, courseRes] = await Promise.all([
                    axios.get('{{ url('') }}/api/dropdown/departments'),  // Use global dropdown routes
                    axios.get(`${apiUrl}/get-courses`)
                ]);
                allDepartments = deptRes.data.departments ? deptRes.data.departments.map(d => d.department) : [];
                allCoursesByDept = courseRes.data.courses || {};

                // Group by department
                const grouped = {};
                lecturers.forEach(l => {
                    const dept = l.department || 'No Department';
                    if (!grouped[dept]) grouped[dept] = [];
                    grouped[dept].push(l);
                });

                let html = '';
                if (lecturers.length === 0) {
                    html = `<div class="empty-state"><div class="empty-icon">🧑‍🏫</div><h3>No assigned lecturers found</h3></div>`;
                } else {
                    Object.keys(grouped).forEach(dept => {
                        html += `
                            <div style="margin-bottom:2rem;">
                                <h4 style="color:var(--primary);margin-bottom:0.5rem;">${dept}</h4>
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Courses</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${grouped[dept].map(l => `
                                            <tr>
                                                <td>${l.name}</td>
                                                <td>
                                                    ${
                                                        l.courses && l.courses.length > 0
                                                        ? l.courses.map(c => `<span class="badge badge-lecturer" style="margin-right:0.25rem;">${c.title}</span>`).join(' ')
                                                        : '<span style="color:#dc2626;">No courses assigned</span>'
                                                    }
                                                </td>
                                                <td>
                                                    <button class="action-btn" onclick="openAssignCourseModal('${l.id}', '${l.department || ''}')">
                                                        <i class="fas fa-edit action-icon"></i>Manage Courses
                                                    </button>
                                                </td>
                                            </tr>
                                        `).join('')}
                                    </tbody>
                                </table>
                            </div>
                        `;
                    });
                }
                container.innerHTML = html;
            } catch (e) {
                console.error('Error in fetchAssignedLecturers:', e);
                container.innerHTML = `<div class="empty-state"><div class="empty-icon" style="color:#ef4444;">⚠️</div><h3>Error loading lecturers</h3><p>${e.message || 'Unknown error'}</p></div>`;
            }
        }

        // --- Filter events ---
        document.getElementById('lecturerCourseFilterBtn').addEventListener('click', function() {
            const val = document.getElementById('lecturerCourseFilter').value.trim();
            fetchAssignedLecturers(val ? { course_title: val } : {});
        });
        document.getElementById('lecturerCourseFilterClearBtn').addEventListener('click', function() {
            document.getElementById('lecturerCourseFilter').value = '';
            fetchAssignedLecturers();
        });
        document.getElementById('lecturerCourseFilter').addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                document.getElementById('lecturerCourseFilterBtn').click();
            }
        });

        // --- Assign Courses Modal Logic ---
        async function openAssignCourseModal(lecturerId, lecturerDept) {
            currentLecturerId = lecturerId;

            // Fetch current lecturer courses
            await fetchCurrentLecturerCourses(lecturerId);

            // Populate department select
            const deptSelect = document.getElementById('assignDepartmentSelect');
            deptSelect.innerHTML = `<option value="">Select Department</option>`;
            allDepartments.forEach(dept => {
                deptSelect.innerHTML += `<option value="${dept}" ${dept === lecturerDept ? 'selected' : ''}>${dept}</option>`;
            });

            // Populate courses for the selected department
            populateCoursesSelect(lecturerDept);

            // Show modal
            document.getElementById('assignCourseModalBackdrop').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        // Close modal function
        function closeAssignCourseModal() {
            document.getElementById('assignCourseModalBackdrop').style.display = 'none';
            document.body.style.overflow = 'auto';
            currentLecturerId = null;
            currentLecturerCourses = [];
        }

        // Fetch current lecturer courses
        async function fetchCurrentLecturerCourses(lecturerId) {
            try {
                const response = await axios.get(`${apiUrl}/assigned-lecturers`);
                if (response.data.status === 'success') {
                    const lecturer = response.data.lecturers.find(l => l.id === lecturerId);

                    if (lecturer && lecturer.courses && Array.isArray(lecturer.courses)) {
                        currentLecturerCourses = lecturer.courses;
                    } else {
                        currentLecturerCourses = [];
                    }

                    // Display current courses
                    displayCurrentCourses();
                } else {
                    currentLecturerCourses = [];
                    displayCurrentCourses();
                }
            } catch (error) {
                console.error('Error fetching lecturer courses:', error);
                currentLecturerCourses = [];
                displayCurrentCourses();
            }
        }

        function displayCurrentCourses() {
            const container = document.getElementById('currentCoursesList');
            container.innerHTML = '';

            if (currentLecturerCourses.length === 0) {
                container.innerHTML = '<div id="noCourses" style="text-align: center; color: var(--muted-foreground); padding: 1rem;">No courses assigned yet</div>';
                return;
            }

            currentLecturerCourses.forEach(course => {
                const courseItem = document.createElement('div');
                courseItem.className = 'course-item';
                courseItem.style.cssText = 'display: flex; justify-content: space-between; align-items: center; padding: 0.5rem; margin: 0.25rem 0; background: var(--muted); border-radius: 4px; font-size: 0.875rem;';
                courseItem.innerHTML = `
                    <div style="display: flex; align-items: center; gap: 0.5rem; flex: 1;">
                        <input type="checkbox" value="${course.id}" class="current-course-checkbox" style="cursor: pointer;">
                        <span>${course.title}</span>
                    </div>
                    <span style="font-size: 0.75rem; color: var(--muted-foreground);">${course.id}</span>
                `;
                container.appendChild(courseItem);
            });
        }

        // Handle course action (add/remove)
        async function handleCourseAction(action) {
            let selectedOptions = [];
            let courseIds = [];
            const container = document.getElementById('currentCoursesList');

            if (action === 'add') {
                selectedOptions = Array.from(document.getElementById('assignCoursesSelect').selectedOptions);
                courseIds = selectedOptions.map(opt => opt.value);
            } else if (action === 'remove') {
                const checkedBoxes = document.querySelectorAll('.current-course-checkbox:checked');
                courseIds = Array.from(checkedBoxes).map(cb => cb.value);
            }

            if (!currentLecturerId || courseIds.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'No Selection',
                    text: `Please select ${action === 'add' ? 'a course to add' : 'courses to remove'}.`,
                    confirmButtonColor: '#3b82f6'
                });
                return;
            }

            const actionBtn = document.getElementById(action === 'add' ? 'addCoursesBtn' : 'removeCoursesBtn');
            const originalText = actionBtn.textContent;
            actionBtn.disabled = true;
            actionBtn.textContent = action === 'add' ? 'Processing...' : 'Removing...';

            let successCount = 0;
            let failCount = 0;
            const failedCourses = [];

            try {
                for (let i = 0; i < courseIds.length; i++) {
                    const courseId = courseIds[i];
                    let courseTitle = '';

                    if (action === 'add') {
                        const courseOption = selectedOptions.find(opt => opt.value === courseId);
                        courseTitle = courseOption ? courseOption.text : 'Unknown Course';
                    } else {
                        const course = currentLecturerCourses.find(c => c.id === courseId);
                        courseTitle = course ? course.title : 'Unknown Course';
                    }

                    try {
                        const response = await axios.post(`${apiUrl}/change-lecturer-courses`, {
                            lecturer_id: currentLecturerId,
                            course_id: courseId,
                            action: action
                        });

                        if (response.data.status === 'success') {
                            successCount++;
                            // Update currentLecturerCourses with fresh data from server
                            if (response.data.courses && Array.isArray(response.data.courses)) {
                                currentLecturerCourses = response.data.courses;
                            }
                            // Force refresh the Current Assigned Courses display
                            displayCurrentCourses();
                        } else {
                            failCount++;
                            failedCourses.push(courseTitle);
                        }
                    } catch (err) {
                        console.error('Course action error:', err);
                        failCount++;
                        failedCourses.push(courseTitle);
                    }

                    if (courseIds.length > 1) {
                        actionBtn.textContent = `${action === 'add' ? 'Adding' : 'Removing'} ${i + 1}/${courseIds.length}...`;
                    }
                }

                let message = '';
                if (successCount > 0) {
                    message += `Successfully ${action === 'add' ? 'added' : 'removed'} ${successCount} course${successCount > 1 ? 's' : ''}.`;
                }
                if (failCount > 0) {
                    message += `\n${failCount} course${failCount > 1 ? 's' : ''} failed: ${failedCourses.join(', ')}`;
                }

                await Swal.fire({
                    icon: failCount > 0 ? 'warning' : 'success',
                    title: failCount > 0 ? 'Partially Completed' : 'Success!',
                    text: message,
                    confirmButtonColor: '#3b82f6'
                });

                // Force a final refresh from the server
                await fetchCurrentLecturerCourses(currentLecturerId);

            } catch (error) {
                console.error('Error in handleCourseAction:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Operation failed. Please try again.',
                    confirmButtonColor: '#3b82f6'
                });
            } finally {
                actionBtn.disabled = false;
                actionBtn.textContent = originalText;
                // Clear selections
                if (action === 'add') {
                    const selectElement = document.getElementById('assignCoursesSelect');
                    if (selectElement) {
                        selectElement.selectedIndex = -1;
                    }
                }
                fetchAssignedLecturers();
            }
        }

        function populateCoursesSelect(department) {
            const coursesSelect = document.getElementById('assignCoursesSelect');
            coursesSelect.innerHTML = '';
            if (department && allCoursesByDept[department]) {
                allCoursesByDept[department].forEach(course => {
                    coursesSelect.innerHTML += `<option value="${course.id}">${course.title}</option>`;
                });
            }
        }

        // Department select change handler
        document.getElementById('assignDepartmentSelect').addEventListener('change', function() {
            populateCoursesSelect(this.value);
        });

        // Close modal button event listener
        document.getElementById('closeAssignCourseModalBtn').addEventListener('click', function() {
            closeAssignCourseModal();
        });

        // Close modal when clicking backdrop
        document.getElementById('assignCourseModalBackdrop').addEventListener('click', function(e) {
            if (e.target.id === 'assignCourseModalBackdrop') {
                closeAssignCourseModal();
            }
        });

        // Add/Remove courses button event listeners (attach once on page load)
        document.addEventListener('DOMContentLoaded', function() {
            fetchRequests();
            fetchAssignedLecturers();
            fetchEnrollmentRequests();
            fetchCoursesForFilter();

            // Attach event listeners for modal buttons
            document.getElementById('addCoursesBtn').addEventListener('click', async function(e) {
                e.preventDefault();
                await handleCourseAction('add');
            });

            document.getElementById('removeCoursesBtn').addEventListener('click', async function(e) {
                e.preventDefault();
                await handleCourseAction('remove');
            });
        });

        // Enrollment tab switching
        function switchEnrollmentTab(tab) {
            currentEnrollmentTab = tab;

            // Update tab buttons
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            if (tab === 'requests') {
                document.getElementById('requestsTab').classList.add('active');
            } else if (tab === 'enrolled') {
                document.getElementById('enrolledTab').classList.add('active');
            } else if (tab === 'unenrollment') {
                document.getElementById('unenrollmentTab').classList.add('active');
            }

            // Show/hide sections
            document.getElementById('enrollmentRequestsSection').style.display = tab === 'requests' ? 'block' : 'none';
            document.getElementById('enrolledStudentsSection').style.display = tab === 'enrolled' ? 'block' : 'none';
            document.getElementById('unenrollmentRequestsSection').style.display = tab === 'unenrollment' ? 'block' : 'none';

            // Load appropriate data
            if (tab === 'requests') {
                fetchEnrollmentRequests();
            } else if (tab === 'enrolled') {
                fetchEnrolledStudents();
            } else if (tab === 'unenrollment') {
                fetchUnenrollmentRequests();
            }
        }

        // Fetch enrollment requests
        async function fetchEnrollmentRequests() {
            try {
                const token = localStorage.getItem('auth_token');
                const response = await axios.get(`${apiUrl}/enrollment-requests`, {  // Correctly: /api/admin/enrollment-requests
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Accept': 'application/json'
                    }
                });

                if (response.data.status === 'success') {
                    populateEnrollmentRequestsTable(response.data.enrollments || []);
                } else {
                    throw new Error(response.data.message || 'Failed to fetch enrollment requests');
                }
            } catch (error) {
                console.error('Error fetching enrollment requests:', error);
                document.getElementById('enrollment-requests').innerHTML = '<tr><td colspan="5" class="text-center">Error loading enrollment requests</td></tr>';
            }
        }

        // Populate enrollment requests table
        function populateEnrollmentRequestsTable(enrollments) {
            const tableBody = document.getElementById('enrollment-requests');
            tableBody.innerHTML = '';

            if (!enrollments || enrollments.length === 0) {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="5" class="text-center" style="padding: 2rem; color: var(--muted-foreground);">
                            No enrollment requests found
                        </td>
                    </tr>
                `;
                return;
            }

            enrollments.forEach(enrollment => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td class="checkbox-column">
                        <input type="checkbox" class="enrollment-checkbox"
                               data-enrollment-id="${enrollment.id}"
                               onchange="updateEnrollmentBulkActionButtons()">
                    </td>
                    <td>${enrollment.user_name}</td>
                    <td>${enrollment.course_title}</td>
                    <td>${enrollment.course_department}</td>
                    <td>${enrollment.requested_at}</td>
                `;
                tableBody.appendChild(row);
            });

            updateEnrollmentBulkActionButtons();
        }

        // Toggle all enrollment checkboxes
        function toggleAllEnrollmentCheckboxes() {
            const selectAllCheckbox = document.getElementById('selectAllEnrollments');
            const checkboxes = document.querySelectorAll('.enrollment-checkbox');

            checkboxes.forEach(checkbox => {
                checkbox.checked = selectAllCheckbox.checked;
            });

            updateEnrollmentBulkActionButtons();
        }

        function toggleAllEnrolledCheckboxes() {
            const selectAllCheckbox = document.getElementById('selectAllEnrolledStudents');
            const checkboxes = document.querySelectorAll('.enrolled-student-checkbox');

            checkboxes.forEach(checkbox => {
                checkbox.checked = selectAllCheckbox.checked;
            });

            updateEnrolledBulkActionButtons();
        }

        // Update enrollment bulk action buttons
        function updateEnrollmentBulkActionButtons() {
            const checkboxes = document.querySelectorAll('.enrollment-checkbox:checked');
            const count = checkboxes.length;

            document.getElementById('enrollmentSelectedCount').textContent = `${count} selected`;
            document.getElementById('bulkEnrollStudents').disabled = count === 0;
            document.getElementById('bulkDeclineEnrollments').disabled = count === 0;
        }

        // Update enrolled bulk action buttons
        function updateEnrolledBulkActionButtons() {
            const checkboxes = document.querySelectorAll('.enrolled-student-checkbox:checked');
            const count = checkboxes.length;

            document.getElementById('enrolledSelectedCount').textContent = `${count} selected`;
            document.getElementById('bulkUnenrollStudents').disabled = count === 0;
        }

        // Bulk enrollment actions
        async function bulkEnrollmentAction(action) {
            const checkboxes = document.querySelectorAll('.enrollment-checkbox:checked');
            const enrollmentIds = Array.from(checkboxes).map(cb => cb.dataset.enrollmentId);

            if (enrollmentIds.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'No Selection',
                    text: 'Please select at least one enrollment request.',
                    confirmButtonColor: '#3b82f6'
                });
                return;
            }

            const result = await Swal.fire({
                title: 'Are you sure?',
                text: `Do you want to ${action} ${enrollmentIds.length} student${enrollmentIds.length > 1 ? 's' : ''}?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: action === 'enroll' ? '#3b82f6' : '#f97316',
                cancelButtonColor: '#6b7280',
                confirmButtonText: `Yes, ${action}!`,
                cancelButtonText: 'Cancel'
            });

            if (!result.isConfirmed) {
                return;
            }

            const button = action === 'enroll'
                ? document.getElementById('bulkEnrollStudents')
                : document.getElementById('bulkDeclineEnrollments');
            const originalText = button.innerHTML;
            button.disabled = true;
            button.innerHTML = `<i class="fas fa-spinner fa-spin"></i> Processing...`;

            try {
                let endpoint = '';
                let requestBody = {};

                if (action === 'enroll') {
                    endpoint = '/enroll-students';
                    requestBody = { enrollment_ids: enrollmentIds };
                } else if (action === 'decline') {
                    endpoint = '/decline-enrollment-requests';
                    requestBody = { enrollment_ids: enrollmentIds };
                }

                const token = localStorage.getItem('auth_token');
                const response = await axios.post(`${apiUrl}${endpoint}`, requestBody, {
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Accept': 'application/json'
                    }
                });

                if (response.data.status === 'success') {
                    await Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.data.message,
                        confirmButtonColor: '#3b82f6'
                    });
                    fetchEnrollmentRequests();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Operation Failed',
                        text: response.data.message,
                        confirmButtonColor: '#3b82f6'
                    });
                }
            } catch (error) {
                console.error('Bulk enrollment action error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Operation failed. Please try again.',
                    confirmButtonColor: '#3b82f6'
                });
            } finally {
                button.disabled = false;
                button.innerHTML = originalText;
            }
        }

        // Fetch enrolled students
        async function fetchEnrolledStudents(filters = {}) {
            try {
                const token = localStorage.getItem('auth_token');
                const response = await axios.get(`${apiUrl}/enrolled-students`, {
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Accept': 'application/json'
                    },
                    params: filters
                });

                if (response.data.status === 'success') {
                    populateEnrolledStudentsTable(response.data.students || []);
                } else {
                    throw new Error(response.data.message || 'Failed to fetch enrolled students');
                }
            } catch (error) {
                console.error('Error fetching enrolled students:', error);
                let errorMessage = 'Error loading enrolled students';
                if (error.response && error.response.status === 401) {
                    errorMessage = 'Authentication failed. Please login again.';
                } else if (error.response && error.response.data && error.response.data.message) {
                    errorMessage = error.response.data.message;
                } else if (error.message) {
                    errorMessage = error.message;
                }
                document.getElementById('enrolled-students').innerHTML =
                    `<tr><td colspan="4" class="text-center" style="color: #ef4444; padding: 2rem;">${errorMessage}</td></tr>`;
            }
        }

        // Populate enrolled students table
        function populateEnrolledStudentsTable(students) {
            const tableBody = document.getElementById('enrolled-students');
            tableBody.innerHTML = '';

            if (students.length === 0) {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="4" class="text-center" style="padding: 2rem; color: var(--muted-foreground);">
                            No enrolled students found
                        </td>
                    </tr>
                `;
                return;
            }

            students.forEach(student => {
                const row = document.createElement('tr');
                const coursesHtml = student.enrolled_courses.map(course => `
                    <div style="display: inline-block; margin: 0.25rem;">
                        <label style="display: flex; align-items: center; background: var(--muted); padding: 0.25rem 0.5rem; border-radius: 4px; cursor: pointer; font-size: 0.75rem;">
                            <input type="checkbox"
                                   class="course-checkbox"
                                   data-student-id="${student.id}"
                                   data-course-id="${course.id}"
                                   data-course-title="${course.title}"
                                   style="margin-right: 0.25rem; transform: scale(0.8);"
                                   onchange="updateUnenrollButtons()">
                            ${course.title}
                        </label>
                    </div>
                `).join('');

                row.innerHTML = `
                    <td class="checkbox-column">
                        <input hidden type="checkbox" class="enrolled-student-checkbox"
                               data-student-id="${student.id}"
                               data-courses='${JSON.stringify(student.enrolled_courses)}'
                               onchange="updateEnrolledBulkActionButtons()">
                    </td>
                    <td>${student.name}</td>
                    <td style="max-width: 400px;">
                        <div style="margin-bottom: 0.5rem;">
                            <button class="btn-action" onclick="toggleStudentCourses('${student.id}')" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">
                                <i class="fas fa-list"></i> Select Courses
                            </button>
                        </div>
                        <div id="courses-${student.id}" style="display: none;">
                            ${coursesHtml}
                        </div>
                        <div class="selected-courses-summary" id="summary-${student.id}" style="color: var(--muted-foreground); font-size: 0.75rem; margin-top: 0.25rem;">
                            ${student.enrolled_courses.length} course${student.enrolled_courses.length !== 1 ? 's' : ''} enrolled
                        </div>
                    </td>
                    <td>${student.email}</td>
                `;
                tableBody.appendChild(row);
            });

            updateEnrolledBulkActionButtons();
        }

        // Toggle student courses visibility
        function toggleStudentCourses(studentId) {
            const coursesDiv = document.getElementById(`courses-${studentId}`);
            const isVisible = coursesDiv.style.display !== 'none';

            // Hide other students' courses
            document.querySelectorAll('[id^="courses-"]').forEach(div => {
                if (div.id !== `courses-${studentId}`) {
                    div.style.display = 'none';
                }
            });

            // Toggle visibility of the selected student's courses
            coursesDiv.style.display = isVisible ? 'none' : 'block';
            updateUnenrollButtons();
        }

        // Update unenroll buttons based on selected courses
        function updateUnenrollButtons() {
            const selectedCourses = document.querySelectorAll('.course-checkbox:checked');
            const count = selectedCourses.length;

            const bulkUnenrollBtn = document.getElementById('bulkUnenrollStudents');
            if (bulkUnenrollBtn) {
                if (count > 0) {
                    bulkUnenrollBtn.innerHTML = `<i class="fas fa-user-times"></i> Unenroll from ${count} Selected Course${count !== 1 ? 's' : ''}`;
                    bulkUnenrollBtn.disabled = false;
                } else {
                    bulkUnenrollBtn.innerHTML = `<i class="fas fa-user-times"></i> Unenroll Selected`;
                    bulkUnenrollBtn.disabled = true;
                }
            }

            // Update individual student summaries
            const studentIds = new Set();
            selectedCourses.forEach(checkbox => {
                studentIds.add(checkbox.dataset.studentId);
            });

            studentIds.forEach(studentId => {
                const studentCourses = document.querySelectorAll(`.course-checkbox[data-student-id="${studentId}"]:checked`);
                const summaryEl = document.getElementById(`summary-${studentId}`);
                if (summaryEl && studentCourses.length > 0) {
                    summaryEl.innerHTML = `${studentCourses.length} course${studentCourses.length !== 1 ? 's' : ''} selected for unenrollment`;
                    summaryEl.style.color = '#dc2626';
                } else if (summaryEl) {
                    const totalCourses = document.querySelectorAll(`.course-checkbox[data-student-id="${studentId}"]`).length;
                    summaryEl.innerHTML = `${totalCourses} course${totalCourses !== 1 ? 's' : ''} enrolled`;
                    summaryEl.style.color = 'var(--muted-foreground)';
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            fetchRequests();
            fetchAssignedLecturers();
            fetchEnrollmentRequests(); // Load enrollment requests by default
            fetchCoursesForFilter();
        });

        // Bulk unenroll action for selected courses
        async function bulkUnenrollAction() {
            const selectedCourses = document.querySelectorAll('.course-checkbox:checked');
            if (selectedCourses.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'No Selection',
                    text: 'Please select courses to unenroll students from.',
                    confirmButtonColor: '#3b82f6'
                });
                return;
            }

            const studentCourseIds = [];
            selectedCourses.forEach(cb => {
                studentCourseIds.push({
                    student_id: cb.dataset.studentId,
                    course_id: cb.dataset.courseId
                });
            });

            const result = await Swal.fire({
                title: 'Are you sure?',
                text: `Do you want to unenroll ${studentCourseIds.length} course enrollment${studentCourseIds.length > 1 ? 's' : ''}?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#f97316',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, unenroll!',
                cancelButtonText: 'Cancel'
            });

            if (!result.isConfirmed) {
                return;
            }

            const bulkUnenrollBtn = document.getElementById('bulkUnenrollStudents');
            const originalText = bulkUnenrollBtn.innerHTML;
            bulkUnenrollBtn.disabled = true;
            bulkUnenrollBtn.innerHTML = `<i class="fas fa-spinner fa-spin"></i> Processing...`;

            try {
                const token = localStorage.getItem('auth_token');
                const response = await axios.post(`${apiUrl}/unenroll-students`, {
                    student_course_ids: studentCourseIds
                }, {
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Accept': 'application/json'
                    }
                });

                if (response.data.status === 'success') {
                    await Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.data.message,
                        confirmButtonColor: '#3b82f6'
                    });
                    fetchEnrolledStudents();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Operation Failed',
                        text: response.data.message,
                        confirmButtonColor: '#3b82f6'
                    });
                }
            } catch (error) {
                console.error('Bulk unenroll error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Operation failed. Please try again.',
                    confirmButtonColor: '#3b82f6'
                });
            } finally {
                bulkUnenrollBtn.disabled = false;
                bulkUnenrollBtn.innerHTML = originalText;
            }
        }

        // Fetch unenrollment requests
        async function fetchUnenrollmentRequests() {
            try {
                const token = localStorage.getItem('auth_token');
                const response = await axios.get(`${apiUrl}/unenrollment-requests`, {
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Accept': 'application/json'
                    }
                });

                if (response.data.status === 'success') {
                    populateUnenrollmentRequestsTable(response.data.unenrollment_requests || []);
                } else {
                    document.getElementById('unenrollment-requests').innerHTML =
                        `<tr><td colspan="5" class="text-center">${response.data.message || 'Failed to fetch unenrollment requests'}</td></tr>`;
                }
            } catch (error) {
                let message = 'Operation failed. Please try again.';
                if (error.response && error.response.data && error.response.data.message) {
                    message = error.response.data.message;
                }
                document.getElementById('unenrollment-requests').innerHTML =
                    `<tr><td colspan="5" class="text-center">${message}</td></tr>`;
            }
        }

        // Populate unenrollment requests table
        function populateUnenrollmentRequestsTable(requests) {
            const tableBody = document.getElementById('unenrollment-requests');
            tableBody.innerHTML = '';

            if (!requests || requests.length === 0) {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="5" class="text-center" style="padding: 2rem; color: var(--muted-foreground);">
                            No unenrollment requests found
                        </td>
                    </tr>
                `;
                return;
            }

            requests.forEach(request => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td class="checkbox-column">
                        <input type="checkbox" class="unenrollment-checkbox"
                               data-enrollment-id="${request.id}"
                               onchange="updateUnenrollmentBulkActionButtons()">
                    </td>
                    <td>${request.user_name}</td>
                    <td>${request.course_title}</td>
                    <td>${request.course_department}</td>
                    <td>${request.unenrollment_requested_at || 'N/A'}</td>
                `;
                tableBody.appendChild(row);
            });

            updateUnenrollmentBulkActionButtons();
        }

        // Toggle all unenrollment checkboxes
        function toggleAllUnenrollmentCheckboxes() {
            const selectAllCheckbox = document.getElementById('selectAllUnenrollments');
            const checkboxes = document.querySelectorAll('.unenrollment-checkbox');

            checkboxes.forEach(checkbox => {
                checkbox.checked = selectAllCheckbox.checked;
            });

            updateUnenrollmentBulkActionButtons();
        }

        // Update unenrollment bulk action buttons
        function updateUnenrollmentBulkActionButtons() {
            const checkboxes = document.querySelectorAll('.unenrollment-checkbox:checked');
            const count = checkboxes.length;

            document.getElementById('unenrollmentSelectedCount').textContent = `${count} selected`;
            document.getElementById('bulkApproveUnenrollments').disabled = count === 0;
            document.getElementById('bulkDeclineUnenrollments').disabled = count === 0;
        }

        // Bulk unenrollment actions
        async function bulkUnenrollmentAction(action) {
            const checkboxes = document.querySelectorAll('.unenrollment-checkbox:checked');
            const enrollmentIds = Array.from(checkboxes).map(cb => cb.dataset.enrollmentId);

            if (enrollmentIds.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'No Selection',
                    text: 'Please select at least one unenrollment request.',
                    confirmButtonColor: '#3b82f6'
                });
                return;
            }

            const actionText = action === 'approve' ? 'approve (unenroll students from)' : 'decline';
            const result = await Swal.fire({
                title: 'Are you sure?',
                text: `Do you want to ${actionText} ${enrollmentIds.length} unenrollment request${enrollmentIds.length > 1 ? 's' : ''}?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: action === 'approve' ? '#f97316' : '#3b82f6',
                cancelButtonColor: '#6b7280',
                confirmButtonText: `Yes, ${action}!`,
                cancelButtonText: 'Cancel'
            });

            if (!result.isConfirmed) {
                return;
            }

            const button = action === 'approve'
                ? document.getElementById('bulkApproveUnenrollments')
                : document.getElementById('bulkDeclineUnenrollments');
            const originalText = button.innerHTML;
            button.disabled = true;
            button.innerHTML = `<i class="fas fa-spinner fa-spin"></i> Processing...`;

            try {
                const token = localStorage.getItem('auth_token');
                const response = await axios.post(`${apiUrl}/process-unenrollment-requests`, {
                    enrollment_ids: enrollmentIds,
                    action: action
                }, {
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Accept': 'application/json'
                    }
                });

                if (response.data.status === 'success') {
                    await Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.data.message,
                        confirmButtonColor: '#3b82f6'
                    });
                    fetchUnenrollmentRequests();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Operation Failed',
                        text: response.data.message,
                        confirmButtonColor: '#3b82f6'
                    });
                }
            } catch (error) {
                console.error('Bulk unenrollment action error:', error);
                let errorMessage = 'Operation failed. Please try again.';
                if (error.response && error.response.data && error.response.data.message) {
                    errorMessage = error.response.data.message;
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: errorMessage,
                    confirmButtonColor: '#3b82f6'
                });
            } finally {
                button.disabled = false;
                button.innerHTML = originalText;
            }
        }

        // Fetch courses for filter (enrollment management)
        async function fetchCoursesForFilter() {
            try {
                const response = await axios.get(`${apiUrl}/get-courses`);  // Correctly: /api/admin/get-courses
                const courses = response.data.courses || [];

                // Populate course filters in enrolled students section
                const enrolledCourseFilter = document.getElementById('enrolledCourseFilter');
                enrolledCourseFilter.innerHTML = '<option value="">All Courses</option>';
                courses.forEach(course => {
                    enrolledCourseFilter.innerHTML += `<option value="${course.id}">${course.title}</option>`;
                });
            } catch (error) {
                console.error('Error fetching courses for filter:', error);
            }
        }

    </script>
@endsection
