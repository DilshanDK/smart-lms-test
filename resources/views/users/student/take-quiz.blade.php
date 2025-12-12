@extends('layouts.app')

@section('title', 'Take Quiz - Smart LMS')

@section('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap');

    :root {
        --primary: #059669;
        --primary-dark: #047857;
        --surface: #ffffff;
        --foreground: #0f172a;
        --muted: #f1f5f9;
        --muted-foreground: #64748b;
        --border: #e2e8f0;
        --danger: #ef4444;
        --radius: 8px;
        --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }

    * {
        font-family: 'Poppins', sans-serif;
    }

    .quiz-container {
        max-width: 900px;
        margin: 2rem auto;
        padding: 2rem;
    }

    .quiz-header {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        color: white;
        padding: 2rem;
        border-radius: var(--radius);
        margin-bottom: 2rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .timer {
        font-size: 1.5rem;
        font-weight: 700;
        padding: 0.5rem 1rem;
        background: rgba(255, 255, 255, 0.2);
        border-radius: var(--radius);
    }

    .question-card {
        background: var(--surface);
        border: 2px solid var(--border);
        border-radius: var(--radius);
        padding: 2rem;
        margin-bottom: 1.5rem;
        box-shadow: var(--shadow-md);
    }

    .question-number {
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--primary);
        margin-bottom: 0.5rem;
    }

    .question-text {
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--foreground);
        margin-bottom: 1.5rem;
    }

    .option-item {
        display: flex;
        align-items: center;
        padding: 1rem;
        margin-bottom: 0.75rem;
        background: var(--muted);
        border: 2px solid var(--border);
        border-radius: var(--radius);
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .option-item:hover {
        border-color: var(--primary);
        background: #ecfdf5;
    }

    .option-item input[type="radio"] {
        margin-right: 1rem;
        width: 20px;
        height: 20px;
        cursor: pointer;
    }

    .option-item.selected {
        border-color: var(--primary);
        background: #ecfdf5;
    }

    .submit-btn {
        background: var(--primary);
        color: white;
        border: none;
        padding: 1rem 3rem;
        border-radius: var(--radius);
        font-size: 1.1rem;
        font-weight: 600;
        cursor: pointer;
        width: 100%;
        margin-top: 2rem;
    }

    .submit-btn:disabled {
        background: var(--muted-foreground);
        cursor: not-allowed;
    }

    /* Calculation Loading Overlay */
    .calculation-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(5, 150, 105, 0.95);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 9999;
        backdrop-filter: blur(10px);
    }

    .calculation-overlay.show {
        display: flex;
    }

    .calculation-content {
        text-align: center;
        color: white;
    }

    .calculation-spinner {
        width: 80px;
        height: 80px;
        border: 8px solid rgba(255, 255, 255, 0.3);
        border-top: 8px solid white;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin: 0 auto 2rem;
    }

    .calculation-text {
        font-size: 1.5rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
        animation: pulse 1.5s ease-in-out infinite;
    }

    .calculation-subtext {
        font-size: 1rem;
        opacity: 0.9;
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.6; }
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>
@endsection

@section('content')
<!-- Calculation Loading Overlay -->
<div class="calculation-overlay" id="calculationOverlay">
    <div class="calculation-content">
        <div class="calculation-spinner"></div>
        <div class="calculation-text">Calculating Your Score...</div>
        <div class="calculation-subtext">Please wait while we evaluate your answers</div>
    </div>
</div>

<div class="quiz-container">
    <div class="quiz-header">
        <div>
            <h1 id="quizTitle" style="margin: 0;"></h1>
            <p id="quizDescription" style="margin: 0.5rem 0 0 0; opacity: 0.9;"></p>
        </div>
        <div class="timer" id="timer">--:--</div>
    </div>

    <div id="quizContainer">
        <div style="text-align: center; padding: 4rem;">
            <div style="width: 40px; height: 40px; border: 3px solid var(--muted); border-top: 3px solid var(--primary); border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto;"></div>
        </div>
    </div>

    <button id="submitBtn" class="submit-btn" style="display: none;" onclick="submitQuiz()">Submit Assignment</button>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const API_BASE = '{{ url('') }}/api';
const assignmentId = new URLSearchParams(window.location.search).get('id');
let quizData = null;
let timerInterval = null;
let timeRemaining = 0;

document.addEventListener('DOMContentLoaded', function() {
    if (!assignmentId) {
        Swal.fire({ icon: 'error', title: 'Invalid assignment ID' });
        window.location.href = '{{ route("student.assignment-management") }}';
        return;
    }
    loadQuiz();
});

