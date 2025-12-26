<section class="royal-gifts section-padding" id="gifts">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Amplop Digital & Hadiah</h2>
            <div class="divider"></div>
            <p class="section-subtitle">Cara mengirim hadiah dan doa untuk mempelai</p>
        </div>
        
        <div class="gifts-container">
            <!-- Bank Transfer Options -->
            <div class="bank-transfer">
                <h3><i class="fas fa-university"></i> Transfer Bank</h3>
                <div class="bank-cards">
                    <?php 
                    $bankAccounts = json_decode($wedding_data['bank_accounts'] ?? '[]', true);
                    foreach($bankAccounts as $bank): 
                    ?>
                    <div class="bank-card">
                        <div class="bank-header">
                            <div class="bank-logo">
                                <img src="<?= base_url($bank['logo'] ?? "assets/images/banks/{$bank['bank']}.png") ?>" 
                                     alt="<?= htmlspecialchars($bank['bank']) ?>"
                                     onerror="this.src='<?= base_url('assets/images/banks/default.png') ?>'">
                            </div>
                            <div class="bank-info">
                                <h4><?= htmlspecialchars($bank['bank']) ?></h4>
                                <p class="account-number"><?= htmlspecialchars($bank['account_number']) ?></p>
                                <p class="account-name">A/N <?= htmlspecialchars($bank['account_name']) ?></p>
                            </div>
                        </div>
                        <div class="bank-actions">
                            <button class="btn-copy" onclick="copyToClipboard('<?= htmlspecialchars($bank['account_number']) ?>', 'Nomor rekening')">
                                <i class="fas fa-copy"></i> Salin
                            </button>
                            <button class="btn-qris" onclick="showQRIS('<?= htmlspecialchars($bank['qris_code']) ?>', '<?= htmlspecialchars($bank['bank']) ?>')">
                                <i class="fas fa-qrcode"></i> QRIS
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- QRIS Modal -->
            <div class="modal" id="qrisModal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3 id="qrisBankName">QRIS Payment</h3>
                        <button class="modal-close" onclick="closeQRISModal()">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="qris-container">
                            <div class="qris-code" id="qrisCode">
                                <!-- QR Code will be generated here -->
                            </div>
                            <div class="qris-instructions">
                                <h4>Cara Pembayaran:</h4>
                                <ol>
                                    <li>Buka aplikasi mobile banking atau e-wallet</li>
                                    <li>Pilih fitur scan QRIS</li>
                                    <li>Arahkan kamera ke kode QR di samping</li>
                                    <li>Masukkan nominal dan konfirmasi pembayaran</li>
                                    <li>Simpan bukti pembayaran</li>
                                </ol>
                                <div class="amount-suggestions">
                                    <h5>Nominal Saran:</h5>
                                    <div class="amount-buttons">
                                        <?php 
                                        $amounts = [50000, 100000, 150000, 200000, 250000, 500000, 1000000];
                                        foreach($amounts as $amount): 
                                        ?>
                                        <button class="amount-btn" onclick="setPaymentAmount(<?= $amount ?>)">
                                            Rp <?= number_format($amount, 0, ',', '.') ?>
                                        </button>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="custom-amount">
                            <label for="customAmount">Atau masukkan nominal lain:</label>
                            <div class="input-group">
                                <span class="input-prefix">Rp</span>
                                <input type="number" 
                                       id="customAmount" 
                                       min="10000" 
                                       step="1000"
                                       placeholder="Masukkan nominal">
                                <button class="btn-apply" onclick="applyCustomAmount()">Terapkan</button>
                            </div>
                        </div>
                        
                        <div class="payment-status">
                            <h4>Status Pembayaran</h4>
                            <div class="status-indicator">
                                <div class="status-dot pending"></div>
                                <span>Menunggu pembayaran</span>
                            </div>
                            <button class="btn-check-status" onclick="checkPaymentStatus()">
                                <i class="fas fa-sync"></i> Cek Status
                            </button>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn-download" onclick="downloadQRIS()">
                            <i class="fas fa-download"></i> Download QR Code
                        </button>
                        <button class="btn-share" onclick="shareQRIS()">
                            <i class="fas fa-share-alt"></i> Share
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- E-Wallet Options -->
            <?php if(!empty($wedding_data['ewallets'])): ?>
            <div class="ewallet-section">
                <h3><i class="fas fa-wallet"></i> E-Wallet</h3>
                <div class="ewallet-cards">
                    <?php 
                    $ewallets = json_decode($wedding_data['ewallets'] ?? '[]', true);
                    foreach($ewallets as $ewallet): 
                    ?>
                    <div class="ewallet-card">
                        <div class="ewallet-logo">
                            <img src="<?= base_url($ewallet['logo'] ?? "assets/images/ewallets/{$ewallet['name']}.png") ?>" 
                                 alt="<?= htmlspecialchars($ewallet['name']) ?>">
                        </div>
                        <div class="ewallet-info">
                            <h4><?= htmlspecialchars($ewallet['name']) ?></h4>
                            <p><?= htmlspecialchars($ewallet['account']) ?></p>
                        </div>
                        <button class="btn-copy" onclick="copyToClipboard('<?= htmlspecialchars($ewallet['account']) ?>', '<?= htmlspecialchars($ewallet['name']) ?>')">
                            <i class="fas fa-copy"></i> Salin
                        </button>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Physical Gifts Info -->
            <div class="physical-gifts">
                <h3><i class="fas fa-gift"></i> Hadiah Fisik</h3>
                <div class="delivery-info">
                    <div class="delivery-address">
                        <h4>Alamat Pengiriman:</h4>
                        <p><?= nl2br(htmlspecialchars($wedding_data['delivery_address'] ?? '')) ?></p>
                        <button class="btn-map" onclick="openDeliveryMap()">
                            <i class="fas fa-map-marker-alt"></i> Buka di Maps
                        </button>
                    </div>
                    <div class="delivery-notes">
                        <h4>Catatan:</h4>
                        <ul>
                            <li>Mohon mencantumkan nama pengirim</li>
                            <li>Disarankan menggunakan jasa pengiriman terpercaya</li>
                            <li>Konfirmasi pengiriman via WhatsApp</li>
                            <li>Maksimal penerimaan: H-1 pernikahan</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- Gift Registry -->
            <?php if(!empty($wedding_data['gift_registry'])): ?>
            <div class="gift-registry">
                <h3><i class="fas fa-list-alt"></i> Daftar Keinginan</h3>
                <div class="registry-grid">
                    <?php 
                    $registryItems = json_decode($wedding_data['gift_registry'] ?? '[]', true);
                    foreach($registryItems as $item): 
                    ?>
                    <div class="registry-item">
                        <div class="item-image">
                            <img src="<?= base_url($item['image'] ?? '') ?>" 
                                 alt="<?= htmlspecialchars($item['name']) ?>"
                                 onerror="this.src='<?= base_url('assets/images/gift-default.jpg') ?>'">
                        </div>
                        <div class="item-details">
                            <h4><?= htmlspecialchars($item['name']) ?></h4>
                            <p class="item-price">Rp <?= number_format($item['price'] ?? 0, 0, ',', '.') ?></p>
                            <p class="item-store"><?= htmlspecialchars($item['store'] ?? '') ?></p>
                            <a href="<?= htmlspecialchars($item['link'] ?? '#') ?>" 
                               target="_blank" 
                               class="btn-buy">
                                <i class="fas fa-shopping-cart"></i> Beli Sekarang
                            </a>
                        </div>
                        <div class="item-status">
                            <?php if($item['reserved'] ?? false): ?>
                            <span class="status-badge reserved">Telah Dipesan</span>
                            <?php else: ?>
                            <span class="status-badge available">Tersedia</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Recent Gifts -->
            <div class="recent-gifts">
                <h3><i class="fas fa-history"></i> Hadiah Terbaru</h3>
                <div class="gifts-list" id="recentGiftsList">
                    <!-- Dynamic content from AJAX -->
                </div>
                <div class="gifts-total">
                    <div class="total-item">
                        <span class="total-label">Total Terkumpul:</span>
                        <span class="total-amount" id="totalAmount">Rp 0</span>
                    </div>
                    <div class="total-item">
                        <span class="total-label">Jumlah Donatur:</span>
                        <span class="total-donors" id="totalDonors">0</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
