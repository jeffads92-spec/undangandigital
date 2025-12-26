<?php
/**
 * ADMIN DASHBOARD - INDEX
 * Halaman utama admin panel
 */

require_once '../config/autoload.php';

$auth = new Auth();

// Redirect to login if not authenticated
if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Check permission
if (!$auth->hasPermission('admin')) {
    header('Location: ../index.php');
    exit;
}

$db = new Database();

// Get statistics
$stats = [
    'total_guests' => $db->fetchColumn("SELECT COUNT(*) FROM guests"),
    'confirmed_yes' => $db->fetchColumn("SELECT COUNT(*) FROM guests WHERE attendance = 'hadir'"),
    'confirmed_no' => $db->fetchColumn("SELECT COUNT(*) FROM guests WHERE attendance = 'tidak'"),
    'pending_rsvp' => $db->fetchColumn("SELECT COUNT(*) FROM guests WHERE attendance = 'pending'"),
    'total_messages' => $db->fetchColumn("SELECT COUNT(*) FROM messages"),
    'total_gifts' => $db->fetchColumn("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE status = 'verified'"),
    'total_photos' => $db->fetchColumn("SELECT COUNT(*) FROM gallery"),
    'total_songs' => $db->fetchColumn("SELECT COUNT(*) FROM wedding_songs")
];

// Get recent activities
$recentActivities = $db->fetchAll(
    "SELECT a.*, u.username 
     FROM activities a 
     LEFT JOIN admin_users u ON a.admin_id = u.id 
     ORDER BY a.created_at DESC 
     LIMIT 10"
);

// Get recent guests
$recentGuests = $db->fetchAll(
    "SELECT * FROM guests 
     ORDER BY created_at DESC 
     LIMIT 10"
);

// Get recent messages
$recentMessages = $db->fetchAll(
    "SELECT * FROM messages 
     WHERE is_approved = 1 
     ORDER BY created_at DESC 
     LIMIT 10"
);

// Get user info
$user = $auth->getCurrentUser();

// Set page title
$pageTitle = "Dashboard - " . SITE_NAME;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        .dashboard-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-icon {
            font-size: 2em;
            color: #8B4513;
            margin-bottom: 10px;
        }
        
        .stat-number {
            font-size: 2em;
            font-weight: bold;
            color: #333;
        }
        
        .stat-label {
            color: #666;
            font-size: 0.9em;
        }
        
        .recent-activities {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-top: 30px;
        }
        
        .activity-item {
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        
        .activity-item:last-child {
            border-bottom: none;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <?php include 'includes/header.php'; ?>
    
    <div class="admin-container">
        <!-- Sidebar -->
        <?php include 'includes/sidebar.php'; ?>
        
        <!-- Main Content -->
        <main class="content">
            <div class="page-header">
                <h1><i class="fas fa-tachometer-alt"></i> Dashboard</h1>
                <p>Selamat datang, <?= htmlspecialchars($user['full_name']) ?>!</p>
            </div>
            
            <!-- Stats Cards -->
            <div class="dashboard-stats">
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-users"></i></div>
                    <div class="stat-number"><?= $stats['total_guests'] ?></div>
                    <div class="stat-label">Total Tamu</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                    <div class="stat-number"><?= $stats['confirmed_yes'] ?></div>
                    <div class="stat-label">Konfirmasi Hadir</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-times-circle"></i></div>
                    <div class="stat-number"><?= $stats['confirmed_no'] ?></div>
                    <div class="stat-label">Tidak Hadir</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-clock"></i></div>
                    <div class="stat-number"><?= $stats['pending_rsvp'] ?></div>
                    <div class="stat-label">Menunggu Konfirmasi</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-gift"></i></div>
                    <div class="stat-number">Rp <?= number_format($stats['total_gifts'], 0, ',', '.') ?></div>
                    <div class="stat-label">Total Hadiah</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-comments"></i></div>
                    <div class="stat-number"><?= $stats['total_messages'] ?></div>
                    <div class="stat-label">Ucapan Tamu</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-images"></i></div>
                    <div class="stat-number"><?= $stats['total_photos'] ?></div>
                    <div class="stat-label">Foto Galeri</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-music"></i></div>
                    <div class="stat-number"><?= $stats['total_songs'] ?></div>
                    <div class="stat-label">Lagu Tersedia</div>
                </div>
            </div>
            
            <!-- Charts -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <h3><i class="fas fa-chart-pie"></i> Statistik Kehadiran</h3>
                        <canvas id="attendanceChart" height="250"></canvas>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card">
                        <h3><i class="fas fa-chart-line"></i> Aktivitas Terbaru</h3>
                        <div class="recent-activities">
                            <?php foreach ($recentActivities as $activity): ?>
                            <div class="activity-item">
                                <div class="activity-header">
                                    <strong><?= htmlspecialchars($activity['username'] ?? 'System') ?></strong>
                                    <small class="text-muted">
                                        <?= date('d/m/Y H:i', strtotime($activity['created_at'])) ?>
                                    </small>
                                </div>
                                <div class="activity-body">
                                    <?= htmlspecialchars($activity['action']) ?> 
                                    <?php if ($activity['details']): ?>
                                    - <?= htmlspecialchars($activity['details']) ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recent Data -->
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card">
                        <h3><i class="fas fa-user-friends"></i> Tamu Terbaru</h3>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th>Telepon</th>
                                    <th>Status</th>
                                    <th>Tanggal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentGuests as $guest): ?>
                                <tr>
                                    <td><?= htmlspecialchars($guest['name']) ?></td>
                                    <td><?= htmlspecialchars($guest['phone']) ?></td>
                                    <td>
                                        <span class="badge badge-<?= 
                                            $guest['attendance'] == 'hadir' ? 'success' : 
                                            ($guest['attendance'] == 'tidak' ? 'danger' : 'warning')
                                        ?>">
                                            <?= $guest['attendance'] == 'hadir' ? 'Hadir' : 
                                               ($guest['attendance'] == 'tidak' ? 'Tidak' : 'Pending') ?>
                                        </span>
                                    </td>
                                    <td><?= date('d/m/Y', strtotime($guest['created_at'])) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card">
                        <h3><i class="fas fa-comment-dots"></i> Ucapan Terbaru</h3>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th>Pesan</th>
                                    <th>Tanggal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentMessages as $message): ?>
                                <tr>
                                    <td><?= htmlspecialchars($message['name']) ?></td>
                                    <td><?= substr(htmlspecialchars($message['message']), 0, 50) ?>...</td>
                                    <td><?= date('d/m/Y', strtotime($message['created_at'])) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>
    
    <script>
    // Attendance Chart
    const ctx = document.getElementById('attendanceChart').getContext('2d');
    const attendanceChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Hadir', 'Tidak Hadir', 'Pending'],
            datasets: [{
                data: [
                    <?= $stats['confirmed_yes'] ?>,
                    <?= $stats['confirmed_no'] ?>,
                    <?= $stats['pending_rsvp'] ?>
                ],
                backgroundColor: [
                    '#28a745', // Green for yes
                    '#dc3545', // Red for no
                    '#ffc107'  // Yellow for pending
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.label || '';
                            if (label) {
                                label += ': ';
                            }
                            label += context.raw + ' orang';
                            return label;
                        }
                    }
                }
            }
        }
    });
    
    // Auto refresh dashboard every 5 minutes
    setTimeout(() => {
        window.location.reload();
    }, 5 * 60 * 1000);
    </script>
</body>
</html>