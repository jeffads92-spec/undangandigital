<?php
require_once '../config/autoload.php';
$auth = new Auth(); if(!$auth->isLoggedIn()) header('Location: login.php');
$backup = new Backup();

// Create backup
if(isset($_POST['create_backup'])) {
    $result = $backup->createBackup($_POST['type'], $_POST['description']);
    if($result['success']) {
        $success = 'Backup berhasil dibuat: ' . $result['filename'];
    } else {
        $error = $result['message'];
    }
}

// Restore backup
if(isset($_GET['restore'])) {
    $result = $backup->restoreBackup($_GET['restore']);
    if($result['success']) {
        $success = 'Backup berhasil direstore';
    } else {
        $error = $result['message'];
    }
}

// Delete backup
if(isset($_GET['delete'])) {
    $result = $backup->deleteBackup($_GET['delete']);
    if($result['success']) {
        $success = 'Backup berhasil dihapus';
    } else {
        $error = $result['message'];
    }
}

// Get all backups
$backups = $backup->getAllBackups();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Backup Sistem - Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <div class="admin-container">
        <?php include 'includes/sidebar.php'; ?>
        <main class="content">
            <h1><i class="fas fa-database"></i> Backup Sistem</h1>
            
            <!-- Create Backup Form -->
            <div class="card" style="margin-bottom: 30px;">
                <h3><i class="fas fa-plus-circle"></i> Buat Backup Baru</h3>
                <form method="POST">
                    <select name="type" required>
                        <option value="full">Full Backup (Database + Files)</option>
                        <option value="database">Database Only</option>
                        <option value="files">Files Only</option>
                    </select>
                    <input type="text" name="description" placeholder="Deskripsi backup" required>
                    <button type="submit" name="create_backup" class="btn-primary">
                        <i class="fas fa-save"></i> Buat Backup
                    </button>
                </form>
            </div>
            
            <!-- Backup List -->
            <div class="card">
                <h3><i class="fas fa-list"></i> Daftar Backup</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nama File</th><th>Tipe</th><th>Ukuran</th><th>Tanggal</th><th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($backups as $backup_item): ?>
                        <tr>
                            <td><?= $backup_item['filename'] ?></td>
                            <td><?= $backup_item['type'] ?></td>
                            <td><?= formatBytes($backup_item['size']) ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($backup_item['created_at'])) ?></td>
                            <td>
                                <a href="?restore=<?= $backup_item['filename'] ?>" class="btn-sm btn-success" onclick="return confirm('Restore backup ini?')">Restore</a>
                                <a href="?delete=<?= $backup_item['filename'] ?>" class="btn-sm btn-danger" onclick="return confirm('Hapus backup ini?')">Hapus</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>
<?php
function formatBytes($bytes) {
    if ($bytes >= 1073741824) return number_format($bytes/1073741824,2).' GB';
    elseif ($bytes >= 1048576) return number_format($bytes/1048576,2).' MB';
    elseif ($bytes >= 1024) return number_format($bytes/1024,2).' KB';
    else return $bytes.' bytes';
}
?>
