<?php
require_once '../config/autoload.php';
$auth = new Auth(); if(!$auth->isLoggedIn()) header('Location: login.php');
$db = new Database();

// Handle actions
if(isset($_GET['approve'])) {
    $db->update('messages', ['is_approved'=>1], 'id=?', [$_GET['approve']]);
    $success = 'Pesan berhasil disetujui';
}
if(isset($_GET['delete'])) {
    $db->delete('messages', 'id=?', [$_GET['delete']]);
    $success = 'Pesan berhasil dihapus';
}

// Get messages
$messages = $db->fetchAll("SELECT m.*, g.name as guest_name FROM messages m LEFT JOIN guests g ON m.guest_id=g.id ORDER BY m.created_at DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Ucapan Tamu - Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        .message-list { background: white; border-radius: 10px; padding: 20px; }
        .message-item { border-bottom: 1px solid #eee; padding: 15px 0; }
        .message-header { display: flex; justify-content: space-between; margin-bottom: 10px; }
        .message-name { font-weight: bold; color: #333; }
        .message-date { color: #666; font-size: 0.9em; }
        .message-content { color: #555; line-height: 1.6; }
        .message-actions { margin-top: 10px; }
        .btn-sm { padding: 5px 10px; font-size: 0.85em; border-radius: 4px; border: none; cursor: pointer; }
        .btn-success { background: #28a745; color: white; }
        .btn-danger { background: #dc3545; color: white; }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <div class="admin-container">
        <?php include 'includes/sidebar.php'; ?>
        <main class="content">
            <h1><i class="fas fa-comments"></i> Ucapan Tamu</h1>
            
            <div class="message-list">
                <?php foreach($messages as $message): ?>
                <div class="message-item">
                    <div class="message-header">
                        <div class="message-name"><?= htmlspecialchars($message['name']) ?></div>
                        <div class="message-date"><?= date('d/m/Y H:i', strtotime($message['created_at'])) ?></div>
                    </div>
                    <div class="message-content"><?= nl2br(htmlspecialchars($message['message'])) ?></div>
                    <div class="message-actions">
                        <?php if(!$message['is_approved']): ?>
                        <a href="?approve=<?= $message['id'] ?>" class="btn-sm btn-success">Setujui</a>
                        <?php endif; ?>
                        <a href="?delete=<?= $message['id'] ?>" class="btn-sm btn-danger" onclick="return confirm('Hapus pesan ini?')">Hapus</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>
</body>
</html>