@extends('layouts.app')

@section('title', 'Complete Your Profile - Smart LMS')

@section('styles')
<style>
/* Modern Design System */
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

:root {
    --primary: #6366f1;
    --primary-hover: #5856eb;
    --primary-light: #e0e7ff;
    --secondary: #f59e0b;
    --secondary-hover: #d97706;
    --secondary-light: #fef3c7;
    --success: #10b981;
    --success-light: #d1fae5;
    --white: #ffffff;
    --gray-50: #f9fafb;
    --gray-100: #f3f4f6;
    --gray-200: #e5e7eb;
    --gray-300: #d1d5db;
    --gray-400: #9ca3af;
    --gray-500: #6b7280;
    --gray-600: #4b5563;
    --gray-700: #374151;
    --gray-800: #1f2937;
    --gray-900: #111827;
    --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
    --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
    --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
    --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
}

* {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

body {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
    font-weight: 400;
    line-height: 1.6;
}

.main-container {
    padding: 2rem 0;
    min-height: 100vh;
    display: flex;
    align-items: center;
}

/* Progress Indicator */
.progress-header {
    background: var(--white);
    border-radius: 16px 16px 0 0;
    padding: 2rem;
    border-bottom: 1px solid var(--gray-200);
}

.progress-steps {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.step {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.step-circle {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 0.875rem;
    transition: all 0.3s ease;
}

.step-circle.active {
    background: var(--primary);
    color: var(--white);
    box-shadow: 0 0 0 4px var(--primary-light);
}

.step-circle.completed {
    background: var(--success);
    color: var(--white);
}

.step-circle.pending {
    background: var(--gray-200);
    color: var(--gray-500);
}

.step-line {
    width: 3rem;
    height: 2px;
    background: var(--gray-200);
    transition: all 0.3s ease;
}

.step-line.completed {
    background: var(--success);
}

.progress-title {
    text-align: center;
    color: var(--gray-800);
    font-size: 1.5rem;
    font-weight: 700;
    margin: 0;
}

.progress-subtitle {
    text-align: center;
    color: var(--gray-600);
    font-size: 1rem;
    margin: 0.5rem 0 0 0;
}

/* Main Card */
.registration-card {
    background: var(--white);
    border-radius: 16px;
    box-shadow: var(--shadow-xl);
    overflow: hidden;
    animation: slideUp 0.6s ease-out;
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(2rem);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Role Selection */
.role-selection {
    padding: 3rem 2rem;
}

.section-title {
    text-align: center;
    font-size: 2rem;
    font-weight: 700;
    color: var(--gray-800);
    margin-bottom: 0.5rem;
}

.section-subtitle {
    text-align: center;
    color: var(--gray-600);
    font-size: 1.125rem;
    margin-bottom: 3rem;
}

.role-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1.5rem;
    max-width: 600px;
    margin: 0 auto;
}

.role-card {
    position: relative;
    background: var(--white);
    border: 2px solid var(--gray-200);
    border-radius: 16px;
    padding: 2rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    overflow: hidden;
}

.role-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, var(--primary), var(--secondary));
    transform: scaleX(0);
    transition: transform 0.3s ease;
}

.role-card:hover {
    border-color: var(--primary);
    transform: translateY(-4px);
    box-shadow: var(--shadow-lg);
}

.role-card:hover::before {
    transform: scaleX(1);
}

.role-icon {
    width: 4rem;
    height: 4rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem;
    font-size: 1.5rem;
    transition: all 0.3s ease;
}

.role-card:hover .role-icon {
    transform: scale(1.1);
}

.student-icon {
    background: var(--primary-light);
    color: var(--primary);
}

.lecturer-icon {
    background: var(--secondary-light);
    color: var(--secondary);
}

.role-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--gray-800);
    margin-bottom: 0.5rem;
}

.role-description {
    color: var(--gray-600);
    font-size: 0.875rem;
    line-height: 1.5;
    margin-bottom: 1rem;
}

.role-features {
    list-style: none;
    padding: 0;
    margin: 0;
}

