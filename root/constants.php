<?php
/**
 * APPLICATION CONSTANTS
 * Define all paths, URLs, and configuration constants
 */

// Prevent direct access
defined('BASE_URL') or die('Direct access not allowed');

// ============================================
// APPLICATION INFORMATION
// ============================================
define('APP_NAME', 'Wedding Digital Premium');
define('APP_VERSION', '2.0.0');
define('APP_AUTHOR', 'Digital Invitation by Jeff');
define('APP_DESCRIPTION', 'Premium Wedding Invitation System with 3 Templates');

// ============================================
// DIRECTORY PATHS
// ============================================
// Root path (absolut)
define('ROOT_PATH', dirname(dirname(__FILE__)) . '/');

// Config path
define('CONFIG_PATH', ROOT_PATH . 'config/');

// Core path
define('CORE_PATH', ROOT_PATH . 'core/');

// Assets path
define('ASSETS_PATH', ROOT_PATH . 'assets/');
define('UPLOADS_PATH', ASSETS_PATH . 'uploads/');
define('MUSIC_PATH', ASSETS_PATH . 'music/');
define('IMAGES_PATH', ASSETS_PATH . 'images/');
define('CSS_PATH', ASSETS_PATH . 'css/');
define('JS_PATH', ASSETS_PATH . 'js/');

// Templates path
define('TEMPLATES_PATH', ROOT_PATH . 'templates/');

// Admin path
define('ADMIN_PATH', ROOT_PATH . 'admin/');

// Database path
define('DATABASE_PATH', ROOT_PATH . 'database/');

// Backups path
define('BACKUPS_PATH', ROOT_PATH . 'assets/backups/');

// Logs path
define('LOGS_PATH', ROOT_PATH . 'logs/');

// ============================================
// URL PATHS (relative)
// ============================================
define('ASSETS_URL', BASE_URL . 'assets/');
define('UPLOADS_URL', ASSETS_URL . 'uploads/');
define('MUSIC_URL', ASSETS_URL . 'music/');
define('IMAGES_URL', ASSETS_URL . 'images/');
define('CSS_URL', ASSETS_URL . 'css/');
define('JS_URL', ASSETS_URL . 'js/');
define('TEMPLATES_URL', BASE_URL . 'templates/');
define('ADMIN_URL', BASE_URL . 'admin/');

// ============================================
// UPLOAD SETTINGS
// ============================================
// Image upload
define('MAX_IMAGE_SIZE', 5242880); // 5MB in bytes
define('MAX_IMAGE_WIDTH', 1920);
define('MAX_IMAGE_HEIGHT', 1080);
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
define('IMAGE_QUALITY', 85); // JPEG quality (0-100)

// Music upload
define('MAX_MUSIC_SIZE', 10485760); // 10MB in bytes
define('ALLOWED_MUSIC_TYPES', ['mp3', 'ogg', 'm4a', 'wav']);

// Thumbnail settings
define('THUMBNAIL_WIDTH', 300);
define('THUMBNAIL_HEIGHT', 300);

// ============================================
// SESSION SETTINGS
// ============================================
define('SESSION_TIMEOUT', 3600); // 1 hour in seconds
define('SESSION_NAME', 'wedding_session');
define('COOKIE_LIFETIME', 2592000); // 30 days in seconds

// ============================================
// SECURITY SETTINGS
// ============================================
define('CSRF_TOKEN_NAME', 'csrf_token');
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_ATTEMPT_TIMEOUT', 900); // 15 minutes in seconds
define('PASSWORD_MIN_LENGTH', 6);
define('ENABLE_BRUTE_FORCE_PROTECTION', true);

// ============================================
// PAGINATION SETTINGS
// ============================================
define('ITEMS_PER_PAGE', 20);
define('GUESTS_PER_PAGE', 20);
define('MESSAGES_PER_PAGE', 10);
define('GALLERY_PER_PAGE', 12);

