@extends('layouts.app')

@section('title', 'Video Stream Management - Smart LMS')

@section('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap');

    :root {
        --primary: #3b82f6;
        --primary-dark: #2563eb;
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

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: var(--surface);
        padding: 1.5rem;
        border-radius: var(--radius);
        box-shadow: var(--shadow-md);
        text-align: center;
    }

    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        color: var(--primary);
    }

    .stat-label {
        color: var(--muted-foreground);
        font-size: 0.875rem;
        margin-top: 0.5rem;
    }

    .youtube-status {
        background: var(--surface);
        padding: 1.5rem;
        border-radius: var(--radius);
        margin-bottom: 2rem;
        box-shadow: var(--shadow-md);
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .status-connected { color: var(--success); }
    .status-disconnected { color: var(--danger); }

    .streams-container {
        background: var(--surface);
        padding: 2rem;
        border-radius: var(--radius);
        box-shadow: var(--shadow-md);
        margin-bottom: 2rem;
    }

    .stream-card {
        border: 1px solid var(--muted);
        border-radius: var(--radius);
        padding: 1.5rem;
        margin-bottom: 1rem;
        transition: all 0.3s ease;
    }

    .stream-card:hover {
        box-shadow: var(--shadow-md);
        border-color: var(--primary);
    }

    .stream-status {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .status-live {
        background: var(--danger);
        color: white;
        animation: pulse 2s infinite;
    }

    .status-scheduled {
        background: var(--warning);
        color: white;
    }

    .status-ended {
        background: var(--muted);
        color: var(--muted-foreground);
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.7; }
    }

    .btn-delete {
        background: var(--danger);
        color: white;
        border: none;
        padding: 0.5rem 1rem;
        border-radius: var(--radius);
        cursor: pointer;
    }

    .loading {
        text-align: center;
        padding: 2rem;
        color: var(--muted-foreground);
    }

    .empty-msg {
        text-align: center;
        color: var(--danger);
        font-size: 1.1rem;
        padding: 2rem;
        background: var(--muted);
        border-radius: var(--radius);
        margin-bottom: 2rem;
    }
</style>
@endsection

@section('content')
<div class="container">
    <!-- Page Header -->
    <div class="page-header">
        <h1 style="font-size: 2rem; font-weight: 700; margin-bottom: 0.5rem;">Video Stream Management</h1>
        <p style="font-size: 1rem; opacity: 0.9; margin: 0;">Monitor and manage all live streaming activities</p>
    </div>

    <!-- YouTube Connection Status -->
    <div class="youtube-status" id="youtubeStatus">
        <div class="loading">Checking YouTube connection...</div>
    </div>

    <!-- Summary Section -->
    <div id="summarySection">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value" id="totalStreams">0</div>
                <div class="stat-label">Total Streams</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" id="liveNow">0</div>
                <div class="stat-label"><span style="color: var(--danger); font-size: 1.2rem;">&#x1F534;</span> Live Now</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" id="scheduledStreams">0</div>
                <div class="stat-label"><span style="color: var(--warning); font-size: 1.2rem;">&#x1F514;</span> Scheduled</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" id="totalViews">0</div>
                <div class="stat-label">Total Views</div>
            </div>
        </div>
    </div>

    <!-- Playlist Analytics Section -->
    <div id="playlistAnalyticsSection" class="streams-container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h2 style="margin: 0;">📊 Playlist Analytics</h2>
            <button class="btn btn-primary" onclick="loadPlaylistAnalytics()">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
        </div>
        <div id="playlistSummaryStats" style="margin-bottom: 1.5rem;">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value" id="totalPlaylists">0</div>
                    <div class="stat-label">Total Playlists</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value" id="averageVideosPerPlaylist">0</div>
                    <div class="stat-label">Avg Videos/Playlist</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value" id="totalPlaylistViews">0</div>
                    <div class="stat-label">Total Playlist Views</div>
                </div>
            </div>
        </div>
        <div id="playlistsList">
            <div class="loading">Loading playlist analytics...</div>
        </div>
    </div>

    <!-- Filter Section -->
    <div style="background: var(--surface); padding: 1rem; border-radius: var(--radius); margin-bottom: 2rem; display: flex; gap: 1rem;">
        <select id="statusFilter" class="form-control" style="max-width: 200px;">
            <option value="">All Status</option>
            <option value="live">Live Now</option>
            <option value="scheduled">Scheduled</option>
            <option value="ended">Ended</option>
        </select>
        <button class="btn btn-primary" onclick="loadStreams()">Apply Filter</button>
    </div>

    <!-- All Video Streams Section -->
    <div id="streamsSection" class="streams-container">
        <h2 style="margin-bottom: 1.5rem;">All Video Streams</h2>
        <div id="streamsList">
            <div class="loading">Loading streams...</div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const API_BASE = '{{ url('') }}/api/admin';
let ytConnected = false;

document.addEventListener('DOMContentLoaded', function() {
    checkYouTubeStatus();
});

async function checkYouTubeStatus() {
    try {
        const token = localStorage.getItem('auth_token');
        const response = await fetch(`${API_BASE}/youtube/status`, {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });
        const result = await response.json();

        const ytStatusDiv = document.getElementById('youtubeStatus');
        if (result.connected) {
            ytConnected = true;
            ytStatusDiv.innerHTML = `
                <i class="fab fa-youtube" style="color: #ef4444; font-size: 2rem;"></i>
                <div style="flex: 1;">
                    <span class="status-connected" style="font-weight:600;">YouTube Connected</span>
                    <div style="color: var(--muted-foreground); font-size: 0.95rem;">
                        Channel: ${result.channel_name || 'Unknown'}
                    </div>
                </div>
                <button class="btn btn-danger" onclick="disconnectYouTube()" style="white-space: nowrap;">
                    <i class="fas fa-unlink"></i> Disconnect
                </button>
            `;
            loadAnalytics();
            loadPlaylistAnalytics();
            loadStreams();
        } else {
            ytConnected = false;
            ytStatusDiv.innerHTML = `
                <i class="fab fa-youtube" style="color: #ef4444; font-size: 2rem;"></i>
                <div style="flex: 1;">
                    <span class="status-disconnected" style="font-weight:600;">YouTube Not Connected</span>
                    <div style="color: var(--muted-foreground); font-size: 0.95rem;">
                        Connect YouTube to manage videos and streams
                    </div>
                </div>
                <button class="btn btn-primary" onclick="connectYouTube()" style="white-space: nowrap;">
                    <i class="fab fa-youtube"></i> Connect YouTube
                </button>
            `;
            showDisconnectedAnalytics();
            showDisconnectedPlaylists();
            showDisconnectedStreams();
        }
    } catch (error) {
        showDisconnectedAnalytics();
        showDisconnectedPlaylists();
        showDisconnectedStreams();
    }
}

function showDisconnectedAnalytics() {
    document.getElementById('totalStreams').textContent = '0';
    document.getElementById('liveNow').textContent = '0';
    document.getElementById('scheduledStreams').textContent = '0';
    document.getElementById('totalViews').textContent = '0';
}

function showDisconnectedPlaylists() {
    document.getElementById('totalPlaylists').textContent = '0';
    document.getElementById('averageVideosPerPlaylist').textContent = '0';
    document.getElementById('totalPlaylistViews').textContent = '0';
    document.getElementById('playlistsList').innerHTML = `
        <div class="empty-msg">
            There is no YouTube channel connected. Please connect it.
        </div>
    `;
}

function showDisconnectedStreams() {
    document.getElementById('streamsList').innerHTML = `
        <div class="empty-msg">
            There is no YouTube channel connected. Please connect it.
        </div>
    `;
}

async function loadAnalytics() {
    try {
        const token = localStorage.getItem('auth_token');
        const response = await fetch(`${API_BASE}/streams/analytics`, {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });
        const result = await response.json();

        if (result.status === 'success') {
            document.getElementById('totalStreams').textContent = result.analytics.total_streams;
            document.getElementById('liveNow').textContent = result.analytics.live_now;
            document.getElementById('scheduledStreams').textContent = result.analytics.scheduled;
            document.getElementById('totalViews').textContent = result.analytics.total_views.toLocaleString();
        }
    } catch (error) {
        showDisconnectedAnalytics();
    }
}

async function loadPlaylistAnalytics() {
    if (!ytConnected) {
        showDisconnectedPlaylists();
        return;
    }
    try {
        const token = localStorage.getItem('auth_token');
        const response = await fetch(`${API_BASE}/playlists/analytics`, {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });
        const result = await response.json();

        if (result.status === 'success') {
            document.getElementById('totalPlaylists').textContent = result.summary.total_playlists;
            document.getElementById('averageVideosPerPlaylist').textContent = result.summary.average_videos_per_playlist;
            document.getElementById('totalPlaylistViews').textContent = result.summary.total_views.toLocaleString();

            const container = document.getElementById('playlistsList');
            if (result.playlists.length > 0) {
                container.innerHTML = result.playlists.map(playlist => `
                    <div class="stream-card" style="background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%); border-left: 4px solid var(--primary);">
                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                            <div style="flex: 1;">
                                <h3 style="margin: 0 0 0.5rem 0; color: var(--primary);">
                                    <i class="fas fa-list"></i> ${playlist.course_name}
                                </h3>
                                <p style="color: var(--muted-foreground); margin: 0; font-size: 0.875rem;">
                                    Playlist ID: ${playlist.playlist_id}
                                </p>
                                ${playlist.sample_videos.length > 0 ? `
                                    <div style="margin-top: 0.5rem; font-size: 0.75rem; color: var(--muted-foreground);">
                                        <strong>Sample Videos:</strong> ${playlist.sample_videos.join(', ')}
                                    </div>
                                ` : ''}
                            </div>
                            <a href="${playlist.playlist_url}" target="_blank" class="btn btn-primary" style="font-size: 0.75rem; padding: 0.5rem 1rem;">
                                <i class="fab fa-youtube"></i> View Playlist
                            </a>
                        </div>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem; padding: 1rem; background: var(--muted); border-radius: var(--radius); margin-bottom: 1rem;">
                            <div style="text-align: center;">
                                <div style="font-size: 1.5rem; font-weight: 700; color: var(--primary);">
                                    ${playlist.video_count}
                                </div>
                                <div style="font-size: 0.75rem; color: var(--muted-foreground);">Videos</div>
                            </div>
                            <div style="text-align: center;">
                                <div style="font-size: 1.5rem; font-weight: 700; color: var(--success);">
                                    ${playlist.total_views.toLocaleString()}
                                </div>
                                <div style="font-size: 0.75rem; color: var(--muted-foreground);">Total Views</div>
                            </div>
                            <div style="text-align: center;">
                                <div style="font-size: 1.5rem; font-weight: 700; color: var(--warning);">
                                    ${playlist.average_views_per_video}
                                </div>
                                <div style="font-size: 0.75rem; color: var(--muted-foreground);">Avg Views/Video</div>
                            </div>
                        </div>
                        ${playlist.latest_video ? `
                            <div style="padding: 0.75rem; background: white; border-radius: var(--radius); border: 1px solid var(--border);">
                                <div style="font-size: 0.75rem; color: var(--muted-foreground); margin-bottom: 0.25rem;">
                                    <i class="fas fa-clock"></i> Latest Video:
                                </div>
                                <div style="font-weight: 600; color: var(--foreground); margin-bottom: 0.25rem;">
                                    ${playlist.latest_video.title}
                                </div>
                                <div style="font-size: 0.75rem; color: var(--muted-foreground);">
                                    ${new Date(playlist.latest_video.uploaded_at).toLocaleDateString()} •
                                    ${playlist.latest_video.views} views
                                </div>
                            </div>
                        ` : `
                            <div style="text-align: center; padding: 1rem; color: var(--muted-foreground); font-size: 0.875rem;">
                                <i class="fas fa-inbox"></i> No videos in this playlist yet
                            </div>
                        `}
                        <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--border); font-size: 0.75rem; color: var(--muted-foreground);">
                            <i class="fas fa-calendar"></i> Created: ${new Date(playlist.created_at).toLocaleString()}
                        </div>
                    </div>
                `).join('');
            } else {
                container.innerHTML = '<p style="text-align: center; color: var(--muted-foreground); padding: 2rem;">No playlists found</p>';
            }
        }
    } catch (error) {
        showDisconnectedPlaylists();
    }
}

async function loadStreams() {
    if (!ytConnected) {
        showDisconnectedStreams();
        return;
    }
    try {
        const token = localStorage.getItem('auth_token');
        const status = document.getElementById('statusFilter').value;
        const url = status ? `${API_BASE}/streams?status=${status}` : `${API_BASE}/streams`;

        const response = await fetch(url, {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });
        const result = await response.json();

        const container = document.getElementById('streamsList');
        if (result.status === 'success' && result.streams.length > 0) {
            container.innerHTML = result.streams.map(stream => `
                <div class="stream-card">
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                        <div>
                            <h3 style="margin: 0 0 0.5rem 0;">${stream.title}</h3>
                            <p style="color: var(--muted-foreground); margin: 0; font-size: 0.875rem;">
                                ${stream.course_name} • ${stream.lecturer_name}
                            </p>
                        </div>
                        <span class="stream-status status-${stream.status}">
                            ${stream.status === 'live' ? '🔴 LIVE' : stream.status.toUpperCase()}
                        </span>
                    </div>
                    <p style="margin-bottom: 1rem; color: var(--muted-foreground);">${stream.description || 'No description'}</p>
                    <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 1rem; border-top: 1px solid var(--muted);">
                        <div style="font-size: 0.875rem; color: var(--muted-foreground);">
                            ${stream.status === 'live' ?
                                `👁 ${stream.peak_viewers} viewers` :
                                `📅 ${new Date(stream.scheduled_start).toLocaleString()}`
                            }
                        </div>
                        <button class="btn-delete" onclick="deleteStream('${stream.id}')">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                </div>
            `).join('');
        } else {
            container.innerHTML = '<p style="text-align: center; color: var(--muted-foreground); padding: 2rem;">No streams found</p>';
        }
    } catch (error) {
        showDisconnectedStreams();
    }
}

async function deleteStream(streamId) {
    const result = await Swal.fire({
        title: 'Delete Stream?',
        text: 'This action cannot be undone',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        confirmButtonText: 'Yes, delete'
    });

    if (result.isConfirmed) {
        try {
            const token = localStorage.getItem('auth_token');
            const response = await fetch(`${API_BASE}/streams/${streamId}`, {
                method: 'DELETE',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                }
            });
            const result = await response.json();

            if (result.status === 'success') {
                Swal.fire('Deleted!', result.message, 'success');
                loadStreams();
                loadAnalytics();
            }
        } catch (error) {
            Swal.fire('Error', 'Failed to delete stream', 'error');
        }
    }
}

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

        if (result.status === 'success' && result.auth_url) {
            window.location.href = result.auth_url;
        } else {
            Swal.fire('Error', result.message || 'Failed to initiate YouTube connection', 'error');
        }
    } catch (error) {
        Swal.fire('Error', 'Failed to connect to YouTube', 'error');
    }
}

async function disconnectYouTube() {
    const result = await Swal.fire({
        title: 'Disconnect YouTube?',
        text: 'This will remove access to your YouTube channel',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        confirmButtonText: 'Yes, disconnect'
    });

    if (result.isConfirmed) {
        try {
            const token = localStorage.getItem('auth_token');
            const response = await fetch(`${API_BASE}/youtube/disconnect`, {
                method: 'DELETE',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                }
            });
            const result = await response.json();

            if (result.status === 'success') {
                Swal.fire('Disconnected!', result.message, 'success');
                checkYouTubeStatus();
            } else {
                Swal.fire('Error', result.message || 'Failed to disconnect', 'error');
            }
        } catch (error) {
            Swal.fire('Error', 'Failed to disconnect YouTube', 'error');
        }
    }
}
</script>
@endsection