async function loadQuiz() {
    try {
        const token = localStorage.getItem('auth_token');
        const response = await fetch(`${API_BASE}/student/assignments/${assignmentId}`, {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });

        const result = await response.json();

        if (response.ok && result.status === 'success') {
            quizData = result.assignment;
            displayQuiz(quizData);
            startTimer(quizData.time_remaining);
        } else {
            Swal.fire({
                icon: 'error',
                title: result.message || 'Failed to load quiz',
                text: result.starts_at ? `Starts at: ${result.starts_at}` : ''
            }).then(() => {
                window.location.href = '{{ route("student.assignment-management") }}';
            });
        }
    } catch (error) {
        console.error('Error loading quiz:', error);
        Swal.fire({ icon: 'error', title: 'Network error' }).then(() => {
            window.location.href = '{{ route("student.assignment-management") }}';
        });
    }
}

function displayQuiz(quiz) {
    document.getElementById('quizTitle').textContent = quiz.title;
    document.getElementById('quizDescription').textContent = quiz.description;

    let html = '';
    quiz.questions.forEach((question, index) => {
        html += `
            <div class="question-card">
                <div class="question-number">Question ${index + 1} of ${quiz.total_questions}</div>
                <div class="question-text">${question.question}</div>
                <div class="options">
        `;
        question.options.forEach((option, optIndex) => {
            html += `
                <label class="option-item" onclick="selectOption(this)">
                    <input type="radio" name="question_${index}" value="${optIndex}" data-question="${index}">
                    <span>${option.text}</span>
                </label>
            `;
        });
        html += `
                </div>
            </div>
        `;
    });

    document.getElementById('quizContainer').innerHTML = html;
    document.getElementById('submitBtn').style.display = 'block';
}

function selectOption(label) {
    const input = label.querySelector('input[type="radio"]');
    const questionName = input.name;
    document.querySelectorAll(`input[name="${questionName}"]`).forEach(radio => {
        radio.closest('.option-item').classList.remove('selected');
    });
    label.classList.add('selected');
}

function startTimer(seconds) {
    timeRemaining = seconds;
    updateTimerDisplay();

    timerInterval = setInterval(() => {
        timeRemaining--;
        updateTimerDisplay();

        if (timeRemaining <= 0) {
            clearInterval(timerInterval);
            Swal.fire({ icon: 'warning', title: 'Time is up!', text: 'Auto-submitting...' });
            submitQuiz();
        }
    }, 1000);
}

function updateTimerDisplay() {
    const hours = Math.floor(timeRemaining / 3600);
    const minutes = Math.floor((timeRemaining % 3600) / 60);
    const seconds = timeRemaining % 60;
    document.getElementById('timer').textContent = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
}

async function submitQuiz() {
    clearInterval(timerInterval);

    // Collect answers
    const answers = {};
    document.querySelectorAll('input[type="radio"]:checked').forEach(radio => {
        const questionIndex = parseInt(radio.dataset.question);
        answers[questionIndex] = parseInt(radio.value);
    });

    // Confirm submission
    const confirm = await Swal.fire({
        title: 'Submit Assignment?',
        text: 'You cannot change your answers after submission.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, Submit',
        confirmButtonColor: '#059669'
    });

    if (!confirm.isConfirmed) {
        startTimer(timeRemaining);
        return;
    }

    // Show calculation overlay
    document.getElementById('calculationOverlay').classList.add('show');

    try {
        const token = localStorage.getItem('auth_token');
        const response = await fetch(`${API_BASE}/student/assignments/${assignmentId}/submit`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ answers: answers })
        });

        const result = await response.json();

        // Hide calculation overlay
        document.getElementById('calculationOverlay').classList.remove('show');

        if (response.ok && result.status === 'success') {
            Swal.fire({
                icon: 'success',
                title: 'Assignment Submitted!',
                html: `
                    <div style="text-align: center; padding: 1rem;">
                        <div style="font-size: 3rem; color: #059669; margin-bottom: 1rem;">🎉</div>
                        <h3 style="color: #059669; font-size: 2rem; margin-bottom: 1rem;">${result.score.score_percentage}%</h3>
                        <p style="font-size: 1.1rem; margin-bottom: 0.5rem;"><strong>Correct Answers:</strong> ${result.score.correct_answers} / ${result.score.total_questions}</p>
                        <p style="color: #64748b; font-size: 0.9rem;">Great job on completing the assignment!</p>
                    </div>
                `,
                confirmButtonColor: '#059669',
                confirmButtonText: 'Back to Assignments'
            }).then(() => {
                window.location.href = '{{ route("student.assignment-management") }}';
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Submission failed',
                text: result.message
            });
            startTimer(timeRemaining);
        }
    } catch (error) {
        console.error('Error submitting:', error);
        // Hide calculation overlay
        document.getElementById('calculationOverlay').classList.remove('show');
        Swal.fire({
            icon: 'error',
            title: 'Network error',
            text: 'Please check your connection and try again.'
        });
        startTimer(timeRemaining);
    }
}
</script>
@endsection

