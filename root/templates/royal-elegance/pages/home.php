<?php
$template_settings = $template->get_settings('royal-elegance');
$colors = json_decode($template_settings['colors'] ?? '{}', true);
$fonts = json_decode($template_settings['fonts'] ?? '{}', true);
?>

<!DOCTYPE html>
<html lang="id" data-theme="royal-elegance">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($wedding_data['title'] ?? 'Undangan Pernikahan') ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/css/royal-elegance/main.css') ?>">
    <style>
        :root {
            --primary-color: <?= $colors['primary'] ?? '#8B4513' ?>;
            --secondary-color: <?= $colors['secondary'] ?? '#D4AF37' ?>;
            --accent-color: <?= $colors['accent'] ?? '#C19A6B' ?>;
            --font-heading: '<?= $fonts['heading'] ?? 'Playfair Display' ?>', serif;
            --font-body: '<?= $fonts['body'] ?? 'Crimson Text' ?>', serif;
        }
    </style>
</head>
<body class="royal-elegance">
    <!-- Navigation -->
    <nav class="royal-nav">
        <div class="nav-container">
            <div class="nav-logo">
                <h1><?= htmlspecialchars($couple['groom_name'] ?? '') ?> & <?= htmlspecialchars($couple['bride_name'] ?? '') ?></h1>
            </div>
            <div class="nav-menu">
                <a href="#home" class="nav-link active">Home</a>
                <a href="#couple" class="nav-link">Couple</a>
                <a href="#events" class="nav-link">Events</a>
                <a href="#gallery" class="nav-link">Gallery</a>
                <a href="#rsvp" class="nav-link">RSVP</a>
                <a href="#gifts" class="nav-link">Gifts</a>
                <a href="#messages" class="nav-link">Messages</a>
            </div>
            <button class="nav-toggle" id="navToggle">☰</button>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="royal-hero" id="home">
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <div class="hero-text">
                <h2 class="hero-title">The Wedding Of</h2>
                <h1 class="couple-names">
                    <span><?= htmlspecialchars($couple['groom_name'] ?? '') ?></span>
                    <span class="ampersand">&</span>
                    <span><?= htmlspecialchars($couple['bride_name'] ?? '') ?></span>
                </h1>
                <p class="wedding-date">
                    <i class="fas fa-calendar-alt"></i>
                    <?= date('d F Y', strtotime($wedding_data['date'] ?? date('Y-m-d'))) ?>
                </p>
                <p class="wedding-location">
                    <i class="fas fa-map-marker-alt"></i>
                    <?= htmlspecialchars($wedding_data['venue'] ?? '') ?>
                </p>
                <div class="countdown-container">
                    <h3>Menuju Hari Bahagia</h3>
                    <div class="countdown" id="countdown">
                        <div class="countdown-item">
                            <span id="days">00</span>
                            <small>Hari</small>
                        </div>
                        <div class="countdown-item">
                            <span id="hours">00</span>
                            <small>Jam</small>
                        </div>
                        <div class="countdown-item">
                            <span id="minutes">00</span>
                            <small>Menit</small>
                        </div>
                        <div class="countdown-item">
                            <span id="seconds">00</span>
                            <small>Detik</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="hero-scroll">
            <a href="#couple" class="scroll-down">
                <i class="fas fa-chevron-down"></i>
            </a>
        </div>
    </section>

    <!-- Audio Player -->
    <div class="audio-player">
        <button id="audioToggle" class="audio-btn">
            <i class="fas fa-music"></i>
            <span class="audio-status">Music: ON</span>
        </button>
        <audio id="weddingAudio" loop>
            <source src="<?= base_url($wedding_data['music'] ?? 'assets/audio/wedding-song.mp3') ?>" type="audio/mpeg">
        </audio>
    </div>

    <script>
        // Countdown Timer
        const weddingDate = new Date("<?= $wedding_data['date'] ?? '2024-12-31' ?>").getTime();
        
        function updateCountdown() {
            const now = new Date().getTime();
            const distance = weddingDate - now;
            
            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);
            
            document.getElementById('days').textContent = days.toString().padStart(2, '0');
            document.getElementById('hours').textContent = hours.toString().padStart(2, '0');
            document.getElementById('minutes').textContent = minutes.toString().padStart(2, '0');
            document.getElementById('seconds').textContent = seconds.toString().padStart(2, '0');
            
            if (distance < 0) {
                clearInterval(countdownInterval);
                document.getElementById('countdown').innerHTML = "<h3>Hari Bahagia Telah Tiba!</h3>";
            }
        }
        
        const countdownInterval = setInterval(updateCountdown, 1000);
        updateCountdown();
        
        // Audio Control
        const audio = document.getElementById('weddingAudio');
        const audioToggle = document.getElementById('audioToggle');
        const audioStatus = document.querySelector('.audio-status');
        
        audioToggle.addEventListener('click', function() {
            if (audio.paused) {
                audio.play();
                audioStatus.textContent = 'Music: ON';
                this.classList.add('playing');
            } else {
                audio.pause();
                audioStatus.textContent = 'Music: OFF';
                this.classList.remove('playing');
            }
        });
        
        // Auto play music on page load (with user interaction requirement)
        document.addEventListener('click', function initAudio() {
            if (audio.paused) {
                audio.play().catch(e => console.log("Audio play failed:", e));
            }
            document.removeEventListener('click', initAudio);
        });
        
        // Navigation
        document.getElementById('navToggle').addEventListener('click', function() {
            document.querySelector('.nav-menu').classList.toggle('active');
        });
        
        // Smooth scroll
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const targetId = this.getAttribute('href');
                if(targetId === '#') return;
                
                const targetElement = document.querySelector(targetId);
                if(targetElement) {
                    window.scrollTo({
                        top: targetElement.offsetTop - 80,
                        behavior: 'smooth'
                    });
                    
                    // Update active nav link
                    document.querySelectorAll('.nav-link').forEach(link => {
                        link.classList.remove('active');
                    });
                    this.classList.add('active');
                }
            });
        });
        
        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const nav = document.querySelector('.royal-nav');
            if (window.scrollY > 100) {
                nav.classList.add('scrolled');
            } else {
                nav.classList.remove('scrolled');
            }
        });
    </script>
</body>
</html>