// ============================================
// BACKUP SETTINGS
// ============================================
define('AUTO_BACKUP_ENABLED', true);
define('AUTO_BACKUP_INTERVAL', 86400); // 24 hours in seconds
define('MAX_BACKUP_FILES', 10); // Keep last 10 backups
define('BACKUP_COMPRESSION', true);

// ============================================
// CACHE SETTINGS
// ============================================
define('CACHE_ENABLED', true);
define('CACHE_LIFETIME', 3600); // 1 hour in seconds
define('CACHE_PATH', ROOT_PATH . 'cache/');

// ============================================
// EMAIL SETTINGS
// ============================================
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_ENCRYPTION', 'tls');
define('SMTP_USERNAME', ''); // Set in settings
define('SMTP_PASSWORD', ''); // Set in settings
define('SMTP_FROM_EMAIL', 'noreply@wedding.com');
define('SMTP_FROM_NAME', 'Wedding Digital');

// ============================================
// WHATSAPP SETTINGS
// ============================================
define('WHATSAPP_API_URL', 'https://wa.me/');
define('WHATSAPP_DEFAULT_MESSAGE', 'Halo, saya melihat undangan pernikahan Anda');

// ============================================
// QRIS SETTINGS
// ============================================
define('QRIS_API_URL', 'https://api.qrserver.com/v1/create-qr-code/');
define('QRIS_SIZE', '300x300');
define('QRIS_FORMAT', 'png');

// ============================================
// PAYMENT SETTINGS
// ============================================
define('PAYMENT_METHODS', ['qris', 'transfer', 'cash']);
define('PAYMENT_STATUS', ['pending', 'pending_verify', 'verified', 'rejected']);
define('AUTO_VERIFY_PAYMENT', false);

// ============================================
// MUSIC PLAYER SETTINGS
// ============================================
define('MUSIC_AUTOPLAY', true);
define('MUSIC_LOOP', true);
define('MUSIC_VOLUME', 0.5); // 0.0 to 1.0
define('MUSIC_CONTROLS', true);

// ============================================
// GALLERY SETTINGS
// ============================================
define('GALLERY_CATEGORIES', ['prewedding', 'event', 'family', 'guests']);
define('GALLERY_THUMBNAIL_SIZE', 300);
define('GALLERY_LIGHTBOX_ENABLED', true);

// ============================================
// RSVP SETTINGS
// ============================================
define('RSVP_STATUS', ['pending', 'hadir', 'tidak']);
define('RSVP_MAX_PEOPLE', 10);
define('RSVP_REQUIRE_PHONE', true);
define('RSVP_REQUIRE_EMAIL', false);

// ============================================
// MESSAGE SETTINGS
// ============================================
define('MESSAGE_MODERATION', true); // Approve before display
define('MESSAGE_MAX_LENGTH', 500);
define('MESSAGE_MIN_LENGTH', 10);
define('MESSAGE_REQUIRE_APPROVAL', true);

// ============================================
// TEMPLATE SETTINGS
// ============================================
define('AVAILABLE_TEMPLATES', [
    'royal-elegance' => 'Royal Elegance',
    'classic-romance' => 'Classic Romance',
    'garden-bliss' => 'Garden Bliss'
]);

define('DEFAULT_TEMPLATE', 'royal-elegance');
define('TEMPLATE_CACHE_ENABLED', true);

// ============================================
// PWA SETTINGS
// ============================================
define('PWA_ENABLED', true);
define('PWA_NAME', 'Wedding Digital');
define('PWA_SHORT_NAME', 'Wedding');
define('PWA_THEME_COLOR', '#8B4513');
define('PWA_BACKGROUND_COLOR', '#f9f5f0');

