<?php
/**
 * ADMIN SIDEBAR
 * Navigation menu untuk admin panel
 */

// Get current page untuk active state
$currentPage = basename($_SERVER['PHP_SELF'], '.php');

// Menu items
$menuItems = [
    [
        'title' => 'Dashboard',
        'icon' => 'tachometer-alt',
        'url' => 'index.php',
        'page' => 'index',
        'badge' => null
    ],
    [
        'title' => 'Kelola Tamu',
        'icon' => 'users',
        'url' => 'guests.php',
        'page' => 'guests',
        'badge' => $pendingRsvp ?? 0
    ],
    [
        'title' => 'Galeri Foto',
        'icon' => 'images',
        'url' => 'gallery.php',
        'page' => 'gallery',
        'badge' => null
    ],
    [
        'title' => 'Musik',
        'icon' => 'music',
        'url' => 'music.php',
        'page' => 'music',
        'badge' => null
    ],
    [
        'title' => 'Ucapan Tamu',
        'icon' => 'comments',
        'url' => 'messages.php',
        'page' => 'messages',
        'badge' => $unreadMessages ?? 0
    ],
    [
        'title' => 'Hadiah & Donasi',
        'icon' => 'gift',
        'url' => 'payments.php',
        'page' => 'payments',
        'badge' => $pendingPayments ?? 0
    ],
    [
        'title' => 'Template',
        'icon' => 'palette',
        'url' => 'templates.php',
        'page' => 'templates',
        'badge' => null
    ],
    [
        'title' => 'Pengaturan',
        'icon' => 'cog',
        'url' => 'settings.php',
        'page' => 'settings',
        'badge' => null
    ],
    [
        'title' => 'Laporan',
        'icon' => 'chart-bar',
        'url' => 'reports.php',
        'page' => 'reports',
        'badge' => null
    ],
    [
        'title' => 'Backup Data',
        'icon' => 'database',
        'url' => 'backups.php',
        'page' => 'backups',
        'badge' => null
    ]
];
?>

<style>
/* Sidebar Styles */
.admin-sidebar {
    position: fixed;
    top: 70px;
    left: 0;
    bottom: 0;
    width: 260px;
    background: #2c3e50;
    color: white;
    overflow-y: auto;
    z-index: 999;
    transition: all 0.3s ease;
}

.sidebar-menu {
    list-style: none;
    padding: 20px 0;
}

.menu-section {
    padding: 0 20px;
    margin: 25px 0 10px;
}

.section-title {
    font-size: 0.75em;
    font-weight: 600;
    text-transform: uppercase;
    color: #95a5a6;
    letter-spacing: 1px;
}

.menu-item {
    margin: 2px 0;
}

.menu-link {
    display: flex;
    align-items: center;
    padding: 12px 20px;
    color: #ecf0f1;
    text-decoration: none;
    transition: all 0.3s;
    border-left: 3px solid transparent;
    position: relative;
}

.menu-link:hover {
    background: rgba(255, 255, 255, 0.05);
    border-left-color: #8B4513;
    padding-left: 25px;
}

.menu-link.active {
    background: linear-gradient(90deg, rgba(139, 69, 19, 0.2), transparent);
    border-left-color: #8B4513;
    color: white;
    font-weight: 500;
}

.menu-icon {
    width: 40px;
    font-size: 1.1em;
    text-align: center;
}

.menu-text {
    flex: 1;
    font-size: 0.95em;
}

.menu-badge {
    background: #e74c3c;
    color: white;
    font-size: 0.7em;
    padding: 3px 8px;
    border-radius: 12px;
    min-width: 22px;
    text-align: center;
    font-weight: 600;
}

.menu-badge.info {
    background: #3498db;
}

.menu-badge.success {
    background: #27ae60;
}

.menu-badge.warning {
    background: #f39c12;
}

/* Sidebar Footer */
.sidebar-footer {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    padding: 20px;
    background: #1a252f;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.sidebar-footer-content {
    text-align: center;
    font-size: 0.85em;
    color: #95a5a6;
}

.sidebar-footer-content p {
    margin: 5px 0;
}

