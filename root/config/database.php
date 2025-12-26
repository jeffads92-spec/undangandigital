<?php
/**
 * DATABASE CONFIGURATION
 * Edit sesuai dengan hosting Anda
 */

class DatabaseConfig {
    // ========== DATABASE SETTINGS ==========
    const DB_HOST = 'localhost';      // Database host
    const DB_NAME = 'wedding_digital'; // Database name
    const DB_USER = 'root';           // Database username
    const DB_PASS = '';               // Database password
    
    // ========== SITE SETTINGS ==========
    const SITE_NAME = 'Undangan Digital Premium';
    const SITE_URL = 'http://localhost/wedding/';
    const TIMEZONE = 'Asia/Jakarta';
    
    // ========== SECURITY SETTINGS ==========
    const ENCRYPTION_KEY = 'wedding_digital_secret_key_2025';
    const JWT_SECRET = 'jwt_wedding_secret_2025';
    
    // ========== UPLOAD SETTINGS ==========
    const MAX_UPLOAD_SIZE = 5242880; // 5MB
    const ALLOWED_IMAGE_TYPES = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    const ALLOWED_AUDIO_TYPES = ['mp3', 'ogg', 'm4a', 'wav'];
    
    // ========== EMAIL SETTINGS (Optional) ==========
    const EMAIL_HOST = 'smtp.gmail.com';
    const EMAIL_PORT = 587;
    const EMAIL_USER = '';
    const EMAIL_PASS = '';
    const EMAIL_FROM = 'noreply@undangan-digital.com';
    
    // ========== WHATSAPP SETTINGS (GRATIS) ==========
    // Gunakan click-to-chat tanpa API
    const WHATSAPP_NUMBER = '6281234567890';
    const WHATSAPP_MESSAGE = 'Halo, saya melihat undangan pernikahan Anda';
    
    // ========== QRIS SETTINGS (GRATIS) ==========
    const BANK_NAME = 'BCA';
    const BANK_ACCOUNT = '1234567890';
    const ACCOUNT_NAME = 'NAMA PEMILIK REKENING';
    
    // ========== TEMPLATE SETTINGS ==========
    const DEFAULT_TEMPLATE = 'royal-elegance';
    const AVAILABLE_TEMPLATES = ['royal-elegance', 'classic-romance', 'garden-bliss'];
    
    /**
     * Initialize configuration
     */
    public static function init() {
        // Set timezone
        date_default_timezone_set(self::TIMEZONE);
        
        // Error reporting (disable in production)
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        
        // Start session if not started
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        // Set upload limits
        ini_set('upload_max_filesize', '10M');
        ini_set('post_max_size', '10M');
        ini_set('max_execution_time', '300');
        
        // Security headers
        if (!headers_sent()) {
            header('X-Content-Type-Options: nosniff');
            header('X-Frame-Options: SAMEORIGIN');
            header('X-XSS-Protection: 1; mode=block');
        }
    }
    
    /**
     * Get database connection
     */
    public static function getConnection() {
        try {
            $dsn = "mysql:host=" . self::DB_HOST . ";dbname=" . self::DB_NAME . ";charset=utf8mb4";
            $pdo = new PDO($dsn, self::DB_USER, self::DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
            
            return $pdo;
        } catch (PDOException $e) {
            die("Koneksi database gagal: " . $e->getMessage());
        }
    }
    
    /**
     * Get base URL
     */
    public static function getBaseUrl() {
        if (defined('BASE_URL')) {
            return BASE_URL;
        }
        
        return self::SITE_URL;
    }
    
    /**
     * Encrypt data
     */
    public static function encrypt($data) {
        $key = self::ENCRYPTION_KEY;
        $method = 'AES-256-CBC';
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($method));
        
        $encrypted = openssl_encrypt($data, $method, $key, 0, $iv);
        return base64_encode($encrypted . '::' . $iv);
    }
    
    /**
     * Decrypt data
     */
    public static function decrypt($data) {
        $key = self::ENCRYPTION_KEY;
        $method = 'AES-256-CBC';
        
        list($encrypted_data, $iv) = explode('::', base64_decode($data), 2);
        return openssl_decrypt($encrypted_data, $method, $key, 0, $iv);
    }
}

// Initialize configuration
DatabaseConfig::init();
?>