// ============================================
// SEO SETTINGS
// ============================================
define('SEO_TITLE_SEPARATOR', ' - ');
define('SEO_DEFAULT_DESCRIPTION', 'Undangan pernikahan digital dengan fitur lengkap');
define('SEO_DEFAULT_KEYWORDS', 'undangan digital, pernikahan, wedding, undangan online');
define('SEO_OG_TYPE', 'website');

// ============================================
// DATE & TIME SETTINGS
// ============================================
define('DEFAULT_TIMEZONE', 'Asia/Jakarta');
define('DATE_FORMAT', 'd/m/Y');
define('DATETIME_FORMAT', 'd/m/Y H:i');
define('TIME_FORMAT', 'H:i');

// Set default timezone
date_default_timezone_set(DEFAULT_TIMEZONE);

// ============================================
// LANGUAGE SETTINGS
// ============================================
define('DEFAULT_LANGUAGE', 'id'); // Indonesian
define('AVAILABLE_LANGUAGES', ['id', 'en']);

// ============================================
// DEBUG & LOGGING
// ============================================
define('DEBUG_MODE', false); // Set to false in production
define('ERROR_LOGGING', true);
define('ERROR_LOG_FILE', LOGS_PATH . 'error.log');
define('ACTIVITY_LOGGING', true);
define('ACTIVITY_LOG_FILE', LOGS_PATH . 'activity.log');

// Error reporting
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', ERROR_LOG_FILE);
}

// ============================================
// ADMIN ROLE HIERARCHY
// ============================================
define('ROLE_SUPERADMIN', 'superadmin');
define('ROLE_ADMIN', 'admin');
define('ROLE_EDITOR', 'editor');

define('ROLE_HIERARCHY', [
    ROLE_SUPERADMIN => 3,
    ROLE_ADMIN => 2,
    ROLE_EDITOR => 1
]);

// ============================================
// NOTIFICATION SETTINGS
// ============================================
define('NOTIFICATION_TYPES', ['success', 'info', 'warning', 'error']);
define('NOTIFICATION_DURATION', 3000); // 3 seconds in milliseconds

// ============================================
// API SETTINGS
// ============================================
define('API_ENABLED', true);
define('API_VERSION', 'v1');
define('API_RATE_LIMIT', 100); // Requests per hour
define('API_TOKEN_EXPIRY', 3600); // 1 hour in seconds

// ============================================
// SOCIAL MEDIA
// ============================================
define('SOCIAL_SHARE_ENABLED', true);
define('SOCIAL_PLATFORMS', ['whatsapp', 'facebook', 'telegram', 'twitter']);

// ============================================
// COUNTDOWN SETTINGS
// ============================================
define('COUNTDOWN_ENABLED', true);
define('COUNTDOWN_FORMAT', 'dhms'); // days, hours, minutes, seconds

// ============================================
// MAINTENANCE MODE
// ============================================
define('MAINTENANCE_MODE', false);
define('MAINTENANCE_MESSAGE', 'Website sedang dalam pemeliharaan. Mohon coba beberapa saat lagi.');
define('MAINTENANCE_ALLOWED_IPS', ['127.0.0.1', '::1']); // Localhost

// ============================================
// CUSTOM SETTINGS
// ============================================
// Add your custom constants here
// define('CUSTOM_SETTING', 'value');

// ============================================
// HELPER FUNCTIONS
// ============================================

/**
 * Get full path
 */
function getPath($path) {
    return ROOT_PATH . ltrim($path, '/');
}

/**
 * Get full URL
 */
function getUrl($path) {
    return BASE_URL . ltrim($path, '/');
}

/**
 * Format file size
 */
function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    
    for ($i = 0; $bytes > 1024; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, $precision) . ' ' . $units[$i];
}

/**
 * Format date
 */
function formatDate($date, $format = DATE_FORMAT) {
    return date($format, strtotime($date));
}

/**
 * Format datetime
 */
