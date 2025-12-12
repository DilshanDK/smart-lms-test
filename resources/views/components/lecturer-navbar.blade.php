@include('components.session-check', ['requiredRole' => 'assigned_lecturer'])

<!-- Lecturer Navigation Bar -->
<nav class="navbar navbar-expand-lg navbar-dark sticky-top"
    style="background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%); box-shadow: 0 2px 10px rgba(0,0,0,0.1); z-index: 1030;">
    <div class="container-fluid px-4">
        <!-- Brand -->
        <a class="navbar-brand fw-bold fs-4" href="{{ route('lecturer.dashboard') }}">
            <i class="fas fa-chalkboard-teacher me-2"></i>
            Smart LMS
        </a>

        <!-- Mobile toggle -->
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#lecturerNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navigation Links -->
        <div class="collapse navbar-collapse" id="lecturerNavbar">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link fw-medium px-3" href="{{ route('lecturer.dashboard') }}">
                        <i class="fas fa-tachometer-alt me-1"></i>
                        Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-medium px-3" href="{{ route('lecturer.course-management') }}">
                        <i class="fas fa-book me-1"></i>
                        My Courses
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-medium px-3" href="{{ route('lecturer.student-management') }}">
                        <i class="fas fa-users me-1"></i>
                        Students
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-medium px-3" href="{{ route('lecturer.assignment-management') }}">
                        <i class="fas fa-clipboard-list me-1"></i>
                        Assignments
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-medium px-3" href="{{ route('lecturer.gradebook-management') }}">
                        <i class="fas fa-chart-bar me-1"></i>
                        Gradebook
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-medium px-3" href="{{ route('lecturer.resource-management') }}">
                        <i class="fas fa-folder-open me-1"></i>
                        Resources
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-medium px-3" href="{{ route('lecturer.videos-management') }}">
                        <i class="fas fa-video me-1"></i>
                        Videos
                    </a>
                </li>
            </ul>

            <!-- User Menu -->
            <div class="navbar-nav">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle fw-medium px-3" href="#" role="button"
                        data-bs-toggle="dropdown">
                        <i class="fas fa-user-tie me-1"></i>
                        <span id="lecturerNameDisplay">Lecturer</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                        <li><a class="dropdown-item py-2" href="#"><i class="fas fa-user me-2"></i>My Profile</a>
                        </li>
                      
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item py-2 text-danger" onclick="handleLogout()"><i
                                    class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</nav>

<script>
    // Update lecturer name in navbar
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
                        const lecturerName = document.getElementById('lecturerNameDisplay');
                        if (lecturerName) {
                            lecturerName.textContent = result.user.name || 'Lecturer';
                        }
                    }
                }
            } catch (error) {
                console.error('Error fetching lecturer profile:', error);
            }
        }
    });
</script>
