<?php
require_once '../config/autoload.php';
$auth = new Auth(); if(!$auth->isLoggedIn()) header('Location: login.php');
$db = new Database();

// Handle verification
if(isset($_GET['verify'])) {
    $db->update('payments', [
        'status' => 'verified',
        'verified_by' => $_SESSION['user_id'],
        'verified_at' => date('Y-m-d H:i:s')
    ], 'id=?', [$_GET['verify']]);
    $success = 'Pembayaran berhasil diverifikasi';
}

// Get payments
$payments = $db->fetchAll("
    SELECT p.*, g.name as guest_name 
    FROM payments p 
    LEFT JOIN guests g ON p.guest_id=g.id 
    ORDER BY p.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tracking Pembayaran - Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <div class="admin-container">
        <?php include 'includes/sidebar.php'; ?>
        <main class="content">
            <h1><i class="fas fa-money-bill-wave"></i> Tracking Pembayaran</h1>
            
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th><th>Nama</th><th>Jumlah</th><th>Metode</th><th>Status</th><th>Tanggal</th><th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($payments as $payment): ?>
                    <tr>
                        <td><?= $payment['transaction_id'] ?></td>
                        <td><?= htmlspecialchars($payment['guest_name'] ?? 'Anonim') ?></td>
                        <td>Rp <?= number_format($payment['amount'],0,',','.') ?></td>
                        <td><?= $payment['method'] ?></td>
                        <td>
                            <span class="badge badge-<?= 
                                $payment['status']=='verified'?'success':
                                ($payment['status']=='pending'?'warning':'danger')
                            ?>">
                                <?= $payment['status'] ?>
                            </span>
                        </td>
                        <td><?= date('d/m/Y', strtotime($payment['created_at'])) ?></td>
                        <td>
                            <?php if($payment['status'] == 'pending'): ?>
                            <a href="?verify=<?= $payment['id'] ?>" class="btn-sm btn-success">Verifikasi</a>
                            <?php endif; ?>
                            <?php if($payment['proof_image']): ?>
                            <a href="../assets/uploads/payments/<?= $payment['proof_image'] ?>" target="_blank" class="btn-sm btn-primary">Lihat Bukti</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </main>
    </div>
</body>
</html>