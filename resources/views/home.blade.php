<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Smart LMS - Modern Learning Management System with Live Streaming">
    <title>Smart LMS - Educational Platform</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Google Sign-In -->
    <script src="https://accounts.google.com/gsi/client" async defer></script>

    <style>
        :root {
            --primary: #059669;
            --primary-dark: #047857;
            --secondary: #10b981;
            --background: #ffffff;
            --surface: #f9fafb;
            --foreground: #1f2937;
            --muted: #e5e7eb;
            --muted-foreground: #6b7280;
            --radius: 8px;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: var(--background);
            color: var(--foreground);
            line-height: 1.6;
        }

        /* Navigation */
        nav {
            background: white;
            box-shadow: var(--shadow);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
        }

        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .nav-links {
            display: flex;
            gap: 2rem;
            align-items: center;
            list-style: none;
        }

        .nav-links a {
            text-decoration: none;
            color: var(--foreground);
            font-weight: 500;
            transition: color 0.3s;
        }

        .nav-links a:hover {
            color: var(--primary);
        }

        /* Hero Section */
        .hero {
            margin-top: 80px;
            padding: 4rem 2rem;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            text-align: center;
        }

        .hero-content {
            max-width: 800px;
            margin: 0 auto;
        }

        .hero h1 {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 1rem;
            line-height: 1.2;
        }

        .hero p {
            font-size: 1.25rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }

        .hero-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-secondary {
            background: transparent;
            color: white;
            padding: 0.75rem 1.5rem;
            border: 2px solid white;
            border-radius: var(--radius);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-secondary:hover {
            background: white;
            color: var(--primary);
        }

        /* Features Section */
        .features {
            padding: 4rem 2rem;
            background: var(--surface);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .section-title {
            text-align: center;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 3rem;
            color: var(--foreground);
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .feature-card {
            background: white;
            padding: 2rem;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            transition: all 0.3s;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        .feature-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
            color: white;
            font-size: 1.5rem;
        }

        .feature-card h3 {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .feature-card p {
            color: var(--muted-foreground);
        }

        /* Stats Section */
        .stats {
            padding: 4rem 2rem;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
            text-align: center;
        }

        .stat-item h3 {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
        }

        .stat-item p {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        /* CTA Section */
        .cta {
            padding: 4rem 2rem;
            background: white;
            text-align: center;
        }

        .cta h2 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .cta p {
            font-size: 1.1rem;
            color: var(--muted-foreground);
            margin-bottom: 2rem;
        }

        /* Footer */
        footer {
            background: var(--foreground);
            color: white;
            padding: 3rem 2rem 1rem;
        }

        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .footer-section h3 {
            margin-bottom: 1rem;
            font-size: 1.2rem;
        }

        .footer-section ul {
            list-style: none;
        }

        .footer-section ul li {
            margin-bottom: 0.5rem;
        }

        .footer-section a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: color 0.3s;
        }

        .footer-section a:hover {
            color: white;
        }

        .footer-bottom {
            text-align: center;
            padding-top: 2rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            color: rgba(255, 255, 255, 0.6);
        }

        .social-links {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }

        .social-links a {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
        }

        .social-links a:hover {
            background: var(--primary);
            transform: translateY(-3px);
        }

        /* Success Popup */
        .popup-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
            z-index: 9998;
            display: none;
        }

        .success-popup {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
            border: 2px solid #22c55e;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 25px 50px rgba(34, 197, 94, 0.3);
            z-index: 9999;
            min-width: 340px;
            max-width: 400px;
            text-align: center;
            display: none;
        }

        .success-popup .popup-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
            border-radius: 50%;
            margin: 0 auto 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .success-popup .popup-icon svg {
            width: 40px;
            height: 40px;
            color: white;
            stroke-width: 3;
        }

        .success-popup h3 {
            color: #166534;
            font-size: 24px;
            font-weight: 700;
            margin: 0 0 1rem 0;
        }

        .success-popup p {
            color: #15803d;
            font-size: 16px;
            margin: 0;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .nav-links {
                display: none;
            }

            .hero h1 {
                font-size: 2rem;
            }

            .hero p {
                font-size: 1rem;
            }

            .section-title {
                font-size: 1.75rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav>
        <div class="nav-container">
            <a href="/" class="logo">
                <i class="fas fa-graduation-cap"></i>
                Smart LMS
            </a>
            <ul class="nav-links">
                <li><a href="#features">Features</a></li>
                <li><a href="#about">About</a></li>
                <li><a href="#contact">Contact</a></li>
                <li>
                    <div id="g_id_onload"
                        data-client_id="{{ config('services.google.client_id') }}"
                        data-callback="handleCredentialResponse"
                        data-auto_prompt="false">
                    </div>
                    <div class="g_id_signin"
                        data-type="standard"
                        data-size="large"
                        data-theme="outline"
                        data-text="sign_in_with"
                        data-shape="rectangular"
                        data-logo_alignment="left">
                    </div>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1>Welcome to Smart LMS</h1>
            <p>A modern learning management system with live streaming, video uploads, and interactive assignments. Transform your educational experience today.</p>
            <div class="hero-buttons">
                <div id="g_id_signin_hero"
                    data-client_id="{{ config('services.google.client_id') }}"
                    data-callback="handleCredentialResponse"
                    data-auto_prompt="false">
                    <div class="g_id_signin"
                        data-type="standard"
                        data-size="large"
                        data-theme="filled_blue"
                        data-text="sign_in_with"
                        data-shape="pill"
                        data-logo_alignment="left">
                    </div>
                </div>
                <a href="#features" class="btn-secondary">Learn More</a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features" id="features">
        <div class="container">
            <h2 class="section-title">Powerful Features</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-video"></i>
                    </div>
                    <h3>Live Streaming</h3>
                    <p>Stream lectures live to YouTube with real-time engagement and automatic recording.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-upload"></i>
                    </div>
                    <h3>Video Management</h3>
                    <p>Upload and organize educational videos with automatic playlist creation per course.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-clipboard-check"></i>
                    </div>
                    <h3>Interactive Assignments</h3>
                    <p>Create secure quizzes with encrypted questions and automated grading.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3>Progress Tracking</h3>
                    <p>Monitor student performance with detailed analytics and progress reports.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3>User Management</h3>
                    <p>Efficient admin panel for managing students, lecturers, and course enrollments.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <h3>Resource Sharing</h3>
                    <p>Share course materials, documents, and resources with organized file management.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section (About) -->
    <section class="stats" id="about">
        <div class="container">
            <h2 class="section-title" style="color: white; margin-bottom: 3rem;">Platform Statistics</h2>
            <div class="stats-grid">
                <div class="stat-item">
                    <h3>500+</h3>
                    <p>Active Students</p>
                </div>
                <div class="stat-item">
                    <h3>50+</h3>
                    <p>Expert Lecturers</p>
                </div>
                <div class="stat-item">
                    <h3>100+</h3>
                    <p>Courses Available</p>
                </div>
                <div class="stat-item">
                    <h3>1000+</h3>
                    <p>Video Lessons</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta">
        <div class="container">
            <h2>Ready to Transform Your Learning?</h2>
            <p>Join thousands of students and educators using Smart LMS</p>
            <div id="g_id_signin_cta"
                data-client_id="{{ config('services.google.client_id') }}"
                data-callback="handleCredentialResponse"
                data-auto_prompt="false">
                <div class="g_id_signin"
                    data-type="standard"
                    data-size="large"
                    data-theme="filled_blue"
                    data-text="sign_in_with"
                    data-shape="pill"
                    data-logo_alignment="left">
                </div>
            </div>
        </div>
    </section>

    <!-- Footer (Contact Section) -->
    <footer id="contact">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3><i class="fas fa-graduation-cap"></i> Smart LMS</h3>
                    <p>Modern learning management system with powerful features for educational institutions.</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>

                <div class="footer-section">
                    <h3>Features</h3>
                    <ul>
                        <li><a href="#features"><i class="fas fa-angle-right"></i> Live Streaming</a></li>
                        <li><a href="#features"><i class="fas fa-angle-right"></i> Video Management</a></li>
                        <li><a href="#features"><i class="fas fa-angle-right"></i> Assignments</a></li>
                        <li><a href="#features"><i class="fas fa-angle-right"></i> Progress Tracking</a></li>
                    </ul>
                </div>

                <div class="footer-section">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="#about"><i class="fas fa-angle-right"></i> About Us</a></li>
                        <li><a href="#contact"><i class="fas fa-angle-right"></i> Contact</a></li>
                        <li><a href="#"><i class="fas fa-angle-right"></i> Privacy Policy</a></li>
                        <li><a href="#"><i class="fas fa-angle-right"></i> Terms of Service</a></li>
                    </ul>
                </div>

                <div class="footer-section">
                    <h3>Contact Us</h3>
                    <ul>
                        <li><i class="fas fa-envelope"></i> support@smartlms.com</li>
                        <li><i class="fas fa-phone"></i> +94 11 234 5678</li>
                        <li><i class="fas fa-map-marker-alt"></i> Colombo, Sri Lanka</li>
                    </ul>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; 2024 Smart LMS. All rights reserved. Built with ❤️ for education.</p>
            </div>
        </div>
    </footer>

    <!-- Success Popup Modal -->
    <div id="success-popup-overlay" class="popup-overlay" style="display: none;"></div>
    <div id="success-popup" class="success-popup" style="display: none;">
        <div class="popup-icon">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>
            </svg>
        </div>
        <h3 id="popup-title">🎉 Login Successful!</h3>
        <p id="popup-message">Redirecting to your dashboard...</p>
    </div>

    <!-- Smooth Scroll Script + Authentication -->
    <script>
        const baseUrl = window.location.origin;

        // --- Session check on page load ---
        document.addEventListener('DOMContentLoaded', async function() {
            const token = localStorage.getItem('auth_token');
            if (token) {
                try {
                    const res = await fetch(`${baseUrl}/api/validate-session`, {
                        headers: {
                            'Authorization': 'Bearer ' + token,
                            'Accept': 'application/json'
                        }
                    });
                    const result = await res.json();
                    if (res.ok && result.status === 'success' && result.user) {
                        // Redirect based on user role with Laravel route URLs
                        let role = result.user.role;
                        if (role === 'pending') {
                            window.location.href = '{{ route('complete-registration') }}';
                        } else if (role === 'requested_student') {
                            window.location.href = '{{ route('student.requested-dashboard') }}';
                        } else if (role === 'requested_lecturer') {
                            window.location.href = '{{ route('lecturer.requested-dashboard') }}';
                        } else if (role === 'assigned_student') {
                            window.location.href = '{{ route('student.dashboard') }}';
                        } else if (role === 'assigned_lecturer') {
                            window.location.href = '{{ route('lecturer.dashboard') }}';
                        } else if (role === 'admin') {
                            window.location.href = '{{ route('admin.dashboard') }}';
                        } else {
                            window.location.href = '{{ route('home') }}';
                            localStorage.removeItem('auth_token');
                        }
                        return;
                    } else {
                        localStorage.removeItem('auth_token');
                    }
                } catch (e) {
                    localStorage.removeItem('auth_token');
                }
            }
        });

        // Google Sign-In callback
        function handleCredentialResponse(response) {
            try {
                const payload = JSON.parse(atob(response.credential.split('.')[1]));
                googleSignInToBackend(response.credential, payload.name, payload.email);
            } catch (e) {
                showErrorPopup('Google sign-in failed. Please try again.');
            }
        }

        async function googleSignInToBackend(id_token, name, email) {
            try {
                const res = await fetch(`${baseUrl}/api/google-signin`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        id_token,
                        name,
                        email
                    })
                });
                const result = await res.json();
                if (res.ok && result.status === 'success') {
                    if (result.token) {
                        localStorage.setItem('auth_token', result.token);
                    }
                    showSuccessPopup(result.redirect_url);
                } else {
                    showErrorPopup(result.message || 'Google sign-in failed');
                }
            } catch (err) {
                showErrorPopup('Network error. Please try again.');
            }
        }

        function showSuccessPopup(redirectUrl) {
            document.getElementById('popup-title').textContent = '🎉 Login Successful!';
            document.getElementById('popup-message').textContent = 'Redirecting to your dashboard...';
            document.getElementById('success-popup-overlay').style.display = 'block';
            document.getElementById('success-popup').style.display = 'block';
            setTimeout(() => {
                window.location.href = redirectUrl || `${baseUrl}/dashboard`;
            }, 2000);
        }

        function showErrorPopup(message) {
            document.getElementById('popup-title').textContent = '❌ Login Failed!';
            document.getElementById('popup-message').textContent = message;
            const popup = document.getElementById('success-popup');
            popup.style.background = 'linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%)';
            popup.style.borderColor = '#ef4444';
            document.querySelector('.popup-icon').style.background = 'linear-gradient(135deg, #ef4444 0%, #dc2626 100%)';
            document.querySelector('.popup-icon svg').innerHTML =
                '<path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>';
            document.getElementById('success-popup-overlay').style.display = 'block';
            popup.style.display = 'block';
            setTimeout(() => {
                document.getElementById('success-popup-overlay').style.display = 'none';
                popup.style.display = 'none';
                // Reset styles
                popup.style.background = 'linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%)';
                popup.style.borderColor = '#22c55e';
                document.querySelector('.popup-icon').style.background = 'linear-gradient(135deg, #22c55e 0%, #16a34a 100%)';
                document.querySelector('.popup-icon svg').innerHTML =
                    '<path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>';
            }, 5000);
        }
    </script>
</body>
</html>
