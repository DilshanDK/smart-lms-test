@extends('layouts.app')

@section('title', 'System Settings - Smart LMS')

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
        --muted-foreground: #4b5563;
        --accent: #f97316;
        --border: #d1d5db;
        --success: #10b981;
        --danger: #ef4444;
        --radius: 8px;
        --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }

    * {
        font-family: 'Poppins', sans-serif;
    }

    body {
        background: var(--background);
    }

    .page-header {
        background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        color: white;
        padding: 2rem;
        border-radius: var(--radius);
        margin-bottom: 2rem;
        box-shadow: var(--shadow-md);
    }

    .settings-section {
        background: var(--surface);
        border-radius: var(--radius);
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: var(--shadow-md);
    }

    .section-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--foreground);
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .connection-card {
        border: 2px solid var(--border);
        border-radius: var(--radius);
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        transition: all 0.3s ease;
    }

    .connection-card.connected {
        border-color: var(--success);
        background: #f0fdf4;
    }

    .connection-card.disconnected {
        border-color: var(--border);
        background: var(--muted);
    }

    .connection-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
    }

    .connection-title {
        font-size: 1.25rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .status-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.875rem;
        font-weight: 600;
    }

    .status-connected {
        background: var(--success);
        color: white;
    }

    .status-disconnected {
        background: var(--muted);
        color: var(--secondary);
    }

    .connection-details {
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid var(--border);
    }

    .detail-row {
        display: flex;
        justify-content: space-between;
        padding: 0.5rem 0;
        font-size: 0.875rem;
    }

    .detail-label {
        color: var(--secondary);
        font-weight: 500;
    }

    .detail-value {
        color: var(--foreground);
        font-weight: 600;
    }

    .btn-connect {
        background: var(--primary);
        color: white;
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: var(--radius);
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-connect:hover {
        background: var(--primary-dark);
        transform: translateY(-2px);
    }

    .btn-disconnect {
        background: var(--danger);
        color: white;
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: var(--radius);
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-disconnect:hover {
        background: #dc2626;
        transform: translateY(-2px);
    }

    .loading {
        text-align: center;
        padding: 2rem;
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
</style>
@endsection

@section('content')
<div class="container" style="max-width: 1200px; margin: 0 auto; padding: 2rem;">
    <!-- Page Header -->
    <div class="page-header">
        <h1 style="font-size: 2.5rem; font-weight: 700; margin-bottom: 0.5rem;">⚙️ System Settings</h1>
        <p style="font-size: 1.1rem; opacity: 0.9; margin: 0;">Manage external service integrations</p>
    </div>

    <!-- YouTube Integration -->
    <div class="settings-section">
        <h2 class="section-title">
            <i class="fab fa-youtube" style="color: #ff0000;"></i>
            YouTube Integration
        </h2>
        <div id="youtubeConnectionCard" class="loading">
            <div class="spinner"></div>
            <p>Loading YouTube connection status...</p>
        </div>
    </div>

    <!-- Google Drive Integration -->
    <div class="settings-section">
        <h2 class="section-title">
            <i class="fab fa-google-drive" style="color: #4285f4;"></i>
            Google Drive Integration
        </h2>
        <div id="driveConnectionCard" class="loading">
            <div class="spinner"></div>
            <p>Loading Google Drive connection status...</p>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const API_BASE = '{{ url('') }}/api/admin';

document.addEventListener('DOMContentLoaded', function() {
    loadYouTubeStatus();
    loadDriveStatus();
});

// Load YouTube connection status
async function loadYouTubeStatus() {
    try {
        const token = localStorage.getItem('auth_token');
        const response = await fetch(`${API_BASE}/youtube/status`, {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });

        const result = await response.json();

        if (response.ok && result.status === 'success') {
            displayYouTubeStatus(result);
        } else {
            showError('youtubeConnectionCard', 'Failed to load YouTube status');
        }
    } catch (error) {
        console.error('Error loading YouTube status:', error);
        showError('youtubeConnectionCard', 'Network error loading YouTube status');
    }
}

// Display YouTube connection status
function displayYouTubeStatus(data) {
    const container = document.getElementById('youtubeConnectionCard');
    const isConnected = data.connected || false;

    container.innerHTML = `
        <div class="connection-card ${isConnected ? 'connected' : 'disconnected'}">
            <div class="connection-header">
                <div class="connection-title">
                    <i class="fab fa-youtube" style="font-size: 2rem; color: #ff0000;"></i>
                    <span>YouTube</span>
                </div>
                <span class="status-badge ${isConnected ? 'status-connected' : 'status-disconnected'}">
                    ${isConnected ? '✓ Connected' : '✗ Disconnected'}
                </span>
            </div>

            ${isConnected ? `
                <div class="connection-details">
                    <div class="detail-row">
                        <span class="detail-label">Channel Name:</span>
                        <span class="detail-value">${data.channel_name || 'N/A'}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Channel ID:</span>
                        <span class="detail-value">${data.channel_id || 'N/A'}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Connected At:</span>
                        <span class="detail-value">${formatDate(data.connected_at)}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Token Expires:</span>
                        <span class="detail-value">${formatDate(data.token_expires_at)}</span>
                    </div>
                </div>
                <div style="margin-top: 1.5rem;">
                    <button onclick="disconnectYouTube()" class="btn-disconnect">
                        <i class="fas fa-unlink me-2"></i>Disconnect YouTube
                    </button>
                </div>
            ` : `
                <p style="color: #6b7280; margin-top: 1rem;">YouTube is not connected. Connect to enable video uploads and live streaming.</p>
                <div style="margin-top: 1.5rem;">
                    <button onclick="connectYouTube()" class="btn-connect">
                        <i class="fab fa-youtube me-2"></i>Connect YouTube
                    </button>
                </div>
            `}
        </div>
    `;
}

// Load Google Drive connection status
async function loadDriveStatus() {
    try {
        const token = localStorage.getItem('auth_token');
        const response = await fetch(`${API_BASE}/google-drive/status`, {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });

        const result = await response.json();

        if (response.ok && result.status === 'success') {
            // Try to get account info if connected
            if (result.connected) {
                const accountResponse = await fetch(`${API_BASE}/google-drive/account-info`, {
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Accept': 'application/json'
                    }
                });

                if (accountResponse.ok) {
                    const accountResult = await accountResponse.json();
                    result.account_email = accountResult.email;
                }
            }

            displayDriveStatus(result);
        } else {
            showError('driveConnectionCard', 'Failed to load Google Drive status');
        }
    } catch (error) {
        console.error('Error loading Drive status:', error);
        showError('driveConnectionCard', 'Network error loading Drive status');
    }
}

// Display Google Drive connection status
function displayDriveStatus(data) {
    const container = document.getElementById('driveConnectionCard');
    const isConnected = data.connected || false;

    container.innerHTML = `
        <div class="connection-card ${isConnected ? 'connected' : 'disconnected'}">
            <div class="connection-header">
                <div class="connection-title">
                    <i class="fab fa-google-drive" style="font-size: 2rem; color: #4285f4;"></i>
                    <span>Google Drive</span>
                </div>
                <span class="status-badge ${isConnected ? 'status-connected' : 'status-disconnected'}">
                    ${isConnected ? '✓ Connected' : '✗ Disconnected'}
                </span>
            </div>

            ${isConnected ? `
                <div class="connection-details">
                    ${data.account_email ? `
                        <div class="detail-row">
                            <span class="detail-label">Account Email:</span>
                            <span class="detail-value">${data.account_email}</span>
                        </div>
                    ` : ''}
                    <div class="detail-row">
                        <span class="detail-label">Root Folder ID:</span>
                        <span class="detail-value">${data.root_folder_id || 'N/A'}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Connected At:</span>
                        <span class="detail-value">${formatDate(data.connected_at)}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Token Expires:</span>
                        <span class="detail-value">${formatDate(data.token_expires_at)}</span>
                    </div>
                </div>
                <div style="margin-top: 1.5rem;">
                    <button onclick="disconnectDrive()" class="btn-disconnect">
                        <i class="fas fa-unlink me-2"></i>Disconnect Google Drive
                    </button>
                </div>
            ` : `
                <p style="color: #6b7280; margin-top: 1rem;">Google Drive is not connected. Connect to enable resource uploads.</p>
                <div style="margin-top: 1.5rem;">
                    <button onclick="connectDrive()" class="btn-connect">
                        <i class="fab fa-google-drive me-2"></i>Connect Google Drive
                    </button>
                </div>
            `}
        </div>
    `;
}

// Connect YouTube
async function connectYouTube() {
    try {
        const token = localStorage.getItem('auth_token');
        const response = await fetch(`${API_BASE}/youtube/connect`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });

        const result = await response.json();

        if (response.ok && result.status === 'success') {
            window.location.href = result.auth_url;
        } else {
            await Swal.fire({
                icon: 'error',
                title: 'Connection Failed',
                text: result.message || 'Failed to initiate YouTube connection'
            });
        }
    } catch (error) {
        console.error('Error connecting YouTube:', error);
        await Swal.fire({
            icon: 'error',
            title: 'Network Error',
            text: 'Failed to connect to YouTube'
        });
    }
}

// Disconnect YouTube
async function disconnectYouTube() {
    const result = await Swal.fire({
        title: 'Disconnect YouTube?',
        text: 'This will revoke access and remove all YouTube data. Are you sure?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, disconnect',
        cancelButtonText: 'Cancel'
    });

    if (!result.isConfirmed) return;

    try {
        const token = localStorage.getItem('auth_token');
        const response = await fetch(`${API_BASE}/youtube/disconnect`, {
            method: 'DELETE',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });

        const responseResult = await response.json();

        if (response.ok && responseResult.status === 'success') {
            await Swal.fire({
                icon: 'success',
                title: 'Disconnected',
                text: 'YouTube disconnected successfully'
            });
            loadYouTubeStatus();
        } else {
            await Swal.fire({
                icon: 'error',
                title: 'Disconnect Failed',
                text: responseResult.message || 'Failed to disconnect YouTube'
            });
        }
    } catch (error) {
        console.error('Error disconnecting YouTube:', error);
        await Swal.fire({
            icon: 'error',
            title: 'Network Error',
            text: 'Failed to disconnect YouTube'
        });
    }
}

// Connect Google Drive
async function connectDrive() {
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

        if (response.ok && result.status === 'success') {
            window.location.href = result.auth_url;
        } else {
            await Swal.fire({
                icon: 'error',
                title: 'Connection Failed',
                text: result.message || 'Failed to initiate Google Drive connection'
            });
        }
    } catch (error) {
        console.error('Error connecting Drive:', error);
        await Swal.fire({
            icon: 'error',
            title: 'Network Error',
            text: 'Failed to connect to Google Drive'
        });
    }
}

