<?php
require_once '../config/autoload.php';
$auth = new Auth(); if(!$auth->isLoggedIn()) header('Location: login.php');
$db = new Database();

// Save settings
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    foreach($_POST['settings'] as $key => $value) {
        $db->update('settings', ['key_value' => $value], 'key_name=?', [$key]);
    }
    $success = 'Pengaturan berhasil disimpan';
}

// Get all settings
$settings_result = $db->fetchAll("SELECT key_name, key_value FROM settings");
$settings = [];
foreach($settings_result as $row) {
    $settings[$row['key_name']] = $row['key_value'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pengaturan Sistem - Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        .settings-form { background: white; padding: 30px; border-radius: 10px; }
        .form-section { margin-bottom: 30px; padding-bottom: 20px; border-bottom: 1px solid #eee; }
        .form-section h3 { color: #8B4513; margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 500; }
        .form-control { width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <div class="admin-container">
        <?php include 'includes/sidebar.php'; ?>
        <main class="content">
            <h1><i class="fas fa-cog"></i> Pengaturan Sistem</h1>
            
            <form method="POST" class="settings-form">
                <!-- Wedding Settings -->
                <div class="form-section">
                    <h3><i class="fas fa-heart"></i> Informasi Pernikahan</h3>
                    <div class="form-group">
                        <label>Judul Pernikahan</label>
                        <input type="text" name="settings[wedding_title]" value="<?= htmlspecialchars($settings['wedding_title'] ?? '') ?>" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Nama Mempelai Pria</label>
                        <input type="text" name="settings[groom_name]" value="<?= htmlspecialchars($settings['groom_name'] ?? '') ?>" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Nama Mempelai Wanita</label>
                        <input type="text" name="settings[bride_name]" value="<?= htmlspecialchars($settings['bride_name'] ?? '') ?>" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Tanggal Acara</label>
                        <input type="date" name="settings[wedding_date]" value="<?= $settings['wedding_date'] ?? '' ?>" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Lokasi</label>
                        <input type="text" name="settings[location]" value="<?= htmlspecialchars($settings['location'] ?? '') ?>" class="form-control">
                    </div>
                </div>
                
                <!-- Contact Settings -->
                <div class="form-section">
                    <h3><i class="fas fa-phone"></i> Kontak</h3>
                    <div class="form-group">
                        <label>Nomor WhatsApp</label>
                        <input type="text" name="settings[whatsapp_number]" value="<?= $settings['whatsapp_number'] ?? '' ?>" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="settings[email]" value="<?= $settings['email'] ?? '' ?>" class="form-control">
                    </div>
                </div>
                
                <!-- Payment Settings -->
                <div class="form-section">
                    <h3><i class="fas fa-credit-card"></i> Pembayaran</h3>
                    <div class="form-group">
                        <label>Nama Bank</label>
                        <input type="text" name="settings[bank_name]" value="<?= htmlspecialchars($settings['bank_name'] ?? '') ?>" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Nomor Rekening</label>
                        <input type="text" name="settings[bank_account]" value="<?= $settings['bank_account'] ?? '' ?>" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Nama Pemilik Rekening</label>
                        <input type="text" name="settings[account_name]" value="<?= htmlspecialchars($settings['account_name'] ?? '') ?>" class="form-control">
                    </div>
                </div>
                
                <!-- SEO Settings -->
                <div class="form-section">
                    <h3><i class="fas fa-search"></i> SEO</h3>
                    <div class="form-group">
                        <label>Meta Description</label>
                        <textarea name="settings[meta_description]" class="form-control" rows="3"><?= htmlspecialchars($settings['meta_description'] ?? '') ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Meta Keywords</label>
                        <input type="text" name="settings[meta_keywords]" value="<?= htmlspecialchars($settings['meta_keywords'] ?? '') ?>" class="form-control">
                    </div>
                </div>
                
                <button type="submit" class="btn-primary">
                    <i class="fas fa-save"></i> Simpan Semua Pengaturan
                </button>
            </form>
        </main>
    </div>
</body>
</html>