.sidebar-footer-links {
    display: flex;
    justify-content: center;
    gap: 15px;
    margin-top: 10px;
}

.footer-link {
    color: #95a5a6;
    text-decoration: none;
    font-size: 0.9em;
    transition: color 0.3s;
}

.footer-link:hover {
    color: #8B4513;
}

/* Quick Actions */
.quick-actions {
    padding: 20px;
    background: rgba(0, 0, 0, 0.2);
    margin: 20px;
    border-radius: 10px;
}

.quick-actions-title {
    font-size: 0.85em;
    font-weight: 600;
    color: #95a5a6;
    margin-bottom: 15px;
}

.quick-action-btn {
    display: flex;
    align-items: center;
    width: 100%;
    padding: 10px 15px;
    margin: 5px 0;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 8px;
    color: white;
    text-decoration: none;
    font-size: 0.9em;
    transition: all 0.3s;
    cursor: pointer;
}

.quick-action-btn:hover {
    background: rgba(139, 69, 19, 0.3);
    border-color: #8B4513;
}

.quick-action-icon {
    width: 30px;
    text-align: center;
    font-size: 1.1em;
}

/* Scrollbar */
.admin-sidebar::-webkit-scrollbar {
    width: 6px;
}

.admin-sidebar::-webkit-scrollbar-track {
    background: #1a252f;
}

.admin-sidebar::-webkit-scrollbar-thumb {
    background: #34495e;
    border-radius: 3px;
}

.admin-sidebar::-webkit-scrollbar-thumb:hover {
    background: #4a5f7f;
}

/* Responsive */
@media (max-width: 768px) {
    .admin-sidebar {
        left: -260px;
    }
    
    .admin-sidebar.active {
        left: 0;
        box-shadow: 5px 0 15px rgba(0,0,0,0.3);
    }
}

/* Main Content Adjustment */
.admin-container {
    display: flex;
    margin-top: 70px;
}

.content {
    flex: 1;
    margin-left: 260px;
    padding: 30px;
    min-height: calc(100vh - 70px);
}

@media (max-width: 768px) {
    .content {
        margin-left: 0;
    }
}

/* Page Header */
.page-header {
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 2px solid #e9ecef;
}

.page-header h1 {
    font-size: 1.8em;
    color: #2c3e50;
    margin-bottom: 5px;
}

.page-header p {
    color: #7f8c8d;
    font-size: 0.95em;
}

.page-header .breadcrumb {
    display: flex;
    gap: 10px;
    align-items: center;
    margin-top: 10px;
    font-size: 0.9em;
    color: #95a5a6;
}

.breadcrumb-item {
    display: flex;
    align-items: center;
    gap: 5px;
}

.breadcrumb-separator {
    color: #bdc3c7;
}

/* Alert Messages */
.alert {
    padding: 15px 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 12px;
    animation: slideInDown 0.3s ease;
}

@keyframes slideInDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.alert-success {
    background: #d4edda;
    border: 1px solid #c3e6cb;
    color: #155724;
}

.alert-error {
    background: #f8d7da;
    border: 1px solid #f5c6cb;
    color: #721c24;
}

.alert-warning {
    background: #fff3cd;
    border: 1px solid #ffeaa7;
    color: #856404;
}

.alert-info {
    background: #d1ecf1;
    border: 1px solid #bee5eb;
    color: #0c5460;
}

.alert i {
    font-size: 1.2em;
}

.alert-close {
    margin-left: auto;
    background: none;
    border: none;
    font-size: 1.2em;
    cursor: pointer;
    opacity: 0.5;
    transition: opacity 0.3s;
}

.alert-close:hover {
    opacity: 1;
}

/* Card Styles */
.card {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    padding: 20px;
    margin-bottom: 20px;
}

.card h3 {
    font-size: 1.2em;
    color: #2c3e50;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.card h3 i {
    color: #8B4513;
}
</style>

