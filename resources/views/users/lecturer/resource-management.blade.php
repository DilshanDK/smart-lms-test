@extends('layouts.app')

@section('title', 'Resource Manager - Smart LMS')

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

    .upload-section {
        background: var(--surface);
        border-radius: var(--radius);
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: var(--shadow-sm);
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
        min-width: 280px;
        max-width: 280px;
        flex-shrink: 0;
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 1.5rem;
        transition: all 0.3s ease;
    }

    .resource-card:hover {
        transform: translateY(-3px);
        box-shadow: var(--shadow-lg);
        border-color: var(--primary);
    }

    .file-icon {
        font-size: 3rem;
        text-align: center;
        margin-bottom: 1rem;
    }

    .btn-upload {
        background: var(--primary);
        color: white;
        border: none;
        padding: 1rem 2rem;
        border-radius: var(--radius);
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-upload:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }

    .drop-zone {
        border: 2px dashed var(--border);
        border-radius: var(--radius);
        padding: 2rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .drop-zone:hover {
        border-color: var(--primary);
        background: var(--muted);
    }

    .drop-zone.dragover {
        border-color: var(--primary);
        background: var(--muted);
    }

    .drive-status-banner {
        background: var(--surface);
        padding: 1rem;
        border-radius: var(--radius);
        margin-bottom: 2rem;
        box-shadow: var(--shadow-sm);
        border-left: 4px solid var(--danger);
    }

    .drive-status-banner.connected {
        border-left-color: var(--success);
    }

    /* FIXED: Center empty state */
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
</style>
@endsection

@section('content')
<div class="main-container">
    <div class="page-header">
        <h1 style="font-size: 2.5rem; font-weight: 700; margin-bottom: 0.5rem;">Resource Manager</h1>
        <p style="font-size: 1.1rem; opacity: 0.9; margin: 0;">Upload and manage course materials (Powered by Google Drive)</p>
    </div>

    <!-- Google Drive Status Banner -->
    <div class="drive-status-banner" id="driveStatusBanner">
        <div class="loading">Checking Google Drive connection...</div>
    </div>

    <!-- Upload Section -->
    <div class="upload-section" id="uploadSection">
        <h3 style="margin-bottom: 1.5rem;">Upload New Resources</h3>
        <form id="uploadForm">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
                <div>
                    <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Course</label>
                    <select id="courseSelect" class="form-control" required style="width: 100%; padding: 0.75rem; border: 2px solid var(--border); border-radius: var(--radius);">
                        <option value="">Select Course</option>
                    </select>
                </div>
                <div>
                    <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Title</label>
                    <input type="text" id="titleInput" class="form-control" required style="width: 100%; padding: 0.75rem; border: 2px solid var(--border); border-radius: var(--radius);" placeholder="Resource title">
                </div>
            </div>
            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Description</label>
                <textarea id="descriptionInput" class="form-control" rows="3" style="width: 100%; padding: 0.75rem; border: 2px solid var(--border); border-radius: var(--radius);" placeholder="Optional description"></textarea>
            </div>
            <div class="drop-zone" id="dropZone">
                <i class="fas fa-cloud-upload-alt" style="font-size: 3rem; color: var(--muted-foreground); margin-bottom: 1rem;"></i>
                <p style="font-size: 1.1rem; font-weight: 600; margin-bottom: 0.5rem;">Drag & Drop files here</p>
                <p style="color: var(--muted-foreground); margin-bottom: 1rem;">or click to browse</p>
                <input type="file" id="fileInput" multiple style="display: none;">
                <button type="button" class="btn-upload" onclick="document.getElementById('fileInput').click()">
                    <i class="fas fa-folder-open me-2"></i>Browse Files
                </button>
            </div>
            <div id="filesList" style="margin-top: 1rem;"></div>
            <button type="submit" class="btn-upload" style="margin-top: 1rem; width: 100%;">
                <i class="fas fa-upload me-2"></i>Upload Files
            </button>
        </form>
    </div>

    <!-- Filter Section -->
    <div style="background: var(--surface); padding: 1rem 2rem; border-radius: var(--radius); margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center;">
        <h3 style="margin: 0;">My Resources</h3>
        <div style="display: flex; gap: 1rem; align-items: center;">
            <label style="margin: 0; font-weight: 500;">Filter by Course:</label>
            <select id="filterCourse" class="form-control" style="width: 300px; padding: 0.5rem; border: 2px solid var(--border); border-radius: var(--radius);">
                <option value="">All Courses</option>
            </select>
        </div>
    </div>

    <!-- Resources Container -->
    <div id="resourcesContainer">
        <div style="text-align: center; padding: 4rem;">
            <div style="width: 40px; height: 40px; border: 3px solid var(--muted); border-top: 3px solid var(--primary); border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto;"></div>
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
let selectedFiles = [];
let driveConnected = false;

document.addEventListener('DOMContentLoaded', function() {
    checkDriveStatus();
    loadCourses();
    loadResources();
    setupDropZone();
    setupFileInput();
    setupFilter();
    setupUploadForm();
});

async function checkDriveStatus() {
    try {
        const token = localStorage.getItem('auth_token');
        const response = await fetch(`${API_BASE}/lecturer/resources/drive-status`, {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });
        const result = await response.json();

        const banner = document.getElementById('driveStatusBanner');
        const uploadSection = document.getElementById('uploadSection');

        if (result.connected) {
            driveConnected = true;
            banner.className = 'drive-status-banner connected';
            banner.innerHTML = `
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fab fa-google-drive" style="color: var(--success); font-size: 1.25rem;"></i>
                    <span style="font-weight: 600;">Google Drive Connected</span>
                </div>
            `;
            // Enable upload form
            uploadSection.style.display = 'block';
            uploadSection.style.opacity = '1';
            uploadSection.style.pointerEvents = 'auto';
        } else {
            driveConnected = false;
            banner.innerHTML = `
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-exclamation-triangle" style="color: var(--danger);"></i>
                        <span style="font-weight: 600;">Google Drive Not Connected</span>
                    </div>
                    <span style="font-size: 0.875rem; color: var(--muted-foreground);">Please contact admin to connect Google Drive</span>
                </div>
            `;
            // Disable upload form
            uploadSection.style.opacity = '0.5';
            uploadSection.style.pointerEvents = 'none';
            uploadSection.style.position = 'relative';

            // Add overlay message
            if (!document.getElementById('disabledOverlay')) {
                const overlay = document.createElement('div');
                overlay.id = 'disabledOverlay';
                overlay.style.cssText = `
                    position: absolute;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    background: rgba(255, 255, 255, 0.9);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    border-radius: var(--radius);
                    z-index: 10;
                `;
                overlay.innerHTML = `
                    <div style="text-align: center; padding: 2rem;">
                        <i class="fas fa-lock" style="font-size: 3rem; color: var(--danger); margin-bottom: 1rem;"></i>
                        <h3 style="color: var(--foreground); margin-bottom: 0.5rem;">Upload Disabled</h3>
                        <p style="color: var(--muted-foreground);">Google Drive must be connected by admin to upload resources</p>
                    </div>
                `;
                uploadSection.style.position = 'relative';
                uploadSection.appendChild(overlay);
            }
        }
    } catch (error) {
        console.error('Error checking Drive status:', error);
    }
}

async function loadCourses() {
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
            const courses = result.courses || [];
            const courseSelect = document.getElementById('courseSelect');
            const filterCourse = document.getElementById('filterCourse');
            courses.forEach(course => {
                courseSelect.innerHTML += `<option value="${course.id}">${course.title}</option>`;
                filterCourse.innerHTML += `<option value="${course.id}">${course.title}</option>`;
            });
        }
    } catch (error) {
        console.error('Error loading courses:', error);
    }
}

