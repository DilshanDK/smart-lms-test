{{-- filepath: d:\Internship\smart-lms\resources\views\users\lecturer\videos-management.blade.php --}}
@extends('layouts.app')

@section('title', 'Videos Management - Lecturer')

@section('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap');

    :root {
        --primary: #7c3aed;
        --primary-dark: #6d28d9;
        --background: #f9fafb;
        --surface: #ffffff;
        --foreground: #1f2937;
        --muted: #e5e7eb;
        --muted-foreground: #6b7280;
        --success: #10b981;
        --danger: #ef4444;
        --warning: #f59e0b;
        --radius: 8px;
        --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        --border: #d1d5db;
    }

    * { font-family: 'Poppins', sans-serif; }
    body { background: var(--background); }

    .page-header {
        background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        color: white;
        padding: 2rem;
        border-radius: var(--radius);
        margin-bottom: 2rem;
        box-shadow: var(--shadow-md);
    }

    .youtube-status-banner {
        background: var(--surface);
        padding: 1rem;
        border-radius: var(--radius);
        margin-bottom: 2rem;
        box-shadow: var(--shadow-md);
        border-left: 4px solid var(--danger);
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .youtube-status-banner.connected {
        border-left-color: var(--success);
        background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
    }

    .youtube-status-banner.disconnected {
        border-left-color: var(--danger);
        background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
    }

    .youtube-status-banner.loading {
        border-left-color: var(--warning);
        background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
    }

    .tabs {
        display: flex;
        gap: 1rem;
        margin-bottom: 2rem;
        border-bottom: 2px solid var(--border);
    }

    .tab {
        background: none;
        border: none;
        padding: 1rem 2rem;
        font-size: 1rem;
        font-weight: 600;
        color: var(--muted-foreground);
        cursor: pointer;
        border-bottom: 3px solid transparent;
        transition: all 0.3s;
    }

    .tab:hover { color: var(--primary); }
    .tab.active {
        color: var(--primary);
        border-bottom-color: var(--primary);
    }

    .tab-content { display: none; }

    /* Video Grid Styles */
    .video-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 1.5rem;
        margin-top: 1.5rem;
    }

    .video-card {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        transition: all 0.3s;
        border: 2px solid transparent;
    }

    .video-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 16px rgba(0,0,0,0.15);
        border-color: var(--primary);
    }

    .video-thumbnail {
        width: 100%;
        height: 180px;
        object-fit: cover;
        background: var(--muted);
    }

    .video-info {
        padding: 1rem;
    }

    .video-title {
        font-size: 1rem;
        font-weight: 600;
        color: var(--foreground);
        margin-bottom: 0.5rem;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .video-meta {
        font-size: 0.875rem;
        color: var(--muted-foreground);
        margin-bottom: 1rem;
    }

    .video-actions {
        display: flex;
        gap: 0.5rem;
    }

    .btn-video {
        flex: 1;
        padding: 0.5rem;
        border: none;
        border-radius: 6px;
        font-size: 0.875rem;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.3s;
    }

    .btn-view {
        background: var(--primary);
        color: white;
    }

    .btn-delete {
        background: var(--danger);
        color: white;
    }

    /* Go Live Form Styles */
    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-label {
        display: block;
        font-weight: 600;
        margin-bottom: 0.5rem;
        color: var(--foreground);
    }

    .form-input,
    .form-textarea,
    .form-select {
        width: 100%;
        padding: 0.75rem;
        border: 2px solid var(--border);
        border-radius: 6px;
        font-size: 1rem;
        transition: border-color 0.3s;
    }

    .form-input:focus,
    .form-textarea:focus,
    .form-select:focus {
        outline: none;
        border-color: var(--primary);
    }

    .form-textarea {
        resize: vertical;
        min-height: 100px;
    }

    .form-checkbox {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 1rem;
    }

    .btn-primary {
        background: var(--primary);
        color: white;
        padding: 0.75rem 2rem;
        border: none;
        border-radius: 6px;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
    }

    .btn-primary:hover {
        background: var(--primary-dark);
        transform: translateY(-2px);
    }

    .btn-primary:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    /* Stream Live Tab Styles */
    .stream-card {
        background: white;
        border: 2px solid var(--border);
        border-radius: 12px;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }

    .stream-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid var(--border);
    }

    .stream-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--foreground);
        margin: 0;
    }

    .stream-course {
        color: var(--muted-foreground);
        font-size: 0.95rem;
        margin-top: 0.25rem;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.875rem;
    }

    .status-live {
        background: #fee2e2;
        color: #dc2626;
        animation: pulse 2s ease-in-out infinite;
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.7; }
    }

    .status-scheduled {
        background: #fef3c7;
        color: #d97706;
    }

    .credentials-section {
        background: var(--muted);
        border-radius: 8px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .credential-group {
        margin-bottom: 1rem;
    }

    .credential-label {
        font-weight: 600;
        color: var(--foreground);
        margin-bottom: 0.5rem;
        display: block;
        font-size: 0.875rem;
    }

    .credential-box {
        display: flex;
        gap: 0.5rem;
        align-items: stretch;
    }

    .credential-value {
        flex: 1;
        background: white;
        border: 2px solid var(--border);
        border-radius: 6px;
        padding: 0.75rem;
        font-family: 'Courier New', monospace;
        font-size: 0.875rem;
        overflow-x: auto;
        white-space: nowrap;
        color: var(--foreground);
    }

    .credential-value.masked {
        -webkit-text-security: disc;
        text-security: disc;
    }

    .btn-copy,
    .btn-toggle-visibility {
        padding: 0.75rem 1rem;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 600;
        font-size: 0.875rem;
        transition: all 0.3s;
        white-space: nowrap;
    }

    .btn-copy {
        background: var(--primary);
        color: white;
    }

    .btn-copy:hover {
        background: var(--primary-dark);
        transform: translateY(-2px);
    }

    .btn-toggle-visibility {
        background: white;
        color: var(--foreground);
        border: 2px solid var(--border);
    }

    .btn-toggle-visibility:hover {
        border-color: var(--primary);
        color: var(--primary);
    }

    .instructions-box {
        background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
        border: 2px solid #3b82f6;
        border-radius: 8px;
        padding: 1.5rem;
        margin-top: 1.5rem;
    }

    .instructions-box h4 {
        color: #1e40af;
        margin-top: 0;
        margin-bottom: 1rem;
        font-size: 1.1rem;
    }

    .instructions-box ol {
        margin: 0;
        padding-left: 1.5rem;
        color: #1e40af;
    }

    .instructions-box li {
        margin-bottom: 0.5rem;
        line-height: 1.6;
    }

    .empty-state {
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        min-height: 50vh;
        text-align: center;
        padding: 3rem;
        color: var(--muted-foreground);
    }

    .empty-icon {
        font-size: 5rem;
        opacity: 0.5;
        margin-bottom: 1.5rem;
    }

    .empty-state h3 {
        font-size: 1.75rem;
        font-weight: 600;
        color: var(--foreground);
        margin-bottom: 0.75rem;
    }

    .loading-spinner {
        width: 40px;
        height: 40px;
        border: 4px solid var(--muted);
        border-top: 4px solid var(--primary);
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin: 0 auto;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .error-message {
        text-align: center;
        padding: 3rem;
        color: var(--danger);
    }

    .btn-retry,
    .btn-upload {
        margin-top: 1rem;
        padding: 0.75rem 1.5rem;
        background: var(--primary);
        color: white;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.3s;
    }

    .btn-retry:hover,
    .btn-upload:hover {
        background: var(--primary-dark);
        transform: translateY(-2px);
    }

    /* Action Bar */
    .action-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .filter-group {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    /* Upload Modal Styles */
    .upload-modal-backdrop {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(0, 0, 0, 0.8);
        backdrop-filter: blur(8px);
        z-index: 99999;
        display: none;
        justify-content: center;
        align-items: center;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .upload-modal-backdrop.show {
        display: flex;
        opacity: 1;
    }

    .upload-modal {
        background: white;
        border-radius: 12px;
        width: 90%;
        max-width: 600px;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 25px 50px rgba(0, 0, 0, 0.5);
        transform: scale(0.9);
        transition: transform 0.3s ease;
    }

    .upload-modal-backdrop.show .upload-modal {
        transform: scale(1);
    }

    .upload-modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1.5rem;
        border-bottom: 2px solid var(--border);
    }

    .upload-modal-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--foreground);
        margin: 0;
    }

    .upload-modal-close {
        background: var(--danger);
        color: white;
        border: none;
        width: 35px;
        height: 35px;
        border-radius: 50%;
        font-size: 1.25rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s;
    }

    .upload-modal-close:hover {
        background: #dc2626;
        transform: scale(1.1);
    }

    .upload-modal-body {
        padding: 2rem;
    }

    .file-upload-area {
        border: 3px dashed var(--border);
        border-radius: 12px;
        padding: 3rem 2rem;
        text-align: center;
        background: var(--muted);
        cursor: pointer;
        transition: all 0.3s;
        margin-bottom: 1.5rem;
    }

    .file-upload-area:hover {
        border-color: var(--primary);
        background: var(--surface);
    }

    .file-upload-area.dragover {
        border-color: var(--success);
        background: #ecfdf5;
    }

    .file-upload-icon {
        font-size: 3rem;
        color: var(--primary);
        margin-bottom: 1rem;
    }

    .file-upload-text {
        font-size: 1rem;
        color: var(--foreground);
        margin-bottom: 0.5rem;
    }

    .file-upload-hint {
        font-size: 0.875rem;
        color: var(--muted-foreground);
    }

    .selected-file {
        background: var(--muted);
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .selected-file-icon {
        font-size: 2rem;
        color: var(--primary);
    }

    .selected-file-info {
        flex: 1;
    }

    .selected-file-name {
        font-weight: 600;
        color: var(--foreground);
        margin-bottom: 0.25rem;
    }

    .selected-file-size {
        font-size: 0.875rem;
        color: var(--muted-foreground);
    }

    .remove-file-btn {
        background: var(--danger);
        color: white;
        border: none;
        padding: 0.5rem 1rem;
        border-radius: 6px;
        cursor: pointer;
        font-size: 0.875rem;
        transition: all 0.3s;
    }

    .remove-file-btn:hover {
        background: #dc2626;
    }

    .upload-progress {
        display: none;
        margin-top: 1.5rem;
        padding: 1.5rem;
        background: var(--muted);
        border-radius: 8px;
    }

    .upload-progress.show {
        display: block;
    }

    .progress-text {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0.75rem;
        font-size: 0.875rem;
        font-weight: 600;
    }

    .progress-bar-container {
        width: 100%;
        height: 24px;
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: inset 0 2px 4px rgba(0,0,0,0.1);
    }

    .progress-bar-fill {
        height: 100%;
        background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        width: 0%;
        transition: width 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 0.75rem;
        font-weight: 700;
    }

    .upload-status {
        text-align: center;
        margin-top: 1rem;
        font-size: 0.875rem;
        color: var(--muted-foreground);
    }

    .btn-submit-upload {
        width: 100%;
        padding: 1rem;
        background: var(--primary);
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        margin-top: 1.5rem;
    }

    .btn-submit-upload:hover {
        background: var(--primary-dark);
        transform: translateY(-2px);
    }

    .btn-submit-upload:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        transform: none;
    }

    /* CRITICAL FIX: SweetAlert z-index override - Must be HIGHER than modal */
    .swal2-container {
        z-index: 999999 !important; /* 10x higher than modal (99999) */
    }

    .swal2-popup {
        z-index: 1000000 !important;
    }

    /* Ensure SweetAlert backdrop appears above modal backdrop */
    .swal2-backdrop-show {
        z-index: 999998 !important;
    }

    /* Override any Bootstrap SweetAlert conflicts */
    body.swal2-shown > [aria-hidden="true"] {
        filter: blur(8px);
    }

    /* Ensure modal doesn't interfere with SweetAlert */
    .upload-modal-backdrop.show ~ .swal2-container {
        z-index: 1000000 !important;
    }
</style>
@endsection

@section('content')
<div class="main-container">
    <div class="page-header">
        <h1 style="font-size: 2.5rem; font-weight: 700; margin-bottom: 0.5rem;">📹 Videos Management</h1>
        <p style="font-size: 1.1rem; opacity: 0.9; margin: 0;">Upload videos, manage playlists, and stream live</p>
    </div>

    <!-- YouTube Status Banner -->
    <div class="youtube-status-banner loading" id="youtubeStatusBanner">
        <i class="fas fa-spinner fa-spin" style="font-size: 1.25rem;"></i>
        <span>Checking YouTube connection...</span>
    </div>

    <!-- Tabs Navigation -->
    <div class="tabs">
        <button class="tab active" data-tab="videos" onclick="switchTab('videos')">
            <i class="fas fa-video"></i> My Videos
        </button>
        <button class="tab" data-tab="golive" onclick="switchTab('golive')">
            <i class="fas fa-broadcast-tower"></i> Go Live
        </button>
        <button class="tab" data-tab="stream" onclick="switchTab('stream')">
            <i class="fas fa-desktop"></i> Stream Live
        </button>
    </div>

    <!-- Tab 1: My Videos -->
    <div id="videosTab" class="tab-content" style="display: block;">
        <div style="background: white; padding: 2rem; border-radius: var(--radius); box-shadow: var(--shadow-md);">
            <div class="action-bar">
                <h2 style="margin: 0;">📚 Your Video Library</h2>
                <button class="btn-upload" onclick="openUploadModal()">
                    <i class="fas fa-upload"></i> Upload Video
                </button>
            </div>

            <div class="filter-group">
                <select id="courseFilter" class="form-select" style="width: auto; min-width: 200px;" onchange="filterVideos()">
                    <option value="">All Courses</option>
                </select>
            </div>

            <div id="videosContainer">
                <div style="text-align: center; padding: 3rem;">
                    <div class="loading-spinner"></div>
                    <p style="margin-top: 1rem; color: var(--muted-foreground);">Loading videos...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab 2: Go Live -->
    <div id="goliveTab" class="tab-content" style="display: none;">
        <div style="background: white; padding: 2rem; border-radius: var(--radius); box-shadow: var(--shadow-md);">
            <h2 style="margin-bottom: 1.5rem;">🔴 Create Live Stream</h2>
            <p style="color: var(--muted-foreground); margin-bottom: 2rem;">Set up a new YouTube live stream for your course.</p>

            <form id="goLiveForm">
                <div class="form-group">
                    <label class="form-label">Stream Title *</label>
                    <input type="text" id="goLiveTitle" class="form-input" placeholder="e.g., Introduction to Web Development" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea id="goLiveDescription" class="form-textarea" placeholder="Describe what students will learn in this live session..."></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Course *</label>
                    <select id="goLiveCourse" class="form-select" required>
                        <option value="">Select a course</option>
                    </select>
                </div>

                <div class="form-checkbox">
                    <input type="checkbox" id="goLiveStartNow" checked>
                    <label for="goLiveStartNow">Start streaming now</label>
                </div>

                <div class="form-group" id="scheduledStartGroup" style="display: none;">
                    <label class="form-label">Scheduled Start Time</label>
                    <input type="datetime-local" id="goLiveScheduledStart" class="form-input">
                </div>

                <button type="submit" class="btn-primary">
                    <i class="fas fa-broadcast-tower"></i> Create Live Stream
                </button>
            </form>
        </div>
    </div>

    <!-- Tab 3: Stream Live -->
    <div id="streamTab" class="tab-content" style="display: none;">
        <div style="background: white; padding: 2rem; border-radius: var(--radius); box-shadow: var(--shadow-md);">
            <h2 style="margin-bottom: 1.5rem;">🎥 Active Live Streams</h2>
            <p style="color: var(--muted-foreground); margin-bottom: 2rem;">Use these credentials in OBS Studio or similar software to start streaming.</p>

            <div id="activeStreamsContainer">
                <div style="text-align: center; padding: 3rem;">
                    <div class="loading-spinner"></div>
                    <p style="margin-top: 1rem; color: var(--muted-foreground);">Loading streams...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Upload Video Modal -->
    <div class="upload-modal-backdrop" id="uploadModalBackdrop">
        <div class="upload-modal">
            <div class="upload-modal-header">
                <h2 class="upload-modal-title">📤 Upload Video to YouTube</h2>
                <button class="upload-modal-close" onclick="closeUploadModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="upload-modal-body">
                <form id="uploadVideoForm">
                    <!-- Video File Upload Area -->
                    <div class="file-upload-area" id="fileUploadArea" onclick="document.getElementById('videoFileInput').click()">
                        <i class="fas fa-cloud-upload-alt file-upload-icon"></i>
                        <div class="file-upload-text">Click to browse or drag & drop video file</div>
                        <div class="file-upload-hint">MP4, MOV, AVI, WMV (Max 512MB)</div>
                    </div>

                    <input type="file" id="videoFileInput" accept="video/mp4,video/mov,video/avi,video/wmv" style="display: none;">

                    <!-- Selected File Display -->
                    <div id="selectedFileDisplay" style="display: none;" class="selected-file">
                        <i class="fas fa-file-video selected-file-icon"></i>
                        <div class="selected-file-info">
                            <div class="selected-file-name" id="selectedFileName">No file selected</div>
                            <div class="selected-file-size" id="selectedFileSize">0 MB</div>
                        </div>
                        <button type="button" class="remove-file-btn" onclick="removeSelectedFile()">
                            <i class="fas fa-trash"></i> Remove
                        </button>
                    </div>

                    <!-- Video Details -->
                    <div class="form-group">
                        <label class="form-label">Video Title *</label>
                        <input type="text" id="uploadVideoTitle" class="form-input" placeholder="e.g., Introduction to JavaScript" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <textarea id="uploadVideoDescription" class="form-textarea" placeholder="Describe what students will learn from this video..." rows="4"></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Course *</label>
                        <select id="uploadVideoCourse" class="form-select" required>
                            <option value="">Select a course</option>
                        </select>
                    </div>

                    <!-- Upload Progress -->
                    <div class="upload-progress" id="uploadProgress">
                        <div class="progress-text">
                            <span>Uploading to YouTube...</span>
                            <span id="uploadPercentage">0%</span>
                        </div>
                        <div class="progress-bar-container">
                            <div class="progress-bar-fill" id="uploadProgressBar">0%</div>
                        </div>
                        <div class="upload-status" id="uploadStatus">Preparing upload...</div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn-submit-upload" id="submitUploadBtn">
                        <i class="fas fa-upload"></i> Upload to YouTube
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const API_BASE = '{{ url('') }}/api';
let youtubeConnected = false;
let allVideos = [];
let lecturerCourses = [];
let selectedVideoFile = null;

// Escape HTML function - ADD THIS FIRST
function escapeHtml(unsafe) {
    if (!unsafe) return '';
    return unsafe.toString()
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

// Check YouTube connection status - UPDATED to return Promise
async function checkYouTubeStatus() {
    const banner = document.getElementById('youtubeStatusBanner');

    try {
        const token = localStorage.getItem('auth_token');
        const response = await fetch(`${API_BASE}/lecturer/youtube-status`, {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });

        const result = await response.json();

        if (response.ok && result.status === 'success') {
            if (result.connected) {
                youtubeConnected = true;
                banner.className = 'youtube-status-banner connected';
                banner.innerHTML = `
                    <i class="fas fa-check-circle" style="font-size: 1.25rem; color: var(--success);"></i>
                    <div style="flex: 1;">
                        <strong>YouTube Connected</strong>
                        <div style="font-size: 0.875rem; color: var(--muted-foreground); margin-top: 0.25rem;">
                            Channel: ${escapeHtml(result.channel_name || 'Unknown')}
                            ${result.token_expired ? ' <span style="color: var(--warning);">(Token expired - contact admin)</span>' : ''}
                        </div>
                    </div>
                `;
            } else {
                youtubeConnected = false;
                banner.className = 'youtube-status-banner disconnected';
                banner.innerHTML = `
                    <i class="fas fa-exclamation-triangle" style="font-size: 1.25rem; color: var(--danger);"></i>
                    <div style="flex: 1;">
                        <strong>YouTube Not Connected</strong>
                        <div style="font-size: 0.875rem; color: var(--muted-foreground); margin-top: 0.25rem;">
                            ${result.message || 'Please contact your administrator to connect YouTube.'}
                        </div>
                    </div>
                `;
            }
        } else {
            youtubeConnected = false;
            banner.className = 'youtube-status-banner disconnected';
            banner.innerHTML = `
                <i class="fas fa-times-circle" style="font-size: 1.25rem; color: var(--danger);"></i>
                <div style="flex: 1;">
                    <strong>Connection Check Failed</strong>
                    <div style="font-size: 0.875rem; color: var(--muted-foreground); margin-top: 0.25rem;">
                        ${result.message || 'Unable to verify YouTube connection status'}
                    </div>
                </div>
            `;
        }
    } catch (error) {
        console.error('YouTube status check error:', error);
        youtubeConnected = false;
        banner.className = 'youtube-status-banner disconnected';
        banner.innerHTML = `
            <i class="fas fa-times-circle" style="font-size: 1.25rem; color: var(--danger);"></i>
            <div style="flex: 1;">
                <strong>Connection Error</strong>
                <div style="font-size: 0.875rem; color: var(--muted-foreground); margin-top: 0.25rem;">
                    Network error checking YouTube status. Please try refreshing the page.
                </div>
            </div>
        `;
    }

    // IMPORTANT: Return the connection status
    return youtubeConnected;
}

// Switch tab function
function switchTab(tabName) {
    document.querySelectorAll('.tab-content').forEach(tab => tab.style.display = 'none');
    document.querySelectorAll('.tab').forEach(btn => btn.classList.remove('active'));
    document.getElementById(tabName + 'Tab').style.display = 'block';
    document.querySelector(`[data-tab="${tabName}"]`).classList.add('active');

    if (tabName === 'videos') {
        loadVideos();
    } else if (tabName === 'golive') {
        checkGoLiveAccess();
    } else if (tabName === 'stream') {
        loadActiveStreams();
    }
}

// Store original Go Live form HTML
let originalGoLiveFormHTML = '';

// Capture original form on page load
document.addEventListener('DOMContentLoaded', async function() {
    // Store the original Go Live form HTML before any modifications
    originalGoLiveFormHTML = document.querySelector('#goliveTab > div').innerHTML;

    // CRITICAL: Check YouTube status FIRST and WAIT for it
    await checkYouTubeStatus();

    // THEN load videos (now youtubeConnected is properly set)
    loadVideos();
});

// UPDATED: Check Go Live tab access
function checkGoLiveAccess() {
    const container = document.querySelector('#goliveTab > div');

    if (!youtubeConnected) {
        container.innerHTML = `
            <h2 style="margin-bottom: 1.5rem;">🔴 Create Live Stream</h2>
            <p style="color: var(--muted-foreground); margin-bottom: 2rem;">Set up a new YouTube live stream for your course.</p>

            <div class="empty-state">
                <i class="fas fa-unlink empty-icon"></i>
                <h3>YouTube Not Connected</h3>
                <p>Please contact your administrator to connect a YouTube channel before creating live streams.</p>
            </div>
        `;
    } else {
        // Restore the original form if it was replaced
        if (!document.getElementById('goLiveForm')) {
            container.innerHTML = originalGoLiveFormHTML;

            // Re-attach event listeners after restoring form
            reattachGoLiveFormListeners();
        }
    }
}

// NEW: Re-attach form listeners after restoration
function reattachGoLiveFormListeners() {
    // Re-attach Go Live form submit handler
    const goLiveForm = document.getElementById('goLiveForm');
    if (goLiveForm) {
        goLiveForm.addEventListener('submit', handleGoLiveSubmit);
    }

    // Re-attach scheduled start toggle
    const startNowCheckbox = document.getElementById('goLiveStartNow');
    if (startNowCheckbox) {
        startNowCheckbox.addEventListener('change', function() {
            document.getElementById('scheduledStartGroup').style.display = this.checked ? 'none' : 'block';
        });
    }
}

// Load videos function
async function loadVideos() {
    const container = document.getElementById('videosContainer');

    if (!youtubeConnected) {
        container.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-unlink empty-icon"></i>
                <h3>YouTube Not Connected</h3>
                <p>Please contact your administrator to connect a YouTube channel before managing videos.</p>
            </div>
        `;
        return;
    }

    container.innerHTML = '<div style="text-align: center; padding: 3rem;"><div class="loading-spinner"></div><p style="margin-top: 1rem;">Loading videos...</p></div>';

    try {
        const token = localStorage.getItem('auth_token');
        const response = await fetch(`${API_BASE}/lecturer/videos`, {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });

        const result = await response.json();

        if (response.ok && result.status === 'success') {
            allVideos = result.videos || [];
            lecturerCourses = result.lecturer_courses || [];

            // Populate ALL course dropdowns (filter, go-live, and upload modal)
            populateCourseDropdowns();

            displayVideos(allVideos);
        } else {
            showError(container, result.message || 'Failed to load videos');
        }
    } catch (error) {
        console.error('Load videos error:', error);
        showError(container, 'Network error loading videos');
    }
}

// NEW: Populate all course dropdowns
function populateCourseDropdowns() {
    // Populate course filter dropdown
    const courseFilter = document.getElementById('courseFilter');
    courseFilter.innerHTML = '<option value="">All Courses</option>';
    lecturerCourses.forEach(course => {
        courseFilter.innerHTML += `<option value="${course.id}">${escapeHtml(course.title)}</option>`;
    });

    // Populate Go Live course dropdown
    const goLiveCourse = document.getElementById('goLiveCourse');
    goLiveCourse.innerHTML = '<option value="">Select a course</option>';
    lecturerCourses.forEach(course => {
        goLiveCourse.innerHTML += `<option value="${course.id}">${escapeHtml(course.title)}</option>`;
    });

    // Populate Upload Video course dropdown
    const uploadVideoCourse = document.getElementById('uploadVideoCourse');
    uploadVideoCourse.innerHTML = '<option value="">Select a course</option>';
    lecturerCourses.forEach(course => {
        uploadVideoCourse.innerHTML += `<option value="${course.id}">${escapeHtml(course.title)}</option>`;
    });
}

// Display videos
function displayVideos(videos) {
    const container = document.getElementById('videosContainer');

    if (videos.length === 0) {
        container.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-video empty-icon"></i>
                <h3>No Videos Yet</h3>
                <p>Start by uploading your first video to this course.</p>
            </div>
        `;
        return;
    }

    let html = '<div class="video-grid">';

    videos.forEach(video => {
        html += `
            <div class="video-card">
                <img src="${video.thumbnail_url || 'https://via.placeholder.com/320x180?text=No+Thumbnail'}"
                     alt="${escapeHtml(video.title)}"
                     class="video-thumbnail">
                <div class="video-info">
                    <div class="video-title">${escapeHtml(video.title)}</div>
                    <div class="video-meta">
                        ${escapeHtml(video.course_name)} • ${video.total_views || 0} views
                    </div>
                    <div class="video-actions">
                        <button class="btn-video btn-view" onclick="window.open('${video.youtube_url}', '_blank')">
                            <i class="fas fa-play"></i> View
                        </button>
                        <button class="btn-video btn-delete" onclick="deleteVideo('${video.id}')">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                </div>
            </div>
        `;
    });

    html += '</div>';
    container.innerHTML = html;
}

// Filter videos
function filterVideos() {
    const courseId = document.getElementById('courseFilter').value;
    const filtered = courseId ? allVideos.filter(v => v.course_id === courseId) : allVideos;
    displayVideos(filtered);
}

// Delete video
async function deleteVideo(videoId) {
    const result = await Swal.fire({
        title: 'Delete Video?',
        text: 'This will remove the video from your library',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        confirmButtonText: 'Yes, delete'
    });

    if (result.isConfirmed) {
        try {
            const token = localStorage.getItem('auth_token');
            const response = await fetch(`${API_BASE}/lecturer/videos/${videoId}`, {
                method: 'DELETE',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                }
            });
            const result = await response.json();

            if (result.status === 'success') {
                Swal.fire('Deleted!', result.message, 'success');
                loadVideos();
            }
        } catch (error) {
            Swal.fire('Error', 'Failed to delete video', 'error');
        }
    }
}

// Open upload modal - UPDATED
function openUploadModal() {
    if (!youtubeConnected) {
        Swal.fire({
            icon: 'error',
            title: 'YouTube Not Connected',
            text: 'Please ask your administrator to connect YouTube first.',
            confirmButtonColor: '#ef4444'
        });
        return;
    }

    // Populate course dropdown (in case loadVideos hasn't been called yet)
    if (lecturerCourses.length > 0) {
        const uploadVideoCourse = document.getElementById('uploadVideoCourse');
        uploadVideoCourse.innerHTML = '<option value="">Select a course</option>';
        lecturerCourses.forEach(course => {
            uploadVideoCourse.innerHTML += `<option value="${course.id}">${escapeHtml(course.title)}</option>`;
        });
    } else {
        // If courses aren't loaded yet, fetch them
        loadLecturerCourses();
    }

    // Reset form
    document.getElementById('uploadVideoForm').reset();
    document.getElementById('selectedFileDisplay').style.display = 'none';
    document.getElementById('uploadProgress').classList.remove('show');
    selectedVideoFile = null;

    // Show modal
    const backdrop = document.getElementById('uploadModalBackdrop');
    backdrop.classList.add('show');
    document.body.style.overflow = 'hidden';
}

// NEW: Load lecturer courses separately (fallback)
async function loadLecturerCourses() {
    try {
        const token = localStorage.getItem('auth_token');
        const response = await fetch(`${API_BASE}/lecturer/my-courses`, {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });

        const result = await response.json();

        if (response.ok && result.status === 'success') {
            lecturerCourses = result.courses.map(course => ({
                id: course.id,
                title: course.title
            }));

            // Populate all dropdowns
            populateCourseDropdowns();
        }
    } catch (error) {
        console.error('Error loading courses:', error);
        Swal.fire({
            icon: 'error',
            title: 'Failed to Load Courses',
            text: 'Could not load your courses. Please refresh the page.',
            confirmButtonColor: '#ef4444'
        });
    }
}

// File input change handler
document.getElementById('videoFileInput').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        handleFileSelection(file);
    }
});