.role-features li {
    color: var(--gray-500);
    font-size: 0.75rem;
    padding: 0.25rem 0;
    position: relative;
    padding-left: 1rem;
}

.role-features li::before {
    content: '✓';
    position: absolute;
    left: 0;
    color: var(--success);
    font-weight: 600;
}

/* Form Sections */
.form-container {
    padding: 2rem;
    background: var(--gray-50);
}

.form-header {
    display: flex;
    justify-content: between;
    align-items: center;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid var(--gray-200);
}

.back-button {
    background: var(--gray-100);
    border: 1px solid var(--gray-300);
    color: var(--gray-700);
    padding: 0.5rem 1rem;
    border-radius: 8px;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.back-button:hover {
    background: var(--gray-200);
    color: var(--gray-800);
    text-decoration: none;
}

.form-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--gray-800);
    margin: 0;
}

.form-section {
    background: var(--white);
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    border: 1px solid var(--gray-200);
    box-shadow: var(--shadow-sm);
}

.section-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 1.5rem;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid var(--gray-200);
}

.section-icon {
    width: 2rem;
    height: 2rem;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.875rem;
}

.personal-icon {
    background: var(--primary-light);
    color: var(--primary);
}

.academic-icon {
    background: var(--secondary-light);
    color: var(--secondary);
}

.contact-icon {
    background: var(--success-light);
    color: var(--success);
}

.section-title-small {
    font-size: 1rem;
    font-weight: 600;
    color: var(--gray-800);
    margin: 0;
}

/* Form Elements */
.form-group {
    margin-bottom: 1.5rem;
}

.form-label {
    display: block;
    font-size: 0.875rem;
    font-weight: 500;
    color: var(--gray-700);
    margin-bottom: 0.5rem;
}

.required {
    color: #ef4444;
}

.form-control {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid var(--gray-300);
    border-radius: 8px;
    font-size: 1rem;
    transition: all 0.2s ease;
    background: var(--white);
}

.form-control:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px var(--primary-light);
}

.form-control:invalid {
    border-color: #ef4444;
}

.form-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

/* Submit Button */
.submit-section {
    padding: 2rem;
    background: var(--white);
    border-top: 1px solid var(--gray-200);
}

.submit-button {
    width: 100%;
    padding: 1rem 2rem;
    background: linear-gradient(135deg, var(--primary), var(--primary-hover));
    color: var(--white);
    border: none;
    border-radius: 12px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.submit-button:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
}

.submit-button:disabled {
    background: var(--gray-300);
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

.button-text {
    position: relative;
    z-index: 1;
}

.submit-button::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: left 0.5s ease;
}

.submit-button:hover::before {
    left: 100%;
}

/* Loading State */
.loading-spinner {
    display: inline-block;
    width: 1rem;
    height: 1rem;
    border: 2px solid rgba(255,255,255,0.3);
    border-radius: 50%;
    border-top-color: white;
    animation: spin 1s ease-in-out infinite;
    margin-right: 0.5rem;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Messages */
.message {
    padding: 1rem;
    border-radius: 8px;
    margin: 1rem 0;
    font-weight: 500;
    animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateX(-1rem);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

.message.success {
    background: var(--success-light);
    color: #065f46;
    border: 1px solid var(--success);
}

.message.error {
    background: #fef2f2;
    color: #991b1b;
    border: 1px solid #ef4444;
}

/* Success/Error Popup Styles */
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
    animation: popupSlideIn 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
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
    animation: iconBounce 0.8s ease-out 0.3s;
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
    margin: 0 0 1.5rem 0;
    line-height: 1.5;
}

.popup-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(5px);
    z-index: 9998;
    animation: overlayFadeIn 0.3s ease-out;
}

@keyframes popupSlideIn {
    from {
        opacity: 0;
        transform: translate(-50%, -60%) scale(0.8);
    }
    to {
        opacity: 1;
        transform: translate(-50%, -50%) scale(1);
    }
}

@keyframes iconBounce {
    0%, 20%, 50%, 80%, 100% {
        transform: translateY(0);
    }
    40% {
        transform: translateY(-10px);
    }
    60% {
        transform: translateY(-5px);
    }
}

