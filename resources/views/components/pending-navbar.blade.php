@include('components.session-check', ['requiredRole' => 'pending'])

<!-- Pending User Navigation Bar -->
<nav class="navbar navbar-expand-lg navbar-dark sticky-top" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); box-shadow: 0 2px 10px rgba(0,0,0,0.1); z-index: 1030;">
    <div class="container-fluid px-4">
        <!-- Brand -->
        <a class="navbar-brand fw-bold fs-4" href="/">
            <i class="fas fa-clock me-2"></i>
            Smart LMS
        </a>
        
        <!-- User Menu -->
        <div class="navbar-nav ms-auto">
            <div class="nav-item dropdown">
                <a class="nav-link dropdown-toggle fw-medium px-3" href="#" role="button" data-bs-toggle="dropdown">
                    <i class="fas fa-user-clock me-1"></i>
                    Profile Pending
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                    <li><a class="dropdown-item py-2 text-danger" onclick="handleLogout()"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>
