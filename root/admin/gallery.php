<?php
require_once '../config/autoload.php';
$auth = new Auth(); if(!$auth->isLoggedIn()) header('Location: login.php');
$db = new Database(); $upload = new Upload();

// Handle upload
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['image'])) {
    $result = $upload->uploadImage($_FILES['image'], [
        'folder' => 'uploads/gallery/',
        'thumbnail' => true
    ]);
    if($result['success']) {
        $db->insert('gallery', [
            'filename' => $result['filename'],
            'title' => $_POST['title'] ?? '',
            'category' => $_POST['category'] ?? 'prewedding',
            'sort_order' => $_POST['sort_order'] ?? 0
        ]);
        $success = 'Foto berhasil diupload';
    } else {
        $error = $result['message'];
    }
}

// Handle delete
if(isset($_GET['delete'])) {
    $image = $db->fetch("SELECT filename FROM gallery WHERE id=?", [$_GET['delete']]);
    if($image) {
        $upload->deleteFile($image['filename'], 'uploads/gallery/');
        $db->delete('gallery', 'id=?', [$_GET['delete']]);
        $success = 'Foto berhasil dihapus';
    }
}

// Get gallery
$gallery = $db->fetchAll("SELECT * FROM gallery ORDER BY sort_order ASC, created_at DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Galeri Foto - Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        .upload-form { background: white; padding: 20px; border-radius: 10px; margin-bottom: 30px; }
        .gallery-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px; }
        .gallery-item { background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .gallery-image { width: 100%; height: 150px; object-fit: cover; }
        .gallery-info { padding: 10px; }
        .gallery-title { font-weight: bold; margin-bottom: 5px; }
        .gallery-category { font-size: 0.85em; color: #666; }
        .gallery-actions { padding: 10px; border-top: 1px solid #eee; }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <div class="admin-container">
        <?php include 'includes/sidebar.php'; ?>
        <main class="content">
            <h1><i class="fas fa-images"></i> Galeri Foto</h1>
            
            <!-- Upload Form -->
            <div class="upload-form">
                <h3><i class="fas fa-upload"></i> Upload Foto Baru</h3>
                <form method="POST" enctype="multipart/form-data">
                    <input type="file" name="image" accept="image/*" required>
                    <input type="text" name="title" placeholder="Judul foto">
                    <select name="category">
                        <option value="prewedding">Prewedding</option>
                        <option value="event">Acara</option>
                        <option value="family">Keluarga</option>
                    </select>
                    <button type="submit">Upload</button>
                </form>
            </div>
            
            <!-- Gallery Grid -->
            <div class="gallery-grid">
                <?php foreach($gallery as $item): ?>
                <div class="gallery-item">
                    <img src="../assets/uploads/gallery/<?= $item['filename'] ?>" class="gallery-image" alt="<?= htmlspecialchars($item['title']) ?>">
                    <div class="gallery-info">
                        <div class="gallery-title"><?= htmlspecialchars($item['title']) ?></div>
                        <div class="gallery-category"><?= $item['category'] ?></div>
                    </div>
                    <div class="gallery-actions">
                        <a href="?delete=<?= $item['id'] ?>" onclick="return confirm('Hapus foto ini?')">Hapus</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>
</body>
</html>