@keyframes overlayFadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

/* Responsive Design */
@media (max-width: 768px) {
    .main-container {
        padding: 1rem 0;
    }

    .progress-header {
        padding: 1.5rem;
    }

    .progress-steps {
        gap: 0.5rem;
    }

    .step-line {
        width: 2rem;
    }

    .role-selection {
        padding: 2rem 1rem;
    }

    .role-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }

    .role-card {
        padding: 1.5rem;
    }

    .form-container {
        padding: 1rem;
    }

    .form-row {
        grid-template-columns: 1fr;
    }

    .submit-section {
        padding: 1rem;
    }
}

@media (max-width: 480px) {
    .progress-title {
        font-size: 1.25rem;
    }

    .section-title {
        font-size: 1.5rem;
    }

    .role-icon {
        width: 3rem;
        height: 3rem;
        font-size: 1.25rem;
    }
}
</style>
@endsection

@section('content')
<div id="roleApp" class="main-container">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-xl-7">
                <div class="registration-card">
                    <!-- Progress Header -->
                    <div class="progress-header">
                        <div class="progress-steps">
                            <div class="step">
                                <div class="step-circle completed">
                                    <i class="fas fa-check"></i>
                                </div>
                            </div>
                            <div class="step-line completed"></div>
                            <div class="step">
                                <div class="step-circle" :class="selectedRole ? 'active' : 'pending'">
                                    2
                                </div>
                            </div>
                            <div class="step-line" :class="selectedRole ? 'completed' : ''"></div>
                            <div class="step">
                                <div class="step-circle pending">
                                    3
                                </div>
                            </div>
                        </div>
                        <h1 class="progress-title">Complete Your Profile</h1>
                        <p class="progress-subtitle">
                            <span v-if="!selectedRole">Choose your role to continue</span>
                            <span v-else-if="selectedRole === 'student'">Student Registration</span>
                            <span v-else>Lecturer Registration</span>
                        </p>
                    </div>

                    <!-- Role Selection -->
                    <div v-if="!selectedRole" class="role-selection">
                        <h2 class="section-title">I am a...</h2>
                        <p class="section-subtitle">Select the option that best describes you</p>

                        <div class="role-grid">
                            <div class="role-card" @click="selectRole('student')">
                                <div class="role-icon student-icon">
                                    <i class="fas fa-graduation-cap"></i>
                                </div>
                                <h3 class="role-title">Student</h3>
                                <p class="role-description">
                                    Access courses, track progress, and learn from expert instructors
                                </p>
                                <ul class="role-features">
                                    <li>Join courses and modules</li>
                                    <li>Track learning progress</li>
                                    <li>Submit assignments</li>
                                    <li>Access study materials</li>
                                </ul>
                            </div>

                            <div class="role-card" @click="selectRole('lecturer')">
                                <div class="role-icon lecturer-icon">
                                    <i class="fas fa-chalkboard-teacher"></i>
                                </div>
                                <h3 class="role-title">Lecturer</h3>
                                <p class="role-description">
                                    Create courses, manage students, and share your expertise
                                </p>
                                <ul class="role-features">
                                    <li>Create and manage courses</li>
                                    <li>Upload learning materials</li>
                                    <li>Grade assignments</li>
                                    <li>Monitor student progress</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Student Registration Form -->
                    <div v-if="selectedRole === 'student'">
                        <div class="form-header">
                            <button class="back-button" @click="goBack">
                                <i class="fas fa-arrow-left"></i>
                                Back to Role Selection
                            </button>
                            <h2 class="form-title">Student Registration</h2>
                        </div>

                        <div class="form-container">
                            <form @submit.prevent="submitStudentForm">
                                <!-- Personal Information -->
                                <div class="form-section">
                                    <div class="section-header">
                                        <div class="section-icon personal-icon">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <h3 class="section-title-small">Personal Information</h3>
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label">Full Name <span class="required">*</span></label>
                                            <input type="text" class="form-control" v-model="studentForm.name" required>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Date of Birth <span class="required">*</span></label>
                                            <input type="date" class="form-control" v-model="studentForm.dob" required>
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label">NIC Number <span class="required">*</span></label>
                                            <input type="text" class="form-control" v-model="studentForm.nic" required>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Gender <span class="required">*</span></label>
                                            <select class="form-control" v-model="studentForm.gender" required>
                                                <option value="">Select Gender</option>
                                                <option value="male">Male</option>
                                                <option value="female">Female</option>
                                                <option value="other">Other</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Contact Information -->
                                <div class="form-section">
                                    <div class="section-header">
                                        <div class="section-icon contact-icon">
                                            <i class="fas fa-phone"></i>
                                        </div>
                                        <h3 class="section-title-small">Contact Information</h3>
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label">Phone Number <span class="required">*</span></label>
                                            <input type="tel" class="form-control" v-model="studentForm.phoneNo" required>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Address <span class="required">*</span></label>
                                            <textarea class="form-control" v-model="studentForm.address" rows="3" required></textarea>
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label">Emergency Contact Relation <span class="required">*</span></label>
                                            <input type="text" class="form-control" v-model="studentForm.emergency_relation" required>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Emergency Contact Number <span class="required">*</span></label>
                                            <input type="tel" class="form-control" v-model="studentForm.emergency_contact" required>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <div class="submit-section">
                            <button type="button" class="submit-button" :disabled="loading" @click="submitStudentForm">
                                <span v-if="loading" class="loading-spinner"></span>
                                <span class="button-text">
                                    <span v-if="loading">Completing Registration...</span>
                                    <span v-else>Complete Student Registration</span>
                                </span>
                            </button>
                        </div>
                    </div>

                    <!-- Lecturer Registration Form -->
                    <div v-if="selectedRole === 'lecturer'">
                        <div class="form-header">
                            <button class="back-button" @click="goBack">
                                <i class="fas fa-arrow-left"></i>
                                Back to Role Selection
                            </button>
                            <h2 class="form-title">Lecturer Registration</h2>
                        </div>

                        <div class="form-container">
                            <form @submit.prevent="submitLecturerForm">
                                <!-- Personal Information -->
                                <div class="form-section">
                                    <div class="section-header">
                                        <div class="section-icon personal-icon">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <h3 class="section-title-small">Personal Information</h3>
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label">Full Name <span class="required">*</span></label>
                                            <input type="text" class="form-control" v-model="lecturerForm.name" required>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">NIC Number <span class="required">*</span></label>
                                            <input type="text" class="form-control" v-model="lecturerForm.nic" required>
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label">Phone Number <span class="required">*</span></label>
                                            <input type="tel" class="form-control" v-model="lecturerForm.phoneNo" required>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Address <span class="required">*</span></label>
                                            <textarea class="form-control" v-model="lecturerForm.address" rows="3" required></textarea>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="submit-section">
                            <button type="button" class="submit-button" :disabled="loading" @click="submitLecturerForm">
                                <span v-if="loading" class="loading-spinner"></span>
                                <span class="button-text">
                                    <span v-if="loading">Completing Registration...</span>
                                    <span v-else>Complete Lecturer Registration</span>
                                </span>
                            </button>
                        </div>
                    </div>

                    <!-- Success/Error Popup Modal -->
                    <div id="success-popup-overlay" class="popup-overlay" style="display: none;"></div>
                    <div id="success-popup" class="success-popup" style="display: none;">
                        <div class="popup-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <h3 id="popup-title">🎉 Registration Successful!</h3>
                        <p id="popup-message">Redirecting to your dashboard...</p>
                    </div>

                    <!-- Messages -->
                    <div v-if="message" :class="'message ' + messageType" v-html="message"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