// Handle file selection
function handleFileSelection(file) {
    // Validate file type
    const allowedTypes = ['video/mp4', 'video/mov', 'video/avi', 'video/x-ms-wmv'];
    if (!allowedTypes.includes(file.type)) {
        Swal.fire({
            icon: 'error',
            title: 'Invalid File Type',
            text: 'Please select a valid video file (MP4, MOV, AVI, or WMV)',
            confirmButtonColor: '#ef4444'
        });
        return;
    }

    // Validate file size (max 512MB)
    const maxSize = 512 * 1024 * 1024; // 512MB in bytes
    if (file.size > maxSize) {
        Swal.fire({
            icon: 'error',
            title: 'File Too Large',
            text: 'Video file must be less than 512MB',
            confirmButtonColor: '#ef4444'
        });
        return;
    }

    // Store file and display info
    selectedVideoFile = file;

    const fileName = file.name;
    const fileSize = (file.size / (1024 * 1024)).toFixed(2) + ' MB';

    document.getElementById('selectedFileName').textContent = fileName;
    document.getElementById('selectedFileSize').textContent = fileSize;
    document.getElementById('selectedFileDisplay').style.display = 'flex';
}

// Remove selected file
function removeSelectedFile() {
    selectedVideoFile = null;
    document.getElementById('videoFileInput').value = '';
    document.getElementById('selectedFileDisplay').style.display = 'none';
}