// QRIS functionality
let currentQRISData = null;
let qrCodeInstance = null;

async function showQRIS(qrisCode, bankName) {
    document.getElementById('qrisBankName').textContent = bankName + ' - QRIS';
    currentQRISData = qrisCode;
    
    // Generate QR Code
    const qrisCodeDiv = document.getElementById('qrisCode');
    qrisCodeDiv.innerHTML = '';
    
    if(window.QRCode) {
        qrCodeInstance = new QRCode(qrisCodeDiv, {
            text: qrisCode,
            width: 250,
            height: 250,
            colorDark: "#000000",
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.H
        });
    } else {
        // Fallback image
        qrisCodeDiv.innerHTML = `<img src="https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=${encodeURIComponent(qrisCode)}" alt="QR Code">`;
    }
    
    // Show modal
    document.getElementById('qrisModal').classList.add('active');
    document.body.style.overflow = 'hidden';
    
    // Start checking payment status
    startPaymentStatusCheck();
}

function closeQRISModal() {
    document.getElementById('qrisModal').classList.remove('active');
    document.body.style.overflow = 'auto';
    stopPaymentStatusCheck();
}

function setPaymentAmount(amount) {
    document.getElementById('customAmount').value = amount;
    updateQRISWithAmount(amount);
}

function applyCustomAmount() {
    const amount = document.getElementById('customAmount').value;
    if(amount && amount >= 10000) {
        updateQRISWithAmount(amount);
        showNotification('Nominal berhasil diubah', 'success');
    } else {
        showNotification('Masukkan nominal minimal Rp 10.000', 'error');
    }
}

