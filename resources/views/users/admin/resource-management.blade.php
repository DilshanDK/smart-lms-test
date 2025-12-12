@extends('layouts.app')

@section('title', 'Resource Management - Smart LMS')

@section('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap');

    :root {
        --primary: #3b82f6;
        --primary-dark: #2563eb;
        --secondary: #6b7280;
        --background: #f9fafb;
        --surface: #ffffff;
        --foreground: #1f2937;
        --muted: #e5e7eb;
        --muted-foreground: #6b7280;
        --success: #10b981;
        --danger: #ef4444;
        --radius: 8px;
        --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
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

    .connection-card {
        background: var(--surface);
        border-radius: var(--radius);
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: var(--shadow-md);
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        border-radius: 12px;
        font-weight: 600;
        font-size: 0.875rem;
    }

    .status-connected {
        background: rgba(16, 185, 129, 0.1);
        color: #059669;
    }

    .status-disconnected {
        background: rgba(239, 68, 68, 0.1);
        color: #dc2626;
    }

    .btn-action {
        background: var(--primary);
        color: white;
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: var(--radius);
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-action:hover {
        background: var(--primary-dark);
        transform: translateY(-2px);
    }

    .btn-danger {
        background: var(--danger);
    }

    .btn-danger:hover {
        background: #dc2626;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: var(--surface);
        padding: 1.5rem;
        border-radius: var(--radius);
        box-shadow: var(--shadow-md);
    }

    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        color: var(--primary);
        margin-bottom: 0.5rem;
    }

    .stat-label {
        color: var(--muted-foreground);
        font-size: 0.875rem;
    }

    .drive-email {
        font-size: 0.875rem;
        color: var(--muted-foreground);
        margin-top: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .drive-email i {
        color: var(--primary);
    }
</style>
@endsection

@section('content')
<div class="container">
    <div class="page-header">
        <h1 style="font-size: 2rem; font-weight: 700; margin-bottom: 0.5rem;">📂 Resource Management</h1>
        <p style="font-size: 1rem; opacity: 0.9; margin: 0;">Manage Google Drive integration for file storage</p>
    </div>

    <!-- Google Drive Connection -->
    <div class="connection-card">
        <h3 style="margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
            <i class="fab fa-google-drive" style="color: var(--primary);"></i>
            Google Drive Connection
        </h3>
        <div id="driveStatus">
            <div class="loading">Checking connection...</div>
        </div>
        <div id="driveActions" style="margin-top: 1rem; display: none;">
            <button id="connectDriveBtn" class="btn-action" onclick="connectGoogleDrive()">
                <i class="fab fa-google-drive"></i> Connect Google Drive
            </button>
            <button id="disconnectDriveBtn" class="btn-action btn-danger" onclick="disconnectGoogleDrive()" style="display:none;">
                <i class="fas fa-unlink"></i> Disconnect
            </button>
        </div>
    </div>

    <!-- Analytics -->
    <div id="analyticsSection" style="display: none;">
        <h3 style="margin-bottom: 1.5rem;">Resource Analytics</h3>
        <div class="stats-grid" id="statsGrid">
            <!-- Populated by JavaScript -->
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const API_BASE = '{{ url('') }}/api/admin';

document.addEventListener('DOMContentLoaded', function() {
    checkDriveStatus();
    loadAnalytics();
    checkUrlParams();
});

function checkUrlParams() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('success') === 'connected') {
        Swal.fire({
            icon: 'success',
            title: 'Connected!',
            text: 'Google Drive connected successfully',
            confirmButtonColor: '#3b82f6'
        }).then(() => {
            window.history.replaceState({}, document.title, window.location.pathname);
            checkDriveStatus();
            loadAnalytics();
        });
    } else if (urlParams.get('error')) {
        Swal.fire({
            icon: 'error',
            title: 'Connection Failed',
            text: 'Failed to connect Google Drive: ' + urlParams.get('error'),
            confirmButtonColor: '#ef4444'
        });
    }
}