// Drag and drop handlers
const fileUploadArea = document.getElementById('fileUploadArea');

fileUploadArea.addEventListener('dragover', function(e) {
    e.preventDefault();
    e.stopPropagation();
    this.classList.add('dragover');
});

fileUploadArea.addEventListener('dragleave', function(e) {
    e.preventDefault();
    e.stopPropagation();
    this.classList.remove('dragover');
});

fileUploadArea.addEventListener('drop', function(e) {
    e.preventDefault();
    e.stopPropagation();
    this.classList.remove('dragover');

    const files = e.dataTransfer.files;
    if (files.length > 0) {
        handleFileSelection(files[0]);
    }
});

// Close upload modal - ADD THIS FUNCTION
function closeUploadModal() {
    const backdrop = document.getElementById('uploadModalBackdrop');
    backdrop.classList.remove('show');
    document.body.style.overflow = '';

    // Reset form
    document.getElementById('uploadVideoForm').reset();
    document.getElementById('selectedFileDisplay').style.display = 'none';
    document.getElementById('uploadProgress').classList.remove('show');
    selectedVideoFile = null;

    // Reset submit button state
    const submitBtn = document.getElementById('submitUploadBtn');
    submitBtn.disabled = false;
    submitBtn.innerHTML = '<i class="fas fa-upload"></i> Upload to YouTube';
}

