<section class="royal-couple section-padding" id="couple">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Pasangan Mempelai</h2>
            <div class="divider"></div>
            <p class="section-subtitle">Yang dipersatukan oleh cinta dan restu keluarga</p>
        </div>
        
        <div class="couple-container">
            <!-- Groom -->
            <div class="couple-card groom">
                <div class="couple-image">
                    <img src="<?= base_url($couple['groom_photo'] ?? 'assets/images/groom.jpg') ?>" 
                         alt="<?= htmlspecialchars($couple['groom_name'] ?? '') ?>"
                         onerror="this.src='<?= base_url('assets/images/default-avatar.jpg') ?>'">
                    <div class="image-frame"></div>
                </div>
                <div class="couple-details">
                    <h3 class="couple-name"><?= htmlspecialchars($couple['groom_name'] ?? '') ?></h3>
                    <p class="couple-title">Putra <?= htmlspecialchars($couple['groom_father'] ?? '') ?></p>
                    <p class="couple-title">& Ibu <?= htmlspecialchars($couple['groom_mother'] ?? '') ?></p>
                    
                    <div class="couple-bio">
                        <p><?= htmlspecialchars($couple['groom_bio'] ?? '') ?></p>
                    </div>
                    
                    <div class="social-links">
                        <?php if(!empty($couple['groom_instagram'])): ?>
                        <a href="https://instagram.com/<?= $couple['groom_instagram'] ?>" target="_blank">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <?php endif; ?>
                        <?php if(!empty($couple['groom_facebook'])): ?>
                        <a href="https://facebook.com/<?= $couple['groom_facebook'] ?>" target="_blank">
                            <i class="fab fa-facebook"></i>
                        </a>
                        <?php endif; ?>
                        <?php if(!empty($couple['groom_whatsapp'])): ?>
                        <a href="https://wa.me/<?= $couple['groom_whatsapp'] ?>" target="_blank">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Heart Divider -->
            <div class="couple-heart">
                <i class="fas fa-heart"></i>
                <p class="wedding-date"><?= date('d.m.Y', strtotime($wedding_data['date'] ?? date('Y-m-d'))) ?></p>
            </div>
            
            <!-- Bride -->
            <div class="couple-card bride">
                <div class="couple-image">
                    <img src="<?= base_url($couple['bride_photo'] ?? 'assets/images/bride.jpg') ?>" 
                         alt="<?= htmlspecialchars($couple['bride_name'] ?? '') ?>"
                         onerror="this.src='<?= base_url('assets/images/default-avatar.jpg') ?>'">
                    <div class="image-frame"></div>
                </div>
                <div class="couple-details">
                    <h3 class="couple-name"><?= htmlspecialchars($couple['bride_name'] ?? '') ?></h3>
                    <p class="couple-title">Putri <?= htmlspecialchars($couple['bride_father'] ?? '') ?></p>
                    <p class="couple-title">& Ibu <?= htmlspecialchars($couple['bride_mother'] ?? '') ?></p>
                    
                    <div class="couple-bio">
                        <p><?= htmlspecialchars($couple['bride_bio'] ?? '') ?></p>
                    </div>
                    
                    <div class="social-links">
                        <?php if(!empty($couple['bride_instagram'])): ?>
                        <a href="https://instagram.com/<?= $couple['bride_instagram'] ?>" target="_blank">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <?php endif; ?>
                        <?php if(!empty($couple['bride_facebook'])): ?>
                        <a href="https://facebook.com/<?= $couple['bride_facebook'] ?>" target="_blank">
                            <i class="fab fa-facebook"></i>
                        </a>
                        <?php endif; ?>
                        <?php if(!empty($couple['bride_whatsapp'])): ?>
                        <a href="https://wa.me/<?= $couple['bride_whatsapp'] ?>" target="_blank">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Love Story Timeline -->
        <div class="love-story">
            <h3 class="story-title">Our Love Story</h3>
            <div class="timeline">
                <?php 
                $stories = json_decode($couple['love_story'] ?? '[]', true);
                foreach($stories as $index => $story): 
                ?>
                <div class="timeline-item <?= $index % 2 == 0 ? 'left' : 'right' ?>">
                    <div class="timeline-content">
                        <div class="timeline-date"><?= htmlspecialchars($story['date'] ?? '') ?></div>
                        <h4><?= htmlspecialchars($story['title'] ?? '') ?></h4>
                        <p><?= htmlspecialchars($story['description'] ?? '') ?></p>
                    </div>
                    <div class="timeline-dot">
                        <i class="fas fa-heart"></i>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>
