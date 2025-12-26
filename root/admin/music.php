<?php
/**
 * MUSIC MANAGEMENT
 * Kelola lagu pernikahan
 */

require_once '../config/autoload.php';

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$musicPlayer = new MusicPlayer();
$upload = new Upload();
$db = new Database();

$error = '';
$success = '';
$currentTab = $_GET['tab'] ?? 'songs';

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['song_file']) && $_FILES['song_file']['error'] === UPLOAD_ERR_OK) {
        $data = [
            'title' => $_POST['title'] ?? '',
            'artist' => $_POST['artist'] ?? 'Unknown'
        ];
        
        $result = $musicPlayer->addSong($data, $_FILES['song_file']);
        
        if ($result['success']) {
            $success = 'Lagu berhasil diupload!';
            $auth->logActivity($_SESSION['user_id'], 'upload_song', "Uploaded: {$data['title']}");
        } else {
            $error = $result['message'];
        }
    } else {
        $error = 'Silakan pilih file lagu';
    }
}

// Handle actions
$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? 0;

if ($action === 'delete' && $id) {
    $result = $musicPlayer->deleteSong($id);
    if ($result['success']) {
        $success = $result['message'];
        $auth->logActivity($_SESSION['user_id'], 'delete_song', "Deleted song ID: {$id}");
    } else {
        $error = $result['message'];
    }
}

if ($action === 'set_default' && $id) {
    $result = $musicPlayer->setDefaultSong($id);
    if ($result['success']) {
        $success = $result['message'];
        $auth->logActivity($_SESSION['user_id'], 'set_default_song', "Set default song ID: {$id}");
    } else {
        $error = $result['message'];
    }
}

// Get all songs
$songs = $musicPlayer->getAllSongs();
$defaultSong = $musicPlayer->getDefaultSong();
$stats = $musicPlayer->getStats();

// Format duration
function formatDuration($seconds) {
    $minutes = floor($seconds / 60);
    $seconds = $seconds % 60;
    return sprintf('%02d:%02d', $minutes, $seconds);
}