// Upload form submit handler
document.getElementById('uploadVideoForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    if (!selectedVideoFile) {
        Swal.fire({
            icon: 'warning',
            title: 'No File Selected',
            text: 'Please select a video file to upload',
            confirmButtonColor: '#f59e0b'
        });
        return;
    }

    const title = document.getElementById('uploadVideoTitle').value.trim();
    const description = document.getElementById('uploadVideoDescription').value.trim();
    const courseId = document.getElementById('uploadVideoCourse').value;

    if (!title || !courseId) {
        Swal.fire({
            icon: 'warning',
            title: 'Missing Information',
            text: 'Please provide video title and select a course',
            confirmButtonColor: '#f59e0b'
        });
        return;
    }

    // Disable submit button
    const submitBtn = document.getElementById('submitUploadBtn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';

    // Show progress bar
    const progressContainer = document.getElementById('uploadProgress');
    progressContainer.classList.add('show');

    try {
        const token = localStorage.getItem('auth_token');
        const formData = new FormData();
        formData.append('title', title);
        formData.append('description', description);
        formData.append('course_id', courseId);
        formData.append('video', selectedVideoFile);

        // Use XMLHttpRequest for progress tracking
        const xhr = new XMLHttpRequest();

        // Upload progress event
        xhr.upload.addEventListener('progress', function(e) {
            if (e.lengthComputable) {
                const percentComplete = Math.round((e.loaded / e.total) * 100);
                updateProgress(percentComplete);
            }
        });

        // Upload complete event
        xhr.addEventListener('load', function() {
            if (xhr.status === 201) {
                const result = JSON.parse(xhr.responseText);

                // FIXED: Close modal BEFORE showing success alert
                closeUploadModal();

                // Show success alert
                Swal.fire({
                    icon: 'success',
                    title: 'Upload Successful!',
                    html: `
                        <p>${result.message}</p>
                        <div style="margin-top: 1rem; padding: 1rem; background: #f3f4f6; border-radius: 8px; text-align: left;">
                            <p style="margin: 0.5rem 0;"><strong>Video ID:</strong> ${result.video.youtube_video_id}</p>
                            <p style="margin: 0.5rem 0;"><strong>Added to playlist:</strong> ${result.video.added_to_playlist ? 'Yes' : 'No'}</p>
                        </div>
                        <a href="${result.video.youtube_url}" target="_blank" style="display: inline-block; margin-top: 1rem; padding: 0.75rem 1.5rem; background: #7c3aed; color: white; text-decoration: none; border-radius: 6px; font-weight: 600;">
                            <i class="fab fa-youtube"></i> View on YouTube
                        </a>
                    `,
                    confirmButtonColor: '#7c3aed',
                    allowOutsideClick: false
                }).then(() => {
                    loadVideos(); // Refresh video list
                });
            } else {
                const error = JSON.parse(xhr.responseText);
                throw new Error(error.message || 'Upload failed');
            }
        });

        // Upload error event
        xhr.addEventListener('error', function() {
            throw new Error('Network error. Please check your connection and try again.');
        });

        // Upload abort event
        xhr.addEventListener('abort', function() {
            throw new Error('Upload cancelled by user.');
        });

        // Open connection and send
        xhr.open('POST', `${API_BASE}/lecturer/videos/upload`);
        xhr.setRequestHeader('Authorization', `Bearer ${token}`);
        xhr.send(formData);

    } catch (error) {
        console.error('Upload error:', error);

        Swal.fire({
            icon: 'error',
            title: 'Upload Failed',
            text: error.message || 'Failed to upload video. Please try again.',
            confirmButtonColor: '#ef4444'
        });

        // Reset UI (but keep modal open so user can try again)
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-upload"></i> Upload to YouTube';
        progressContainer.classList.remove('show');
    }
});

