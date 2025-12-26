<?php
/**
 * WEDDING DIGITAL PREMIUM - MAIN ROUTER
 * Fitur: 3 Template, Admin Kompleks, Music Player, QRIS & WhatsApp Gratis
 */

session_start();

// Auto-detect base URL
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$base_url = $protocol . "://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);
$base_url = rtrim($base_url, '/') . '/';

define('BASE_URL', $base_url);
define('SITE_NAME', 'Undangan Digital Premium');

// Load configuration
require_once 'config/database.php';
require_once 'config/autoload.php';

// Initialize core classes
$db = new Database();
$auth = new Auth();

// Get active template from database or default
$template = 'royal-elegance'; // Default
try {
    $active_template = $db->fetch("SELECT folder_name FROM templates WHERE is_active = 1 LIMIT 1");
    if ($active_template) {
        $template = $active_template['folder_name'];
    }
} catch (Exception $e) {
    // Use default template if error
}

// Get wedding settings
$settings = [];
try {
    $settings_result = $db->fetchAll("SELECT key_name, key_value FROM settings");
    foreach ($settings_result as $row) {
        $settings[$row['key_name']] = $row['key_value'];
    }
} catch (Exception $e) {
    // Default settings if table doesn't exist yet
    $settings = [
        'wedding_title' => 'Pernikahan Kita',
        'groom_name' => 'Mempelai Pria',
        'bride_name' => 'Mempelai Wanita',
        'wedding_date' => date('Y-m-d', strtotime('+30 days')),
        'wedding_time' => '14:00',
        'location' => 'Jakarta',
        'whatsapp_number' => '6281234567890',
        'bank_account' => '1234567890',
        'bank_name' => 'BCA'
    ];
}

// Route requests
$page = $_GET['page'] ?? 'home';
$allowed_pages = ['home', 'couple', 'events', 'gallery', 'rsvp', 'gifts', 'messages'];

if (in_array($page, $allowed_pages)) {
    // Load template file
    $template_file = "templates/{$template}/{$page}.php";
    
    if (file_exists($template_file)) {
        // Pass data to template
        $template_data = [
            'settings' => $settings,
            'base_url' => BASE_URL,
            'template' => $template,
            'page' => $page
        ];
        
        extract($template_data);
        
        // Include template header
        include "templates/{$template}/partials/header.php";
        
        // Include content page
        include $template_file;
        
        // Include template footer
        include "templates/{$template}/partials/footer.php";
    } else {
        // Fallback to default
        include "templates/royal-elegance/home.php";
    }
} else {
    // 404 page
    header("HTTP/1.0 404 Not Found");
    echo "<h1>Halaman Tidak Ditemukan</h1>";
    echo "<p>Halaman yang Anda cari tidak tersedia.</p>";
    echo '<a href="' . BASE_URL . '">Kembali ke Beranda</a>';
}

// Close database connection
$db->close();
?>