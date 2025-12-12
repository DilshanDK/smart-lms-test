@include('components.session-check', ['requiredRole' => 'assigned_student'])

<!-- Student Navigation Bar -->
<nav class="navbar navbar-expand-lg navbar-dark sticky-top"
    style="background: linear-gradient(135deg, #059669 0%, #047857 100%); box-shadow: 0 2px 10px rgba(0,0,0,0.1); z-index: 1030;">
    <div class="container-fluid px-4">
        <!-- Brand -->
        <a class="navbar-brand fw-bold fs-4" href="{{ route('student.dashboard') }}">
            <i class="fas fa-graduation-cap me-2"></i>
            Smart LMS
        </a>

        <!-- Mobile toggle -->
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#studentNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navigation Links -->
        <div class="collapse navbar-collapse" id="studentNavbar">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link fw-medium px-3" href="{{ route('student.dashboard') }}">
                        <i class="fas fa-tachometer-alt me-1"></i>
                        Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-medium px-3" href="{{ route('student.course-management') }}">
                        <i class="fas fa-book-open me-1"></i>
                        Course Enrollment
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-medium px-3" href="{{ route('student.assignment-management') }}">
                        <i class="fas fa-clipboard-list me-1"></i>
                        Assignments
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-medium px-3" href="{{ route('student.progress-management') }}">
                        <i class="fas fa-chart-line me-1"></i>
                        My Progress
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-medium px-3" href="{{ route('student.video-management') }}">
                        <i class="fas fa-video me-1"></i>
                        Videos
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-medium px-3" href="{{ route('student.resource-management') }}">
                        <i class="fas fa-folder-open me-1"></i>
                        Resources
                    </a>
                </li>
            </ul>

            <!-- User Menu -->
            <div class="navbar-nav">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle fw-medium px-3" href="#" role="button"
                        data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle me-1"></i>
                        <span id="nav-student-name">Student</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                        <li><a class="dropdown-item py-2" href="#"><i class="fas fa-user me-2"></i>My Profile</a>
                        </li>
                        <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item py-2 text-danger" href="#" onclick="handleLogout()"><i
                                    class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</nav>

<script>
    // Update student name in navbar
    document.addEventListener('DOMContentLoaded', async function() {
        const token = localStorage.getItem('auth_token');
        if (token) {
            try {
                const response = await fetch('/api/user/profile', {
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Accept': 'application/json'
                    }
                });

                if (response.ok) {
                    const result = await response.json();
                    if (result.status === 'success' && result.user) {
                        const navStudentName = document.getElementById('nav-student-name');
                        if (navStudentName) {
                            navStudentName.textContent = result.user.name || 'Student';
                        }
                    }
                }
            } catch (error) {
                console.error('Error fetching user profile:', error);
            }
        }
    });
</script>
