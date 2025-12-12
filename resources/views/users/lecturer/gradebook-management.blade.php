@extends('layouts.app')

@section('title', 'Gradebook - Smart LMS')

@section('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap');

    :root {
        --primary: #7c3aed;
        --primary-dark: #6d28d9;
        --background: #f8fafc;
        --surface: #ffffff;
        --foreground: #0f172a;
        --muted: #f1f5f9;
        --muted-foreground: #64748b;
        --border: #e2e8f0;
        --success: #22c55e;
        --warning: #f59e0b;
        --danger: #ef4444;
        --radius: 8px;
        --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }

    * { font-family: 'Poppins', sans-serif; }
    body { background: var(--background); }

    .main-container {
        max-width: 1400px;
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

    .student-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 1.5rem;
        margin-bottom: 1rem;
        transition: all 0.3s ease;
    }

    .student-card:hover {
        box-shadow: var(--shadow-md);
        border-color: var(--primary);
    }

    .score-badge {
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 700;
        font-size: 1.1rem;
    }

    .score-excellent { background: linear-gradient(135deg, #22c55e, #16a34a); color: white; }
    .score-good { background: linear-gradient(135deg, #3b82f6, #2563eb); color: white; }
    .score-average { background: linear-gradient(135deg, #f59e0b, #d97706); color: white; }
    .score-poor { background: linear-gradient(135deg, #ef4444, #dc2626); color: white; }

    .submission-item {
        padding: 0.75rem;
        background: var(--muted);
        border-radius: var(--radius);
        margin-top: 0.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>
@endsection

@section('content')
<div class="main-container">
    <div class="page-header">
        <h1 style="font-size: 2.5rem; font-weight: 700; margin-bottom: 0.5rem;">Gradebook</h1>
        <p style="font-size: 1.1rem; opacity: 0.9; margin: 0;">View student performance and grades</p>
    </div>

    <div id="gradebookContainer">
        <div style="text-align: center; padding: 4rem;">
            <div style="width: 40px; height: 40px; border: 3px solid var(--muted); border-top: 3px solid var(--primary); border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto;"></div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
const API_BASE = '{{ url('') }}/api';

document.addEventListener('DOMContentLoaded', function() {
    loadGradebook();
});

async function loadGradebook() {
    try {
        const token = localStorage.getItem('auth_token');
        const response = await fetch(`${API_BASE}/lecturer/gradebook`, {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });

        const result = await response.json();

        if (response.ok && result.status === 'success') {
            displayGradebook(result.students);
        }
    } catch (error) {
        console.error('Error loading gradebook:', error);
    }
}

function displayGradebook(students) {
    const container = document.getElementById('gradebookContainer');

    if (students.length === 0) {
        container.innerHTML = '<div style="text-align: center; padding: 4rem; color: var(--muted-foreground);"><i class="fas fa-users" style="font-size: 4rem; margin-bottom: 1rem;"></i><h3>No student submissions yet</h3></div>';
        return;
    }

    let html = '<div style="background: var(--surface); padding: 2rem; border-radius: var(--radius);">';

    students.forEach(student => {
        html += `
            <div class="student-card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                    <div>
                        <h3 style="margin: 0; color: var(--foreground);">${student.student_name}</h3>
                        <p style="margin: 0.25rem 0 0 0; color: var(--muted-foreground); font-size: 0.875rem;">${student.student_email}</p>
                    </div>
                    <div class="${getScoreBadgeClass(student.average_score)} score-badge">
                        ${student.average_score}%
                    </div>
                </div>
                <div style="color: var(--muted-foreground); font-size: 0.875rem; margin-bottom: 0.5rem;">
                    <i class="fas fa-clipboard-check me-1"></i>
                    ${student.total_assignments} Assignment${student.total_assignments !== 1 ? 's' : ''} Submitted
                </div>
                <details>
                    <summary style="cursor: pointer; color: var(--primary); font-weight: 600;">View Submissions</summary>
                    <div style="margin-top: 1rem;">
        `;

        student.submissions.forEach(sub => {
            html += `
                <div class="submission-item">
                    <div>
                        <div style="font-weight: 600;">${sub.assignment_title}</div>
                        <div style="font-size: 0.75rem; color: var(--muted-foreground);">
                            ${new Date(sub.submitted_at).toLocaleDateString()} - ${sub.correct_answers}/${sub.total_questions} correct
                        </div>
                    </div>
                    <div class="${getScoreBadgeClass(sub.score_percentage)} score-badge" style="font-size: 0.9rem; padding: 0.25rem 0.75rem;">
                        ${sub.score_percentage}%
                    </div>
                </div>
            `;
        });

        html += `
                    </div>
                </details>
            </div>
        `;
    });

    html += '</div>';
    container.innerHTML = html;
}

function getScoreBadgeClass(score) {
    if (score >= 90) return 'score-excellent';
    if (score >= 70) return 'score-good';
    if (score >= 50) return 'score-average';
    return 'score-poor';
}
</script>
@endsection