async function loadResources(courseId = '') {
    try {
        const token = localStorage.getItem('auth_token');
        const url = courseId ? `${API_BASE}/lecturer/resources?course_id=${courseId}` : `${API_BASE}/lecturer/resources`;
        const response = await fetch(url, {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });
        const result = await response.json();
        if (response.ok && result.status === 'success') {
            displayResources(result.resources || []);
        }
    } catch (error) {
        console.error('Error loading resources:', error);
    }
}

function displayResources(resources) {
    const container = document.getElementById('resourcesContainer');
    if (resources.length === 0) {
        container.innerHTML = `
            <div class="empty-state">
                <div class="empty-icon">📁</div>
                <h3>No resources uploaded yet</h3>
                <p>Upload your first resource to get started!</p>
            </div>
        `;
        return;
    }

    // Group resources by course
    const groupedResources = {};
    resources.forEach(resource => {
        const courseId = resource.course_id || 'unknown';
        const courseName = resource.course_name || 'Unknown Course';

        if (!groupedResources[courseId]) {
            groupedResources[courseId] = {
                courseName: courseName,
                resources: []
            };
        }
        groupedResources[courseId].resources.push(resource);
    });

    let html = '';

    // Display each course group with horizontal scroll
    Object.keys(groupedResources).forEach(courseId => {
        const group = groupedResources[courseId];

        html += `
            <div class="resources-section">
                <div class="course-header">
                    <i class="fas fa-book" style="color: var(--primary); font-size: 1.5rem;"></i>
                    <h3 style="margin: 0; color: var(--foreground); font-size: 1.25rem; font-weight: 600;">
                        ${group.courseName}
                    </h3>
                    <span style="color: var(--muted-foreground); font-size: 0.875rem;">
                        (${group.resources.length} file${group.resources.length !== 1 ? 's' : ''})
                    </span>
                </div>

                <div class="resources-horizontal">
        `;

        group.resources.forEach(resource => {
            html += `
                <div class="resource-card">
                    <div class="file-icon">${getFileIcon(resource.file_type)}</div>
                    <h4 style="margin-bottom: 0.5rem; font-size: 1rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="${resource.title}">${resource.title}</h4>
                    <p style="color: var(--primary); font-size: 0.75rem; margin-bottom: 0.5rem; font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="${resource.file_name}">
                        <i class="fas fa-file-alt me-1"></i>${resource.file_name}
                    </p>
                    <p style="color: var(--muted-foreground); font-size: 0.75rem; margin-bottom: 1rem;">${formatFileSize(resource.file_size)}</p>
                    <div style="display: flex; gap: 0.5rem;">
                        <button onclick="downloadResource('${resource.encrypted_id}', '${resource.file_name}')" class="btn-upload" style="flex: 1; text-align: center; text-decoration: none; padding: 0.5rem; font-size: 0.875rem;">
                            <i class="fas fa-download"></i> Download
                        </button>
                        <button onclick="deleteResource('${resource.encrypted_id}')" style="background: var(--danger); color: white; border: none; padding: 0.5rem; border-radius: var(--radius); cursor: pointer;">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            `;
        });

        html += `
                </div>
            </div>
        `;
    });

    container.innerHTML = html;
}

