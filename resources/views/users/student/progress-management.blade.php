@extends('layouts.app')

@section('title', 'My Progress - Smart LMS')

@section('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap');

    :root {
        --primary: #059669;
        --primary-dark: #047857;
        --primary-light: #10b981;
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
        --danger: #ef4444;
        --info: #3b82f6;
        --radius: 12px;
        --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.05);
        --shadow-md: 0 4px 12px rgba(0, 0, 0, 0.08);
        --shadow-lg: 0 10px 30px rgba(0, 0, 0, 0.12);
        --shadow-xl: 0 20px 40px rgba(0, 0, 0, 0.15);
    }

    * {
        font-family: 'Poppins', sans-serif;
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 50%, #d1fae5 100%);
        min-height: 100vh;
    }

    .dashboard-container {
        max-width: 1600px;
        margin: 0 auto;
        padding: 2rem;
    }

    /* Hero Header Section */
    .hero-header {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        color: white;
        padding: 3rem 2.5rem;
        border-radius: var(--radius);
        margin-bottom: 2.5rem;
        box-shadow: var(--shadow-xl);
        position: relative;
        overflow: hidden;
    }

    .hero-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 400px;
        height: 400px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
        filter: blur(60px);
    }

    .hero-content {
        position: relative;
        z-index: 1;
    }

    .hero-title {
        font-size: 3rem;
        font-weight: 800;
        margin-bottom: 0.75rem;
        letter-spacing: -0.5px;
    }

    .hero-subtitle {
        font-size: 1.25rem;
        opacity: 0.95;
        font-weight: 400;
    }

    /* Stats Cards Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1.75rem;
        margin-bottom: 2.5rem;
    }

    .stat-card {
        background: var(--surface);
        padding: 2rem;
        border-radius: var(--radius);
        box-shadow: var(--shadow-md);
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
        border: 1px solid var(--border);
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 4px;
        background: linear-gradient(90deg, var(--primary), var(--primary-light));
    }

    .stat-card:hover {
        transform: translateY(-8px);
        box-shadow: var(--shadow-xl);
        border-color: var(--primary);
    }

    .stat-icon {
        width: 56px;
        height: 56px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
        margin-bottom: 1.25rem;
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        color: white;
        box-shadow: 0 4px 12px rgba(5, 150, 105, 0.25);
    }

    .stat-value {
        font-size: 3rem;
        font-weight: 800;
        color: var(--foreground);
        margin-bottom: 0.5rem;
        line-height: 1;
    }

    .stat-label {
        color: var(--muted-foreground);
        font-size: 0.95rem;
        font-weight: 500;
        letter-spacing: 0.3px;
    }

    /* Charts Row */
    .charts-row {
        display: grid;
        grid-template-columns: 1.2fr 1fr;
        gap: 2rem;
        margin-bottom: 2.5rem;
    }

    .chart-card {
        background: var(--surface);
        padding: 2.5rem;
        border-radius: var(--radius);
        box-shadow: var(--shadow-md);
        border: 1px solid var(--border);
        transition: all 0.3s ease;
    }

    .chart-card:hover {
        box-shadow: var(--shadow-lg);
    }

    .chart-header {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 2rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid var(--muted);
    }

    .chart-icon {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        color: white;
    }

    .chart-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--foreground);
    }

    .chart-canvas {
        position: relative;
        height: 320px;
    }

    /* Course Progress Section */
    .progress-section {
        background: var(--surface);
        padding: 2.5rem;
        border-radius: var(--radius);
        box-shadow: var(--shadow-md);
        border: 1px solid var(--border);
        margin-bottom: 2.5rem;
    }

    .section-header {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 2rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid var(--muted);
    }

    .section-icon {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        color: white;
    }

    .section-title {
        font-size: 1.75rem;
        font-weight: 700;
        color: var(--foreground);
    }

    /* Course Card */
    .course-card {
        background: var(--muted);
        border: 2px solid var(--border);
        border-radius: var(--radius);
        padding: 2rem;
        margin-bottom: 1.5rem;
        transition: all 0.3s ease;
    }

    .course-card:hover {
        box-shadow: var(--shadow-lg);
        border-color: var(--primary);
        transform: translateX(8px);
    }

    .course-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }

    .course-name {
        font-size: 1.35rem;
        font-weight: 700;
        color: var(--foreground);
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .course-icon {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: white;
        color: var(--primary);
        font-size: 1.25rem;
    }

    .score-badge {
        padding: 0.75rem 1.5rem;
        border-radius: 25px;
        font-weight: 700;
        font-size: 1.1rem;
        box-shadow: var(--shadow-sm);
    }

    .score-excellent {
        background: linear-gradient(135deg, #22c55e, #16a34a);
        color: white;
    }
    .score-good {
        background: linear-gradient(135deg, #3b82f6, #2563eb);
        color: white;
    }
    .score-average {
        background: linear-gradient(135deg, #f59e0b, #d97706);
        color: white;
    }
    .score-poor {
        background: linear-gradient(135deg, #ef4444, #dc2626);
        color: white;
    }

    .course-stats {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .course-stat {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 1rem;
        background: white;
        border-radius: 10px;
        box-shadow: var(--shadow-sm);
    }

    .course-stat-icon {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        background: linear-gradient(135deg, var(--primary), var(--primary-light));
        color: white;
    }

    .course-stat-value {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--foreground);
    }

    .course-stat-label {
        font-size: 0.8rem;
        color: var(--muted-foreground);
        font-weight: 500;
    }

    /* Progress Bar */
    .progress-bar-container {
        background: white;
        height: 16px;
        border-radius: 10px;
        overflow: hidden;
        margin-bottom: 0.75rem;
        box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.06);
    }

    .progress-bar {
        height: 100%;
        background: linear-gradient(90deg, var(--primary) 0%, var(--primary-light) 100%);
        border-radius: 10px;
        transition: width 0.8s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }

    .progress-bar::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
        animation: shimmer 2s infinite;
    }

    @keyframes shimmer {
        0% { transform: translateX(-100%); }
        100% { transform: translateX(100%); }
    }

    .progress-text {
        text-align: right;
        font-size: 0.85rem;
        color: var(--muted-foreground);
        font-weight: 600;
    }

    /* Recent Submissions */
    .submission-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1.5rem;
        background: var(--muted);
        border: 2px solid var(--border);
        border-radius: var(--radius);
        margin-bottom: 1rem;
        transition: all 0.3s ease;
    }

    .submission-item:hover {
        background: white;
        box-shadow: var(--shadow-md);
        border-color: var(--primary);
        transform: translateX(8px);
    }

    .submission-info h5 {
        font-size: 1.15rem;
        font-weight: 600;
        color: var(--foreground);
        margin-bottom: 0.5rem;
    }

    .submission-meta {
        display: flex;
        align-items: center;
        gap: 1.5rem;
        font-size: 0.9rem;
        color: var(--muted-foreground);
    }

    .submission-score {
        text-align: right;
    }

    .submission-correct {
        margin-top: 0.5rem;
        font-size: 0.85rem;
        color: var(--muted-foreground);
        font-weight: 500;
    }

    /* Loading & Empty States */
    .loading-container {
        text-align: center;
        padding: 6rem 2rem;
    }

    .loading-spinner {
        width: 64px;
        height: 64px;
        border: 4px solid var(--muted);
        border-top: 4px solid var(--primary);
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin: 0 auto 1.5rem;
    }

    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        color: var(--muted-foreground);
    }

    .empty-icon {
        font-size: 5rem;
        margin-bottom: 1.5rem;
        opacity: 0.4;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* Responsive Design */
    @media (max-width: 1200px) {
        .charts-row {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .dashboard-container {
            padding: 1rem;
        }

        .hero-header {
            padding: 2rem 1.5rem;
        }

        .hero-title {
            font-size: 2rem;
        }

        .stats-grid {
            grid-template-columns: 1fr;
        }

        .course-stats {
            grid-template-columns: 1fr;
        }

        .submission-item {
            flex-direction: column;
            align-items: flex-start;
            gap: 1rem;
        }
    }
</style>
@endsection

@section('content')
<div class="dashboard-container">
    <!-- Hero Header -->
    <div class="hero-header">
        <div class="hero-content">
            <h1 class="hero-title">My Learning Progress</h1>
            <p class="hero-subtitle">Track your academic journey and celebrate your achievements</p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-grid" id="statsGrid">
        <div class="loading-container">
            <div class="loading-spinner"></div>
            <p style="color: var(--muted-foreground); font-weight: 500;">Loading your progress...</p>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="charts-row">
        <!-- Performance Trend -->
        <div class="chart-card">
            <div class="chart-header">
                <div class="chart-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h3 class="chart-title">Performance Trend</h3>
            </div>
            <div class="chart-canvas">
                <canvas id="performanceChart"></canvas>
            </div>
        </div>

        <!-- Score Distribution -->
        <div class="chart-card">
            <div class="chart-header">
                <div class="chart-icon">
                    <i class="fas fa-chart-pie"></i>
                </div>
                <h3 class="chart-title">Score Distribution</h3>
            </div>
            <div class="chart-canvas">
                <canvas id="scoreChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Course Progress -->
    <div class="progress-section">
        <div class="section-header">
            <div class="section-icon">
                <i class="fas fa-graduation-cap"></i>
            </div>
            <h3 class="section-title">Progress by Course</h3>
        </div>
        <div id="courseProgress"></div>
    </div>

    <!-- Recent Submissions -->
    <div class="progress-section">
        <div class="section-header">
            <div class="section-icon">
                <i class="fas fa-history"></i>
            </div>
            <h3 class="section-title">Recent Submissions</h3>
        </div>
        <div id="recentSubmissions"></div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const API_BASE = '{{ url('') }}/api';
let performanceChart = null;
let scoreChart = null;

document.addEventListener('DOMContentLoaded', function() {
    loadProgress();
});

async function loadProgress() {
    try {
        const token = localStorage.getItem('auth_token');
        const response = await fetch(`${API_BASE}/student/progress`, {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });

        const result = await response.json();

        if (response.ok && result.status === 'success') {
            displayStats(result.overall_stats);
            displayCourseProgress(result.course_progress);
            displayRecentSubmissions(result.recent_submissions);
            createPerformanceChart(result.performance_trend);
            createScoreChart(result.recent_submissions);
        } else {
            console.error('Failed to load progress:', result);
        }
    } catch (error) {
        console.error('Error loading progress:', error);
    }
}

function displayStats(stats) {
    const statsGrid = document.getElementById('statsGrid');
    statsGrid.innerHTML = `
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-clipboard-list"></i></div>
            <div class="stat-value">${stats.total_assignments}</div>
            <div class="stat-label">Total Assignments</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
            <div class="stat-value">${stats.completed_assignments}</div>
            <div class="stat-label">Completed</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-clock"></i></div>
            <div class="stat-value">${stats.pending_assignments}</div>
            <div class="stat-label">Pending</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-star"></i></div>
            <div class="stat-value">${stats.average_score}%</div>
            <div class="stat-label">Average Score</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-chart-line"></i></div>
            <div class="stat-value">${stats.completion_rate}%</div>
            <div class="stat-label">Completion Rate</div>
        </div>
    `;
}

function displayCourseProgress(courses) {
    const container = document.getElementById('courseProgress');
    if (courses.length === 0) {
        container.innerHTML = '<div class="empty-state"><div class="empty-icon">📚</div><h3>No course data available</h3><p>Enroll in courses to see your progress</p></div>';
        return;
    }

    container.innerHTML = courses.map(course => `
        <div class="course-card">
            <div class="course-header">
                <div class="course-name">
                    <div class="course-icon"><i class="fas fa-book"></i></div>
                    ${course.course_name}
                </div>
                <span class="${getScoreBadgeClass(course.average_score)} score-badge">${course.average_score}%</span>
            </div>
            <div class="course-stats">
                <div class="course-stat">
                    <div class="course-stat-icon"><i class="fas fa-tasks"></i></div>
                    <div>
                        <div class="course-stat-value">${course.total_assignments}</div>
                        <div class="course-stat-label">Total</div>
                    </div>
                </div>
                <div class="course-stat">
                    <div class="course-stat-icon"><i class="fas fa-check"></i></div>
                    <div>
                        <div class="course-stat-value">${course.completed}</div>
                        <div class="course-stat-label">Completed</div>
                    </div>
                </div>
                <div class="course-stat">
                    <div class="course-stat-icon"><i class="fas fa-hourglass-half"></i></div>
                    <div>
                        <div class="course-stat-value">${course.pending}</div>
                        <div class="course-stat-label">Pending</div>
                    </div>
                </div>
            </div>
            <div class="progress-bar-container">
                <div class="progress-bar" style="width: ${course.completion_rate}%"></div>
            </div>
            <div class="progress-text">${course.completion_rate}% Complete</div>
        </div>
    `).join('');
}

function displayRecentSubmissions(submissions) {
    const container = document.getElementById('recentSubmissions');
    if (submissions.length === 0) {
        container.innerHTML = '<div class="empty-state"><div class="empty-icon">📝</div><h3>No submissions yet</h3><p>Complete assignments to see your submissions here</p></div>';
        return;
    }

    container.innerHTML = submissions.map(sub => `
        <div class="submission-item">
            <div class="submission-info">
                <h5>${sub.assignment_title}</h5>
                <div class="submission-meta">
                    <span><i class="fas fa-calendar me-1"></i>${new Date(sub.submitted_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}</span>
                    <span><i class="fas fa-clock me-1"></i>${new Date(sub.submitted_at).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })}</span>
                </div>
            </div>
            <div class="submission-score">
                <div class="${getScoreBadgeClass(sub.score_percentage)} score-badge">${sub.score_percentage}%</div>
                <p class="submission-correct">${sub.correct_answers}/${sub.total_questions} Correct</p>
            </div>
        </div>
    `).join('');
}

function createPerformanceChart(trend) {
    if (trend.length === 0) return;

    const ctx = document.getElementById('performanceChart');
    if (performanceChart) performanceChart.destroy();

    performanceChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: trend.map(t => new Date(t.date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' })),
            datasets: [{
                label: 'Score %',
                data: trend.map(t => t.score),
                borderColor: '#059669',
                backgroundColor: 'rgba(5, 150, 105, 0.1)',
                borderWidth: 3,
                tension: 0.4,
                fill: true,
                pointRadius: 6,
                pointHoverRadius: 8,
                pointBackgroundColor: '#059669',
                pointBorderColor: '#fff',
                pointBorderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(15, 23, 42, 0.95)',
                    padding: 12,
                    titleFont: { size: 14, weight: 'bold' },
                    bodyFont: { size: 13 },
                    cornerRadius: 8
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    ticks: {
                        callback: value => value + '%',
                        font: { size: 12 }
                    },
                    grid: { color: 'rgba(0, 0, 0, 0.05)' }
                },
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 12 } }
                }
            }
        }
    });
}

function createScoreChart(submissions) {
    if (submissions.length === 0) return;

    const scores = submissions.map(s => s.score_percentage);
    const excellent = scores.filter(s => s >= 90).length;
    const good = scores.filter(s => s >= 70 && s < 90).length;
    const average = scores.filter(s => s >= 50 && s < 70).length;
    const poor = scores.filter(s => s < 50).length;

    const ctx = document.getElementById('scoreChart');
    if (scoreChart) scoreChart.destroy();

    scoreChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Excellent (90-100%)', 'Good (70-89%)', 'Average (50-69%)', 'Poor (<50%)'],
            datasets: [{
                data: [excellent, good, average, poor],
                backgroundColor: ['#22c55e', '#3b82f6', '#f59e0b', '#ef4444'],
                borderWidth: 3,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 15,
                        font: { size: 12, weight: '500' }
                    }
                }
            }
        }
    });
}

function getScoreBadgeClass(score) {
    if (score >= 90) return 'score-excellent';
    if (score >= 70) return 'score-good';
    if (score >= 50) return 'score-average';
    return 'score-poor';
}
</script>
@endsection
