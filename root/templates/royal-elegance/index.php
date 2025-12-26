<?php
/**
 * TEMPLATE: ROYAL ELEGANCE
 * Template premium untuk pernikahan dengan sentuhan kerajaan
 */

// Load template configuration
$templateConfig = require_once __DIR__ . '/config.php';

// Get settings from database or default
$settings = $GLOBALS['settings'] ?? [];
$currentPage = $data['page'] ?? 'home';
$baseUrl = $data['base_url'] ?? BASE_URL;

// SEO Settings
$seoTitle = $settings['wedding_title'] ?? 'Pernikahan Kita';
$seoDescription = $settings['meta_description'] ?? 'Undangan pernikahan digital dengan fitur lengkap';
$seoKeywords = $settings['meta_keywords'] ?? 'undangan digital, pernikahan, wedding';

// Music Player
$musicPlayer = new MusicPlayer();
$songs = $musicPlayer->getPlaylist();
$defaultSong = $musicPlayer->getDefaultSong();

// WhatsApp Link
$whatsappNumber = $settings['whatsapp_number'] ?? '6281234567890';
$whatsappLink = "https://wa.me/{$whatsappNumber}?text=" . urlencode(
    'Halo, saya melihat undangan pernikahan ' . ($settings['wedding_title'] ?? 'Anda')
);

// QRIS Payment
$bankName = $settings['bank_name'] ?? 'BCA';
$bankAccount = $settings['bank_account'] ?? '1234567890';
$accountName = $settings['account_name'] ?? 'Nama Pemilik Rekening';

// Countdown
$weddingDate = $settings['wedding_date'] ?? date('Y-m-d', strtotime('+30 days'));
$weddingTime = $settings['wedding_time'] ?? '14:00';
?>
<!DOCTYPE html>
<html lang="id" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- SEO Meta Tags -->
    <title><?= htmlspecialchars($seoTitle) ?> - Royal Elegance</title>
    <meta name="description" content="<?= htmlspecialchars($seoDescription) ?>">
    <meta name="keywords" content="<?= htmlspecialchars($seoKeywords) ?>">
    <meta name="author" content="<?= SITE_NAME ?>">
    
    <!-- Open Graph -->
    <meta property="og:title" content="<?= htmlspecialchars($seoTitle) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($seoDescription) ?>">
    <meta property="og:image" content="<?= $baseUrl ?>assets/images/cover.jpg">
    <meta property="og:url" content="<?= $baseUrl ?>">
    <meta property="og:type" content="website">
    
    <!-- PWA -->
    <link rel="manifest" href="<?= $baseUrl ?>manifest.json">
    <meta name="theme-color" content="#8B4513">
    
    <!-- Favicon -->
    <link rel="icon" href="<?= $baseUrl ?>assets/icons/favicon.ico">
    <link rel="apple-touch-icon" href="<?= $baseUrl ?>assets/icons/icon-192.png">
    
    <!-- CSS -->
    <link rel="stylesheet" href="<?= $baseUrl ?>assets/css/main.css">
    <link rel="stylesheet" href="<?= $baseUrl ?>templates/royal-elegance/assets/css/style.css">
    <link rel="stylesheet" href="<?= $baseUrl ?>templates/royal-elegance/assets/css/custom.css">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Poppins:wght@300;400;500;600&family=Great+Vibes&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- JSON-LD Structured Data -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Event",
        "name": "<?= htmlspecialchars($seoTitle) ?>",
        "description": "<?= htmlspecialchars($seoDescription) ?>",
        "startDate": "<?= $weddingDate ?>T<?= $weddingTime ?>",
        "endDate": "<?= $weddingDate ?>T17:00",
        "eventAttendanceMode": "https://schema.org/OfflineEventAttendanceMode",
        "eventStatus": "https://schema.org/EventScheduled",
        "location": {
            "@type": "Place",
            "name": "<?= $settings['location'] ?? 'Jakarta' ?>",
            "address": {
                "@type": "PostalAddress",
                "addressLocality": "Jakarta",
                "addressCountry": "ID"
            }
        },
        "organizer": {
            "@type": "Person",
            "name": "<?= $settings['groom_name'] ?? 'Mempelai Pria' ?> & <?= $settings['bride_name'] ?? 'Mempelai Wanita' ?>"
        },
        "image": "<?= $baseUrl ?>assets/images/cover.jpg",
        "performer": {
            "@type": "Person",
            "name": "<?= $settings['groom_name'] ?? 'Mempelai Pria' ?> & <?= $settings['bride_name'] ?? 'Mempelai Wanita' ?>"
        }
    }
    </script>
    
    <style>
        /* Template Variables */
        :root {
            --primary-color: <?= $templateConfig['colors']['primary'] ?? '#8B4513' ?>;
            --secondary-color: <?= $templateConfig['colors']['secondary'] ?? '#DAA520' ?>;
            --accent-color: <?= $templateConfig['colors']['accent'] ?? '#4B0082' ?>;
            --text-color: <?= $templateConfig['colors']['text'] ?? '#333333' ?>;
            --background-color: <?= $templateConfig['colors']['background'] ?? '#f9f5f0' ?>;
            --card-color: <?= $templateConfig['colors']['card'] ?? '#ffffff' ?>;
            --font-primary: <?= $templateConfig['fonts']['primary'] ?? "'Playfair Display', serif" ?>;
            --font-secondary: <?= $templateConfig['fonts']['secondary'] ?? "'Poppins', sans-serif" ?>;
            --font-script: <?= $templateConfig['fonts']['script'] ?? "'Great Vibes', cursive" ?>;
        }
    </style>