async function downloadResource(encryptedId, fileName) {
    if (!driveConnected) {
        Swal.fire({
            icon: 'error',
            title: 'Google Drive Not Connected',
            text: 'Please contact admin to connect Google Drive'
        });
        return;
    }

    try {
        const token = localStorage.getItem('auth_token');
        const response = await fetch(`${API_BASE}/lecturer/download-resource/${encryptedId}`, {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });
        const result = await response.json();

        if (result.status === 'success') {
            window.open(result.drive_url, '_blank');
        }
    } catch (error) {
        Swal.fire('Error', 'Failed to download file', 'error');
    }
}

function getFileIcon(fileType) {
    if (fileType.includes('pdf')) return '<i class="fas fa-file-pdf" style="color: #ef4444;"></i>';
    if (fileType.includes('word') || fileType.includes('document')) return '<i class="fas fa-file-word" style="color: #2563eb;"></i>';
    if (fileType.includes('excel') || fileType.includes('spreadsheet')) return '<i class="fas fa-file-excel" style="color: #10b981;"></i>';
    if (fileType.includes('powerpoint') || fileType.includes('presentation')) return '<i class="fas fa-file-powerpoint" style="color: #f59e0b;"></i>';
    if (fileType.includes('image')) return '<i class="fas fa-file-image" style="color: #8b5cf6;"></i>';
    if (fileType.includes('video')) return '<i class="fas fa-file-video" style="color: #ec4899;"></i>';
    return '<i class="fas fa-file" style="color: var(--muted-foreground);"></i>';
}

function formatFileSize(bytes) {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(2) + ' KB';
    return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
}

function setupDropZone() {
    const dropZone = document.getElementById('dropZone');
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, preventDefaults, false);
    });
    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }
    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, () => dropZone.classList.add('dragover'), false);
    });
    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, () => dropZone.classList.remove('dragover'), false);
    });
    dropZone.addEventListener('drop', handleDrop, false);
}

function handleDrop(e) {
    const dt = e.dataTransfer;
    const files = dt.files;
    handleFiles(files);
}

function setupFileInput() {
    document.getElementById('fileInput').addEventListener('change', function(e) {
        handleFiles(this.files);
    });
}

function handleFiles(files) {
    selectedFiles = Array.from(files);
    displaySelectedFiles();
}

