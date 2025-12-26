<section class="royal-gallery section-padding" id="gallery">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Galeri Kenangan</h2>
            <div class="divider"></div>
            <p class="section-subtitle">Momen indah yang telah kami lewati bersama</p>
        </div>
        
        <!-- Gallery Filter -->
        <div class="gallery-filter">
            <button class="filter-btn active" data-filter="all">Semua</button>
            <button class="filter-btn" data-filter="prewedding">Prewedding</button>
            <button class="filter-btn" data-filter="engagement">Lamaran</button>
            <button class="filter-btn" data-filter="family">Keluarga</button>
            <button class="filter-btn" data-filter="couple">Pasangan</button>
        </div>
        
        <!-- Gallery Grid -->
        <div class="gallery-grid" id="galleryGrid">
            <?php 
            $galleries = json_decode($wedding_data['gallery'] ?? '[]', true);
            foreach($galleries as $index => $photo): 
            ?>
            <div class="gallery-item" data-category="<?= htmlspecialchars($photo['category'] ?? 'prewedding') ?>">
                <div class="gallery-card">
                    <img src="<?= base_url($photo['url'] ?? '') ?>" 
                         alt="<?= htmlspecialchars($photo['caption'] ?? '') ?>"
                         loading="lazy"
                         onerror="this.src='<?= base_url('assets/images/default-gallery.jpg') ?>'">
                    <div class="gallery-overlay">
                        <div class="gallery-caption">
                            <h4><?= htmlspecialchars($photo['caption'] ?? '') ?></h4>
                            <p><?= htmlspecialchars($photo['date'] ?? '') ?></p>
                        </div>
                        <div class="gallery-actions">
                            <button class="btn-view" onclick="openLightbox(<?= $index ?>)">
                                <i class="fas fa-expand"></i>
                            </button>
                            <button class="btn-share" onclick="sharePhoto('<?= base_url($photo['url']) ?>', '<?= htmlspecialchars($photo['caption']) ?>')">
                                <i class="fas fa-share-alt"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Video Gallery -->
        <?php if(!empty($wedding_data['videos'])): ?>
        <div class="video-gallery">
            <h3 class="video-title">Video Kenangan</h3>
            <div class="video-grid">
                <?php 
                $videos = json_decode($wedding_data['videos'] ?? '[]', true);
                foreach($videos as $video): 
                ?>
                <div class="video-item">
                    <div class="video-wrapper">
                        <?php if(strpos($video['url'], 'youtube') !== false): ?>
                        <!-- YouTube Embed -->
                        <iframe src="https://www.youtube.com/embed/<?= getYouTubeId($video['url']) ?>" 
                                frameborder="0" 
                                allowfullscreen>
                        </iframe>
                        <?php else: ?>
                        <!-- Local Video -->
                        <video controls poster="<?= base_url($video['thumbnail'] ?? '') ?>">
                            <source src="<?= base_url($video['url']) ?>" type="video/mp4">
                        </video>
                        <?php endif; ?>
                        <div class="video-caption">
                            <h4><?= htmlspecialchars($video['title'] ?? '') ?></h4>
                            <p><?= htmlspecialchars($video['description'] ?? '') ?></p>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Lightbox Modal -->
    <div class="lightbox" id="lightbox">
        <div class="lightbox-content">
            <button class="lightbox-close" onclick="closeLightbox()">&times;</button>
            <button class="lightbox-nav prev" onclick="changePhoto(-1)">❮</button>
            <button class="lightbox-nav next" onclick="changePhoto(1)">❯</button>
            
            <div class="lightbox-image-container">
                <img id="lightbox-image" src="" alt="">
                <div class="lightbox-caption">
                    <h3 id="lightbox-title"></h3>
                    <p id="lightbox-date"></p>
                </div>
            </div>
            
            <div class="lightbox-actions">
                <button class="btn-download" onclick="downloadPhoto()">
                    <i class="fas fa-download"></i> Download
                </button>
                <button class="btn-share" onclick="shareCurrentPhoto()">
                    <i class="fas fa-share-alt"></i> Share
                </button>
            </div>
        </div>
    </div>
</section>

<script>
let currentPhotoIndex = 0;
const photos = <?= json_encode($galleries) ?>;

function openLightbox(index) {
    currentPhotoIndex = index;
    const photo = photos[index];
    
    document.getElementById('lightbox-image').src = '<?= base_url() ?>' + photo.url;
    document.getElementById('lightbox-title').textContent = photo.caption || '';
    document.getElementById('lightbox-date').textContent = photo.date || '';
    
    document.getElementById('lightbox').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeLightbox() {
    document.getElementById('lightbox').classList.remove('active');
    document.body.style.overflow = 'auto';
}

function changePhoto(direction) {
    currentPhotoIndex += direction;
    
    if (currentPhotoIndex < 0) {
        currentPhotoIndex = photos.length - 1;
    } else if (currentPhotoIndex >= photos.length) {
        currentPhotoIndex = 0;
    }
    
    openLightbox(currentPhotoIndex);
}

// Gallery Filter
document.querySelectorAll('.filter-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const filter = this.getAttribute('data-filter');
        
        // Update active button
        document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        
        // Filter items
        document.querySelectorAll('.gallery-item').forEach(item => {
            if (filter === 'all' || item.getAttribute('data-category') === filter) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
    });
});

// Keyboard navigation
document.addEventListener('keydown', function(e) {
    if (document.getElementById('lightbox').classList.contains('active')) {
        if (e.key === 'Escape') closeLightbox();
        if (e.key === 'ArrowLeft') changePhoto(-1);
        if (e.key === 'ArrowRight') changePhoto(1);
    }
});

// Share functionality
function sharePhoto(imageUrl, caption) {
    if (navigator.share) {
        navigator.share({
            title: 'Galeri Pernikahan',
            text: caption,
            url: window.location.href,
        });
    } else {
        // Fallback: copy to clipboard
        const shareUrl = '<?= base_url() ?>' + imageUrl;
        navigator.clipboard.writeText(shareUrl).then(() => {
            alert('Link foto berhasil disalin!');
        });
    }
}

function shareCurrentPhoto() {
    const photo = photos[currentPhotoIndex];
    sharePhoto(photo.url, photo.caption);
}

function downloadPhoto() {
    const photo = photos[currentPhotoIndex];
    const link = document.createElement('a');
    link.href = '<?= base_url() ?>' + photo.url;
    link.download = `wedding-photo-${currentPhotoIndex + 1}.jpg`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
</script>

<?php
function getYouTubeId($url) {
    preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $url, $matches);
    return $matches[1] ?? '';
}
?>