function updateQRISWithAmount(amount) {
    // Update QRIS data with amount
    // This would typically call an API to generate new QRIS with specific amount
    console.log('Update QRIS with amount:', amount);
    
    // For demo purposes
    const newQRISData = currentQRISData + '&amount=' + amount;
    if(qrCodeInstance) {
        qrCodeInstance.makeCode(newQRISData);
    }
}

function downloadQRIS() {
    const qrCodeImg = document.querySelector('#qrisCode img');
    if(qrCodeImg) {
        const link = document.createElement('a');
        link.href = qrCodeImg.src;
        link.download = 'qris-payment.png';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        showNotification('QR Code berhasil diunduh', 'success');
    }
}

function shareQRIS() {
    if(navigator.share) {
        navigator.share({
            title: 'QRIS Pembayaran Pernikahan',
            text: 'Scan QRIS ini untuk transfer hadiah pernikahan',
            url: window.location.href,
        });
    } else {
        const qrCodeImg = document.querySelector('#qrisCode img');
        if(qrCodeImg) {
            navigator.clipboard.writeText(currentQRISData).then(() => {
                showNotification('Kode QRIS berhasil disalin', 'success');
            });
        }
    }
}

// Payment status checking
let paymentCheckInterval = null;

function startPaymentStatusCheck() {
    // Check every 10 seconds
    paymentCheckInterval = setInterval(checkPaymentStatus, 10000);
}

function stopPaymentStatusCheck() {
    if(paymentCheckInterval) {
        clearInterval(paymentCheckInterval);
        paymentCheckInterval = null;
    }
}

