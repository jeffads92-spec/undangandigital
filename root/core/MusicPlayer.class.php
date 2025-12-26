<?php
/**
 * MUSIC PLAYER CLASS
 * Sistem kelola lagu pernikahan
 */

class MusicPlayer {
    private $db;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->db = new Database();
    }
    
    /**
     * Get all songs
     */
    public function getAllSongs($activeOnly = false) {
        $sql = "SELECT * FROM wedding_songs";
        
        if ($activeOnly) {
            $sql .= " WHERE is_active = 1";
        }
        
        $sql .= " ORDER BY play_order ASC, title ASC";
        
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Get active playlist
     */
    public function getPlaylist() {
        return $this->db->fetchAll(
            "SELECT * FROM wedding_songs 
             WHERE is_active = 1 
             ORDER BY play_order ASC, title ASC"
        );
    }
    
    /**
     * Get default song
     */
    public function getDefaultSong() {
        return $this->db->fetch(
            "SELECT * FROM wedding_songs 
             WHERE is_default = 1 AND is_active = 1 
             LIMIT 1"
        );
    }
    
    /**
     * Add new song
     */
    public function addSong($data, $file) {
        // Validate data
        if (empty($data['title']) || empty($file['name'])) {
            return ['success' => false, 'message' => 'Judul lagu dan file wajib diisi'];
        }
        
        // Upload file
        $upload = new Upload();
        $result = $upload->uploadMusic($file, $data);
        
        if (!$result['success']) {
            return $result;
        }
        
        // If this is the first song, set as default
        $count = $this->db->fetchColumn("SELECT COUNT(*) FROM wedding_songs");
        $isDefault = ($count == 0) ? 1 : 0;
        
        // Get next play order
        $maxOrder = $this->db->fetchColumn("SELECT MAX(play_order) FROM wedding_songs");
        $playOrder = ($maxOrder ? $maxOrder + 1 : 1);
        
        // Insert to database
        $songId = $this->db->insert('wedding_songs', [
            'title' => $data['title'],
            'artist' => $data['artist'] ?? 'Unknown',
            'filename' => $result['filename'],
            'duration' => $result['duration'],
            'filetype' => $file['type'],
            'filesize' => $file['size'],
            'is_active' => 1,
            'is_default' => $isDefault,
            'play_order' => $playOrder,
            'uploaded_at' => date('Y-m-d H:i:s')
        ]);
        
        return [
            'success' => true,
            'id' => $songId,
            'data' => $result
        ];
    }
    
    /**
     * Update song
     */
    public function updateSong($id, $data) {
        $updateData = [];
        
        if (isset($data['title'])) {
            $updateData['title'] = $data['title'];
        }
        
        if (isset($data['artist'])) {
            $updateData['artist'] = $data['artist'];
        }
        
        if (isset($data['is_active'])) {
            $updateData['is_active'] = $data['is_active'];
        }
        
        if (isset($data['play_order'])) {
            $updateData['play_order'] = $data['play_order'];
        }
        
        if (empty($updateData)) {
            return ['success' => false, 'message' => 'Tidak ada data yang diupdate'];
        }
        
        $updateData['updated_at'] = date('Y-m-d H:i:s');
        
        $this->db->update('wedding_songs', $updateData, 'id = ?', [$id]);
        
        return ['success' => true, 'message' => 'Lagu berhasil diupdate'];
    }
    
    /**
     * Set default song
     */
    public function setDefaultSong($id) {
        // Reset all songs to not default
        $this->db->update('wedding_songs', 
            ['is_default' => 0, 'updated_at' => date('Y-m-d H:i:s')],
            'is_default = 1'
        );
        
        // Set selected song as default
        $this->db->update('wedding_songs', 
            ['is_default' => 1, 'updated_at' => date('Y-m-d H:i:s')],
            'id = ?', [$id]
        );
        
        return ['success' => true, 'message' => 'Lagu default berhasil diubah'];
    }
    
    /**
     * Delete song
     */
    public function deleteSong($id) {
        // Get song filename
        $song = $this->db->fetch("SELECT filename FROM wedding_songs WHERE id = ?", [$id]);
        
        if (!$song) {
            return ['success' => false, 'message' => 'Lagu tidak ditemukan'];
        }
        
        // Delete file
        $upload = new Upload();
        $deleteResult = $upload->deleteFile($song['filename'], 'music/');
        
        if (!$deleteResult['success']) {
            return $deleteResult;
        }
        
        // Delete from database
        $this->db->delete('wedding_songs', 'id = ?', [$id]);
        
        // If deleted song was default, set another as default
        $default = $this->getDefaultSong();
        if (!$default) {
            $firstSong = $this->db->fetch(
                "SELECT id FROM wedding_songs WHERE is_active = 1 LIMIT 1"
            );
            
            if ($firstSong) {
                $this->setDefaultSong($firstSong['id']);
            }
        }
        
        return ['success' => true, 'message' => 'Lagu berhasil dihapus'];
    }
    
    /**
     * Reorder songs
     */
    public function reorderSongs($order) {
        $this->db->beginTransaction();
        
        try {
            foreach ($order as $position => $songId) {
                $this->db->update('wedding_songs', 
                    ['play_order' => $position + 1, 'updated_at' => date('Y-m-d H:i:s')],
                    'id = ?', [$songId]
                );
            }
            
            $this->db->commit();
            return ['success' => true, 'message' => 'Urutan lagu berhasil diupdate'];
            
        } catch (Exception $e) {
            $this->db->rollback();
            return ['success' => false, 'message' => 'Gagal mengupdate urutan: ' . $e->getMessage()];
        }
    }
    
    /**
     * Get song URL
     */
    public function getSongUrl($filename) {
        return BASE_URL . 'assets/music/' . $filename;
    }
    
    /**
     * Generate HTML player
     */
    public function generatePlayer($autoplay = false, $loop = true, $controls = true) {
        $songs = $this->getPlaylist();
        
        if (empty($songs)) {
            return '<p>Tidak ada lagu yang tersedia</p>';
        }
        
        $defaultSong = $this->getDefaultSong();
        $firstSong = $defaultSong ?: $songs[0];
        
        $html = '
        <div class="music-player">
            <audio id="weddingMusic" ' . ($controls ? 'controls' : '') . ' 
                   ' . ($autoplay ? 'autoplay' : '') . ' 
                   ' . ($loop ? 'loop' : '') . '>
                <source src="' . $this->getSongUrl($firstSong['filename']) . '" type="audio/mpeg">
                Browser Anda tidak mendukung pemutar audio.
            </audio>
            
            <div class="playlist" style="display: none;">
                <select id="songSelect" onchange="changeSong(this.value)">
                    <option value="">Pilih Lagu</option>';
        
        foreach ($songs as $song) {
            $selected = ($song['id'] == $firstSong['id']) ? 'selected' : '';
            $html .= '<option value="' . $song['id'] . '" ' . $selected . '>' 
                   . htmlspecialchars($song['title']) . ' - ' . htmlspecialchars($song['artist']) 
                   . '</option>';
        }
        
        $html .= '
                </select>
            </div>
            
            <div class="player-controls">
                <button onclick="toggleMusic()" id="playBtn">
                    <i class="fas fa-play"></i> <span id="playText">Putar</span>
                </button>
                <button onclick="nextSong()">
                    <i class="fas fa-forward"></i>
                </button>
                <button onclick="changeVolume(0.1)">
                    <i class="fas fa-volume-up"></i>
                </button>
                <span id="nowPlaying">' . htmlspecialchars($firstSong['title']) . '</span>
            </div>
        </div>
        
        <script>
        const songs = ' . json_encode($songs) . ';
        const audio = document.getElementById("weddingMusic");
        let currentSongIndex = 0;
        
        function toggleMusic() {
            if (audio.paused) {
                audio.play();
                document.getElementById("playBtn").innerHTML = \'<i class="fas fa-pause"></i> <span id="playText">Jeda</span>\';
            } else {
                audio.pause();
                document.getElementById("playBtn").innerHTML = \'<i class="fas fa-play"></i> <span id="playText">Putar</span>\';
            }
        }
        
        function changeSong(songId) {
            const song = songs.find(s => s.id == songId);
            if (song) {
                audio.src = "' . BASE_URL . 'assets/music/" + song.filename;
                audio.play();
                document.getElementById("nowPlaying").textContent = song.title;
                document.getElementById("playBtn").innerHTML = \'<i class="fas fa-pause"></i> <span id="playText">Jeda</span>\';
                currentSongIndex = songs.findIndex(s => s.id == songId);
            }
        }
        
        function nextSong() {
            currentSongIndex = (currentSongIndex + 1) % songs.length;
            const song = songs[currentSongIndex];
            audio.src = "' . BASE_URL . 'assets/music/" + song.filename;
            audio.play();
            document.getElementById("nowPlaying").textContent = song.title;
            document.getElementById("songSelect").value = song.id;
        }
        
        function changeVolume(delta) {
            audio.volume = Math.min(1, Math.max(0, audio.volume + delta));
        }
        
        // Auto-play on user interaction
        document.addEventListener("click", function initAudio() {
            if (audio.paused && ' . ($autoplay ? 'true' : 'false') . ') {
                audio.play().catch(e => console.log("Auto-play prevented:", e));
            }
            document.removeEventListener("click", initAudio);
        });
        
        // Update play button state
        audio.addEventListener("play", function() {
            document.getElementById("playBtn").innerHTML = \'<i class="fas fa-pause"></i> <span id="playText">Jeda</span>\';
        });
        
        audio.addEventListener("pause", function() {
            document.getElementById("playBtn").innerHTML = \'<i class="fas fa-play"></i> <span id="playText">Putar</span>\';
        });
        
        // Auto-next when song ends
        audio.addEventListener("ended", function() {
            if (!audio.loop) {
                nextSong();
            }
        });
        </script>';
        
        return $html;
    }
    
    /**
     * Get statistics
     */
    public function getStats() {
        return [
            'total_songs' => $this->db->fetchColumn("SELECT COUNT(*) FROM wedding_songs"),
            'active_songs' => $this->db->fetchColumn("SELECT COUNT(*) FROM wedding_songs WHERE is_active = 1"),
            'total_duration' => $this->db->fetchColumn("SELECT SUM(duration) FROM wedding_songs"),
            'total_size' => $this->db->fetchColumn("SELECT SUM(filesize) FROM wedding_songs")
        ];
    }
}
?>