async function checkDriveStatus() {
    try {
        const token = localStorage.getItem('auth_token');
        const response = await fetch(`${API_BASE}/google-drive/status`, {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });
        const result = await response.json();

        const statusDiv = document.getElementById('driveStatus');
        const actionsDiv = document.getElementById('driveActions');
        const connectBtn = document.getElementById('connectDriveBtn');
        const disconnectBtn = document.getElementById('disconnectDriveBtn');

        actionsDiv.style.display = 'block';

        if (result.connected) {
            // Fetch Google account email
            let driveEmail = 'Loading...';
            let driveName = '';

            statusDiv.innerHTML = `
                <div class="status-badge status-connected">
                    <i class="fas fa-check-circle"></i>
                    Connected to Google Drive
                </div>
                <div class="drive-email">
                    <i class="fas fa-spinner fa-spin"></i>
                    <span>Fetching account details...</span>
                </div>
                <p style="margin-top: 0.5rem; font-size: 0.875rem; color: var(--muted-foreground);">
                    Connected on ${result.connected_at || 'N/A'}
                </p>
            `;

            connectBtn.style.display = 'none';
            disconnectBtn.style.display = 'inline-block';
            document.getElementById('analyticsSection').style.display = 'block';

            // Fetch account info
            try {
                const accountResponse = await fetch(`${API_BASE}/google-drive/account-info`, {
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Accept': 'application/json'
                    }
                });

                if (accountResponse.ok) {
                    const accountData = await accountResponse.json();
                    if (accountData.status === 'success') {
                        driveEmail = accountData.email || 'Unknown Email';
                        driveName = accountData.name || '';

                        // Update with actual email
                        statusDiv.innerHTML = `
                            <div class="status-badge status-connected">
                                <i class="fas fa-check-circle"></i>
                                Connected to Google Drive
                            </div>
                            <div class="drive-email">
                                <i class="fas fa-user-circle"></i>
                                <span><strong>Account:</strong> ${driveName ? driveName + ' (' + driveEmail + ')' : driveEmail}</span>
                            </div>
                            <p style="margin-top: 0.5rem; font-size: 0.875rem; color: var(--muted-foreground);">
                                Connected on ${result.connected_at || 'N/A'}
                            </p>
                        `;
                    } else {
                        throw new Error('Failed to fetch account info');
                    }
                } else {
                    throw new Error('Account info request failed');
                }
            } catch (accountError) {
                console.error('Error fetching Drive account info:', accountError);
                // Show connection without email if fetch fails
                statusDiv.innerHTML = `
                    <div class="status-badge status-connected">
                        <i class="fas fa-check-circle"></i>
                        Connected to Google Drive
                    </div>
                    <div class="drive-email" style="color: var(--warning);">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span>Connected (Unable to fetch account details)</span>
                    </div>
                    <p style="margin-top: 0.5rem; font-size: 0.875rem; color: var(--muted-foreground);">
                        Connected on ${result.connected_at || 'N/A'}
                    </p>
                `;
            }
        } else {
            statusDiv.innerHTML = `
                <div class="status-badge status-disconnected">
                    <i class="fas fa-times-circle"></i>
                    Not Connected
                </div>
                <p style="margin-top: 0.5rem; font-size: 0.875rem; color: var(--muted-foreground);">
                    Connect Google Drive to enable file storage for lecturers
                </p>
            `;
            connectBtn.style.display = 'inline-block';
            disconnectBtn.style.display = 'none';
            document.getElementById('analyticsSection').style.display = 'none';
        }
    } catch (error) {
        console.error('Error checking Drive status:', error);
        const statusDiv = document.getElementById('driveStatus');
        statusDiv.innerHTML = `
            <div class="status-badge status-disconnected">
                <i class="fas fa-exclamation-triangle"></i>
                Error Checking Connection
            </div>
            <p style="margin-top: 0.5rem; font-size: 0.875rem; color: var(--danger);">
                ${error.message || 'Failed to check Google Drive status'}
            </p>
        `;
    }
}

async function connectGoogleDrive() {
    try {
        const token = localStorage.getItem('auth_token');
        const response = await fetch(`${API_BASE}/google-drive/connect`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });
        const result = await response.json();

        if (result.status === 'success') {
            window.location.href = result.auth_url;
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Connection Failed',
                text: result.message,
                confirmButtonColor: '#ef4444'
            });
        }
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to initiate Google Drive connection',
            confirmButtonColor: '#ef4444'
        });
    }
}

async function disconnectGoogleDrive() {
    const result = await Swal.fire({
        title: 'Disconnect Google Drive?',
        text: 'This will prevent lecturers from uploading new resources',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        confirmButtonText: 'Yes, disconnect'
    });

    if (!result.isConfirmed) return;

    try {
        const token = localStorage.getItem('auth_token');
        const response = await fetch(`${API_BASE}/google-drive/disconnect`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });
        const result = await response.json();

        if (result.status === 'success') {
            Swal.fire({
                icon: 'success',
                title: 'Disconnected!',
                text: result.message,
                confirmButtonColor: '#3b82f6'
            });
            checkDriveStatus();
            loadAnalytics();
        }
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to disconnect Google Drive',
            confirmButtonColor: '#ef4444'
        });
    }
}

async function loadAnalytics() {
    try {
        const token = localStorage.getItem('auth_token');
        const response = await fetch(`${API_BASE}/resources/analytics`, {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });
        const result = await response.json();

        if (result.status === 'success') {
            const analytics = result.analytics;
            document.getElementById('statsGrid').innerHTML = `
                <div class="stat-card">
                    <div class="stat-value">${analytics.total_resources}</div>
                    <div class="stat-label">Total Resources</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">${analytics.total_size_mb} MB</div>
                    <div class="stat-label">Total Storage Used</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">${analytics.resources_by_course.length}</div>
                    <div class="stat-label">Courses with Resources</div>
                </div>
            `;
        }
    } catch (error) {
        console.error('Error loading analytics:', error);
    }
}
</script>
@endsection
