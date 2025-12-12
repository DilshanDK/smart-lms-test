@extends('layouts.app')

@section('title', 'Lecturer Dashboard - Smart LMS')

@section('styles')
    <style>
        /* Modern Typography System - Poppins Font */
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap');

        :root {
            --primary: #7c3aed;
            --primary-dark: #6d28d9;
            --secondary: #64748b;
            --background: #ffffff;
            --surface: #f8fafc;
            --foreground: #0f172a;
            --muted: #f1f5f9;
            --muted-foreground: #64748b;
            --accent: #8b5cf6;
            --border: #e2e8f0;
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
    <div id="lecturer-dashboard">
        <!-- Dashboard Header -->
        <div class="dashboard-header">
            <div class="container">
                <h1 class="dashboard-title">Welcome Back, Lecturer!</h1>
                <p class="dashboard-subtitle">Manage your courses and students</p>
            </div>
        </div>

        <div class="container">
            <!-- Stats Row -->
            <div class="stats-section">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="stat-number" id="totalCourses">0</div>
                    <div class="stat-label">Total Courses</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-number" id="totalStudents">0</div>
                    <div class="stat-label">Total Students</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <div class="stat-number" id="totalAssignments">0</div>
                    <div class="stat-label">Assignments</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="stat-number" id="avgStudents">0</div>
                    <div class="stat-label">Avg Students/Course</div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card action-card">
                        <i class="fas fa-book action-icon"></i>
                        <h3 class="action-title">My Courses</h3>
                        <p class="action-description">View and manage your assigned courses</p>
                        <a href="{{ route('lecturer.course-management') }}" class="btn-action">View Courses</a>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card action-card">
                        <i class="fas fa-users action-icon"></i>
                        <h3 class="action-title">Students</h3>
                        <p class="action-description">Manage students enrolled in your courses</p>
                        <a href="{{ route('lecturer.student-management') }}" class="btn-action">View Students</a>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card action-card">
                        <i class="fas fa-clipboard-list action-icon"></i>
                        <h3 class="action-title">Assignments</h3>
                        <p class="action-description">Create and manage course assignments</p>
                        <a href="{{ route('lecturer.assignment-management') }}" class="btn-action">Manage Assignments</a>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card action-card">
                        <i class="fas fa-chart-bar action-icon"></i>
                        <h3 class="action-title">Gradebook</h3>
                        <p class="action-description">View and manage student grades</p>
                        <a href="{{ route('lecturer.gradebook-management') }}" class="btn-action">View Gradebook</a>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card action-card">
                        <i class="fas fa-video action-icon"></i>
                        <h3 class="action-title">Videos</h3>
                        <p class="action-description">Upload and manage course videos</p>
                        <a href="{{ route('lecturer.videos-management') }}" class="btn-action">Manage Videos</a>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card action-card">
                        <i class="fas fa-folder-open action-icon"></i>
                        <h3 class="action-title">Resources</h3>
                        <p class="action-description">Upload learning materials and resources</p>
                        <a href="{{ route('lecturer.resource-management') }}" class="btn-action">Manage Resources</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        const API_BASE = '{{ url('') }}/api';

        document.addEventListener('DOMContentLoaded', async function() {
            const token = localStorage.getItem('auth_token');

            if (!token) {
                return;
            }

            try {
                // Fetch student count by course
                const studentCountResponse = await fetch(`${API_BASE}/lecturer/student-count-by-course`, {
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Accept': 'application/json'
                    }
                });

                if (studentCountResponse.ok) {
                    const studentCountResult = await studentCountResponse.json();
                    if (studentCountResult.status === 'success') {
                        document.getElementById('totalCourses').textContent = studentCountResult.total_courses || 0;
                        document.getElementById('totalStudents').textContent = studentCountResult.total_students || 0;
                        document.getElementById('avgStudents').textContent = studentCountResult.average_students_per_course || 0;
                    }
                }

                // Fetch assignments count (you can implement this later)
                // For now, set to 0
                document.getElementById('totalAssignments').textContent = '0';

            } catch (error) {
                console.error('Error loading dashboard stats:', error);
            }
        });
    </script>
@endsection
