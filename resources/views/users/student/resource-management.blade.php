{{-- filepath: d:\Internship\smart-lms\resources\views\users\student\resource-management.blade.php --}}
@extends('layouts.app')

@section('title', 'Learning Resources - Student')

@section('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap');

    :root {
        --primary: #059669;
        --primary-dark: #047857;
        --background: #f8fafc;
        --surface: #ffffff;
        --foreground: #0f172a;
        --muted: #f1f5f9;
        --muted-foreground: #64748b;
        --border: #e2e8f0;
        --success: #10b981;
        --danger: #ef4444;
        --radius: 8px;
        --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    }

    * {
        font-family: 'Poppins', sans-serif;
    }

    body {
        background: var(--background);
    }

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

    .resources-section {
        margin-bottom: 3rem;
    }

    .course-header {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid var(--border);
    }

    .resources-horizontal {
        display: flex;
        gap: 1.5rem;
        overflow-x: auto;
        padding: 1rem 0;
        scroll-behavior: smooth;
    }

    .resources-horizontal::-webkit-scrollbar {
        height: 8px;
    }

    .resources-horizontal::-webkit-scrollbar-track {
        background: var(--muted);
        border-radius: 4px;
    }

    .resources-horizontal::-webkit-scrollbar-thumb {
        background: var(--primary);
        border-radius: 4px;
    }

    .resources-horizontal::-webkit-scrollbar-thumb:hover {
        background: var(--primary-dark);
    }

    .resource-card {
        min-width: 180px;
        max-width: 180px;
        flex-shrink: 0;
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 0.6rem 0.6rem 0.4rem 0.6rem;
        transition: all 0.3s ease;
        height: 160px;
        display: flex;
        flex-direction: column;
        justify-content: flex-start;
    }

    .resource-card:hover {
        transform: translateY(-3px) scale(1.04);
        box-shadow: var(--shadow-lg);
        border-color: var(--primary);
    }

    .file-icon {
        font-size: 2rem;
        text-align: center;
        margin-bottom: 0.2rem;
    }

    /* UPDATED: Block display for buttons */
    .btn-download,
    .btn-save-drive {
        width: 100%;
        display: block;
        text-align: center;
        padding: 0.75rem;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 0.9rem;
        margin-bottom: 0.5rem;
        border: none;
    }

    .btn-download {
        background: var(--primary);
        color: white;
    }

    .btn-download:hover {
        background: var(--primary-dark);
        transform: translateY(-2px);
    }

    .btn-save-drive {
        background: #fff;
        color: #475569;
        border: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }

    .btn-save-drive:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
    }

    .btn-save-drive:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .resource-title {
        font-size: 0.95rem;
        font-weight: 600;
        margin-bottom: 0.3rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .resource-description {
        font-size: 0.75rem;
        color: var(--muted-foreground);
        margin-bottom: 0.3rem;
        min-height: 24px;
        max-height: 32px;
        overflow: hidden;
    }

    .resource-meta {
        font-size: 0.7rem;
        color: var(--muted-foreground);
        margin-bottom: 0.15rem;
    }

    .empty-state {
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        min-height: 60vh;
        text-align: center;
        padding: 3rem;
        color: var(--muted-foreground);
    }

    .empty-icon {
        font-size: 5rem;
        opacity: 0.5;
        margin-bottom: 1.5rem;
        animation: float 3s ease-in-out infinite;
    }

    @keyframes float {
        0%, 100% {
            transform: translateY(0);
        }
        50% {
            transform: translateY(-10px);
        }
    }

    .empty-state h3 {
        font-size: 1.75rem;
        font-weight: 600;
        color: var(--foreground);
        margin-bottom: 0.75rem;
    }

    .empty-state p {
        font-size: 1.1rem;
        color: var(--muted-foreground);
        margin: 0;
    }

    .scroll-hint {
        text-align: center;
        color: var(--muted-foreground);
        font-size: 0.875rem;
        margin-top: 0.5rem;
        font-style: italic;
    }

    /* NEW: Drive Banner Styles */
    .drive-connect-banner {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    .drive-info {
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    .drive-status {
        font-size: 0.9rem;
        color: #64748b;
    }
    .btn-connect-drive {
        background: #fff;
        border: 1px solid #cbd5e1;
        color: #475569;
        padding: 0.5rem 1rem;
        border-radius: 6px;
        font-weight: 500;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.2s;
    }
    .btn-connect-drive:hover {
        background: #f8fafc;
        border-color: #94a3b8;
    }

    /* FIXED: Disconnect button styles */
    .btn-disconnect-drive {
        background: #fee2e2;
        border: 1px solid #fca5a5;
        color: #dc2626;
        padding: 0.5rem 1rem;
        border-radius: 6px;
        font-weight: 500;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.2s;
    }

    .btn-disconnect-drive:hover {
        background: #fecaca;
        border-color: #f87171;
    }
</style>
@endsection

@section('content')
<div class="main-container">
    <div class="page-header">
        <h1 style="font-size: 2.5rem; font-weight: 700; margin-bottom: 0.5rem;">📚 Learning Resources</h1>
        <p style="font-size: 1.1rem; opacity: 0.9; margin: 0;">Access study materials from your enrolled courses</p>
    </div>

    <!-- Drive Connection Banner - FIXED: Simple FontAwesome icon instead of SVG -->
    <div class="drive-connect-banner" id="driveBanner">
        <div class="drive-info">
            <!-- FIXED: Use FontAwesome icon instead of broken SVG -->
            <i class="fab fa-google-drive" style="font-size: 2rem; color: #4285F4;"></i>
            <div>
                <h3 style="margin:0; font-size:1.1rem;">Google Drive Integration</h3>
                <p class="drive-status" id="driveStatusText">Checking connection...</p>
            </div>
        </div>
        <div id="driveButtonContainer">
            <button class="btn-connect-drive" id="connectDriveBtn" onclick="connectDrive()" style="display:none;">
                <i class="fab fa-google-drive"></i> Connect Drive
            </button>
            <button class="btn-disconnect-drive" id="disconnectDriveBtn" onclick="disconnectDrive()" style="display:none;">
                <i class="fas fa-unlink"></i> Disconnect
            </button>
        </div>
    </div>

    <!-- Resources Container -->
    <div id="resourcesContainer">
        <div style="text-align: center; padding: 4rem;">
            <div style="width: 40px; height: 40px; border: 3px solid var(--muted); border-top: 3px solid var(--primary); border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto;"></div>
            <p style="margin-top: 1rem; color: var(--muted-foreground);">Loading resources...</p>
        </div>
    </div>
</div>

<style>
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const API_BASE = '{{ url('') }}/api';
let isDriveConnected = false;

document.addEventListener('DOMContentLoaded', function() {
    checkDriveStatus();
    loadResources();

    // Check URL params for success
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('success') && urlParams.get('success') === 'drive_connected') {
        Swal.fire({ icon: 'success', title: 'Connected!', text: 'Google Drive connected successfully.' });
        window.history.replaceState({}, document.title, window.location.pathname);
    }
});

// Check Drive Status
async function checkDriveStatus() {
    try {
        const token = localStorage.getItem('auth_token');
        const res = await fetch(`${API_BASE}/student/resources/drive-status`, {
            headers: { 'Authorization': `Bearer ${token}` }
        });
        const data = await res.json();

        const connectBtn = document.getElementById('connectDriveBtn');
        const disconnectBtn = document.getElementById('disconnectDriveBtn');
        const statusText = document.getElementById('driveStatusText');

        if (data.status === 'success' && data.connected) {
            isDriveConnected = true;
            statusText.innerHTML = `Connected as <strong>${data.email}</strong>`;

            // Show disconnect button, hide connect button
            connectBtn.style.display = 'none';
            disconnectBtn.style.display = 'flex';
        } else {
            isDriveConnected = false;
            statusText.innerHTML = 'Connect your Google Drive to save resources directly.';

            // Show connect button, hide disconnect button
            connectBtn.style.display = 'flex';
            disconnectBtn.style.display = 'none';
        }
    } catch (e) {
        console.error('Drive status error:', e);
    }
}

// Connect Drive
async function connectDrive() {
    try {
        const token = localStorage.getItem('auth_token');
        const res = await fetch(`${API_BASE}/student/resources/drive/connect`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });
        const data = await res.json();
        if (data.status === 'success') {
            window.location.href = data.auth_url;
        } else {
            Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'Failed to connect' });
        }
    } catch (e) {
        console.error('Connect error:', e);
        Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to connect. Please try again.' });
    }
}