// Disconnect Google Drive
async function disconnectDrive() {
    const result = await Swal.fire({
        title: 'Disconnect Google Drive?',
        text: 'This will revoke access and remove all permissions. Resource files will remain but will be inaccessible. Are you sure?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, disconnect',
        cancelButtonText: 'Cancel'
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

        const responseResult = await response.json();

        if (response.ok && responseResult.status === 'success') {
            await Swal.fire({
                icon: 'success',
                title: 'Disconnected',
                text: 'Google Drive disconnected successfully. All permissions revoked.'
            });
            loadDriveStatus();
        } else {
            await Swal.fire({
                icon: 'error',
                title: 'Disconnect Failed',
                text: responseResult.message || 'Failed to disconnect Google Drive'
            });
        }
    } catch (error) {
        console.error('Error disconnecting Drive:', error);
        await Swal.fire({
            icon: 'error',
            title: 'Network Error',
            text: 'Failed to disconnect Google Drive'
        });
    }
}

// Utility: Format date
function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Show error
function showError(containerId, message) {
    const container = document.getElementById(containerId);
    container.innerHTML = `
        <div style="text-align: center; padding: 2rem; color: #ef4444;">
            <i class="fas fa-exclamation-circle" style="font-size: 3rem; margin-bottom: 1rem;"></i>
            <p>${message}</p>
            <button onclick="location.reload()" class="btn-connect" style="margin-top: 1rem;">
                <i class="fas fa-redo"></i> Retry
            </button>
        </div>
    `;
}
</script>
@endsection
