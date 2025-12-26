<?php
/**
 * SYSTEM CONSTANTS
 * Jangan diubah kecuali diperlukan
 */

// ========== SYSTEM PATHS ==========
define('ROOT_PATH', dirname(dirname(__FILE__)) . '/');
define('CORE_PATH', ROOT_PATH . 'core/');
define('ADMIN_PATH', ROOT_PATH . 'admin/');
define('TEMPLATES_PATH', ROOT_PATH . 'templates/');
define('ASSETS_PATH', ROOT_PATH . 'assets/');
define('UPLOADS_PATH', ASSETS_PATH . 'uploads/');
define('MUSIC_PATH', ASSETS_PATH . 'music/');
define('BACKUPS_PATH', ASSETS_PATH . 'backups/');

// ========== USER ROLES ==========
define('ROLE_SUPERADMIN', 1);
define('ROLE_ADMIN', 2);
define('ROLE_GUEST', 3);

// ========== ATTENDANCE STATUS ==========
define('ATTENDANCE_PENDING', 'pending');
define('ATTENDANCE_YES', 'hadir');
define('ATTENDANCE_NO', 'tidak');

// ========== PAYMENT STATUS ==========
define('PAYMENT_PENDING', 'pending');
define('PAYMENT_VERIFIED', 'verified');
define('PAYMENT_REJECTED', 'rejected');

// ========== GALLERY CATEGORIES ==========
define('GALLERY_PREWEDDING', 'prewedding');
define('GALLERY_EVENT', 'event');
define('GALLERY_FAMILY', 'family');
define('GALLERY_GUESTS', 'guests');

// ========== TEMPLATE SETTINGS ==========
define('TEMPLATE_ROYAL', 'royal-elegance');
define('TEMPLATE_CLASSIC', 'classic-romance');
define('TEMPLATE_GARDEN', 'garden-bliss');

// ========== DEFAULT SETTINGS ==========
$DEFAULT_SETTINGS = [
    'wedding_title' => 'Pernikahan Kita',
    'groom_name' => 'Mempelai Pria',
    'bride_name' => 'Mempelai Wanita',
    'wedding_date' => date('Y-m-d', strtotime('+30 days')),
    'wedding_time' => '14:00',
    'location' => 'Jakarta Convention Center',
    'google_maps' => 'https://maps.app.goo.gl/xxxx',
    'whatsapp_number' => '6281234567890',
    'bank_name' => 'BCA',
    'bank_account' => '1234567890',
    'account_name' => 'Nama Pemilik Rekening',
    'groom_parents' => 'Bapak A & Ibu A',
    'bride_parents' => 'Bapak B & Ibu B',
    'groom_bio' => 'Putra pertama dari...',
    'bride_bio' => 'Putri kedua dari...',
    'instagram' => '@pengantin',
    'facebook' => 'facebook.com/pengantin',
    'meta_description' => 'Undangan pernikahan digital dengan fitur lengkap',
    'meta_keywords' => 'undangan digital, pernikahan, wedding, undangan online',
    'theme_color' => '#8B4513',
    'secondary_color' => '#DAA520',
    'font_family' => 'Poppins, sans-serif',
    'music_autoplay' => '1',
    'show_countdown' => '1',
    'show_gallery' => '1',
    'show_gifts' => '1',
    'allow_messages' => '1'
];

// ========== RESPONSE CODES ==========
define('RESPONSE_SUCCESS', 200);
define('RESPONSE_CREATED', 201);
define('RESPONSE_BAD_REQUEST', 400);
define('RESPONSE_UNAUTHORIZED', 401);
define('RESPONSE_FORBIDDEN', 403);
define('RESPONSE_NOT_FOUND', 404);
define('RESPONSE_SERVER_ERROR', 500);

// ========== MESSAGES ==========
$MESSAGES = [
    'success_save' => 'Data berhasil disimpan',
    'success_update' => 'Data berhasil diperbarui',
    'success_delete' => 'Data berhasil dihapus',
    'success_upload' => 'File berhasil diupload',
    'error_save' => 'Gagal menyimpan data',
    'error_update' => 'Gagal memperbarui data',
    'error_delete' => 'Gagal menghapus data',
    'error_upload' => 'Gagal mengupload file',
    'error_login' => 'Username atau password salah',
    'error_permission' => 'Anda tidak memiliki izin',
    'error_not_found' => 'Data tidak ditemukan',
    'error_required' => 'Field ini wajib diisi',
    'error_email' => 'Format email tidak valid',
    'error_phone' => 'Format nomor telepon tidak valid',
    'error_file_type' => 'Tipe file tidak diizinkan',
    'error_file_size' => 'Ukuran file terlalu besar'
];
?>
