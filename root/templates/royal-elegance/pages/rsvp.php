<section class="royal-rsvp section-padding" id="rsvp">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Konfirmasi Kehadiran</h2>
            <div class="divider"></div>
            <p class="section-subtitle">Mohon konfirmasi kehadiran Anda sebelum <?= date('d F Y', strtotime('-3 days', strtotime($wedding_data['date'] ?? date('Y-m-d')))) ?></p>
        </div>
        
        <div class="rsvp-container">
            <div class="rsvp-stats">
                <div class="stat-card">
                    <div class="stat-number" id="totalGuests">0</div>
                    <div class="stat-label">Total Tamu</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" id="confirmedGuests">0</div>
                    <div class="stat-label">Akan Hadir</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" id="pendingGuests">0</div>
                    <div class="stat-label">Belum Konfirmasi</div>
                </div>
            </div>
            
            <div class="rsvp-form-container">
                <!-- Guest Search (for invited guests) -->
                <div class="guest-search">
                    <h4>Cari Nama Anda</h4>
                    <div class="search-box">
                        <input type="text" 
                               id="guestSearch" 
                               placeholder="Masukkan nama Anda..."
                               onkeyup="searchGuest()">
                        <i class="fas fa-search"></i>
                    </div>
                    <div class="search-results" id="searchResults"></div>
                </div>
                
                <!-- RSVP Form -->
                <form id="rsvpForm" class="rsvp-form">
                    <input type="hidden" id="guestId" name="guest_id">
                    <input type="hidden" id="invitationCode" name="invitation_code">
                    
                    <div class="form-group">
                        <label for="name">Nama Lengkap *</label>
                        <input type="text" 
                               id="name" 
                               name="name" 
                               required
                               placeholder="Nama sesuai undangan">
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="phone">Nomor WhatsApp *</label>
                            <input type="tel" 
                                   id="phone" 
                                   name="phone" 
                                   required
                                   placeholder="0812xxxxxx">
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" 
                                   id="email" 
                                   name="email"
                                   placeholder="email@example.com">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Kehadiran *</label>
                        <div class="attendance-options">
                            <label class="option-card">
                                <input type="radio" 
                                       name="attendance" 
                                       value="yes" 
                                       required>
                                <div class="option-content">
                                    <i class="fas fa-check-circle"></i>
                                    <span>Hadir</span>
                                    <p>Dengan senang hati akan menghadiri</p>
                                </div>
                            </label>
                            <label class="option-card">
                                <input type="radio" 
                                       name="attendance" 
                                       value="no" 
                                       required>
                                <div class="option-content">
                                    <i class="fas fa-times-circle"></i>
                                    <span>Tidak Hadir</span>
                                    <p>Mohon maaf tidak dapat menghadiri</p>
                                </div>
                            </label>
                            <label class="option-card">
                                <input type="radio" 
                                       name="attendance" 
                                       value="maybe" 
                                       required>
                                <div class="option-content">
                                    <i class="fas fa-question-circle"></i>
                                    <span>Masih Ragu</span>
                                    <p>Belum dapat memastikan</p>
                                </div>
                            </label>
                        </div>
                    </div>
                    
                    <div class="form-group" id="guestCountGroup" style="display: none;">
                        <label for="guest_count">Jumlah Tamu *</label>
                        <select id="guest_count" name="guest_count" required>
                            <option value="">Pilih jumlah</option>
                            <?php for($i = 1; $i <= 10; $i++): ?>
                            <option value="<?= $i ?>"><?= $i ?> orang</option>
                            <?php endfor; ?>
                        </select>
                        <small class="form-text">Termasuk Anda</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="message">Pesan/Ucapan</label>
                        <textarea id="message" 
                                  name="message" 
                                  rows="3"
                                  placeholder="Tulis ucapan atau doa untuk mempelai..."></textarea>
                    </div>
                    
                    <div class="form-group">
                        <div class="checkbox">
                            <input type="checkbox" 
                                   id="receive_updates" 
                                   name="receive_updates" 
                                   checked>
                            <label for="receive_updates">
                                Saya ingin menerima update acara via WhatsApp
                            </label>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn-submit" id="submitRsvp">
                        <span class="btn-text">Kirim Konfirmasi</span>
                        <div class="spinner hidden" id="loadingSpinner"></div>
                    </button>
                    
                    <p class="form-note">
                        <i class="fas fa-info-circle"></i>
                        Konfirmasi juga bisa via WhatsApp: 
                        <a href="https://wa.me/<?= $couple['whatsapp'] ?? '628123456789' ?>?text=Halo%20saya%20mengkonfirmasi%20kehadiran%20di%20pernikahan%20<?= urlencode($couple['groom_name'] ?? '') ?>%20%26%20<?= urlencode($couple['bride_name'] ?? '') ?>">
                            Klik di sini
                        </a>
                    </p>
                </form>
            </div>
            
            <!-- Confirmed Guests List -->
            <div class="confirmed-guests">
                <h3>Tamu yang Telah Mengkonfirmasi</h3>
                <div class="guests-list" id="guestsList">
                    <!-- Dynamic content from AJAX -->
                </div>
                <button class="btn-load-more" id="loadMoreGuests">Tampilkan Lebih Banyak</button>
            </div>
        </div>
    </div>
</section>

<script>
// Global variables
let currentPage = 1;
let isLoading = false;

// Load RSVP stats
async function loadStats() {
    try {
        const response = await fetch('<?= base_url("api/rsvp/stats") ?>');
        const data = await response.json();
        
        if(data.success) {
            document.getElementById('totalGuests').textContent = data.data.total;
            document.getElementById('confirmedGuests').textContent = data.data.confirmed;
            document.getElementById('pendingGuests').textContent = data.data.pending;
        }
    } catch(error) {
        console.error('Error loading stats:', error);
    }
}

// Search guest functionality
async function searchGuest() {
    const searchTerm = document.getElementById('guestSearch').value.trim();
    const resultsDiv = document.getElementById('searchResults');
    
    if(searchTerm.length < 2) {
        resultsDiv.innerHTML = '';
        return;
    }
    
    try {
        const response = await fetch(`<?= base_url("