<!-- Admin Sidebar -->
<aside class="admin-sidebar" id="adminSidebar">
    <nav>
        <ul class="sidebar-menu">
            <!-- Main Menu -->
            <li class="menu-section">
                <span class="section-title">Menu Utama</span>
            </li>
            
            <?php 
            $mainMenu = array_slice($menuItems, 0, 6);
            foreach ($mainMenu as $item): 
                $isActive = ($currentPage === $item['page']) ? 'active' : '';
            ?>
            <li class="menu-item">
                <a href="<?= BASE_URL ?>admin/<?= $item['url'] ?>" class="menu-link <?= $isActive ?>">
                    <span class="menu-icon">
                        <i class="fas fa-<?= $item['icon'] ?>"></i>
                    </span>
                    <span class="menu-text"><?= $item['title'] ?></span>
                    <?php if ($item['badge'] && $item['badge'] > 0): ?>
                    <span class="menu-badge"><?= $item['badge'] ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <?php endforeach; ?>
            
            <!-- System Menu -->
            <li class="menu-section">
                <span class="section-title">Sistem</span>
            </li>
            
            <?php 
            $systemMenu = array_slice($menuItems, 6);
            foreach ($systemMenu as $item): 
                $isActive = ($currentPage === $item['page']) ? 'active' : '';
            ?>
            <li class="menu-item">
                <a href="<?= BASE_URL ?>admin/<?= $item['url'] ?>" class="menu-link <?= $isActive ?>">
                    <span class="menu-icon">
                        <i class="fas fa-<?= $item['icon'] ?>"></i>
                    </span>
                    <span class="menu-text"><?= $item['title'] ?></span>
                    <?php if ($item['badge'] && $item['badge'] > 0): ?>
                    <span class="menu-badge"><?= $item['badge'] ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
        
        <!-- Quick Actions -->
        <div class="quick-actions">
            <div class="quick-actions-title">
                <i class="fas fa-bolt"></i> Quick Actions
            </div>
            
            <a href="<?= BASE_URL ?>admin/guests.php?action=add" class="quick-action-btn">
                <span class="quick-action-icon"><i class="fas fa-user-plus"></i></span>
                <span>Tambah Tamu</span>
            </a>
            
            <a href="<?= BASE_URL ?>admin/gallery.php?action=upload" class="quick-action-btn">
                <span class="quick-action-icon"><i class="fas fa-cloud-upload-alt"></i></span>
                <span>Upload Foto</span>
            </a>
            
            <a href="<?= BASE_URL ?>" target="_blank" class="quick-action-btn">
                <span class="quick-action-icon"><i class="fas fa-external-link-alt"></i></span>
                <span>Lihat Website</span>
            </a>
            
            <button onclick="createBackup()" class="quick-action-btn">
                <span class="quick-action-icon"><i class="fas fa-save"></i></span>
                <span>Backup Sekarang</span>
            </button>
        </div>
    </nav>
    
    <!-- Sidebar Footer -->
    <div class="sidebar-footer">
        <div class="sidebar-footer-content">
            <p><strong>Wedding Digital v2.0</strong></p>
            <p>&copy; <?= date('Y') ?> All Rights Reserved</p>
            <div class="sidebar-footer-links">
                <a href="<?= BASE_URL ?>admin/help.php" class="footer-link">
                    <i class="fas fa-question-circle"></i> Bantuan
                </a>
                <a href="<?= BASE_URL ?>admin/docs.php" class="footer-link">
                    <i class="fas fa-book"></i> Docs
                </a>
            </div>
        </div>
    </div>
</aside>

<script>
// Quick Backup Function
function createBackup() {
    if (confirm('Buat backup database sekarang?')) {
        window.location.href = '<?= BASE_URL ?>admin/backups.php?action=create&type=full';
    }
}

// Close sidebar on mobile when clicking menu
document.querySelectorAll('.menu-link').forEach(link => {
    link.addEventListener('click', function() {
        if (window.innerWidth <= 768) {
            document.getElementById('adminSidebar').classList.remove('active');
        }
    });
});

// Close sidebar when clicking outside on mobile
document.addEventListener('click', function(e) {
    if (window.innerWidth <= 768) {
        const sidebar = document.getElementById('adminSidebar');
        const menuToggle = document.getElementById('menuToggle');
        
        if (!sidebar.contains(e.target) && e.target !== menuToggle && !menuToggle.contains(e.target)) {
            sidebar.classList.remove('active');
        }
    }
});
</script>
