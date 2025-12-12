@extends('layouts.app')

@section('title', 'Lecturer Registration Pending - Smart LMS')

@section('styles')
    <style>
        /* Modern Typography System - Poppins Font */
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap');

        :root {
            --primary: #7c3aed;
            --primary-dark: #6d28d9;
            --warning: #f59e0b;
            --warning-light: #fef3c7;
            --background: #ffffff;
            --surface: #f8fafc;
            --foreground: #0f172a;
            --muted: #f1f5f9;
            --muted-foreground: #64748b;
            --border: #e2e8f0;
            --radius: 8px;
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        * {
            font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        body {
            background: var(--surface);
        }

        .dashboard-header {
            background: linear-gradient(135deg, var(--warning) 0%, #d97706 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }

        .dashboard-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .dashboard-subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .pending-container {
            max-width: 800px;
            margin: 0 auto;
            background: var(--background);
            border-radius: 16px;
            box-shadow: var(--shadow-md);
            padding: 3rem 2rem;
            text-align: center;
        }

        .pending-icon {
            font-size: 4rem;
            color: var(--warning);
            margin-bottom: 1.5rem;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.7;
            }
        }

        .status-card {
            background: var(--warning-light);
            border: 1px solid var(--warning);
            border-radius: var(--radius);
            padding: 1.5rem;
            margin: 2rem 0;
            text-align: left;
        }

        .info-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 1.5rem;
            margin: 2rem 0;
            text-align: left;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: var(--radius);
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            color: white;
            text-decoration: none;
        }

        .btn-outline {
            background: transparent;
            border: 2px solid var(--border);
            color: var(--muted-foreground);
        }

        .btn-outline:hover {
            border-color: var(--primary);
            color: var(--primary);
            text-decoration: none;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
        }

        @media (max-width: 768px) {
            .pending-container {
                margin: 2rem 1rem;
                padding: 2rem 1rem;
            }

            .action-buttons {
                flex-direction: column;
            }

            .dashboard-title {
                font-size: 2rem;
            }
        }
    </style>
@endsection

@section('content')
    <div id="lecturer-pending-dashboard">
        <!-- Loading State -->
        <div id="loading-state" class="dashboard-header">
            <div class="container text-center">
                <div class="spinner-border text-light" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-3">Checking your application status...</p>
            </div>
        </div>

        <!-- Pending Status -->
        <div id="pending-content" style="display: none;">
            <div class="dashboard-header">
                <div class="container">
                    <h1 class="dashboard-title">Application Under Review</h1>
                    <p class="dashboard-subtitle">Your lecturer application is being processed</p>
                </div>
            </div>

            <div class="container">
                <div class="pending-container">
                    <div class="pending-icon">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>

                    <h2 style="color: var(--foreground); margin-bottom: 1rem;">Lecturer Application Submitted</h2>
                    <p style="color: var(--muted-foreground); margin-bottom: 2rem; font-size: 1.1rem;">
                        Thank you for your interest in joining our teaching team! Your lecturer application is currently being
                        reviewed by our academic administrators.
                    </p>

                    <div class="status-card">
                        <h3 style="margin-top: 0; color: var(--warning);">
                            <i class="fas fa-info-circle"></i> Application Status
                        </h3>
                        <p><strong>Status:</strong> Pending Academic Review</p>
                        <p><strong>Submitted:</strong> <span id="submitted-date">Loading...</span></p>
                        <p><strong>Expected Response:</strong> Within 3-5 business days</p>
                    </div>

                    <div class="info-card">
                        <h4 style="margin-top: 0; color: var(--foreground);">
                            <i class="fas fa-lightbulb"></i> What happens next?
                        </h4>
                        <ul style="text-align: left; color: var(--muted-foreground);">
                            <li>Academic committee will review your credentials</li>
                            <li>Background verification will be conducted</li>
                            <li>You'll receive email notification once approved</li>
                            <li>Access to course creation and management tools</li>
                            <li>Ability to enroll and manage students</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Declined Status -->
        <div id="declined-content" style="display: none;">
            <div class="dashboard-header" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);">
                <div class="container">
                    <h1 class="dashboard-title">Application Declined</h1>
                    <p class="dashboard-subtitle">Your lecturer application has been reviewed</p>
                </div>
            </div>

            <div class="container">
                <div class="pending-container">
                    <div class="pending-icon" style="color: #ef4444;">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>

                    <h2 style="color: var(--foreground); margin-bottom: 1rem;">Application Not Approved</h2>
                    <p style="color: var(--muted-foreground); margin-bottom: 2rem; font-size: 1.1rem;">
                        Unfortunately, your lecturer application has been declined by our academic committee.
                    </p>

                    <div class="status-card" style="background: #fef2f2; border-color: #ef4444;">
                        <h3 style="margin-top: 0; color: #ef4444;">
                            <i class="fas fa-times-circle"></i> Application Status
                        </h3>
                        <p><strong>Status:</strong> <span style="color: #ef4444;">Declined</span></p>
                        <p><strong>Submitted:</strong> <span id="declined-submitted-date">Loading...</span></p>
                        <p><strong>Processed:</strong> <span id="declined-processed-date">Loading...</span></p>
                        <div id="admin-notes" style="margin-top: 1rem; display: none;">
                            <p><strong>Admin Notes:</strong></p>
                            <p id="admin-notes-text" style="font-style: italic; color: #7f1d1d;"></p>
                        </div>
                    </div>

                    <div class="info-card">
                        <h4 style="margin-top: 0; color: var(--foreground);">
                            <i class="fas fa-question-circle"></i> What can you do?
                        </h4>
                        <ul style="text-align: left; color: var(--muted-foreground);">
                            <li>Contact our academic office for detailed feedback</li>
                            <li>Request information about reapplication requirements</li>
                            <li>Ask about professional development opportunities</li>
                            <li>Discuss qualification enhancement programs</li>
                        </ul>

                        <div style="margin-top: 2rem; padding: 1rem; background: var(--muted); border-radius: var(--radius);">
                            <h5 style="margin-top: 0; color: var(--foreground);">Contact Academic Office</h5>
                            <p style="margin-bottom: 0.5rem;"><strong>Email:</strong> academic@smartlms.com</p>
                            <p style="margin-bottom: 0.5rem;"><strong>Phone:</strong> +94 11 234 5678</p>
                            <p style="margin-bottom: 0;"><strong>Office Hours:</strong> Monday - Friday, 9:00 AM - 5:00 PM</p>
                        </div>
                    </div>

                    <div class="action-buttons">
                        <a href="mailto:academic@smartlms.com" class="btn btn-primary">
                            <i class="fas fa-envelope"></i> Contact Academic Office
                        </a>
                        <a href="#" onclick="handleLogout()" class="btn btn-outline">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        (function() {
            document.addEventListener('DOMContentLoaded', async function() {
                try {
                    const token = localStorage.getItem('auth_token');

                    if (!token) {
                        console.error('Authorization token not found');
                        window.location.href = '/';
                        return;
                    }

                    // Check registration status
                    const statusResponse = await fetch('/api/registration-status', {
                        headers: {
                            'Authorization': 'Bearer ' + token
                        }
                    });

                    if (statusResponse.ok) {
                        const statusData = await statusResponse.json();

                        // If user is already assigned, redirect to their dashboard
                        if (statusData.redirect_url) {
                            window.location.href = statusData.redirect_url;
                            return;
                        }

                        // Hide loading state
                        document.getElementById('loading-state').style.display = 'none';

                        // Show appropriate content based on status
                        if (statusData.request_status === 'declined') {
                            showDeclinedContent(statusData);
                        } else {
                            showPendingContent(statusData);
                        }
                    } else {
                        console.error('Failed to fetch registration status');
                        document.getElementById('loading-state').style.display = 'none';
                        showPendingContent(); // Fallback to pending
                    }
                } catch (error) {
                    console.error('Error checking registration status:', error);
                    document.getElementById('loading-state').style.display = 'none';
                    showPendingContent(); // Fallback to pending
                }
            });

            function showPendingContent(statusData = {}) {
                document.getElementById('pending-content').style.display = 'block';

                if (statusData.submitted_at) {
                    const submittedDate = new Date(statusData.submitted_at);
                    document.getElementById('submitted-date').textContent = submittedDate.toLocaleDateString('en-US', {
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric'
                    }) + ' at ' + submittedDate.toLocaleTimeString('en-US', {
                        hour: '2-digit',
                        minute: '2-digit',
                        hour12: true
                    });
                } else {
                    document.getElementById('submitted-date').textContent = 'Date not available';
                }
            }

            function showDeclinedContent(statusData) {
                document.getElementById('declined-content').style.display = 'block';

                // Set submitted date
                if (statusData.submitted_at) {
                    const submittedDate = new Date(statusData.submitted_at);
                    document.getElementById('declined-submitted-date').textContent = submittedDate.toLocaleDateString('en-US', {
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric'
                    }) + ' at ' + submittedDate.toLocaleTimeString('en-US', {
                        hour: '2-digit',
                        minute: '2-digit',
                        hour12: true
                    });
                }

                // Set processed date
                if (statusData.processed_at) {
                    const processedDate = new Date(statusData.processed_at);
                    document.getElementById('declined-processed-date').textContent = processedDate.toLocaleDateString('en-US', {
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric'
                    }) + ' at ' + processedDate.toLocaleTimeString('en-US', {
                        hour: '2-digit',
                        minute: '2-digit',
                        hour12: true
                    });
                } else {
                    document.getElementById('declined-processed-date').textContent = 'Recently processed';
                }

                // Show admin notes if available
                if (statusData.admin_notes) {
                    document.getElementById('admin-notes').style.display = 'block';
                    document.getElementById('admin-notes-text').textContent = statusData.admin_notes;
                }
            }
        })();
    </script>
@endsection
