<?php
/**
 * ADMIN HEADER
 * Top navigation bar untuk admin panel
 */

// Get current user info
$currentUser = $auth->getCurrentUser();
$userFullName = $currentUser['full_name'] ?? 'Admin';
$userRole = $currentUser['role'] ?? 'admin';
$userAvatar = $currentUser['avatar'] ?? '../assets/images/default-avatar.png';

// Get unread notifications
$db = new Database();
$unreadMessages = $db->fetchColumn("SELECT COUNT(*) FROM messages WHERE is_approved = 0");
$pendingRsvp = $db->fetchColumn("SELECT COUNT(*) FROM guests WHERE attendance = 'pending'");
$pendingPayments = $db->fetchColumn("SELECT COUNT(*) FROM payments WHERE status = 'pending_verify'");

$totalNotifications = $unreadMessages + $pendingRsvp + $pendingPayments;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    
    <!-- Title -->
    <title><?= $pageTitle ?? 'Admin Panel' ?> - <?= SITE_NAME ?></title>
    
    <!-- Favicon -->
    <link rel="icon" href="<?= BASE_URL ?>assets/icons/favicon.ico">
    
    <!-- CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    
    <!-- Custom styles for this page -->
    <?php if (isset($customStyles)): ?>
    <style><?= $customStyles ?></style>
    <?php endif; ?>
    
    <style>
        /* Admin Header Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
            color: #333;
        }
        
        .admin-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 70px;
            background: #fff;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 30px;
        }
        
        .header-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .menu-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 1.5em;
            color: #333;
            cursor: pointer;
            padding: 10px;
        }
        
        .header-logo {
            display: flex;
            align-items: center;
            gap: 15px;
            text-decoration: none;
        }
        
        .logo-icon {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, #8B4513, #D2691E);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5em;
        }
        
        .logo-text h1 {
            font-size: 1.2em;
            font-weight: 600;
            color: #333;
        }
        
        .logo-text p {
            font-size: 0.75em;
            color: #666;
        }
        
        .header-search {
            position: relative;
            display: flex;
            align-items: center;
        }
        
        .search-input {
            width: 350px;
            padding: 10px 40px 10px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 0.95em;
            transition: all 0.3s;
        }
        
        .search-input:focus {
            outline: none;
            border-color: #8B4513;
            box-shadow: 0 0 0 3px rgba(139, 69, 19, 0.1);
        }
        
        .search-btn {
            position: absolute;
            right: 10px;
            background: none;
            border: none;
            color: #666;
            cursor: pointer;
            font-size: 1.1em;
            padding: 5px;
        }
        
        .header-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .header-actions {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .header-btn {
            position: relative;
            background: none;
            border: none;
            color: #666;
            cursor: pointer;
            font-size: 1.2em;
            padding: 10px;
            border-radius: 8px;
            transition: all 0.3s;
        }
        
        .header-btn:hover {
            background: #f0f0f0;
            color: #8B4513;
        }
        
        .notification-badge {
            position: absolute;
            top: 5px;
            right: 5px;
            background: #dc3545;
            color: white;
            font-size: 0.6em;
            padding: 2px 5px;
            border-radius: 10px;
            min-width: 18px;
            text-align: center;
        }
        
        .user-menu {
            position: relative;
        }
        
        .user-btn {
            display: flex;
            align-items: center;
            gap: 10px;
            background: none;
            border: none;
            cursor: pointer;
            padding: 8px 12px;
            border-radius: 8px;
            transition: all 0.3s;
        }
        
        .user-btn:hover {
            background: #f0f0f0;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #8B4513;
        }
        
        .user-info {
            text-align: left;
        }
        
        .user-name {
            font-size: 0.9em;
            font-weight: 600;
            color: #333;
            display: block;
        }
        
        .user-role {
            font-size: 0.75em;
            color: #666;
            text-transform: capitalize;
        }
        
        .dropdown-icon {
            font-size: 0.8em;
            color: #666;
        }
        
        .user-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            margin-top: 10px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
            min-width: 220px;
            padding: 10px 0;
            display: none;
            z-index: 1001;
        }
        
        .user-dropdown.active {
            display: block;
            animation: dropdownFade 0.3s ease;
        }
        
        @keyframes dropdownFade {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .dropdown-header {
            padding: 15px 20px;
            border-bottom: 1px solid #eee;
        }
        
        .dropdown-header .user-name {
            font-size: 1em;
            margin-bottom: 3px;
        }
        
        .dropdown-header .user-role {
            font-size: 0.85em;
        }
        
        .dropdown-menu {
            list-style: none;
            padding: 5px 0;
        }
        
        .dropdown-item {
            padding: 12px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            color: #333;
            text-decoration: none;
            transition: all 0.3s;
            cursor: pointer;
        }
        
        .dropdown-item:hover {
            background: #f8f9fa;
            color: #8B4513;
        }
        
        .dropdown-item i {
            width: 20px;
            font-size: 1em;
        }
        
        .dropdown-divider {
            height: 1px;
            background: #eee;
            margin: 5px 0;
        }
        
        .dropdown-item.logout {
            color: #dc3545;
        }
        
        .dropdown-item.logout:hover {
            background: #fee;
        }
        
        /* Notification Dropdown */
        .notification-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            margin-top: 10px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
            width: 350px;
            max-height: 400px;
            overflow-y: auto;
            display: none;
            z-index: 1001;
        }
        
        .notification-dropdown.active {
            display: block;
            animation: dropdownFade 0.3s ease;
        }
        
        .notification-header {
            padding: 15px 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .notification-header h3 {
            font-size: 1em;
            color: #333;
        }
        
        .mark-read-btn {
            background: none;
            border: none;
            color: #8B4513;
            font-size: 0.85em;
            cursor: pointer;
        }
        
        .notification-list {
            list-style: none;
        }
        
        .notification-item {
            padding: 15px 20px;
            border-bottom: 1px solid #f0f0f0;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .notification-item:hover {
            background: #f8f9fa;
        }
        
        .notification-item.unread {
            background: #f0f8ff;
        }
        
        .notification-content {
            display: flex;
            gap: 12px;
        }
        
        .notification-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        
        .notification-icon.info { background: #e3f2fd; color: #1976d2; }
        .notification-icon.success { background: #e8f5e9; color: #388e3c; }
        .notification-icon.warning { background: #fff3e0; color: #f57c00; }
        .notification-icon.danger { background: #ffebee; color: #d32f2f; }
        
        .notification-text h4 {
            font-size: 0.9em;
            font-weight: 600;
            color: #333;
            margin-bottom: 3px;
        }
        
        .notification-text p {
            font-size: 0.85em;
            color: #666;
            margin-bottom: 5px;
        }
        
        .notification-time {
            font-size: 0.75em;
            color: #999;
        }
        
        .notification-empty {
            padding: 40px 20px;
            text-align: center;
            color: #999;
        }
        
        .notification-empty i {
            font-size: 3em;
            margin-bottom: 10px;
            opacity: 0.3;
        }
        
        /* Responsive */
        @media (max-width: 1024px) {
            .header-search {
                display: none;
            }
        }
        
        @media (max-width: 768px) {
            .admin-header {
                padding: 0 15px;
            }
            
            .menu-toggle {
                display: block;
            }
            
            .logo-text {
                display: none;
            }
            
            .user-info {
                display: none;
            }
        }
    </style>
</head>
<body>
    <!-- Admin Header -->
    <header class="admin-header">
        <div class="header-left">
            <button class="menu-toggle" id="menuToggle">
                <i class="fas fa-bars"></i>
            </button>
            
            <a href="<?= BASE_URL ?>admin/" class="header-logo">
                <div class="logo-icon">
                    <i class="fas fa-heart"></i>
                </div>
                <div class="logo-text">
                    <h1>Wedding Digital</h1>
                    <p>Admin Panel v2.0</p>
                </div>
            </a>
        </div>
        
        <div class="header-search">
            <input type="text" class="search-input" placeholder="Cari tamu, ucapan, atau menu..." id="globalSearch">
            <button class="search-btn">
                <i class="fas fa-search"></i>
            </button>
        </div>
        
        <div class="header-right">
            <div class="header-actions">
                <!-- Notifications -->
                <div class="notification-menu">
                    <button class="header-btn" id="notificationBtn">
                        <i class="fas fa-bell"></i>
                        <?php if ($totalNotifications > 0): ?>
                        <span class="notification-badge"><?= $totalNotifications ?></span>
                        <?php endif; ?>
                    </button>
                    
                    <div class="notification-dropdown" id="notificationDropdown">
                        <div class="notification-header">
                            <h3>Notifikasi</h3>
                            <button class="mark-read-btn">Tandai dibaca</button>
                        </div>
                        <ul class="notification-list">
                            <?php if ($unreadMessages > 0): ?>
                            <li class="notification-item unread">
                                <div class="notification-content">
                                    <div class="notification-icon info">
                                        <i class="fas fa-comments"></i>
                                    </div>
                                    <div class="notification-text">
                                        <h4>Ucapan Baru</h4>
                                        <p><?= $unreadMessages ?> ucapan menunggu persetujuan</p>
                                        <span class="notification-time">Baru saja</span>
                                    </div>
                                </div>
                            </li>
                            <?php endif; ?>
                            
                            <?php if ($pendingRsvp > 0): ?>
                            <li class="notification-item unread">
                                <div class="notification-content">
                                    <div class="notification-icon warning">
                                        <i class="fas fa-user-clock"></i>
                                    </div>
                                    <div class="notification-text">
                                        <h4>RSVP Pending</h4>
                                        <p><?= $pendingRsvp ?> tamu belum konfirmasi</p>
                                        <span class="notification-time">Hari ini</span>
                                    </div>
                                </div>
                            </li>
                            <?php endif; ?>
                            
                            <?php if ($pendingPayments > 0): ?>
                            <li class="notification-item unread">
                                <div class="notification-content">
                                    <div class="notification-icon success">
                                        <i class="fas fa-gift"></i>
                                    </div>
                                    <div class="notification-text">
                                        <h4>Hadiah Masuk</h4>
                                        <p><?= $pendingPayments ?> pembayaran perlu verifikasi</p>
                                        <span class="notification-time">Hari ini</span>
                                    </div>
                                </div>
                            </li>
                            <?php endif; ?>
                            
                            <?php if ($totalNotifications == 0): ?>
                            <li class="notification-empty">
                                <i class="fas fa-bell-slash"></i>
                                <p>Tidak ada notifikasi baru</p>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <button class="header-btn" onclick="window.open('<?= BASE_URL ?>', '_blank')" title="Lihat Website">
                    <i class="fas fa-external-link-alt"></i>
                </button>
                
                <button class="header-btn" onclick="window.location.href='<?= BASE_URL ?>admin/settings.php'" title="Pengaturan">
                    <i class="fas fa-cog"></i>
                </button>
            </div>
            
            <!-- User Menu -->
            <div class="user-menu">
                <button class="user-btn" id="userMenuBtn">
                    <img src="<?= BASE_URL . $userAvatar ?>" alt="Avatar" class="user-avatar">
                    <div class="user-info">
                        <span class="user-name"><?= htmlspecialchars($userFullName) ?></span>
                        <span class="user-role"><?= htmlspecialchars($userRole) ?></span>
                    </div>
                    <i class="fas fa-chevron-down dropdown-icon"></i>
                </button>
                
                <div class="user-dropdown" id="userDropdown">
                    <div class="dropdown-header">
                        <span class="user-name"><?= htmlspecialchars($userFullName) ?></span>
                        <span class="user-role"><?= htmlspecialchars($userRole) ?></span>
                    </div>
                    <ul class="dropdown-menu">
                        <li>
                            <a href="<?= BASE_URL ?>admin/profile.php" class="dropdown-item">
                                <i class="fas fa-user"></i>
                                <span>Profil Saya</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?= BASE_URL ?>admin/settings.php" class="dropdown-item">
                                <i class="fas fa-cog"></i>
                                <span>Pengaturan</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?= BASE_URL ?>admin/activity.php" class="dropdown-item">
                                <i class="fas fa-history"></i>
                                <span>Riwayat Aktivitas</span>
                            </a>
                        </li>
                        <div class="dropdown-divider"></div>
                        <li>
                            <a href="<?= BASE_URL ?>admin/help.php" class="dropdown-item">
                                <i class="fas fa-question-circle"></i>
                                <span>Bantuan</span>
                            </a>
                        </li>
                        <div class="dropdown-divider"></div>
                        <li>
                            <a href="<?= BASE_URL ?>admin/logout.php" class="dropdown-item logout" onclick="return confirm('Yakin ingin logout?')">
                                <i class="fas fa-sign-out-alt"></i>
                                <span>Logout</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </header>
    
    <script>
    // Toggle User Dropdown
    document.getElementById('userMenuBtn').addEventListener('click', function(e) {
        e.stopPropagation();
        document.getElementById('userDropdown').classList.toggle('active');
        document.getElementById('notificationDropdown').classList.remove('active');
    });
    
    // Toggle Notification Dropdown
    document.getElementById('notificationBtn').addEventListener('click', function(e) {
        e.stopPropagation();
        document.getElementById('notificationDropdown').classList.toggle('active');
        document.getElementById('userDropdown').classList.remove('active');
    });
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function() {
        document.getElementById('userDropdown').classList.remove('active');
        document.getElementById('notificationDropdown').classList.remove('active');
    });
    
    // Menu Toggle for Mobile
    document.getElementById('menuToggle').addEventListener('click', function() {
        document.querySelector('.admin-sidebar').classList.toggle('active');
    });
    
    // Global Search
    document.getElementById('globalSearch').addEventListener('keyup', function(e) {
        if (e.key === 'Enter') {
            const query = this.value;
            window.location.href = '<?= BASE_URL ?>admin/search.php?q=' + encodeURIComponent(query);
        }
    });
    </script>
