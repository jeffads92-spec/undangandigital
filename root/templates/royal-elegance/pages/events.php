<section class="royal-events section-padding" id="events">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Jadwal Acara</h2>
            <div class="divider"></div>
            <p class="section-subtitle">Momen bahagia yang kami nantikan bersama Anda</p>
        </div>
        
        <div class="events-timeline">
            <?php 
            $events = json_decode($wedding_data['events'] ?? '[]', true);
            foreach($events as $event): 
            ?>
            <div class="event-card">
                <div class="event-icon">
                    <i class="<?= htmlspecialchars($event['icon'] ?? 'fas fa-heart') ?>"></i>
                </div>
                <div class="event-content">
                    <h3 class="event-title"><?= htmlspecialchars($event['title'] ?? '') ?></h3>
                    <div class="event-details">
                        <div class="event-detail">
                            <i class="fas fa-calendar-alt"></i>
                            <span><?= date('d F Y', strtotime($event['date'] ?? '')) ?></span>
                        </div>
                        <div class="event-detail">
                            <i class="fas fa-clock"></i>
                            <span><?= htmlspecialchars($event['time'] ?? '') ?> WIB</span>
                        </div>
                        <div class="event-detail">
                            <i class="fas fa-map-marker-alt"></i>
                            <span><?= htmlspecialchars($event['venue'] ?? '') ?></span>
                        </div>
                    </div>
                    <p class="event-description"><?= htmlspecialchars($event['description'] ?? '') ?></p>
                    
                    <?php if(!empty($event['maps_link'])): ?>
                    <div class="event-actions">
                        <a href="<?= htmlspecialchars($event['maps_link']) ?>" 
                           target="_blank" 
                           class="btn-map">
                            <i class="fas fa-map"></i> Buka di Google Maps
                        </a>
                        <button class="btn-save" onclick="addToCalendar(
                            '<?= htmlspecialchars($event['title']) ?>',
                            '<?= date('Y-m-d', strtotime($event['date'])) ?>',
                            '<?= htmlspecialchars($event['time']) ?>',
                            '<?= htmlspecialchars($event['venue']) ?>',
                            '<?= htmlspecialchars($event['description']) ?>'
                        )">
                            <i class="fas fa-calendar-plus"></i> Simpan ke Kalender
                        </button>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Transportation Info -->
        <?php if(!empty($wedding_data['transportation'])): ?>
        <div class="transportation-info">
            <h3><i class="fas fa-car"></i> Informasi Transportasi & Parkir</h3>
            <div class="transportation-content">
                <?= htmlspecialchars($wedding_data['transportation']) ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Dress Code -->
        <?php if(!empty($wedding_data['dress_code'])): ?>
        <div class="dress-code">
            <h3><i class="fas fa-tshirt"></i> Dress Code</h3>
            <div class="dress-colors">
                <?php 
                $dressCodes = json_decode($wedding_data['dress_code'] ?? '[]', true);
                foreach($dressCodes as $dress): 
                ?>
                <div class="color-item">
                    <div class="color-box" style="background-color: <?= htmlspecialchars($dress['color'] ?? '#FFFFFF') ?>"></div>
                    <span><?= htmlspecialchars($dress['name'] ?? '') ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <p class="dress-note"><?= htmlspecialchars($wedding_data['dress_note'] ?? '') ?></p>
        </div>
        <?php endif; ?>
    </div>
</section>

<script>
function addToCalendar(title, date, time, location, description) {
    // Format date for calendar
    const eventDate = new Date(`${date}T${time}:00`);
    const endDate = new Date(eventDate.getTime() + (2 * 60 * 60 * 1000)); // 2 hours duration
    
    // Google Calendar URL
    const googleCalendarUrl = `https://calendar.google.com/calendar/render?action=TEMPLATE&text=${encodeURIComponent(title)}&dates=${formatDateForGoogle(eventDate)}/${formatDateForGoogle(endDate)}&location=${encodeURIComponent(location)}&details=${encodeURIComponent(description)}`;
    
    // Open options
    if(confirm('Simpan acara ke kalender?')) {
        window.open(googleCalendarUrl, '_blank');
    }
}

function formatDateForGoogle(date) {
    return date.toISOString().replace(/-|:|\.\d+/g, '');
}

// Auto update event times
function updateEventTimes() {
    const eventElements = document.querySelectorAll('.event-time');
    eventElements.forEach(el => {
        const eventTime = el.getAttribute('data-time');
        // Add logic to show countdown to event
    });
}
</script>
