@extends('layouts.app')

@section('title', 'Admin Dashboard - Smart LMS')

@section('styles')
    <style>
        /* Modern Typography System - Poppins Font */
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap');

        :root {
            --primary: #3b82f6;
            /* Light blue */
            --primary-dark: #2563eb;
            /* Darker blue */
            --secondary: #6b7280;
            /* Neutral gray */
            --background: #f9fafb;
            /* Light gray */
            --surface: #ffffff;
            /* White */
            --foreground: #1f2937;
            /* Dark gray */
            --muted: #e5e7eb;
            /* Muted light gray */
            --muted-foreground: #4b5563;
            /* Medium gray */
            --accent: #f97316;
            /* Orange for accents */
            --border: #d1d5db;
            /* Light border gray */
            --radius: 8px;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        * {
            font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        body {
            background: var(--background);
        }

        .dashboard-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 2rem 0;
            border-radius: var(--radius);
            margin-bottom: 2rem;
            box-shadow: var(--shadow-md);
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

        .stats-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--surface);
            border-radius: var(--radius);
            padding: 1.5rem;
            box-shadow: var(--shadow-md);
            transition: all 0.3s ease;
            text-align: center;
            border: none;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
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

        .action-card {
            background: var(--surface);
            border-radius: var(--radius);
            padding: 1.5rem;
            box-shadow: var(--shadow-md);
            transition: all 0.3s ease;
            border: none;
            height: 100%;
        }

        .action-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
        }

        .action-icon {
            width: 2.5rem;
            height: 2.5rem;
            margin-bottom: 1rem;
            color: var(--primary);
        }

        .action-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.75rem;
            color: var(--foreground);
        }

        .action-description {
            color: var(--muted-foreground);
            margin-bottom: 1rem;
        }

        .btn-action {
            background: var(--primary);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: var(--radius);
            font-weight: 500;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-action:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            color: white;
            text-decoration: none;
        }
    </style>
@endsection

@section('content')
    <div id="admin-dashboard">
        <!-- Dashboard Header -->
        <div class="dashboard-header">
            <div class="container">
                <h1 class="dashboard-title">Welcome Back, Admin!</h1>
                <p class="dashboard-subtitle">Manage your learning management system</p>
            </div>
        </div>

        <div class="container">
            <!-- Stats Row -->
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
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <div class="stat-number" id="totalLecturers">0</div>
                    <div class="stat-label">Total Lecturers</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="stat-number" id="totalCourses">0</div>
                    <div class="stat-label">Total Courses</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-user-clock"></i>
                    </div>
                    <div class="stat-number" id="pendingRequests">0</div>
                    <div class="stat-label">Pending Requests</div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card action-card">
                        <i class="fas fa-users-cog action-icon"></i>
                        <h3 class="action-title">User Management</h3>
                        <p class="action-description">Manage students and lecturers</p>
                        <a href="{{ route('admin.user-management') }}" class="btn-action">Manage Users</a>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card action-card">
                        <i class="fas fa-book action-icon"></i>
                        <h3 class="action-title">Course Management</h3>
                        <p class="action-description">Add and manage courses</p>
                        <a href="{{ route('admin.course-management') }}" class="btn-action">Manage Courses</a>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card action-card">
                        <i class="fas fa-video action-icon"></i>
                        <h3 class="action-title">Live Streams</h3>
                        <p class="action-description">Monitor live streaming sessions</p>
                        <a href="{{ route('admin.video-stream-management') }}" class="btn-action">View Streams</a>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card action-card">
                        <i class="fas fa-folder-open action-icon"></i>
                        <h3 class="action-title">Resources</h3>
                        <p class="action-description">Manage learning resources</p>
                        <a href="{{ route('admin.resource-management') }}" class="btn-action">Manage Resources</a>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card action-card">
                        <i class="fas fa-cog action-icon"></i>
                        <h3 class="action-title">System Settings</h3>
                        <p class="action-description">Configure system-wide settings</p>
                        <a href="{{ route('admin.system-management') }}" class="btn-action">Manage Settings</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
const API_BASE = '{{ url('') }}/api/admin';

document.addEventListener('DOMContentLoaded', async function() {
    const token = localStorage.getItem('auth_token');
    
    if (!token) {
        return;
    }

    try {
        // Fetch all statistics in parallel
        const [studentsResponse, lecturersResponse, coursesResponse, requestsResponse] = await Promise.all([
            fetch(`${API_BASE}/assigned-students-count`, {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                }
            }),
            fetch(`${API_BASE}/assigned-lecturers-count`, {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                }
            }),
            fetch(`${API_BASE}/active-courses-count`, {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                }
            }),
            fetch(`${API_BASE}/pending-requests-count`, {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                }
            })
        ]);

        // Update Total Students
        if (studentsResponse.ok) {
            const studentsResult = await studentsResponse.json();
            if (studentsResult.status === 'success') {
                document.getElementById('totalStudents').textContent = studentsResult.data.total_students || 0;
            }
        }

        // Update Total Lecturers
        if (lecturersResponse.ok) {
            const lecturersResult = await lecturersResponse.json();
            if (lecturersResult.status === 'success') {
                document.getElementById('totalLecturers').textContent = lecturersResult.data.total_lecturers || 0;
            }
        }

        // Update Total Courses (active courses)
        if (coursesResponse.ok) {
            const coursesResult = await coursesResponse.json();
            if (coursesResult.status === 'success') {
                document.getElementById('totalCourses').textContent = coursesResult.data.active_courses || 0;
            }
        }

        // Update Pending Requests
        if (requestsResponse.ok) {
            const requestsResult = await requestsResponse.json();
            if (requestsResult.status === 'success') {
                document.getElementById('pendingRequests').textContent = requestsResult.data.total_all_pending || 0;
            }
        }

    } catch (error) {
        console.error('Error loading dashboard stats:', error);
    }
});
</script>
@endsection