// Format filesize
function formatFilesize($bytes) {
    if ($bytes >= 1048576) {
        return round($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return round($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Musik - Admin Panel</title>
    
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        .music-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .stat-icon {
            font-size: 2em;
            color: #8B4513;
            margin-bottom: 10px;
        }
        
        .stat-number {
            font-size: 1.8em;
            font-weight: bold;
            color: #333;
        }
        
        .stat-label {
            color: #666;
            font-size: 0.9em;
        }
        
        .upload-form {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #555;
            font-weight: 500;
        }
        
        .form-control {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
        }
        
        .file-input {
            padding: 8px;
            border: 2px dashed #ddd;
            border-radius: 6px;
            text-align: center;
            background: #f9f9f9;
            cursor: pointer;
        }
        
        .file-input:hover {
            border-color: #8B4513;
            background: #f5f0e6;
        }
        
        .song-list {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .song-item {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            border-bottom: 1px solid #eee;
            transition: background 0.3s;
        }
        
        .song-item:hover {
            background: #f9f9f9;
        }
        
        .song-item.default {
            background: #fff8e1;
            border-left: 4px solid #ffc107;
        }
        
        .song-info {
            flex: 1;
        }
        
        .song-title {
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }
        
        .song-meta {
            display: flex;
            gap: 15px;
            font-size: 0.85em;
            color: #666;
        }
        
        .song-actions {
            display: flex;
            gap: 10px;
        }
        
        .btn-action {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9em;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .btn-play { background: #28a745; color: white; }
        .btn-default { background: #ffc107; color: #333; }
        .btn-edit { background: #17a2b8; color: white; }
        .btn-delete { background: #dc3545; color: white; }
        
        .player-preview {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-top: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .audio-player {
            width: 100%;
            margin: 15px 0;
        }
        
        .tab-nav {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            border-bottom: 2px solid #dee2e6;
        }
        
        .tab-link {
            padding: 10px 20px;
            background: none;
            border: none;
            border-bottom: 2px solid transparent;
            color: #666;
            cursor: pointer;
            font-weight: 500;
        }
        
        .tab-link.active {
            color: #8B4513;
            border-bottom-color: #8B4513;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .drag-drop-area {
            border: 3px dashed #8B4513;
            border-radius: 10px;
            padding: 40px;
            text-align: center;
            background: #f9f5f0;
            margin: 20px 0;
            cursor: pointer;
        }
        
        .drag-drop-area:hover {
            background: #f5f0e6;
        }
        
        .drag-drop-icon {
            font-size: 3em;
            color: #8B4513;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <?php include 'includes/header.php'; ?>
    
    <div class="admin-container">
        <!-- Sidebar -->
        <?php include 'includes/sidebar.php'; ?>
        
        <!-- Main Content -->
        <main class="content">
            <div class="page-header">
                <h1><i class="fas fa-music"></i> Kelola Musik Pernikahan</h1>
                <p>Upload dan kelola lagu untuk website pernikahan</p>
            </div>
            
            <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?>
            </div>
            <?php endif; ?>
            
            <!-- Stats -->
            <div class="music-stats">
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-headphones"></i></div>
                    <div class="stat-number"><?= $stats['total_songs'] ?></div>
                    <div class="stat-label">Total Lagu</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-play-circle"></i></div>
                    <div class="stat-number"><?= $stats['active_songs'] ?></div>
                    <div class="stat-label">Lagu Aktif</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-clock"></i></div>
                    <div class="stat-number"><?= floor($stats['total_duration'] / 60) ?>m</div>
                    <div class="stat-label">Total Durasi</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-hdd"></i></div>
                    <div class="stat-number"><?= formatFilesize($stats['total_size']) ?></div>
                    <div class="stat-label">Total Ukuran</div>
                </div>
            </div>
            
            <!-- Tabs -->
            <div class="tab-nav">
                <button class="tab-link <?= $currentTab == 'songs' ? 'active' : '' ?>" 
                        data-tab="songs">
                    <i class="fas fa-list"></i> Daftar Lagu
                </button>
                <button class="tab-link <?= $currentTab == 'upload' ? 'active' : '' ?>" 
                        data-tab="upload">
                    <i class="fas fa-upload"></i> Upload Lagu
                </button>
                <button class="tab-link <?= $currentTab == 'player' ? 'active' : '' ?>" 
                        data-tab="player">
                    <i class="fas fa-play"></i> Preview Player
                </button>
            </div>
            
            <!-- Tab Content: Songs List -->
            <div class="tab-content <?= $currentTab == 'songs' ? 'active' : '' ?>" id="songs">
                <div class="song-list">
                    <?php if (empty($songs)): ?>
                    <div style="padding: 40px; text-align: center; color: #666;">
                        <i class="fas fa-music" style="font-size: 3em; margin-bottom: 15px; opacity: 0.3;"></i>
                        <h3>Belum ada lagu</h3>
                        <p>Upload lagu pertama Anda untuk memulai</p>
                    </div>
                    <?php else: ?>
                    <?php foreach ($songs as $song): ?>
                    <div class="song-item <?= $song['is_default'] ? 'default' : '' ?>">
                        <div class="song-info">
                            <div class="song-title">
                                <?= htmlspecialchars($song['title']) ?>
                                <?php if ($song['is_default']): ?>
                                <span style="color: #ffc107; font-size: 0.8em;">
                                    <i class="fas fa-star"></i> Default
                                </span>
                                <?php endif; ?>
                            </div>
                            <div class="song-meta">
                                <span><i class="fas fa-user"></i> <?= htmlspecialchars($song['artist']) ?></span>
                                <span><i class="fas fa-clock"></i> <?= formatDuration($song['duration']) ?></span>
                                <span><i class="fas fa-hdd"></i> <?= formatFilesize($song['filesize']) ?></span>
                                <span><i class="fas fa-calendar"></i> <?= date('d/m/Y', strtotime($song['uploaded_at'])) ?></span>
                            </div>
                        </div>
                        
                        <div class="song-actions">
                            <button class="btn-action btn-play" 
                                    onclick="playPreview('<?= $song['filename'] ?>', '<?= htmlspecialchars($song['title']) ?>')">
                                <i class="fas fa-play"></i> Play
                            </button>
                            
                            <?php if (!$song['is_default']): ?>
                            <a href="?action=set_default&id=<?= $song['id'] ?>&tab=songs" 
                               class="btn-action btn-default">
                                <i class="fas fa-star"></i> Set Default
                            </a>
                            <?php endif; ?>
                            
                            <button class="btn-action btn-edit" 
                                    onclick="editSong(<?= $song['id'] ?>)">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            
                            <button class="btn-action btn-delete" 
                                    onclick="confirmDelete(<?= $song['id'] ?>, '<?= htmlspecialchars($song['title']) ?>')">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </div>
                        
                        <!-- Hidden audio element for preview -->
                        <audio id="preview-<?= $song['id'] ?>" 
                               src="../assets/music/<?= $song['filename'] ?>">
                        </audio>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <?php if (!empty($songs)): ?>
                <div style="margin-top: 20px; text-align: center; color: #666; font-size: 0.9em;">
                    <p>Total <?= count($songs) ?> lagu • 
                       <?= floor($stats['total_duration'] / 60) ?> menit • 
                       <?= formatFilesize($stats['total_size']) ?></p>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Tab Content: Upload -->
            <div class="tab-content <?= $currentTab == 'upload' ? 'active' : '' ?>" id="upload">
                <div class="upload-form">
                    <h3><i class="fas fa-upload"></i> Upload Lagu Baru</h3>
                    <p>Upload file MP3, OGG, M4A, atau WAV (maks. 10MB)</p>
                    
                    <!-- Drag & Drop Area -->
                    <div class="drag-drop-area" id="dragDropArea">
                        <div class="drag-drop-icon">
                            <i class="fas fa-cloud-upload-alt"></i>
                        </div>
                        <h4>Drag & Drop file lagu di sini</h4>
                        <p>atau klik untuk memilih file</p>
                        <input type="file" id="fileInput" name="song_file" accept=".mp3,.ogg,.m4a,.wav" style="display: none;">
                        <div id="fileName" style="margin-top: 10px; font-weight: bold;"></div>
                    </div>
                    
                    <form method="POST" enctype="multipart/form-data" id="uploadForm">
                        <input type="hidden" name="song_file" id="hiddenFileInput">
                        
                        <div class="form-group">
                            <label for="title"><i class="fas fa-heading"></i> Judul Lagu *</label>
                            <input type="text" 
                                   id="title" 
                                   name="title" 
                                   class="form-control" 
                                   placeholder="Contoh: Wedding March" 
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label for="artist"><i class="fas fa-user"></i> Nama Artis</label>
                            <input type="text" 
                                   id="artist" 
                                   name="artist" 
                                   class="form-control" 
                                   placeholder="Contoh: Johann Sebastian Bach">
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-info-circle"></i> Format yang didukung:</label>
                            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                                <span style="background: #e9ecef; padding: 5px 10px; border-radius: 4px;">.mp3</span>
                                <span style="background: #e9ecef; padding: 5px 10px; border-radius: 4px;">.ogg</span>
                                <span style="background: #e9ecef; padding: 5px 10px; border-radius: 4px;">.m4a</span>
                                <span style="background: #e9ecef; padding: 5px 10px; border-radius: 4px;">.wav</span>
                            </div>
                            <small style="color: #666; display: block; margin-top: 5px;">
                                Ukuran maksimal: 10MB per file
                            </small>
                        </div>
                        
                        <button type="submit" class="btn-primary" style="width: 100%; padding: 12px;">
                            <i class="fas fa-upload"></i> Upload Lagu
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Tab Content: Player Preview -->
            <div class="tab-content <?= $currentTab == 'player' ? 'active' : '' ?>" id="player">
                <div class="player-preview">
                    <h3><i class="fas fa-play-circle"></i> Preview Music Player</h3>
                    <p>Ini adalah preview player yang akan muncul di website:</p>
                    
                    <div id="playerContainer">
                        <?= $musicPlayer->generatePlayer(true, true, true) ?>
                    </div>
                    
                    <div style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 8px;">
                        <h4><i class="fas fa-cog"></i> Pengaturan Player</h4>
                        
                        <div style="display: flex; gap: 20px; margin-top: 10px;">
                            <label>
                                <input type="checkbox" id="autoplay" checked> Autoplay
                            </label>
                            <label>
                                <input type="checkbox" id="loop" checked> Loop
                            </label>
                            <label>
                                <input type="checkbox" id="controls" checked> Tampilkan Kontrol
                            </label>
                        </div>
                        
                        <button onclick="updatePlayer()" class="btn-primary" style="margin-top: 15px;">
                            <i class="fas fa-sync"></i> Update Player
                        </button>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>
    
    <script>
    // Tab Navigation
    document.querySelectorAll('.tab-link').forEach(tab => {
        tab.addEventListener('click', function() {
            document.querySelectorAll('.tab-link').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            
            this.classList.add('active');
            const tabId = this.getAttribute('data-tab');
            document.getElementById(tabId).classList.add('active');
            
            // Update URL
            const url = new URL(window.location);
            url.searchParams.set('tab', tabId);
            window.history.pushState({}, '', url);
        });
    });
    
    // Play Preview
    function playPreview(filename, title) {
        const audio = new Audio(`../assets/music/${filename}`);
        audio.play();
        
        // Show notification
        showNotification(`Memutar: ${title}`, 'info');
    }
    
    // Edit Song
    function editSong(id) {
        window.location.href = `music-edit.php?id=${id}`;
    }
    
    // Confirm Delete
    function confirmDelete(id, title) {
        if (confirm(`Hapus lagu "${title}"?`)) {
            window.location.href = `?action=delete&id=${id}&tab=songs`;
        }
    }
    
    // Drag & Drop Upload
    const dragDropArea = document.getElementById('dragDropArea');
    const fileInput = document.getElementById('fileInput');
    const fileName = document.getElementById('fileName');
    const hiddenFileInput = document.getElementById('hiddenFileInput');
    
    dragDropArea.addEventListener('click', () => fileInput.click());
    
    fileInput.addEventListener('change', function() {
        if (this.files.length > 0) {
            const file = this.files[0];
            fileName.textContent = `File terpilih: ${file.name} (${formatBytes(file.size)})`;
            
            // Auto-fill title from filename
            const titleInput = document.getElementById('title');
            if (!titleInput.value) {
                const nameWithoutExt = file.name.replace(/\.[^/.]+$/, "");
                titleInput.value = nameWithoutExt.replace(/[_-]/g, ' ');
            }
        }
    });
    
    dragDropArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        dragDropArea.style.borderColor = '#8B4513';
        dragDropArea.style.background = '#f5f0e6';
    });
    
    dragDropArea.addEventListener('dragleave', () => {
        dragDropArea.style.borderColor = '#8B4513';
        dragDropArea.style.background = '#f9f5f0';
    });
    
    dragDropArea.addEventListener('drop', (e) => {
        e.preventDefault();
        dragDropArea.style.borderColor = '#8B4513';
        dragDropArea.style.background = '#f9f5f0';
        
        if (e.dataTransfer.files.length > 0) {
            const file = e.dataTransfer.files[0];
            
            // Check file type
            const allowedTypes = ['audio/mpeg', 'audio/ogg', 'audio/mp4', 'audio/wav', 'audio/x-m4a'];
            if (!allowedTypes.includes(file.type)) {
                alert('Hanya file audio yang diizinkan (MP3, OGG, M4A, WAV)');
                return;
            }
            
            // Check file size (10MB)
            if (file.size > 10 * 1024 * 1024) {
                alert('Ukuran file maksimal 10MB');
                return;
            }
            
            fileInput.files = e.dataTransfer.files;
            fileName.textContent = `File terpilih: ${file.name} (${formatBytes(file.size)})`;
            
            // Auto-fill title from filename
            const titleInput = document.getElementById('title');
            if (!titleInput.value) {
                const nameWithoutExt = file.name.replace(/\.[^/.]+$/, "");
                titleInput.value = nameWithoutExt.replace(/[_-]/g, ' ');
            }
        }
    });
    
    function formatBytes(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
    
    // Update Player Preview
    function updatePlayer() {
        const autoplay = document.getElementById('autoplay').checked;
        const loop = document.getElementById('loop').checked;
        const controls = document.getElementById('controls').checked;
        
        // Reload page with new settings
        const playerContainer = document.getElementById('playerContainer');
        playerContainer.innerHTML = `
            <div style="text-align: center; padding: 20px;">
                <i class="fas fa-spinner fa-spin"></i> Memuat player...
            </div>
        `;
        
        // In a real implementation, you would make an AJAX call here
        // For now, just reload the player with current settings
        setTimeout(() => {
            playerContainer.innerHTML = `
                <audio controls ${autoplay ? 'autoplay' : ''} ${loop ? 'loop' : ''} 
                       ${controls ? '' : 'style="display: none;"'}>
                    <source src="../assets/music/<?= $defaultSong['filename'] ?? '' ?>" type="audio/mpeg">
                </audio>
                ${!controls ? `
                <div style="margin-top: 10px;">
                    <button onclick="this.parentElement.previousElementSibling.play()" class="btn-primary">
                        <i class="fas fa-play"></i> Play
                    </button>
                    <button onclick="this.parentElement.previousElementSibling.pause()" class="btn-secondary">
                        <i class="fas fa-pause"></i> Pause
                    </button>
                </div>
                ` : ''}
            `;
        }, 500);
    }
    
    // Show notification
    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            background: ${type === 'info' ? '#17a2b8' : type === 'success' ? '#28a745' : '#dc3545'};
            color: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 10000;
            animation: slideIn 0.3s ease;
        `;
        notification.innerHTML = `
            <i class="fas fa-${type === 'info' ? 'info-circle' : type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
            ${message}
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }
    
    // Add CSS animations
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes slideOut {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(100%); opacity: 0; }
        }
    `;
    document.head.appendChild(style);
    </script>
</body>
</html>