// Update progress bar
function updateProgress(percent) {
    const progressBar = document.getElementById('uploadProgressBar');
    const progressPercentage = document.getElementById('uploadPercentage');
    const uploadStatus = document.getElementById('uploadStatus');

    progressBar.style.width = percent + '%';
    progressBar.textContent = percent + '%';
    progressPercentage.textContent = percent + '%';

    if (percent < 30) {
        uploadStatus.textContent = 'Preparing upload...';
    } else if (percent < 70) {
        uploadStatus.textContent = 'Uploading to YouTube...';
    } else if (percent < 100) {
        uploadStatus.textContent = 'Almost done...';
    } else {
        uploadStatus.textContent = 'Processing video...';
    }
}

// Close modal when clicking outside
document.getElementById('uploadModalBackdrop').addEventListener('click', function(e) {
    if (e.target === this) {
        closeUploadModal();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const backdrop = document.getElementById('uploadModalBackdrop');
        if (backdrop.classList.contains('show')) {
            closeUploadModal();
        }
    }
});

// UPDATED: Extract Go Live form submit handler into separate function
async function handleGoLiveSubmit(e) {
    e.preventDefault();

    if (!youtubeConnected) {
        Swal.fire({
            icon: 'error',
            title: 'YouTube Not Connected',
            text: 'Please ask your administrator to connect YouTube first.',
            confirmButtonColor: '#ef4444'
        });
        return;
    }

    const title = document.getElementById('goLiveTitle').value.trim();
    const description = document.getElementById('goLiveDescription').value.trim();
    const courseId = document.getElementById('goLiveCourse').value;
    const startNow = document.getElementById('goLiveStartNow').checked;
    const scheduledStart = document.getElementById('goLiveScheduledStart').value;

    if (!title || !courseId) {
        Swal.fire({ icon: 'warning', title: 'Missing information', text: 'Please provide title and select a course' });
        return;
    }

    // Get submit button and show loading state
    const submitBtn = document.querySelector('#goLiveForm button[type="submit"]');
    const originalBtnText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating Live Stream...';

    try {
        const token = localStorage.getItem('auth_token');
        const payload = { title, description, course_id: courseId, start_now: startNow };
        if (!startNow && scheduledStart) payload.scheduled_start = new Date(scheduledStart).toISOString();

        const response = await fetch(`${API_BASE}/lecturer/videos/go-live`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        });

        const data = await response.json();

        if (response.ok && data.status === 'success') {
            Swal.fire({ icon: 'success', title: 'Live stream created', text: data.message });
            document.getElementById('goLiveForm').reset();
            switchTab('stream'); // Switch to Stream Live tab
        } else {
            Swal.fire({ icon: 'error', title: 'Failed', text: (data.message || 'Could not create live stream') });
        }
    } catch (err) {
        console.error('Go Live error', err);
        Swal.fire({ icon: 'error', title: 'Error', text: 'Network error' });
    } finally {
        // Restore button state
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalBtnText;
    }
}