// Disconnect Drive
async function disconnectDrive() {
    const confirm = await Swal.fire({
        title: 'Disconnect Google Drive?',
        text: 'You will no longer be able to save resources to your Drive.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        confirmButtonText: 'Yes, disconnect'
    });

    if (!confirm.isConfirmed) return;

    try {
        const token = localStorage.getItem('auth_token');
        const res = await fetch(`${API_BASE}/student/resources/disconnect-drive`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });
        const data = await res.json();

        if (data.status === 'success') {
            Swal.fire({ icon: 'success', title: 'Disconnected', text: 'Google Drive disconnected successfully.', timer: 2000 });
            checkDriveStatus();
        } else {
            Swal.fire({ icon: 'error', title: 'Error', text: data.message });
        }
    } catch (e) {
        console.error('Disconnect error:', e);
        Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to disconnect.' });
    }
}

// Load Resources
async function loadResources() {
    const container = document.getElementById('resourcesContainer');

    try {
        const token = localStorage.getItem('auth_token');
        if (!token) {
            showError('Authentication required. Please login again.');
            return;
        }

        console.log('Loading resources...');
        const response = await fetch(`${API_BASE}/student/resources`, {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });

        console.log('Resources response:', response.status);

        if (!response.ok) {
            const errorText = await response.text();
            console.error('Resources error:', response.status, errorText);
            showError(`Failed to load resources (Error ${response.status})`);
            return;
        }

        const result = await response.json();
        console.log('Resources data:', result);

        if (result.status === 'success') {
            const courses = result.courses || [];
            console.log('Courses:', courses.length);

            if (courses.length === 0) {
                showEmptyState('No resources available', 'Your lecturers haven\'t uploaded any resources yet.');
            } else {
                displayResources(courses);
            }
        } else {
            console.error('API error:', result);
            showError(result.message || 'Failed to load resources');
        }
    } catch (error) {
        console.error('Load exception:', error);
        showError('Network error. Please check your connection.');
    }
}

