@extends('layouts.app')

@section('title', 'My Assignments - Smart LMS')

@section('styles')
    <style>
        /* Modern Typography System - Poppins Font */
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap');

        :root {
            --primary: #059669;
            --primary-dark: #047857;
            --secondary: #64748b;
            --background: #f8fafc;
            --surface: #ffffff;
            --foreground: #0f172a;
            --muted: #f1f5f9;
            --muted-foreground: #64748b;
            --accent: #10b981;
            --border: #e2e8f0;
            --success: #22c55e;
            --warning: #f59e0b;
            --radius: 8px;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        * {
            font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        body {
            background: var(--background);
        }

        .main-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .page-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 2rem;
            border-radius: var(--radius);
            margin-bottom: 2rem;
            box-shadow: var(--shadow-md);
        }

        .page-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .page-subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
            margin: 0;
        }

        .assignments-section {
            background: var(--surface);
            border-radius: var(--radius);
            padding: 2rem;
            box-shadow: var(--shadow-sm);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--muted);
        }

        .section-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--foreground);
            margin: 0;
        }

        .assignments-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 1.5rem;
        }

        .assignment-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 1.5rem;
            transition: all 0.3s ease;
            position: relative;
        }

        .assignment-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--accent));
        }

        .assignment-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
            border-color: var(--primary);
        }

        .assignment-header {
            margin-bottom: 1rem;
        }

        .assignment-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--foreground);
            margin: 0 0 0.5rem 0;
        }

        .assignment-course {
            color: var(--primary);
            font-size: 0.875rem;
            font-weight: 500;
        }

        .assignment-meta {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
            font-size: 0.875rem;
            color: var(--muted-foreground);
            flex-wrap: wrap;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .assignment-description {
            color: var(--muted-foreground);
            line-height: 1.5;
            margin-bottom: 1rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .due-date-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .due-soon {
            background: #fef3c7;
            color: #92400e;
        }

        .due-later {
            background: #dcfce7;
            color: #166534;
        }

        .assignment-actions {
            display: flex;
            gap: 0.5rem;
        }

        .btn-start {
            flex: 1;
            background: var(--primary);
            color: white;
            border: none;
            padding: 0.75rem;
            border-radius: var(--radius);
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            text-align: center;
        }

        .btn-start:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
            color: white;
            text-decoration: none;
        }

        /* Loading States */
        .loading {
            text-align: center;
            padding: 4rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            min-height: 300px;
        }

        .spinner {
            width: 40px;
            height: 40px;
            border: 3px solid var(--muted);
            border-top: 3px solid var(--primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--muted-foreground);
        }

        .empty-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .main-container {
                padding: 1rem;
            }

            .page-header {
                padding: 1.5rem;
            }

            .page-title {
                font-size: 2rem;
            }

            .assignments-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endsection

@section('content')
    <div class="main-container">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">My Assignments</h1>
            <p class="page-subtitle">View and complete quizzes and assignments from your courses</p>
        </div>

        <!-- Assignments Section -->
        <div class="assignments-section">
            <div class="section-header">
                <h2 class="section-title">Available Assignments</h2>
            </div>

            <div id="assignmentsContainer">
                <div class="loading">
                    <div class="spinner"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        // API Configuration
        const API_BASE = '{{ url('') }}/api';

        // DOM Elements
        const assignmentsContainer = document.getElementById('assignmentsContainer');

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            loadAssignments();
        });

        // Load assignments
        async function loadAssignments() {
            try {
                const token = localStorage.getItem('auth_token');
                if (!token) {
                    showError('Authentication required. Please login again.');
                    return;
                }

                const response = await fetch(`${API_BASE}/student/assignments`, {
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Accept': 'application/json'
                    }
                });

                const result = await response.json();

                if (response.ok && result.status === 'success') {
                    displayAssignments(result.assignments);
                } else {
                    showError(result.message || 'Failed to load assignments');
                }
            } catch (error) {
                console.error('Network error:', error);
                showError('Network error. Please check your connection.');
            }
        }

        // Display assignments
        function displayAssignments(assignments) {
            if (!assignments || assignments.length === 0) {
                assignmentsContainer.innerHTML = `
                    <div class="empty-state">
                        <div class="empty-icon">📝</div>
                        <h3>No Assignments Available</h3>
                        <p>There are no published assignments for your enrolled courses yet.</p>
                    </div>
                `;
                return;
            }

            let html = '<div class="assignments-grid">';

            assignments.forEach(assignment => {
                const dueDate = assignment.due_date ? new Date(assignment.due_date) : null;
                const now = new Date();
                let dueDateBadge = '';

                if (dueDate) {
                    const daysUntilDue = Math.ceil((dueDate - now) / (1000 * 60 * 60 * 24));
                    const dueDateClass = daysUntilDue <= 3 ? 'due-soon' : 'due-later';
                    dueDateBadge = `<div class="due-date-badge ${dueDateClass}">
                        <i class="fas fa-clock"></i> Due: ${dueDate.toLocaleDateString()}
                    </div>`;
                }

                // Check if already submitted
                let actionButton = '';
                if (assignment.has_submitted) {
                    actionButton = `
                        <button class="btn-start" disabled style="background: #6b7280; cursor: not-allowed; opacity: 0.6;">
                            <i class="fas fa-check-circle"></i> Submitted (${assignment.submission_score}%)
                        </button>
                    `;
                } else {
                    actionButton = `
                        <button class="btn-start" onclick="startAssignment('${assignment.id}')">
                            <i class="fas fa-play"></i> Start Assignment
                        </button>
                    `;
                }

                html += `
                    <div class="assignment-card">
                        <div class="assignment-header">
                            <h3 class="assignment-title">${assignment.title}</h3>
                            <div class="assignment-course">
                                <i class="fas fa-book"></i> ${assignment.course_name}
                            </div>
                        </div>

                        <div class="assignment-meta">
                            <div class="meta-item">
                                <i class="fas fa-chalkboard-teacher"></i>
                                ${assignment.lecturer_name}
                            </div>
                            <div class="meta-item">
                                <i class="fas fa-question-circle"></i>
                                ${assignment.total_questions} questions
                            </div>
                        </div>

                        ${assignment.description ? `
                            <p class="assignment-description">${assignment.description}</p>
                        ` : ''}

                        ${dueDateBadge}

                        <div class="assignment-actions">
                            ${actionButton}
                        </div>
                    </div>
                `;
            });

            html += '</div>';
            assignmentsContainer.innerHTML = html;
        }

        // Start assignment (placeholder)
        function startAssignment(assignmentId) {
            // Navigate to quiz taking page
            window.location.href = `{{ route('student.take-quiz') }}?id=${assignmentId}`;
        }

        // Show error
        function showError(message) {
            assignmentsContainer.innerHTML = `
                <div class="empty-state">
                    <div class="empty-icon" style="color: #ef4444;">⚠️</div>
                    <h3>Error Loading Assignments</h3>
                    <p>${message}</p>
                    <button onclick="loadAssignments()" class="btn-start" style="max-width: 200px; margin: 1rem auto 0;">
                        <i class="fas fa-refresh"></i> Try Again
                    </button>
                </div>
            `;
        }
    </script>
@endsection