// Go Live form submit - UPDATED to use extracted handler
document.getElementById('goLiveForm').addEventListener('submit', handleGoLiveSubmit);

// Toggle scheduled start field
document.getElementById('goLiveStartNow').addEventListener('change', function() {
    document.getElementById('scheduledStartGroup').style.display = this.checked ? 'none' : 'block';
});

// Load active streams
async function loadActiveStreams() {
    const container = document.getElementById('activeStreamsContainer');

    if (!youtubeConnected) {
        container.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-unlink empty-icon"></i>
                <h3>YouTube Not Connected</h3>
                <p>Please contact your administrator to connect a YouTube channel before managing live streams.</p>
            </div>
        `;
        return;
    }

    container.innerHTML = '<div style="text-align: center; padding: 3rem;"><div class="loading-spinner"></div><p style="margin-top: 1rem; color: var(--muted-foreground);">Loading streams...</p></div>';

    try {
        const token = localStorage.getItem('auth_token');
        const response = await fetch(`${API_BASE}/lecturer/my-live-streams`, {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });

        const result = await response.json();

        if (response.ok && result.status === 'success') {
            displayStreams(result.streams || []);
        } else {
            showStreamError('Failed to load streams: ' + (result.message || 'Unknown error'));
        }
    } catch (error) {
        console.error('Load streams error:', error);
        showStreamError('Network error loading streams');
    }
}

// Display streams - UPDATED with delete button
function displayStreams(streams) {
    const container = document.getElementById('activeStreamsContainer');

    if (streams.length === 0) {
        container.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-video-slash empty-icon"></i>
                <h3>No Active Streams</h3>
                <p>Create a broadcast using the "Go Live" tab to see streaming credentials here.</p>
            </div>
        `;
        return;
    }

    let html = '';

    streams.forEach((stream, index) => {
        const statusClass = stream.status === 'live' ? 'status-live' : 'status-scheduled';
        const statusIcon = stream.status === 'live' ? '🔴' : '⏱️';
        const statusText = stream.status === 'live' ? 'Live Now' : 'Scheduled';

        html += `
            <div class="stream-card">
                <div class="stream-header">
                    <div>
                        <h3 class="stream-title">${escapeHtml(stream.title)}</h3>
                        <p class="stream-course">${escapeHtml(stream.course_name)}</p>
                    </div>
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <span class="status-badge ${statusClass}">
                            ${statusIcon} ${statusText}
                        </span>
                        <button onclick="deleteLiveStream('${stream.id}')"
                                class="btn-video btn-delete"
                                title="Delete this stream">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                </div>

                <div class="credentials-section">
                    <div class="credential-group">
                        <div class="credential-label">🌐 Stream URL (Server):</div>
                        <div class="credential-box">
                            <div class="credential-value" id="streamUrl${index}">${stream.stream_url || 'N/A'}</div>
                            <button class="btn-copy" onclick="copyToClipboard('streamUrl${index}', this)">
                                <i class="fas fa-copy"></i> Copy
                            </button>
                        </div>
                    </div>

                    <div class="credential-group">
                        <div class="credential-label">🔑 Stream Key:</div>
                        <div class="credential-box">
                            <div class="credential-value masked" id="streamKey${index}" data-masked="true">${stream.stream_key || 'N/A'}</div>
                            <button class="btn-toggle-visibility" onclick="toggleVisibility('streamKey${index}', this)">
                                <i class="fas fa-eye"></i> Show
                            </button>
                            <button class="btn-copy" onclick="copyToClipboard('streamKey${index}', this)">
                                <i class="fas fa-copy"></i> Copy
                            </button>
                        </div>
                    </div>

                    <div class="credential-group">
                        <div class="credential-label">📺 Watch URL (Share with students):</div>
                        <div class="credential-box">
                            <div class="credential-value" id="watchUrl${index}">${stream.watch_url || 'N/A'}</div>
                            <button class="btn-copy" onclick="copyToClipboard('watchUrl${index}', this)">
                                <i class="fas fa-copy"></i> Copy
                            </button>
                            <button class="btn-copy" onclick="window.open('${stream.watch_url}', '_blank')" style="background: var(--success);">
                                <i class="fas fa-external-link-alt"></i> Open
                            </button>
                        </div>
                    </div>
                </div>

                <div class="instructions-box">
                    <h4><i class="fas fa-info-circle"></i> How to Stream with OBS Studio:</h4>
                    <ol>
                        <li>Open <strong>OBS Studio</strong> on your computer</li>
                        <li>Go to <strong>Settings → Stream</strong></li>
                        <li>Select Service: <strong>Custom</strong></li>
                        <li>Paste the <strong>Stream URL</strong> above into the "Server" field</li>
                        <li>Paste the <strong>Stream Key</strong> above into the "Stream Key" field</li>
                        <li>Click <strong>OK</strong> to save settings</li>
                        <li>Click <strong>"Start Streaming"</strong> in OBS</li>
                        <li>Share the <strong>Watch URL</strong> with your students!</li>
                    </ol>
                </div>
            </div>
        `;
    });

    container.innerHTML = html;
}