async function checkPaymentStatus() {
    try {
        const response = await fetch('<?= base_url("api/payments/status") ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                qris_data: currentQRISData
            })
        });
        
        const data = await response.json();
        
        if(data.success) {
            updatePaymentStatusUI(data.data);
        }
    } catch(error) {
        console.error('Error checking payment status:', error);
    }
}

function updatePaymentStatusUI(statusData) {
    const statusIndicator = document.querySelector('.status-indicator');
    const statusDot = statusIndicator.querySelector('.status-dot');
    const statusText = statusIndicator.querySelector('span');
    
    statusDot.className = 'status-dot ' + statusData.status;
    statusText.textContent = statusData.message;
    
    if(statusData.status === 'success') {
        showNotification('Pembayaran berhasil diterima!', 'success');
        stopPaymentStatusCheck();
        loadRecentGifts(); // Refresh gifts list
    }
}

// Copy to clipboard function
function copyToClipboard(text, label) {
    navigator.clipboard.writeText(text).then(() => {
        showNotification(`${label} berhasil disalin`, 'success');
    }).catch(err => {
        console.error('Copy failed:', err);
        showNotification('Gagal menyalin', 'error');
    });
}

// Load recent gifts
async function loadRecentGifts() {
    try {
        const response = await fetch('<?= base_url("api/gifts/recent") ?>');
        const data = await response.json();
        
        if(data.success) {
            const giftsList = document.getElementById('recentGiftsList');
            giftsList.innerHTML = '';
            
            data.data.gifts.forEach(gift => {
                const giftElement = document.createElement('div');
                giftElement.className = 'gift-item';
                giftElement.innerHTML = `
                    <div class="gift-avatar">
                        ${gift.name.charAt(0).toUpperCase()}
                    </div>
                    <div class="gift-info">
                        <h4>${gift.name}</h4>
                        <p class="gift-message">${gift.message || 'Mengirimkan hadiah'}</p>
                        <small class="gift-time">${formatTime(gift.created_at)}</small>
                    </div>
                    <div class="gift-amount">
                        <span class="amount">Rp ${formatCurrency(gift.amount)}</span>
                        <span class="method">${gift.payment_method}</span>
                    </div>
                `;
                giftsList.appendChild(giftElement);
            });
            
            // Update totals
            document.getElementById('totalAmount').textContent = 
                'Rp ' + formatCurrency(data.data.total_amount);
            document.getElementById('totalDonors').textContent = 
                data.data.total_donors;
        }
    } catch(error) {
        console.error('Error loading gifts:', error);
    }
}

function formatCurrency(amount) {
    return amount.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

function formatTime(dateString) {
    // Same as in RSVP script
    const date = new Date(dateString);
    const now = new Date();
    const diffMs = now - date;
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMins / 60);
    const diffDays = Math.floor(diffHours / 24);
    
    if(diffMins < 60) {
        return `${diffMins} menit yang lalu`;
    } else if(diffHours < 24) {
        return `${diffHours} jam yang lalu`;
    } else if(diffDays < 7) {
        return `${diffDays} hari yang lalu`;
    } else {
        return date.toLocaleDateString('id-ID');
    }
}

function showNotification(message, type) {
    // Same as in RSVP script
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
        <span>${message}</span>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 5000);
}

function openDeliveryMap() {
    const address = `<?= urlencode($wedding_data['delivery_address'] ?? '') ?>`;
    window.open(`https://www.google.com/maps/search/?api=1&query=${address}`, '_blank');
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    loadRecentGifts();
    
    // Close modal on outside click
    document.getElementById('qrisModal').addEventListener('click', function(e) {
        if(e.target === this) {
            closeQRISModal();
        }
    });
    
    // Close modal on ESC key
    document.addEventListener('keydown', function(e) {
        if(e.key === 'Escape' && document.getElementById('qrisModal').classList.contains('active')) {
            closeQRISModal();
        }
    });
});

// Load QR Code library if not loaded
if(!window.QRCode) {
    const script = document.createElement('script');
    script.src = 'https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js';
    document.head.appendChild(script);
}
</script>
