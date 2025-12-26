<?php
require_once '../config/autoload.php';
$auth = new Auth();
if(!$auth->isLoggedIn()) header('Location: login.php');

$db = new Database();

// Get statistics
$stats = [
    'total_guests' => $db->fetchColumn("SELECT COUNT(*) FROM guests"),
    'confirmed' => $db->fetchColumn("SELECT COUNT(*) FROM guests WHERE attendance='hadir'"),
    'pending' => $db->fetchColumn("SELECT COUNT(*) FROM guests WHERE attendance='pending'"),
    'total_gifts' => $db->fetchColumn("SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='verified'"),
    'total_messages' => $db->fetchColumn("SELECT COUNT(*) FROM messages"),
    'today_visitors' => $db->fetchColumn("SELECT COUNT(*) FROM visitors WHERE DATE(visited_at)=CURDATE()")
];

// Recent data
$recent_guests = $db->fetchAll("SELECT * FROM guests ORDER BY created_at DESC LIMIT 5");
$recent_payments = $db->fetchAll("SELECT p.*, g.name FROM payments p LEFT JOIN guests g ON p.guest_id=g.id ORDER BY p.created_at DESC LIMIT 5");
$recent_messages = $db->fetchAll("SELECT * FROM messages WHERE is_approved=1 ORDER BY created_at DESC LIMIT 5");

// Charts data
$attendance_data = $db->fetchAll("SELECT attendance, COUNT(*) as count FROM guests GROUP BY attendance");
$payment_data = $db->fetchAll("SELECT DATE(created_at) as date, SUM(amount) as total FROM payments WHERE status='verified' AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) GROUP BY DATE(created_at)");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; border-radius: 10px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .stat-icon { font-size: 2em; color: #8B4513; margin-bottom: 10px; }
        .stat-number { font-size: 2em; font-weight: bold; color: #333; }
        .stat-label { color: #666; font-size: 0.9em; }
        .chart-container { background: white; border-radius: 10px; padding: 20px; margin: 20px 0; }
        .recent-table { background: white; border-radius: 10px; padding: 20px; margin-top: 20px; }
        .table { width: 100%; border-collapse: collapse; }
        .table th, .table td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        .table th { background: #f8f9fa; font-weight: 600; color: #495057; }
        .badge { padding: 4px 8px; border-radius: 4px; font-size: 0.85em; }
        .badge-success { background: #d4edda; color: #155724; }
        .badge-warning { background: #fff3cd; color: #856404; }
        .badge-danger { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <div class="admin-container">
        <?php include 'includes/sidebar.php'; ?>
        <main class="content">
            <h1><i class="fas fa-tachometer-alt"></i> Dashboard</h1>
            
            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card"><div class="stat-icon"><i class="fas fa-users"></i></div><div class="stat-number"><?= $stats['total_guests'] ?></div><div class="stat-label">Total Tamu</div></div>
                <div class="stat-card"><div class="stat-icon"><i class="fas fa-check-circle"></i></div><div class="stat-number"><?= $stats['confirmed'] ?></div><div class="stat-label">Konfirmasi Hadir</div></div>
                <div class="stat-card"><div class="stat-icon"><i class="fas fa-clock"></i></div><div class="stat-number"><?= $stats['pending'] ?></div><div class="stat-label">Menunggu Konfirmasi</div></div>
                <div class="stat-card"><div class="stat-icon"><i class="fas fa-gift"></i></div><div class="stat-number">Rp <?= number_format($stats['total_gifts'],0,',','.') ?></div><div class="stat-label">Total Hadiah</div></div>
                <div class="stat-card"><div class="stat-icon"><i class="fas fa-comment"></i></div><div class="stat-number"><?= $stats['total_messages'] ?></div><div class="stat-label">Ucapan Tamu</div></div>
                <div class="stat-card"><div class="stat-icon"><i class="fas fa-eye"></i></div><div class="stat-number"><?= $stats['today_visitors'] ?></div><div class="stat-label">Pengunjung Hari Ini</div></div>
            </div>
            
            <!-- Charts -->
            <div class="chart-container">
                <h3><i class="fas fa-chart-pie"></i> Statistik Kehadiran</h3>
                <canvas id="attendanceChart" height="100"></canvas>
            </div>
            
            <!-- Recent Data -->
            <div class="row">
                <div class="col-md-6">
                    <div class="recent-table">
                        <h3><i class="fas fa-user-friends"></i> Tamu Terbaru</h3>
                        <table class="table">
                            <thead><tr><th>Nama</th><th>Telepon</th><th>Status</th></tr></thead>
                            <tbody>
                                <?php foreach($recent_guests as $guest): ?>
                                <tr>
                                    <td><?= htmlspecialchars($guest['name']) ?></td>
                                    <td><?= htmlspecialchars($guest['phone']) ?></td>
                                    <td><span class="badge badge-<?= $guest['attendance']=='hadir'?'success':($guest['attendance']=='tidak'?'danger':'warning') ?>"><?= $guest['attendance'] ?></span></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="recent-table">
                        <h3><i class="fas fa-gift"></i> Hadiah Terbaru</h3>
                        <table class="table">
                            <thead><tr><th>Nama</th><th>Jumlah</th><th>Status</th></tr></thead>
                            <tbody>
                                <?php foreach($recent_payments as $payment): ?>
                                <tr>
                                    <td><?= htmlspecialchars($payment['name'] ?? 'Anonim') ?></td>
                                    <td>Rp <?= number_format($payment['amount'],0,',','.') ?></td>
                                    <td><span class="badge badge-<?= $payment['status']=='verified'?'success':'warning' ?>"><?= $payment['status'] ?></span></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <script>
        // Attendance Chart
        const ctx = document.getElementById('attendanceChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Hadir', 'Tidak Hadir', 'Pending'],
                datasets: [{
                    data: [
                        <?= $attendance_data[0]['count'] ?? 0 ?>,
                        <?= $attendance_data[1]['count'] ?? 0 ?>,
                        <?= $attendance_data[2]['count'] ?? 0 ?>
                    ],
                    backgroundColor: ['#28a745', '#dc3545', '#ffc107']
                }]
            }
        });
    </script>
</body>
</html>