// NEW: Delete live stream function
async function deleteLiveStream(streamId) {
    const result = await Swal.fire({
        title: 'Delete Live Stream?',
        text: 'This will permanently delete the stream configuration',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, delete it',
        cancelButtonText: 'Cancel'
    });

    if (!result.isConfirmed) return;

    try {
        const token = localStorage.getItem('auth_token');
        const response = await fetch(`${API_BASE}/lecturer/live-streams/${streamId}`, {
            method: 'DELETE',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });
        const data = await response.json();

        if (response.ok && data.status === 'success') {
            Swal.fire({
                icon: 'success',
                title: 'Deleted!',
                text: data.message,
                timer: 2000,
                showConfirmButton: false
            });
            loadActiveStreams();
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Delete Failed',
                text: data.message || 'Failed to delete stream'
            });
        }
    } catch (error) {
        console.error('Delete stream error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Network error. Please try again.'
        });
    }
}

// Copy to clipboard
async function copyToClipboard(elementId, button) {
    const element = document.getElementById(elementId);
    const text = element.textContent;

    try {
        await navigator.clipboard.writeText(text);
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="fas fa-check"></i> Copied!';
        button.style.background = 'var(--success)';
        setTimeout(() => {
            button.innerHTML = originalText;
            button.style.background = '';
        }, 2000);
    } catch (err) {
        Swal.fire({ icon: 'error', title: 'Copy Failed', text: 'Please select and copy the text manually' });
    }
}