function displaySelectedFiles() {
    const filesList = document.getElementById('filesList');
    if (selectedFiles.length === 0) {
        filesList.innerHTML = '';
        return;
    }
    filesList.innerHTML = '<h4 style="margin-top: 1rem; margin-bottom: 0.5rem;">Selected Files:</h4>' +
        selectedFiles.map((file, index) => `
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.5rem; background: var(--muted); border-radius: var(--radius); margin-bottom: 0.5rem;">
                <span><i class="fas fa-file me-2"></i>${file.name} (${formatFileSize(file.size)})</span>
                <button onclick="removeFile(${index})" style="background: var(--danger); color: white; border: none; padding: 0.25rem 0.5rem; border-radius: var(--radius); cursor: pointer;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `).join('');
}

function removeFile(index) {
    selectedFiles.splice(index, 1);
    displaySelectedFiles();
}

function setupFilter() {
    document.getElementById('filterCourse').addEventListener('change', function() {
        loadResources(this.value);
    });
}

function setupUploadForm() {
    document.getElementById('uploadForm').addEventListener('submit', async function(e) {
        e.preventDefault();

        if (!driveConnected) {
            Swal.fire({
                icon: 'error',
                title: 'Google Drive Not Connected',
                text: 'Please contact admin to connect Google Drive'
            });
            return;
        }

        const courseId = document.getElementById('courseSelect').value;
        const title = document.getElementById('titleInput').value;
        const description = document.getElementById('descriptionInput').value;

        // Validation
        if (!courseId || !title) {
            Swal.fire({
                icon: 'warning',
                title: 'Missing Information',
                text: 'Please select a course and enter a title'
            });
            return;
        }

        if (selectedFiles.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'No Files Selected',
                text: 'Please select at least one file to upload'
            });
            return;
        }

        // FIXED: Create FormData and append files correctly
        const formData = new FormData();
        formData.append('course_id', courseId);
        formData.append('title', title);
        formData.append('description', description);

        // Append each file individually with the key 'files[]'
        for (let i = 0; i < selectedFiles.length; i++) {
            formData.append('files[]', selectedFiles[i], selectedFiles[i].name);
            console.log(`Appending file ${i + 1}: ${selectedFiles[i].name}, size: ${selectedFiles[i].size} bytes`);
        }

        // Debug: Log FormData contents
        console.log('FormData entries:');
        for (let pair of formData.entries()) {
            if (pair[1] instanceof File) {
                console.log(`${pair[0]}: File(${pair[1].name}, ${pair[1].size} bytes)`);
            } else {
                console.log(`${pair[0]}: ${pair[1]}`);
            }
        }

        // Show loading state
        const submitButton = document.querySelector('#uploadForm button[type="submit"]');
        const originalButtonText = submitButton.innerHTML;
        submitButton.disabled = true;
        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Uploading...';

        try {
            const token = localStorage.getItem('auth_token');

            console.log('Sending upload request to:', `${API_BASE}/lecturer/upload-resources`);
            console.log('Total files to upload:', selectedFiles.length);

            const response = await fetch(`${API_BASE}/lecturer/upload-resources`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                    // CRITICAL: DO NOT set Content-Type - let browser set it with boundary
                },
                body: formData
            });

            console.log('Response status:', response.status);
            const result = await response.json();
            console.log('Response data:', result);

            if (response.ok && result.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: result.message,
                    timer: 3000,
                    showConfirmButton: false
                });

                // Reset form
                document.getElementById('uploadForm').reset();
                selectedFiles = [];
                displaySelectedFiles();
                loadResources();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Upload Failed',
                    text: result.message || 'An error occurred during upload',
                    confirmButtonText: 'OK'
                });
            }
        } catch (error) {
            console.error('Upload error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Network Error',
                text: 'Failed to connect to server. Please check your connection.',
                confirmButtonText: 'OK'
            });
        } finally {
            // Restore button state
            submitButton.disabled = false;
            submitButton.innerHTML = originalButtonText;
        }
    });
}

async function deleteResource(encryptedId) {
    const confirm = await Swal.fire({
        title: 'Are you sure?',
        text: 'This will permanently delete the resource',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        confirmButtonText: 'Yes, delete it'
    });

    if (!confirm.isConfirmed) return;

    try {
        const token = localStorage.getItem('auth_token');
        const response = await fetch(`${API_BASE}/lecturer/delete-resource/${encryptedId}`, {
            method: 'DELETE',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });
        const result = await response.json();
        if (response.ok && result.status === 'success') {
            Swal.fire({ icon: 'success', title: result.message });
            loadResources();
        } else {
            Swal.fire({ icon: 'error', title: 'Delete failed', text: result.message });
        }
    } catch (error) {
        console.error('Error deleting:', error);
        Swal.fire({ icon: 'error', title: 'Delete failed', text: 'Network error' });
    }
}
</script>
@endsection
