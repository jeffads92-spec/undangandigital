<?php
/**
 * ADMIN FOOTER
 * Footer untuk admin panel dengan JavaScript utilities
 */
?>

<style>
/* Footer Styles */
.admin-footer {
    margin-top: 50px;
    padding: 20px 0;
    border-top: 1px solid #e9ecef;
    text-align: center;
    color: #7f8c8d;
    font-size: 0.9em;
}

.admin-footer p {
    margin: 5px 0;
}

.admin-footer a {
    color: #8B4513;
    text-decoration: none;
    transition: color 0.3s;
}

.admin-footer a:hover {
    color: #6B3510;
    text-decoration: underline;
}

/* Loading Overlay */
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.7);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}

.loading-overlay.active {
    display: flex;
}

.loading-content {
    background: white;
    padding: 30px 50px;
    border-radius: 10px;
    text-align: center;
}

.loading-spinner {
    width: 50px;
    height: 50px;
    border: 5px solid #f3f3f3;
    border-top: 5px solid #8B4513;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto 15px;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.loading-text {
    color: #333;
    font-size: 1.1em;
    font-weight: 500;
}

/* Toast Notification */
.toast-container {
    position: fixed;
    top: 90px;
    right: 20px;
    z-index: 9998;
    max-width: 350px;
}

.toast {
    background: white;
    border-radius: 8px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.15);
    padding: 15px 20px;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 12px;
    animation: slideInRight 0.3s ease;
    border-left: 4px solid #8B4513;
}