// Toggle visibility
function toggleVisibility(elementId, button) {
    const element = document.getElementById(elementId);
    const isMasked = element.dataset.masked === 'true';

    if (isMasked) {
        element.classList.remove('masked');
        element.dataset.masked = 'false';
        button.innerHTML = '<i class="fas fa-eye-slash"></i> Hide';
    } else {
        element.classList.add('masked');
        element.dataset.masked = 'true';
        button.innerHTML = '<i class="fas fa-eye"></i> Show';
    }
}

// Show error message
function showError(container, message) {
    container.innerHTML = `
        <div class="error-message">
            <i class="fas fa-exclamation-triangle" style="font-size: 3rem; margin-bottom: 1rem;"></i>
            <h3 style="color: var(--foreground);">Error</h3>
            <p>${escapeHtml(message)}</p>
            <button class="btn-retry" onclick="loadVideos()">
                <i class="fas fa-redo"></i> Try Again
            </button>
        </div>
    `;
}

// Show stream error
function showStreamError(message) {
    const container = document.getElementById('activeStreamsContainer');
    container.innerHTML = `
        <div class="error-message">
            <i class="fas fa-exclamation-triangle" style="font-size: 3rem; margin-bottom: 1rem;"></i>
            <h3 style="color: var(--foreground);">Error</h3>
            <p>${escapeHtml(message)}</p>
            <button class="btn-retry" onclick="loadActiveStreams()">
                <i class="fas fa-redo"></i> Try Again
            </button>
        </div>
    `;
}
</script>
@endsection
