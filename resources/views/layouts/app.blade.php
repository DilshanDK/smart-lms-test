<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Smart LMS')</title>

    <!-- Poppins Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">

    <!-- Vue.js 3 CDN -->
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>

    <!-- Axios for API calls -->
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Google Sign-In -->
    <script src="https://accounts.google.com/gsi/client" async defer></script>

    <!-- Modern Design System CSS -->
    <style>
        :root {
            /* Modern Color Palette */
            --primary: #059669;
            --primary-dark: #047857;
            --secondary: #64748b;
            --background: #ffffff;
            --surface: #f8fafc;
            --foreground: #0f172a;
            --muted: #f1f5f9;
            --muted-foreground: #64748b;
            --accent: #10b981;
            --border: #e2e8f0;
            --success: #22c55e;
            --success-light: #dcfce7;
            --error: #ef4444;
            --error-light: #fef2f2;
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
            background: var(--surface);
            color: var(--foreground);
            line-height: 1.6;
        }

        /* Modern Navigation */
        .navbar {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%) !important;
            box-shadow: var(--shadow-lg);
            border: none;
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 1020;
            backdrop-filter: blur(10px);
        }

        .navbar-brand {
            font-weight: 700 !important;
            font-size: 1.75rem !important;
            color: white !important;
            letter-spacing: -0.02em;
            transition: all 0.3s ease;
        }

        .navbar-brand:hover {
            color: rgba(255, 255, 255, 0.9) !important;
            transform: translateY(-1px);
        }

        .navbar-text {
            color: rgba(255, 255, 255, 0.9) !important;
            font-weight: 500;
        }

        .btn-outline-light {
            border: 2px solid rgba(255, 255, 255, 0.3);
            color: white;
            font-weight: 600;
            border-radius: var(--radius);
            transition: all 0.3s ease;
        }

        .btn-outline-light:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: white;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 255, 255, 0.2);
        }

        /* Main Container */
        .main-container {
            min-height: calc(100vh - 80px);
            padding: 2rem 0;
        }

        .vue-container {
            min-height: 80vh;
            padding: 1rem;
        }

        /* Modern Alert Styles */
        .alert {
            border: none;
            border-radius: var(--radius);
            padding: 1rem 1.25rem;
            margin: 1rem 0;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            box-shadow: var(--shadow-sm);
        }

        .alert-success,
        .success {
            background: var(--success-light);
            color: #166534;
            border-left: 4px solid var(--success);
        }

        .alert-danger,
        .error {
            background: var(--error-light);
            color: #991b1b;
            border-left: 4px solid var(--error);
        }

        .alert-warning {
            background: var(--warning-light);
            color: #92400e;
            border-left: 4px solid var(--warning);
        }

        .alert-info {
            background: #e0f2fe;
            color: #0c4a6e;
            border-left: 4px solid #0ea5e9;
        }

        /* Loading Styles */
        .loading {
            text-align: center;
            padding: 3rem;
            color: var(--muted-foreground);
        }

        .loading-spinner {
            width: 40px;
            height: 40px;
            border: 3px solid var(--muted);
            border-top: 3px solid var(--primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 1rem;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        /* Modern Card Styles */
        .card {
            border: none;
            border-radius: var(--radius);
            box-shadow: var(--shadow-md);
            background: var(--background);
            transition: all 0.3s ease;
        }

        .card:hover {
            box-shadow: var(--shadow-lg);
            transform: translateY(-2px);
        }

        .card-header {
            background: var(--muted);
            border-bottom: 1px solid var(--border);
            font-weight: 600;
            color: var(--foreground);
        }

        /* Modern Button Styles */
        .btn {
            border-radius: var(--radius);
            font-weight: 500;
            padding: 0.5rem 1.25rem;
            transition: all 0.3s ease;
            border: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            box-shadow: var(--shadow-sm);
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, var(--primary-dark) 0%, #065f46 100%);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .btn-success {
            background: linear-gradient(135deg, var(--success) 0%, #16a34a 100%);
            color: white;
        }

        .btn-success:hover {
            background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
            transform: translateY(-2px);
        }

        /* Form Styles */
        .form-control {
            border: 2px solid var(--border);
            border-radius: var(--radius);
            padding: 0.75rem;
            transition: all 0.3s ease;
            font-weight: 400;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.1);
            outline: none;
        }

        .form-label {
            font-weight: 500;
            color: var(--foreground);
            margin-bottom: 0.5rem;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .navbar-brand {
                font-size: 1.5rem !important;
            }

            .main-container {
                padding: 1rem 0;
            }

            .container {
                padding: 0 1rem;
            }
        }

        /* Smooth Animations */
        * {
            transition: box-shadow 0.3s ease, transform 0.3s ease;
        }

        /* Focus States for Accessibility */
        .btn:focus-visible,
        .form-control:focus-visible,
        .navbar-brand:focus-visible {
            outline: 2px solid var(--primary);
            outline-offset: 2px;
        }
    </style>

    @yield('styles')
</head>

<body>

    <!-- Dynamic Navigation Based on Route -->
    @if (request()->routeIs('admin.*'))
        @include('components.admin-navbar')
    @elseif(request()->routeIs('student.*') && !request()->routeIs('student.requested-dashboard'))
        @include('components.student-navbar')
    @elseif(request()->routeIs('lecturer.*') && !request()->routeIs('lecturer.requested-dashboard'))
        @include('components.lecturer-navbar')
    @elseif(request()->routeIs('complete-registration'))
        @include('components.pending-navbar')
    @elseif(request()->routeIs('student.requested-dashboard'))
        @include('components.requested-student-navbar')
    @elseif(request()->routeIs('lecturer.requested-dashboard'))
        @include('components.requested-lecturer-navbar')
    @else
        {{-- Always show a default navbar on home and other routes --}}
        @include('components.default-navbar')
    @endif

    <!-- Main Content -->
    <main class="main-container">
        <div class="container">
            @yield('content')
        </div>
    </main>

    <!-- Footer -->
    <footer class="mt-auto">
        <div class="container text-center py-3">
            <small class="text-muted">
                © {{ date('Y') }} Smart LMS. All rights reserved.
            </small>
        </div>
    </footer>



    <!-- Vue.js Global Configuration -->
    <script>
        // Global Vue configuration
        window.App = {
            apiUrl: '{{ config('app.url') }}/api',
            googleClientId: '415824389753-vo8hmp9o8dnk7dfmihg04t46hfvfmltm.apps.googleusercontent.com',
            csrfToken: '{{ csrf_token() }}',
            user: @json(auth()->user() ?? null)
        };

        // Axios default configuration
        axios.defaults.baseURL = window.App.apiUrl;
        axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
        axios.defaults.headers.common['X-CSRF-TOKEN'] = window.App.csrfToken;

        // Add Bearer token if available
        const token = localStorage.getItem('auth_token');
        if (token) {
            axios.defaults.headers.common['Authorization'] = 'Bearer ' + token;
        }

        // Global error handler for axios
        axios.interceptors.response.use(
            response => response,
            error => {
                if (error.response?.status === 401) {
                    localStorage.removeItem('auth_token');
                    window.location.href = '{{ route('home') }}';
                }
                return Promise.reject(error);
            }
        );

        // Global logout function
        async function handleLogout() {
            const token = localStorage.getItem('auth_token');

            if (token) {
                try {
                    // Call API to invalidate session
                    await fetch('{{ config('app.url') }}/api/signout', {
                        method: 'POST',
                        headers: {
                            'Authorization': 'Bearer ' + token,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });
                } catch (error) {
                    console.error('Logout API error:', error);
                }
            }

            // Clear local storage and redirect
            localStorage.removeItem('auth_token');
            window.location.href = '{{ route('home') }}';
        }

        // Add logout event listener when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            const logoutBtn = document.getElementById('logout-btn');
            if (logoutBtn) {
                logoutBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    handleLogout();
                });
            }
        });
    </script>

    @yield('scripts')

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
