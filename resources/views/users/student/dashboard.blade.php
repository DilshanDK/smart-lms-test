@extends('layouts.app')

@section('title', 'Student Dashboard - Smart LMS')

@section('styles')
    <style>
        /* Modern Typography System - Poppins Font */
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap');

        :root {
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
            --radius: 8px;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        * {
            font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
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

        .stats-card {
            background: white;
            border-radius: var(--radius);
            padding: 1.5rem;
            box-shadow: var(--shadow-md);
            transition: all 0.3s ease;
            text-align: center;
            border: none;
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        .stats-icon {
            width: 3rem;
            height: 3rem;
            margin: 0 auto 1rem;
            background: var(--primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }

        .stats-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 0.5rem;
        }

        .stats-label {
            color: var(--muted-foreground);
            font-weight: 500;
        }

        .action-card {
            background: white;
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
    <div id="student-dashboard">
        <!-- Dashboard Header -->
        <div class="dashboard-header">
            <div class="container">
                <h1 class="dashboard-title">Welcome Back, Student!</h1>
                <p class="dashboard-subtitle">Continue your learning journey</p>
            </div>
        </div>

        <div class="container">
            <!-- Stats Row -->
            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <div class="card stats-card">
                        <div class="stats-icon">
                            <i class="fas fa-book"></i>
                        </div>
                        <div class="stats-number" id="enrolledCoursesCount">...</div>
                        <div class="stats-label">Enrolled Courses</div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card stats-card">
                        <div class="stats-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stats-number">3</div>
                        <div class="stats-label">Completed</div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card stats-card">
                        <div class="stats-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stats-number">2</div>
                        <div class="stats-label">In Progress</div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card stats-card">
                        <div class="stats-icon">
                            <i class="fas fa-star"></i>
                        </div>
                        <div class="stats-number" id="averageGrade">...</div>
                        <div class="stats-label">Average Grade</div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card action-card">
                        <i class="fas fa-book-open action-icon"></i>
                        <h3 class="action-title">Course Enrollment</h3>
                        <p class="action-description">Browse and enroll in available courses</p>
                        <a href="{{ route('student.course-management') }}" class="btn-action" onclick="setCourseTab('available')">Browse Courses</a>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card action-card">
                        <i class="fas fa-graduation-cap action-icon"></i>
                        <h3 class="action-title">My Enrolled Courses</h3>
                        <p class="action-description">View your enrolled courses and access materials</p>
                        <a href="{{ route('student.course-management') }}" class="btn-action" onclick="setCourseTab('enrolled')">My Courses</a>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card action-card">
                        <i class="fas fa-tasks action-icon"></i>
                        <h3 class="action-title">Assignments</h3>
                        <p class="action-description">Check upcoming assignments and submit your work</p>
                        <a href="{{ route('student.assignment-management') }}" class="btn-action">View Assignments</a>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card action-card">
                        <i class="fas fa-chart-line action-icon"></i>
                        <h3 class="action-title">Academic Progress</h3>
                        <p class="action-description">Track your learning progress and performance</p>
                        <a href="{{ route('student.progress-management') }}" class="btn-action">View Progress</a>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card action-card">
                        <i class="fas fa-video action-icon"></i>
                        <h3 class="action-title">Course Videos</h3>
                        <p class="action-description">Watch video lectures and recorded sessions</p>
                        <a href="{{ route('student.video-management') }}" class="btn-action">Watch Videos</a>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card action-card">
                        <i class="fas fa-folder-open action-icon"></i>
                        <h3 class="action-title">Learning Resources</h3>
                        <p class="action-description">Access study materials and course resources</p>
                        <a href="{{ route('student.resource-management') }}" class="btn-action">View Resources</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
function setCourseTab(tab) {
    localStorage.setItem('studentCourseTab', tab);
}

document.addEventListener('DOMContentLoaded', async function() {
    const token = localStorage.getItem('auth_token');
    // Enrolled courses count
    if (token) {
        try {
            const response = await fetch('/api/student/courses/enrolled-count', {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                }
            });
            if (response.ok) {
                const result = await response.json();
                if (result.status === 'success') {
                    document.getElementById('enrolledCoursesCount').textContent = result.enrolled_courses_count;
                } else {
                    document.getElementById('enrolledCoursesCount').textContent = '0';
                }
            } else {
                document.getElementById('enrolledCoursesCount').textContent = '0';
            }
        } catch (error) {
            document.getElementById('enrolledCoursesCount').textContent = '0';
        }
    } else {
        document.getElementById('enrolledCoursesCount').textContent = '0';
    }

    // Average grade
    if (token) {
        try {
            const response = await fetch('/api/student/progress/average-score', {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                }
            });
            if (response.ok) {
                const result = await response.json();
                if (result.status === 'success') {
                    document.getElementById('averageGrade').textContent = result.average_score + '%';
                } else {
                    document.getElementById('averageGrade').textContent = '0%';
                }
            } else {
                document.getElementById('averageGrade').textContent = '0%';
            }
        } catch (error) {
            document.getElementById('averageGrade').textContent = '0%';
        }
    } else {
        document.getElementById('averageGrade').textContent = '0%';
    }
});
</script>
@endsection
