@include('components.session-check', ['requiredRole' => 'admin'])

<!-- Admin Navigation Bar -->
<nav class="navbar navbar-expand-lg navbar-dark sticky-top"
    style="background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%); box-shadow: 0 2px 10px rgba(0,0,0,0.1); z-index: 1030;">
    <div class="container-fluid px-4">
        <!-- Brand -->
        <a class="navbar-brand fw-bold fs-4" href="{{ route('admin.dashboard') }}">
            <i class="fas fa-user-shield me-2"></i>
            Smart LMS
        </a>

        <!-- Mobile toggle -->
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navigation Links -->
        <div class="collapse navbar-collapse" id="adminNavbar">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link fw-medium px-3" href="{{ route('admin.dashboard') }}">
                        <i class="fas fa-tachometer-alt me-1"></i>
                        Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-medium px-3" href="{{ route('admin.user-management') }}">
                        <i class="fas fa-users-cog me-1"></i>
                        User Management
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-medium px-3" href="{{ route('admin.course-management') }}">
                        <i class="fas fa-book me-1"></i>
                        Course Management
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-medium px-3" href="{{ route('admin.video-stream-management') }}">
                        <i class="fas fa-video me-1"></i>
                        Video Streams
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-medium px-3" href="{{ route('admin.resource-management') }}">
                        <i class="fas fa-folder-open me-1"></i>
                        Resource Management
                    </a>
                </li>
            </ul>

            <!-- User Menu -->
            <div class="navbar-nav">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle fw-medium px-3" href="#" role="button"
                        data-bs-toggle="dropdown">
                        <i class="fas fa-user-shield me-1"></i>
                        <span id="adminNameDisplay">Admin</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                        <li><a class="dropdown-item py-2" href="#"><i class="fas fa-user me-2"></i>My Profile</a>
                        </li>
                        <li><a class="dropdown-item py-2" href="{{ route('admin.system-management') }}"><i
                                    class="fas fa-cog me-2"></i>System Settings</a></li>
                        <li>
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
    // Update admin name in navbar
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
                        const adminName = document.getElementById('adminNameDisplay');
                        if (adminName) {
                            adminName.textContent = result.user.name || 'Admin';
                        }
                    }
                }
            } catch (error) {
                console.error('Error fetching admin profile:', error);
            }
        }
    });
</script>