@keyframes slideInRight {
    from {
        opacity: 0;
        transform: translateX(100%);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

.toast.success { border-left-color: #27ae60; }
.toast.error { border-left-color: #e74c3c; }
.toast.warning { border-left-color: #f39c12; }
.toast.info { border-left-color: #3498db; }

.toast-icon {
    font-size: 1.5em;
}

.toast-icon.success { color: #27ae60; }
.toast-icon.error { color: #e74c3c; }
.toast-icon.warning { color: #f39c12; }
.toast-icon.info { color: #3498db; }

.toast-content {
    flex: 1;
}

.toast-title {
    font-weight: 600;
    color: #333;
    margin-bottom: 3px;
}

.toast-message {
    font-size: 0.9em;
    color: #666;
}

.toast-close {
    background: none;
    border: none;
    font-size: 1.2em;
    color: #999;
    cursor: pointer;
    padding: 5px;
    transition: color 0.3s;
}

.toast-close:hover {
    color: #333;
}

/* Confirm Dialog */
.confirm-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}

.confirm-overlay.active {
    display: flex;
}

.confirm-dialog {
    background: white;
    border-radius: 10px;
    padding: 30px;
    max-width: 400px;
    width: 90%;
    animation: zoomIn 0.3s ease;
}

@keyframes zoomIn {
    from {
        opacity: 0;
        transform: scale(0.8);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

.confirm-icon {
    font-size: 3em;
    text-align: center;
    margin-bottom: 15px;
}

.confirm-icon.warning { color: #f39c12; }
.confirm-icon.danger { color: #e74c3c; }
.confirm-icon.info { color: #3498db; }

.confirm-title {
    font-size: 1.3em;
    font-weight: 600;
    color: #333;
    text-align: center;
    margin-bottom: 10px;
}

.confirm-message {
    color: #666;
    text-align: center;
    margin-bottom: 25px;
    line-height: 1.5;
}

.confirm-buttons {
    display: flex;
    gap: 10px;
}

.confirm-btn {
    flex: 1;
    padding: 12px;
    border: none;
    border-radius: 6px;
    font-size: 1em;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s;
}

.confirm-btn.primary {
    background: #8B4513;
    color: white;
}

.confirm-btn.primary:hover {
    background: #6B3510;
}

.confirm-btn.secondary {
    background: #e9ecef;
    color: #495057;
}

.confirm-btn.secondary:hover {
    background: #dee2e6;
}

.confirm-btn.danger {
    background: #e74c3c;
    color: white;
}

.confirm-btn.danger:hover {
    background: #c0392b;
}
</style>

<!-- Admin Footer -->
<footer class="admin-footer">
    <p>&copy; <?= date('Y') ?> <?= SITE_NAME ?>. All rights reserved.</p>
    <p>
        Version <?= APP_VERSION ?> | 
        <a href="<?= BASE_URL ?>admin/help.php">Bantuan</a> | 
        <a href="<?= BASE_URL ?>admin/docs.php">Dokumentasi</a> | 
        <a href="mailto:support@example.com">Support</a>
    </p>
</footer>

</div> <!-- End .content -->
</div> <!-- End .admin-container -->

<!-- Loading Overlay -->
<div class="loading-overlay" id="loadingOverlay">
    <div class="loading-content">
        <div class="loading-spinner"></div>
        <p class="loading-text">Memproses...</p>
    </div>
</div>

<!-- Toast Container -->
<div class="toast-container" id="toastContainer"></div>

<!-- Confirm Dialog -->
<div class="confirm-overlay" id="confirmOverlay">
    <div class="confirm-dialog">
        <div class="confirm-icon" id="confirmIcon">
            <i class="fas fa-question-circle"></i>
        </div>
        <h3 class="confirm-title" id="confirmTitle">Konfirmasi</h3>
        <p class="confirm-message" id="confirmMessage">Apakah Anda yakin?</p>
        <div class="confirm-buttons">
            <button class="confirm-btn secondary" id="confirmCancel">Batal</button>
            <button class="confirm-btn primary" id="confirmOk">OK</button>
        </div>
    </div>
</div>

<!-- JavaScript -->
<script src="<?= BASE_URL ?>assets/js/admin.js"></script>

<script>
/**
 * ADMIN UTILITIES
 * Helper functions untuk admin panel
 */

// Show Loading
function showLoading(message = 'Memproses...') {
    const overlay = document.getElementById('loadingOverlay');
    const text = overlay.querySelector('.loading-text');
    text.textContent = message;
    overlay.classList.add('active');
}

// Hide Loading
function hideLoading() {
    document.getElementById('loadingOverlay').classList.remove('active');
}

// Show Toast
function showToast(message, type = 'info', duration = 3000) {
    const container = document.getElementById('toastContainer');
    
    const icons = {
        success: 'fa-check-circle',
        error: 'fa-exclamation-circle',
        warning: 'fa-exclamation-triangle',
        info: 'fa-info-circle'
    };
    
    const titles = {
        success: 'Berhasil',
        error: 'Error',
        warning: 'Peringatan',
        info: 'Informasi'
    };
    
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.innerHTML = `
        <i class="fas ${icons[type]} toast-icon ${type}"></i>
        <div class="toast-content">
            <div class="toast-title">${titles[type]}</div>
            <div class="toast-message">${message}</div>
        </div>
        <button class="toast-close" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    container.appendChild(toast);
    
    setTimeout(() => {
        toast.style.animation = 'slideOutRight 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, duration);
}

// Show Confirm Dialog
function showConfirm(options = {}) {
    return new Promise((resolve) => {
        const overlay = document.getElementById('confirmOverlay');
        const icon = document.getElementById('confirmIcon');
        const title = document.getElementById('confirmTitle');
        const message = document.getElementById('confirmMessage');
        const okBtn = document.getElementById('confirmOk');
        const cancelBtn = document.getElementById('confirmCancel');
        
        // Set content
        title.textContent = options.title || 'Konfirmasi';
        message.textContent = options.message || 'Apakah Anda yakin?';
        
        // Set icon
        const iconClass = options.type === 'danger' ? 'danger' : 
                         options.type === 'warning' ? 'warning' : 'info';
        icon.className = `confirm-icon ${iconClass}`;
        
        const iconTypes = {
            danger: 'fa-exclamation-circle',
            warning: 'fa-exclamation-triangle',
            info: 'fa-question-circle'
        };
        icon.innerHTML = `<i class="fas ${iconTypes[options.type] || iconTypes.info}"></i>`;
        
        // Set button text
        okBtn.textContent = options.okText || 'OK';
        cancelBtn.textContent = options.cancelText || 'Batal';
        
        // Set button style
        okBtn.className = `confirm-btn ${options.type === 'danger' ? 'danger' : 'primary'}`;
        
        // Show overlay
        overlay.classList.add('active');
        
        // Handle OK
        okBtn.onclick = () => {
            overlay.classList.remove('active');
            resolve(true);
        };
        
        // Handle Cancel
        cancelBtn.onclick = () => {
            overlay.classList.remove('active');
            resolve(false);
        };
        
        // Handle click outside
        overlay.onclick = (e) => {
            if (e.target === overlay) {
                overlay.classList.remove('active');
                resolve(false);
            }
        };
    });
}

// AJAX Request Helper
async function ajaxRequest(url, options = {}) {
    const defaultOptions = {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    };
    
    const config = { ...defaultOptions, ...options };
    
    if (config.body && typeof config.body === 'object') {
        config.body = JSON.stringify(config.body);
    }
    
    try {
        const response = await fetch(url, config);
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('AJAX Error:', error);
        throw error;
    }
}

// Format Number
function formatNumber(number) {
    return new Intl.NumberFormat('id-ID').format(number);
}

// Format Currency
function formatCurrency(amount) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(amount);
}

// Format Date
function formatDate(dateString) {
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    }).format(date);
}

// Format DateTime
function formatDateTime(dateString) {
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    }).format(date);
}

// Debounce Function
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Copy to Clipboard
async function copyToClipboard(text) {
    try {
        await navigator.clipboard.writeText(text);
        showToast('Teks berhasil disalin!', 'success');
        return true;
    } catch (err) {
        showToast('Gagal menyalin teks', 'error');
        return false;
    }
}

// Download File
function downloadFile(url, filename) {
    const link = document.createElement('a');
    link.href = url;
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// Auto-dismiss alerts
document.querySelectorAll('.alert').forEach(alert => {
    const closeBtn = alert.querySelector('.alert-close');
    if (closeBtn) {
        closeBtn.addEventListener('click', () => {
            alert.style.animation = 'slideOutUp 0.3s ease';
            setTimeout(() => alert.remove(), 300);
        });
    }
    
    // Auto close after 5 seconds
    setTimeout(() => {
        if (alert.parentElement) {
            alert.style.animation = 'slideOutUp 0.3s ease';
            setTimeout(() => alert.remove(), 300);
        }
    }, 5000);
});

// Form validation
document.querySelectorAll('form[data-validate]').forEach(form => {
    form.addEventListener('submit', function(e) {
        const requiredFields = form.querySelectorAll('[required]');
        let isValid = true;
        
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                isValid = false;
                field.style.borderColor = '#e74c3c';
                
                setTimeout(() => {
                    field.style.borderColor = '';
                }, 2000);
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            showToast('Mohon lengkapi semua field yang wajib diisi', 'error');
        }
    });
});

// Table row actions
document.querySelectorAll('[data-delete]').forEach(btn => {
    btn.addEventListener('click', async function(e) {
        e.preventDefault();
        
        const url = this.getAttribute('data-delete');
        const name = this.getAttribute('data-name') || 'item ini';
        
        const confirmed = await showConfirm({
            title: 'Hapus Data',
            message: `Apakah Anda yakin ingin menghapus "${name}"? Tindakan ini tidak dapat dibatalkan.`,
            type: 'danger',
            okText: 'Hapus',
            cancelText: 'Batal'
        });
        
        if (confirmed) {
            showLoading('Menghapus data...');
            
            try {
                const response = await ajaxRequest(url, { method: 'DELETE' });
                
                hideLoading();
                
                if (response.success) {
                    showToast(response.message || 'Data berhasil dihapus', 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast(response.message || 'Gagal menghapus data', 'error');
                }
            } catch (error) {
                hideLoading();
                showToast('Terjadi kesalahan: ' + error.message, 'error');
            }
        }
    });
});

// Auto-save draft
let autoSaveTimer;
document.querySelectorAll('[data-autosave]').forEach(field => {
    field.addEventListener('input', debounce(function() {
        const key = this.getAttribute('data-autosave');
        localStorage.setItem('draft_' + key, this.value);
        
        // Show saved indicator
        const indicator = document.createElement('span');
        indicator.textContent = '✓ Tersimpan';
        indicator.style.cssText = 'color: #27ae60; font-size: 0.85em; margin-left: 10px;';
        
        const existing = this.parentElement.querySelector('.save-indicator');
        if (existing) existing.remove();
        
        indicator.className = 'save-indicator';
        this.parentElement.appendChild(indicator);
        
        setTimeout(() => indicator.remove(), 2000);
    }, 1000));
    
    // Restore draft on load
    const key = field.getAttribute('data-autosave');
    const draft = localStorage.getItem('draft_' + key);
    if (draft && !field.value) {
        field.value = draft;
    }
});

// Print function
function printContent(elementId) {
    const content = document.getElementById(elementId);
    if (!content) return;
    
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Print</title>
            <style>
                body { font-family: Arial, sans-serif; padding: 20px; }
                @media print {
                    .no-print { display: none; }
                }
            </style>
        </head>
        <body>
            ${content.innerHTML}
            <script>
                window.onload = function() {
                    window.print();
                    window.close();
                };
            </script>
        </body>
        </html>
    `);
    printWindow.document.close();
}

// Initialize tooltips (if using tooltips library)
document.querySelectorAll('[data-tooltip]').forEach(element => {
    element.setAttribute('title', element.getAttribute('data-tooltip'));
});

console.log('✅ Admin utilities loaded successfully');
</script>

<?php if (isset($customScripts)): ?>
<!-- Custom scripts for this page -->
<script><?= $customScripts ?></script>
<?php endif; ?>

</body>
</html>
