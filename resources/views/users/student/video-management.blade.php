@extends('layouts.app')

@section('title', 'Course Videos - Smart LMS')

@section('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap');

    :root {
        --primary: #10b981;
        --primary-dark: #059669;
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

    /* Tabs Navigation */
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

    .course-section {
        background: var(--surface);
        border-radius: var(--radius);
        padding: 1.5rem;
        margin-bottom: 2rem;
        box-shadow: var(--shadow-md);
    }

    .course-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
        padding-bottom: 0.75rem;
        border-bottom: 2px solid var(--muted);
    }

    .course-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--primary);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .course-stats {
        display: flex;
        gap: 1.5rem;
        font-size: 0.875rem;
        color: var(--muted-foreground);
    }

    .videos-scroll-container {
        overflow-x: auto;
        overflow-y: hidden;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: thin;
        scrollbar-color: var(--primary) var(--muted);
        padding-bottom: 1rem;
        cursor: grab;
    }

    .videos-scroll-container:active {
        cursor: grabbing;
    }

    .videos-scroll-container::-webkit-scrollbar {
        height: 8px;
    }

    .videos-scroll-container::-webkit-scrollbar-track {
        background: var(--muted);
        border-radius: 4px;
    }

    .videos-scroll-container::-webkit-scrollbar-thumb {
        background: var(--primary);
        border-radius: 4px;
    }

    .videos-horizontal {
        display: flex;
        gap: 1.5rem;
        min-width: min-content;
    }

    .video-card {
        background: var(--surface);
        border-radius: var(--radius);
        overflow: hidden;
        box-shadow: var(--shadow-md);
        transition: all 0.3s ease;
        width: 320px;
        flex-shrink: 0;
        border: 2px solid transparent;
        cursor: pointer;
    }

    .video-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        border-color: var(--primary);
    }

    .video-thumbnail {
        width: 100%;
        height: 180px;
        object-fit: cover;
        background: var(--muted);
        position: relative;
    }

    /* NEW: No thumbnail design for live streams */
    .video-thumbnail.no-thumbnail {
        background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .no-thumbnail-content {
        text-align: center;
        color: white;
        padding: 1rem;
        z-index: 1;
    }

    .no-thumbnail-play {
        width: 80px;
        height: 80px;
        background: rgba(255, 255, 255, 0.2);
        border: 3px solid white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
        transition: all 0.3s ease;
    }

    .video-card:hover .no-thumbnail-play {
        background: rgba(255, 255, 255, 0.3);
        transform: scale(1.1);
    }

    .no-thumbnail-play i {
        font-size: 2rem;
        margin-left: 5px;
    }

    .no-thumbnail-title {
        font-size: 1.1rem;
        font-weight: 600;
        line-height: 1.4;
        max-width: 280px;
        margin: 0 auto;
    }

    /* FIXED: Separate positioning for LIVE badge and duration */
    .video-duration {
        position: absolute;
        bottom: 8px;
        right: 8px;
        background: rgba(0, 0, 0, 0.8);
        color: white;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 600;
        z-index: 1;
    }

    .live-badge {
        position: absolute;
        top: 8px;
        left: 8px;
        background: var(--danger);
        color: white;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 600;
        animation: pulse 2s ease-in-out infinite;
        z-index: 1;
        box-shadow: 0 2px 8px rgba(239, 68, 68, 0.5);
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.7; }
    }

    .video-info {
        padding: 1rem;
    }

    .video-title {
        font-size: 1rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
        color: var(--foreground);
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        min-height: 3rem;
    }

    .lecturer-name {
        font-size: 0.75rem;
        color: var(--muted-foreground);
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .video-stats {
        display: flex;
        gap: 1rem;
        font-size: 0.75rem;
        color: var(--muted-foreground);
    }

    .playlist-link {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        background: var(--muted);
        color: var(--primary);
        border-radius: var(--radius);
        text-decoration: none;
        font-size: 0.875rem;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .playlist-link:hover {
        background: var(--primary);
        color: white;
        text-decoration: none;
    }

    .empty-state {
        text-align: center;
        padding: 3rem;
        background: var(--surface);
        border-radius: var(--radius);
        box-shadow: var(--shadow-md);
    }

    .empty-icon {
        font-size: 4rem;
        opacity: 0.5;
        margin-bottom: 1rem;
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

    .loading {
        text-align: center;
        padding: 2rem;
        color: var(--primary);
    }
</style>
@endsection

@section('content')
<div class="container">
    <!-- Page Header -->
    <div class="page-header">
        <h1 style="font-size: 2rem; font-weight: 700; margin-bottom: 0.5rem;">📚 Course Videos</h1>
        <p style="font-size: 1rem; opacity: 0.9; margin: 0;">Watch educational videos and live streams from your enrolled courses</p>
    </div>

    <!-- Tabs Navigation -->
    <div class="tabs">
        <button class="tab active" data-tab="videos" onclick="switchTab('videos')">
            <i class="fas fa-video"></i> My Videos
        </button>
        <button class="tab" data-tab="livestreams" onclick="switchTab('livestreams')">
            <i class="fas fa-broadcast-tower"></i> Live Streams
        </button>
    </div>

    <!-- Tab 1: My Videos -->
    <div id="videosTab" class="tab-content" style="display: block;">
        <div id="videosContainer">
            <div class="loading">
                <div class="loading-spinner"></div>
                <p style="margin-top: 1rem;">Loading videos...</p>
            </div>
        </div>
    </div>

    <!-- Tab 2: Live Streams -->
    <div id="livestreamsTab" class="tab-content" style="display: none;">
        <div id="livestreamsContainer">
            <div class="loading">
                <div class="loading-spinner"></div>
                <p style="margin-top: 1rem;">Loading live streams...</p>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const API_BASE = '{{ url('') }}/api/student';

let enrolledCourses = [];
let allVideos = [];
let allLiveStreams = [];
let youtubeConnected = false;

// FIXED: Use async IIFE to ensure YouTube check completes first
document.addEventListener('DOMContentLoaded', async function() {
    // CRITICAL: Wait for YouTube connection check to complete
    await checkYouTubeConnection();

    // THEN load videos and streams (now youtubeConnected is properly set)
    await loadVideos();
    await loadLiveStreams();

    setTimeout(initializeMouseWheelScrolling, 1000);
});

// FIXED: Check YouTube connection status with proper async/await
async function checkYouTubeConnection() {
    try {
        const token = localStorage.getItem('auth_token');
        const response = await fetch(`${API_BASE}/youtube-connection`, {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });
        const result = await response.json();

        console.log('YouTube connection check result:', result); // Debug log

        if (result.status === 'success') {
            youtubeConnected = result.connected;
        } else {
            youtubeConnected = false;
        }
    } catch (error) {
        console.error('Error checking YouTube connection:', error);
        youtubeConnected = false;
    }

    console.log('YouTube connected status:', youtubeConnected); // Debug log
}

// Switch tab function
function switchTab(tabName) {
    document.querySelectorAll('.tab-content').forEach(tab => tab.style.display = 'none');
    document.querySelectorAll('.tab').forEach(btn => btn.classList.remove('active'));
    document.getElementById(tabName + 'Tab').style.display = 'block';
    document.querySelector(`[data-tab="${tabName}"]`).classList.add('active');

    // FIXED: Don't reload if already loaded
    if (tabName === 'videos') {
        if (allVideos.length === 0 && youtubeConnected) {
            loadVideos();
        }
    } else if (tabName === 'livestreams') {
        if (allLiveStreams.length === 0 && youtubeConnected) {
            loadLiveStreams();
        }
    }

    setTimeout(initializeMouseWheelScrolling, 100);
}

// UPDATED: Load regular videos
async function loadVideos() {
    const container = document.getElementById('videosContainer');

    console.log('loadVideos called, youtubeConnected:', youtubeConnected); // Debug log

    // Show disconnect message if YouTube not connected
    if (!youtubeConnected) {
        container.innerHTML = `
            <div class="empty-state">
                <div class="empty-icon">📺</div>
                <h3>YouTube Not Connected</h3>
                <p style="color: var(--danger); font-weight: 600;">The administrator has not connected the YouTube channel yet.</p>
                <p style="margin-top: 1rem;">Please contact your administrator to enable video streaming.</p>
            </div>
        `;
        return;
    }

    // Show loading state
    container.innerHTML = `
        <div class="loading">
            <div class="loading-spinner"></div>
            <p style="margin-top: 1rem;">Loading videos...</p>
        </div>
    `;

    try {
        const token = localStorage.getItem('auth_token');
        const response = await fetch(`${API_BASE}/videos`, {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });
        const result = await response.json();

        console.log('Videos API response:', result); // Debug log

        if (result.status === 'success') {
            allVideos = result.videos || [];
            enrolledCourses = result.enrolled_courses || [];
            displayVideos(allVideos);
        } else {
            throw new Error(result.message || 'Failed to load videos');
        }
    } catch (error) {
        console.error('Error loading videos:', error);
        container.innerHTML = `
            <div class="empty-state">
                <div class="empty-icon">⚠️</div>
                <h3>Error Loading Videos</h3>
                <p>${error.message}</p>
            </div>
        `;
    }
}

// UPDATED: Load live streams
async function loadLiveStreams() {
    const container = document.getElementById('livestreamsContainer');

    console.log('loadLiveStreams called, youtubeConnected:', youtubeConnected); // Debug log

    // Show disconnect message if YouTube not connected
    if (!youtubeConnected) {
        container.innerHTML = `
            <div class="empty-state">
                <div class="empty-icon">📡</div>
                <h3>YouTube Not Connected</h3>
                <p style="color: var(--danger); font-weight: 600;">The administrator has not connected the YouTube channel yet.</p>
                <p style="margin-top: 1rem;">Please contact your administrator to enable live streaming.</p>
            </div>
        `;
        return;
    }

    // Show loading state
    container.innerHTML = `
        <div class="loading">
            <div class="loading-spinner"></div>
            <p style="margin-top: 1rem;">Loading live streams...</p>
        </div>
    `;

    try {
        const token = localStorage.getItem('auth_token');
        const response = await fetch(`${API_BASE}/live-streams`, {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });
        const result = await response.json();

        console.log('Live streams API response:', result); // Debug log

        if (result.status === 'success') {
            allLiveStreams = result.streams || [];
            displayLiveStreams(allLiveStreams);
        } else {
            throw new Error(result.message || 'Failed to load live streams');
        }
    } catch (error) {
        console.error('Error loading live streams:', error);
        container.innerHTML = `
            <div class="empty-state">
                <div class="empty-icon">⚠️</div>
                <h3>Error Loading Live Streams</h3>
                <p>${error.message}</p>
            </div>
        `;
    }
}

// Display regular videos grouped by course
function displayVideos(videos) {
    const container = document.getElementById('videosContainer');

    if (videos.length === 0) {
        container.innerHTML = `
            <div class="empty-state">
                <div class="empty-icon">🎬</div>
                <h3>No Videos Available</h3>
                <p>No videos have been uploaded for your enrolled courses yet.</p>
            </div>
        `;
        return;
    }

    // Group videos by course
    const videosByCourse = {};
    videos.forEach(video => {
        const courseId = video.course_id || 'uncategorized';
        if (!videosByCourse[courseId]) {
            videosByCourse[courseId] = {
                courseName: video.course_name || 'Uncategorized',
                videos: []
            };
        }
        videosByCourse[courseId].videos.push(video);
    });

    let html = '';

    Object.keys(videosByCourse).forEach(courseId => {
        const courseData = videosByCourse[courseId];
        const totalViews = courseData.videos.reduce((sum, v) => sum + (v.total_views || 0), 0);

        html += `
            <div class="course-section">
                <div class="course-header">
                    <div class="course-title">
                        <i class="fas fa-book"></i>
                        ${courseData.courseName}
                    </div>
                    <div class="course-stats">
                        <div class="stat-item">
                            <i class="fas fa-video"></i>
                            <span>${courseData.videos.length} video${courseData.videos.length !== 1 ? 's' : ''}</span>
                        </div>
                        <div class="stat-item">
                            <i class="fas fa-eye"></i>
                            <span>${totalViews.toLocaleString()} views</span>
                        </div>
                        ${courseData.videos[0].playlist_url ? `
                            <a href="${courseData.videos[0].playlist_url}" target="_blank" class="playlist-link">
                                <i class="fab fa-youtube"></i>
                                View Full Playlist
                            </a>
                        ` : ''}
                    </div>
                </div>

                <div class="videos-scroll-container">
                    <div class="videos-horizontal">
                        ${courseData.videos.map(video => createVideoCard(video, false)).join('')}
                    </div>
                </div>

                ${courseData.videos.length > 3 ? `
                    <div style="text-align: center; font-size: 0.75rem; color: var(--muted-foreground); margin-top: 0.5rem;">
                        <i class="fas fa-arrow-right"></i> Scroll horizontally to see more videos
                    </div>
                ` : ''}
            </div>
        `;
    });

    container.innerHTML = html;
    setTimeout(initializeMouseWheelScrolling, 100);
}

// Display live streams grouped by course
function displayLiveStreams(streams) {
    const container = document.getElementById('livestreamsContainer');

    if (streams.length === 0) {
        container.innerHTML = `
            <div class="empty-state">
                <div class="empty-icon">📡</div>
                <h3>No Live Streams Available</h3>
                <p>No live streams are currently scheduled or ongoing for your enrolled courses.</p>
            </div>
        `;
        return;
    }

    // Group streams by course
    const streamsByCourse = {};
    streams.forEach(stream => {
        const courseId = stream.course_id || 'uncategorized';
        if (!streamsByCourse[courseId]) {
            streamsByCourse[courseId] = {
                courseName: stream.course_name || 'Uncategorized',
                streams: []
            };
        }
        streamsByCourse[courseId].streams.push(stream);
    });

    let html = '';

    Object.keys(streamsByCourse).forEach(courseId => {
        const courseData = streamsByCourse[courseId];
        const liveCount = courseData.streams.filter(s => s.status === 'live').length;

        html += `
            <div class="course-section">
                <div class="course-header">
                    <div class="course-title">
                        <i class="fas fa-book"></i>
                        ${courseData.courseName}
                    </div>
                    <div class="course-stats">
                        <div class="stat-item">
                            <i class="fas fa-broadcast-tower"></i>
                            <span>${courseData.streams.length} stream${courseData.streams.length !== 1 ? 's' : ''}</span>
                        </div>
                        ${liveCount > 0 ? `
                            <div class="stat-item" style="color: var(--danger); font-weight: 600;">
                                <span>🔴 ${liveCount} LIVE NOW</span>
                            </div>
                        ` : ''}
                    </div>
                </div>

                <div class="videos-scroll-container">
                    <div class="videos-horizontal">
                        ${courseData.streams.map(stream => createVideoCard(stream, true)).join('')}
                    </div>
                </div>

                ${courseData.streams.length > 3 ? `
                    <div style="text-align: center; font-size: 0.75rem; color: var(--muted-foreground); margin-top: 0.5rem;">
                        <i class="fas fa-arrow-right"></i> Scroll horizontally to see more streams
                    </div>
                ` : ''}
            </div>
        `;
    });

    container.innerHTML = html;
    setTimeout(initializeMouseWheelScrolling, 100);
}

// Create video/stream card (unified function)
function createVideoCard(item, isLiveStream) {
    const isLive = item.status === 'live';
    const url = isLiveStream ? item.watch_url : item.youtube_url;
    const hasNoThumbnail = !item.thumbnail_url || item.thumbnail_url.includes('placeholder');

    // NEW: Different design for live streams without thumbnail
    if (isLiveStream && hasNoThumbnail) {
        return `
            <div class="video-card" onclick="playVideo('${item.id}', '${url}', ${isLiveStream})">
                <div style="position: relative;">
                    <div class="video-thumbnail no-thumbnail">
                        ${isLive ? `<div class="live-badge">🔴 LIVE</div>` : ''}
                        <div class="no-thumbnail-content">
                            <div class="no-thumbnail-play">
                                <i class="fas fa-play" style="color: white;"></i>
                            </div>
                            <div class="no-thumbnail-title">${item.title}</div>
                        </div>
                    </div>
                </div>
                <div class="video-info">
                    <h3 class="video-title">${item.title}</h3>
                    <div class="lecturer-name">
                        <i class="fas fa-chalkboard-teacher"></i>
                        ${item.lecturer_name}
                    </div>
                    <div class="video-stats">
                        ${!isLiveStream || !isLive ? `
                            <span><i class="fas fa-eye"></i> ${(item.total_views || 0).toLocaleString()} views</span>
                            <span><i class="fas fa-circle" style="font-size: 4px; margin: 0 0.25rem;"></i></span>
                        ` : ''}
                        <span><i class="fas fa-calendar"></i> ${new Date(item.created_at || item.scheduled_start).toLocaleDateString()}</span>
                    </div>
                </div>
            </div>
        `;
    }

    // Original design for videos with thumbnails
    return `
        <div class="video-card" onclick="playVideo('${item.id}', '${url}', ${isLiveStream})">
            <div style="position: relative;">
                <img src="${item.thumbnail_url || 'https://via.placeholder.com/320x180?text=No+Thumbnail'}"
                     alt="${item.title}"
                     class="video-thumbnail">
                <div class="play-overlay">
                    <i class="fas fa-play play-icon"></i>
                </div>
                ${isLive ? `
                    <div class="live-badge">🔴 LIVE</div>
                ` : ''}
                ${item.duration_seconds > 0 && !isLiveStream ? `
                    <div class="video-duration">${formatDuration(item.duration_seconds)}</div>
                ` : ''}
            </div>
            <div class="video-info">
                <h3 class="video-title">${item.title}</h3>
                <div class="lecturer-name">
                    <i class="fas fa-chalkboard-teacher"></i>
                    ${item.lecturer_name}
                </div>
                <div class="video-stats">
                    ${!isLiveStream || !isLive ? `
                        <span><i class="fas fa-eye"></i> ${(item.total_views || 0).toLocaleString()} views</span>
                        <span><i class="fas fa-circle" style="font-size: 4px; margin: 0 0.25rem;"></i></span>
                    ` : ''}
                    <span><i class="fas fa-calendar"></i> ${new Date(item.created_at || item.scheduled_start).toLocaleDateString()}</span>
                </div>
            </div>
        </div>
    `;
}

// Play video or stream
async function playVideo(itemId, url, isLiveStream) {
    if (!url) {
        Swal.fire({
            icon: 'error',
            title: isLiveStream ? 'Stream Unavailable' : 'Video Unavailable',
            text: 'This content is currently unavailable',
            confirmButtonColor: '#10b981'
        });
        return;
    }

    // Record view (only for regular videos, not live streams)
    if (!isLiveStream) {
        try {
            const token = localStorage.getItem('auth_token');
            await fetch(`${API_BASE}/videos/${itemId}/view`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                }
            });
        } catch (error) {
            console.error('Error recording view:', error);
        }
    }

    // Open content
    window.open(url, '_blank');
}

function formatDuration(seconds) {
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return `${mins}:${secs.toString().padStart(2, '0')}`;
}

// Mouse wheel horizontal scrolling
function initializeMouseWheelScrolling() {
    const scrollContainers = document.querySelectorAll('.videos-scroll-container');

    scrollContainers.forEach(container => {
        container.addEventListener('wheel', function(e) {
            if (e.deltaY !== 0) {
                e.preventDefault();
                container.scrollLeft += e.deltaY;
            }
        }, { passive: false });

        let isDown = false;
        let startX;
        let scrollLeft;

        container.addEventListener('mousedown', (e) => {
            isDown = true;
            container.style.cursor = 'grabbing';
            startX = e.pageX - container.offsetLeft;
            scrollLeft = container.scrollLeft;
        });

        container.addEventListener('mouseleave', () => {
            isDown = false;
            container.style.cursor = 'grab';
        });

        container.addEventListener('mouseup', () => {
            isDown = false;
            container.style.cursor = 'grab';
        });

        container.addEventListener('mousemove', (e) => {
            if (!isDown) return;
            e.preventDefault();
            const x = e.pageX - container.offsetLeft;
            const walk = (x - startX) * 2;
            container.scrollLeft = scrollLeft - walk;
        });
    });
}
</script>
@endsection