</head>
<body>
    <!-- Loading Screen -->
    <div id="loading" class="loading-screen">
        <div class="loading-content">
            <div class="loading-spinner"></div>
            <p class="loading-text">Membuka Undangan...</p>
        </div>
    </div>
    
    <!-- Music Player (Floating) -->
    <div class="music-player-floating" id="musicPlayer">
        <div class="player-controls">
            <button onclick="toggleMusic()" id="playBtn" class="player-btn">
                <i class="fas fa-play"></i>
            </button>
            <div class="player-info">
                <span id="nowPlaying"><?= $defaultSong['title'] ?? 'Wedding Music' ?></span>
                <select id="songSelect" onchange="changeSong(this.value)">
                    <option value="">Pilih Lagu</option>
                    <?php foreach ($songs as $song): ?>
                    <option value="<?= $song['id'] ?>"><?= htmlspecialchars($song['title']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button onclick="togglePlayer()" class="player-btn close-btn">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <audio id="weddingAudio" loop>
            <source src="<?= $baseUrl ?>assets/music/<?= $defaultSong['filename'] ?? 'default.mp3' ?>" type="audio/mpeg">
        </audio>
    </div>
    
    <!-- Navigation -->
    <nav class="main-nav">
        <div class="nav-container">
            <div class="nav-logo">
                <h1><?= htmlspecialchars($settings['groom_name'] ?? 'Mempelai Pria') ?> & <?= htmlspecialchars($settings['bride_name'] ?? 'Mempelai Wanita') ?></h1>
                <p class="nav-subtitle">The Wedding</p>
            </div>
            
            <div class="nav-menu" id="navMenu">
                <a href="#home" class="nav-link active" onclick="showSection('home')">
                    <i class="fas fa-home"></i> Beranda
                </a>
                <a href="#couple" class="nav-link" onclick="showSection('couple')">
                    <i class="fas fa-heart"></i> Pasangan
                </a>
                <a href="#events" class="nav-link" onclick="showSection('events')">
                    <i class="fas fa-calendar-alt"></i> Acara
                </a>
                <a href="#gallery" class="nav-link" onclick="showSection('gallery')">
                    <i class="fas fa-images"></i> Galeri
                </a>
                <a href="#rsvp" class="nav-link" onclick="showSection('rsvp')">
                    <i class="fas fa-check-circle"></i> RSVP
                </a>
                <a href="#gifts" class="nav-link" onclick="showSection('gifts')">
                    <i class="fas fa-gift"></i> Hadiah
                </a>
                <a href="#messages" class="nav-link" onclick="showSection('messages')">
                    <i class="fas fa-comments"></i> Ucapan
                </a>
            </div>
            
            <button class="nav-toggle" id="navToggle">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </nav>
    
    <!-- Main Content -->
    <main class="main-content">
        <!-- Home Section -->
        <section id="home" class="section active">
            <div class="hero-section">
                <div class="hero-overlay"></div>
                <div class="hero-content">
                    <div class="hero-text">
                        <h2 class="hero-subtitle">The Wedding of</h2>
                        <h1 class="hero-title">
                            <span class="groom-name"><?= htmlspecialchars($settings['groom_name'] ?? 'Mempelai Pria') ?></span>
                            <span class="and">&</span>
                            <span class="bride-name"><?= htmlspecialchars($settings['bride_name'] ?? 'Mempelai Wanita') ?></span>
                        </h1>
                        <p class="hero-date">
                            <i class="far fa-calendar-alt"></i>
                            <?= date('d F Y', strtotime($weddingDate)) ?>
                        </p>
                        <p class="hero-time">
                            <i class="far fa-clock"></i>
                            <?= $weddingTime ?> WIB
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Countdown -->
            <div class="countdown-section">
                <h3 class="countdown-title">Menuju Hari Bahagia</h3>
                <div class="countdown-timer" data-date="<?= $weddingDate ?> <?= $weddingTime ?>">
                    <div class="countdown-item">
                        <span class="countdown-number" id="days">00</span>
                        <span class="countdown-label">Hari</span>
                    </div>
                    <div class="countdown-item">
                        <span class="countdown-number" id="hours">00</span>
                        <span class="countdown-label">Jam</span>
                    </div>
                    <div class="countdown-item">
                        <span class="countdown-number" id="minutes">00</span>
                        <span class="countdown-label">Menit</span>
                    </div>
                    <div class="countdown-item">
                        <span class="countdown-number" id="seconds">00</span>
                        <span class="countdown-label">Detik</span>
                    </div>
                </div>
            </div>
            
            <!-- Opening Button -->
            <div class="opening-section">
                <button class="btn-open-invitation" onclick="openInvitation()">
                    <i class="fas fa-envelope-open-text"></i> Buka Undangan
                </button>
                <p class="opening-note">
                    Klik tombol di atas untuk membuka undangan secara resmi
                </p>
            </div>
        </section>
        
        <!-- Other sections (couple, events, gallery, rsvp, gifts, messages) -->
        <!-- Will be loaded via AJAX or included based on routing -->
        
        <!-- Default content if no section specified -->
        <?php if ($currentPage === 'home'): ?>
            <?php include __DIR__ . '/pages/home.php'; ?>
        <?php elseif ($currentPage === 'couple'): ?>
            <?php include __DIR__ . '/pages/couple.php'; ?>
        <?php elseif ($currentPage === 'events'): ?>
            <?php include __DIR__ . '/pages/events.php'; ?>
        <?php elseif ($currentPage === 'gallery'): ?>
            <?php include __DIR__ . '/pages/gallery.php'; ?>
        <?php elseif ($currentPage === 'rsvp'): ?>
            <?php include __DIR__ . '/pages/rsvp.php'; ?>
        <?php elseif ($currentPage === 'gifts'): ?>
            <?php include __DIR__ . '/pages/gifts.php'; ?>
        <?php elseif ($currentPage === 'messages'): ?>
            <?php include __DIR__ . '/pages/messages.php'; ?>
        <?php endif; ?>
    </main>
    
    <!-- Footer -->
    <footer class="main-footer">
        <div class="footer-container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3 class="footer-title"><?= htmlspecialchars($settings['groom_name'] ?? 'Mempelai Pria') ?> & <?= htmlspecialchars($settings['bride_name'] ?? 'Mempelai Wanita') ?></h3>
                    <p class="footer-text">Undangan Pernikahan Digital</p>
                </div>
                
                <div class="footer-section">
                    <h4 class="footer-subtitle">Kontak</h4>
                    <p><i class="fas fa-phone"></i> <?= $settings['whatsapp_number'] ?? '081234567890' ?></p>
                    <p><i class="fas fa-envelope"></i> <?= $settings['email'] ?? 'info@example.com' ?></p>
                </div>
                
                <div class="footer-section">
                    <h4 class="footer-subtitle">Bagikan</h4>
                    <div class="social-share">
                        <button class="share-btn" onclick="shareWhatsApp()">
                            <i class="fab fa-whatsapp"></i>
                        </button>
                        <button class="share-btn" onclick="shareFacebook()">
                            <i class="fab fa-facebook"></i>
                        </button>
                        <button class="share-btn" onclick="shareTelegram()">
                            <i class="fab fa-telegram"></i>
                        </button>
                        <button class="share-btn" onclick="copyLink()">
                            <i class="fas fa-link"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> <?= SITE_NAME ?>. All rights reserved.</p>
                <p>Made with <i class="fas fa-heart" style="color: #e74c3c;"></i> for your special day</p>
            </div>
        </div>
    </footer>
    
    <!-- WhatsApp Float -->
    <a href="<?= $whatsappLink ?>" class="whatsapp-float" target="_blank">
        <i class="fab fa-whatsapp"></i>
    </a>
    
    <!-- Back to Top -->
    <button class="back-to-top" id="backToTop" onclick="scrollToTop()">
        <i class="fas fa-chevron-up"></i>
    </button>
    
    <!-- JavaScript -->
    <script src="<?= $baseUrl ?>assets/js/main.js"></script>
    <script src="<?= $baseUrl ?>templates/royal-elegance/assets/js/script.js"></script>
    
    <script>
    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        // Hide loading screen
        setTimeout(() => {
            document.getElementById('loading').style.display = 'none';
        }, 1500);
        
        // Initialize countdown
        initCountdown('<?= $weddingDate ?> <?= $weddingTime ?>');
        
        // Initialize music player
        initMusicPlayer();
        
        // Check PWA
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('<?= $baseUrl ?>service-worker.js');
        }
    });
    
    // Open invitation function
    function openInvitation() {
        // Play music
        const audio = document.getElementById('weddingAudio');
        audio.play().then(() => {
            document.getElementById('playBtn').innerHTML = '<i class="fas fa-pause"></i>';
        }).catch(e => {
            console.log('Autoplay prevented:', e);
        });
        
        // Show notification
        showNotification('Undangan telah dibuka! Selamat menikmati.', 'success');
        
        // Scroll to next section
        document.getElementById('couple').scrollIntoView({ behavior: 'smooth' });
    }
    
    // Navigation
    function showSection(sectionId) {
        // Hide all sections
        document.querySelectorAll('.section').forEach(section => {
            section.classList.remove('active');
        });
        
        // Show selected section
        document.getElementById(sectionId).classList.add('active');
        
        // Update nav links
        document.querySelectorAll('.nav-link').forEach(link => {
            link.classList.remove('active');
        });
        document.querySelector(`[href="#${sectionId}"]`).classList.add('active');
        
        // Scroll to section
        window.scrollTo({
            top: document.getElementById(sectionId).offsetTop - 80,
            behavior: 'smooth'
        });
    }
    
    // Share functions
    function shareWhatsApp() {
        const url = encodeURIComponent(window.location.href);
        const text = encodeURIComponent('Undangan pernikahan <?= htmlspecialchars($settings['groom_name'] ?? '') ?> & <?= htmlspecialchars($settings['bride_name'] ?? '') ?>');
        window.open(`https://wa.me/?text=${text}%20${url}`, '_blank');
    }
    
    function copyLink() {
        navigator.clipboard.writeText(window.location.href);
        showNotification('Link berhasil disalin!', 'success');
    }
    
    // Music player functions
    function initMusicPlayer() {
        const audio = document.getElementById('weddingAudio');
        audio.volume = 0.5;
    }
    
    function toggleMusic() {
        const audio = document.getElementById('weddingAudio');
        const btn = document.getElementById('playBtn');
        
        if (audio.paused) {
            audio.play();
            btn.innerHTML = '<i class="fas fa-pause"></i>';
        } else {
            audio.pause();
            btn.innerHTML = '<i class="fas fa-play"></i>';
        }
    }
    
    function changeSong(songId) {
        // In real implementation, fetch song URL via AJAX
        console.log('Changing to song:', songId);
    }
    
    function togglePlayer() {
        const player = document.getElementById('musicPlayer');
        player.classList.toggle('collapsed');
    }
    
    // Utility functions
    function showNotification(message, type = 'info') {
        // Implementation for showing notifications
        console.log(`[${type.toUpperCase()}] ${message}`);
    }
    
    function scrollToTop() {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
    </script>
</body>
</html>