function displayResources(courses) {
    const container = document.getElementById('resourcesContainer');
    let html = '';

    courses.forEach(course => {
        const resources = course.resources || [];

        html += `
            <div class="resources-section">
                <div class="course-header">
                    <i class="fas fa-book" style="color: var(--primary); font-size: 1.5rem;"></i>
                    <h3 style="margin: 0; font-size: 1.25rem;">${escapeHtml(course.title)}</h3>
                    <span style="color: var(--muted-foreground);">(${resources.length} resource${resources.length !== 1 ? 's' : ''})</span>
                </div>
                <div class="resources-horizontal">
        `;

        resources.forEach(resource => {
            html += `
                <div class="resource-card" style="height: auto; min-height: 220px;">
                    <div class="file-icon">${getFileIcon(resource.file_type)}</div>
                    <h4 class="resource-title" title="${escapeHtml(resource.title)}">${escapeHtml(resource.title)}</h4>
                    <p class="resource-description">${escapeHtml(resource.description || 'No description')}</p>
                    <div style="border-top: 1px solid var(--border); padding-top: 0.5rem; margin-bottom: 0.5rem;">
                        <p class="resource-meta"><i class="fas fa-file-alt"></i> ${escapeHtml(resource.file_name)}</p>
                        <p class="resource-meta"><i class="fas fa-database"></i> ${formatFileSize(resource.file_size)}</p>
                    </div>
                    <button onclick="downloadResource('${resource.id}')" class="btn-download">
                        <i class="fas fa-download"></i> Download
                    </button>
                    <button onclick="saveToDrive('${resource.id}', this)" class="btn-save-drive">
                        <i class="fab fa-google-drive"></i> Save to Drive
                    </button>
                </div>
            `;
        });

        html += `
                </div>
                <p class="scroll-hint">💡 Scroll horizontally to see more</p>
            </div>
        `;
    });

    container.innerHTML = html;
    addHorizontalScrollSupport();
}