const { createApp } = Vue;

document.addEventListener('DOMContentLoaded', function() {
    createApp({
        data() {
            return {
                selectedRole: null,
                loading: false,
                institutes: [],
                departments: [],
                courses: [],
                filteredCourses: [],
                studentForm: {
                    name: '',
                    dob: '',
                    course_id: '', // Changed from 'course' to 'course_id'
                    nic: '',
                    gender: '',
                    phoneNo: '',
                    address: '',
                    emergency_relation: '',
                    emergency_contact: '',
                    institute_id: '' // Changed from 'institute' to 'institute_id'
                },
                lecturerForm: {
                    name: '',
                    phoneNo: '',
                    nic: '',
                    address: ''
                }
            };
        },
        mounted() {
            this.fetchDropdowns();
        },
        methods: {
            async fetchDropdowns() {
                try {
                    const [instRes, deptRes, courseRes] = await Promise.all([
                        axios.get('/dropdown/institutes'),
                        axios.get('/dropdown/departments'),
                        axios.get('/dropdown/courses')
                    ]);
                    this.institutes = instRes.data.institutes || [];
                    this.departments = deptRes.data.departments || [];
                    this.courses = courseRes.data.courses || [];
                } catch (e) {
                    this.institutes = [];
                    this.departments = [];
                    this.courses = [];
                }
                this.filteredCourses = [];
            },
            // Remove onDepartmentChange method as it's no longer needed
            selectRole(role) {
                this.selectedRole = role;
            },
            goBack() {
                this.selectedRole = null;
            },
            async submitStudentForm() {
                this.loading = true;
                try {
                    const response = await axios.post('/user/student-registration-request', this.studentForm);
                    if (response.data.status === 'success') {
                        this.showSuccessPopup(response.data.redirect_url);
                    }
                } catch (error) {
                    const errorMessage = error.response?.data?.message || 'Registration failed. Please try again.';
                    this.showErrorPopup(errorMessage);
                } finally {
                    this.loading = false;
                }
            },
            async submitLecturerForm() {
                this.loading = true;
                try {
                    const response = await axios.post('/user/lecturer-registration-request', this.lecturerForm);
                    if (response.data.status === 'success') {
                        this.showSuccessPopup(response.data.redirect_url);
                    }
                } catch (error) {
                    const errorMessage = error.response?.data?.message || 'Registration failed. Please try again.';
                    this.showErrorPopup(errorMessage);
                } finally {
                    this.loading = false;
                }
            },
            showSuccessPopup(redirectUrl) {
                document.getElementById('popup-title').textContent = '🎉 Registration Successful!';
                document.getElementById('popup-message').textContent = 'Redirecting to your dashboard...';
                document.getElementById('success-popup-overlay').style.display = 'block';
                document.getElementById('success-popup').style.display = 'block';
                setTimeout(() => {
                    this.hidePopup();
                    window.location.href = redirectUrl || '/dashboard';
                }, 2000);
            },
            showErrorPopup(message) {
                document.getElementById('popup-title').textContent = '❌ Registration Failed!';
                document.getElementById('popup-message').textContent = message || 'Something went wrong. Please try again.';
                document.getElementById('success-popup').style.background = 'linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%)';
                document.getElementById('success-popup').style.borderColor = '#ef4444';
                document.querySelector('.popup-icon').style.background = 'linear-gradient(135deg, #ef4444 0%, #dc2626 100%)';
                document.querySelector('.popup-icon svg').innerHTML =
                    '<path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>';
                document.getElementById('success-popup-overlay').style.display = 'block';
                document.getElementById('success-popup').style.display = 'block';
                setTimeout(() => {
                    this.hidePopup();
                    this.resetPopupStyles();
                }, 5000);
            },
            hidePopup() {
                document.getElementById('success-popup-overlay').style.display = 'none';
                document.getElementById('success-popup').style.display = 'none';
            },
            resetPopupStyles() {
                document.getElementById('success-popup').style.background = 'linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%)';
                document.getElementById('success-popup').style.borderColor = '#22c55e';
                document.querySelector('.popup-icon').style.background = 'linear-gradient(135deg, #22c55e 0%, #16a34a 100%)';
                document.querySelector('.popup-icon svg').innerHTML =
                    '<path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>';
            }
        }
    }).mount('#roleApp');
});
</script>
@endsection