function formatDatetime($datetime, $format = DATETIME_FORMAT) {
    return date($format, strtotime($datetime));
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if user has role
 */
function hasRole($role) {
    if (!isLoggedIn()) {
        return false;
    }
    
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

/**
 * Check if user has permission
 */
function hasPermission($requiredRole) {
    if (!isLoggedIn()) {
        return false;
    }
    
    $userRole = $_SESSION['role'] ?? '';
    
    if (!isset(ROLE_HIERARCHY[$userRole]) || !isset(ROLE_HIERARCHY[$requiredRole])) {
        return false;
    }
    
    return ROLE_HIERARCHY[$userRole] >= ROLE_HIERARCHY[$requiredRole];
}

/**
 * Sanitize input
 */
function sanitize($input) {
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

/**
 * Generate random string
 */
function generateRandomString($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Redirect to URL
 */
function redirect($url, $statusCode = 302) {
    header('Location: ' . $url, true, $statusCode);
    exit;
}

/**
 * Flash message
 */
function setFlash($type, $message) {
    $_SESSION['flash'][$type] = $message;
}

/**
 * Get flash message
 */
function getFlash($type) {
    if (isset($_SESSION['flash'][$type])) {
        $message = $_SESSION['flash'][$type];
        unset($_SESSION['flash'][$type]);
        return $message;
    }
    return null;
}

/**
 * Check if request is AJAX
 */
function isAjax() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Check if request is POST
 */
function isPost() {
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

/**
 * Get client IP address
 */
function getClientIp() {
    $ipaddress = '';
    
    if (isset($_SERVER['HTTP_CLIENT_IP'])) {
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } elseif (isset($_SERVER['HTTP_X_FORWARDED'])) {
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    } elseif (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    } elseif (isset($_SERVER['HTTP_FORWARDED'])) {
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    } elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    } else {
        $ipaddress = 'UNKNOWN';
    }
    
    return $ipaddress;
}

/**
 * Log activity
 */
function logActivity($message, $level = 'info') {
    if (!ACTIVITY_LOGGING) {
        return false;
    }
    
    $logMessage = date('[Y-m-d H:i:s]') . " [{$level}] {$message}" . PHP_EOL;
    
    if (!is_dir(LOGS_PATH)) {
        mkdir(LOGS_PATH, 0755, true);
    }
    
    return file_put_contents(ACTIVITY_LOG_FILE, $logMessage, FILE_APPEND);
}

/**
 * Create directory if not exists
 */
function ensureDirectoryExists($path) {
    if (!is_dir($path)) {
        return mkdir($path, 0755, true);
    }
    return true;
}

// ============================================
// CREATE REQUIRED DIRECTORIES
// ============================================
$requiredDirs = [
    UPLOADS_PATH,
    UPLOADS_PATH . 'gallery/',
    UPLOADS_PATH . 'payments/',
    UPLOADS_PATH . 'temp/',
    MUSIC_PATH,
    BACKUPS_PATH,
    LOGS_PATH,
    CACHE_PATH
];

foreach ($requiredDirs as $dir) {
    ensureDirectoryExists($dir);
}

// ============================================
// MAINTENANCE MODE CHECK
// ============================================
if (MAINTENANCE_MODE && !in_array(getClientIp(), MAINTENANCE_ALLOWED_IPS)) {
    // Skip maintenance for admin area
    $currentUrl = $_SERVER['REQUEST_URI'] ?? '';
    if (strpos($currentUrl, '/admin/') === false) {
        http_response_code(503);
        die('
        <!DOCTYPE html>
        <html>
        <head>
            <title>Maintenance Mode</title>
            <style>
                body { font-family: Arial, sans-serif; text-align: center; padding: 50px; background: #f5f5f5; }
                h1 { color: #8B4513; }
                p { color: #666; }
            </style>
        </head>
        <body>
            <h1>🔧 Maintenance Mode</h1>
            <p>' . MAINTENANCE_MESSAGE . '</p>
        </body>
        </html>
        ');
    }
}

// End of constants.php