function addHorizontalScrollSupport() {
    document.querySelectorAll('.resources-horizontal').forEach(container => {
        container.addEventListener('wheel', (e) => {
            if (e.deltaY !== 0) {
                e.preventDefault();
                container.scrollLeft += e.deltaY;
            }
        });
    });
}

async function downloadResource(resourceId) {
    try {
        const token = localStorage.getItem('auth_token');
        const response = await fetch(`${API_BASE}/student/resources/${resourceId}/download`, {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });

        const result = await response.json();

        if (response.ok && result.status === 'success') {
            window.open(result.drive_url, '_blank');
        } else {
            Swal.fire({ icon: 'error', title: 'Download Failed', text: result.message });
        }
    } catch (error) {
        console.error('Download error:', error);
        Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to download.' });
    }
}

async function saveToDrive(resourceId, btnElement) {
    if (!isDriveConnected) {
        Swal.fire({ icon: 'info', title: 'Connect Drive', text: 'Please connect your Google Drive first.' });
        return;
    }

    const originalText = btnElement.innerHTML;
    btnElement.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
    btnElement.disabled = true;

    try {
        const token = localStorage.getItem('auth_token');
        const res = await fetch(`${API_BASE}/student/resources/${resourceId}/save-to-drive`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });
        const data = await res.json();

        if (data.status === 'success') {
            btnElement.innerHTML = '<i class="fas fa-check"></i> Saved';
            Swal.fire({
                icon: 'success',
                title: 'Saved!',
                text: 'File saved to "Smart LMS Resources" in your Drive.',
                timer: 2000,
                showConfirmButton: false
            });
        } else {
            throw new Error(data.message);
        }
    } catch (e) {
        Swal.fire({ icon: 'error', title: 'Error', text: e.message || 'Failed to save' });
        btnElement.innerHTML = originalText;
        btnElement.disabled = false;
    }
}

function getFileIcon(fileType) {
    const icons = {
        'pdf': '<i class="fas fa-file-pdf" style="color: #ef4444;"></i>',
        'doc': '<i class="fas fa-file-word" style="color: #2563eb;"></i>',
        'docx': '<i class="fas fa-file-word" style="color: #2563eb;"></i>',
        'ppt': '<i class="fas fa-file-powerpoint" style="color: #f59e0b;"></i>',
        'pptx': '<i class="fas fa-file-powerpoint" style="color: #f59e0b;"></i>',
        'xls': '<i class="fas fa-file-excel" style="color: #10b981;"></i>',
        'xlsx': '<i class="fas fa-file-excel" style="color: #10b981;"></i>',
        'zip': '<i class="fas fa-file-archive" style="color: #8b5cf6;"></i>',
        'mp4': '<i class="fas fa-file-video" style="color: #ec4899;"></i>',
        'jpg': '<i class="fas fa-file-image" style="color: #06b6d4;"></i>',
        'png': '<i class="fas fa-file-image" style="color: #06b6d4;"></i>'
    };
    return icons[fileType?.toLowerCase()] || '<i class="fas fa-file" style="color: var(--muted-foreground);"></i>';
}

function formatFileSize(bytes) {
    if (!bytes || bytes === 0) return '0 B';
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(2) + ' KB';
    return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
}

function escapeHtml(unsafe) {
    if (!unsafe) return '';
    return unsafe.toString()
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

function showEmptyState(title, message) {
    const container = document.getElementById('resourcesContainer');
    container.innerHTML = `
        <div class="empty-state">
            <div class="empty-icon">📂</div>
            <h3>${escapeHtml(title)}</h3>
            <p>${escapeHtml(message)}</p>
        </div>
    `;
}

function showError(message) {
    const container = document.getElementById('resourcesContainer');
    container.innerHTML = `
        <div class="empty-state">
            <div class="empty-icon" style="color: var(--danger);">⚠️</div>
            <h3>Error Loading Resources</h3>
            <p>${escapeHtml(message)}</p>
            <button onclick="loadResources()" class="btn-download" style="margin-top: 1rem; display: inline-block;">
                <i class="fas fa-redo"></i> Try Again
            </button>
        </div>
    `;
}
</script>
@endsection
