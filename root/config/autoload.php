<?php
/**
 * AUTO LOADER FOR CLASSES
 * Load semua class secara otomatis
 */

spl_autoload_register(function ($class_name) {
    // List of directories to search
    $directories = [
        ROOT_PATH . 'core/',
        ROOT_PATH . 'admin/classes/',
        ROOT_PATH . 'api/classes/'
    ];
    
    // Convert class name to filename
    $class_file = str_replace('\\', '/', $class_name) . '.php';
    
    // Search in directories
    foreach ($directories as $directory) {
        $file_path = $directory . $class_file;
        
        if (file_exists($file_path)) {
            require_once $file_path;
            return;
        }
    }
    
    // Also check for class files without namespace
    $simple_class = $class_name . '.class.php';
    foreach ($directories as $directory) {
        $file_path = $directory . $simple_class;
        
        if (file_exists($file_path)) {
            require_once $file_path;
            return;
        }
    }
    
    // Throw error if class not found
    throw new Exception("Class {$class_name} tidak ditemukan");
});

// Load helper functions
require_once ROOT_PATH . 'core/functions.php';

// Load configuration
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/constants.php';

// Check if installation is needed
function checkInstallation() {
    $install_file = ROOT_PATH . 'install.php';
    $config_file = __DIR__ . '/database.php';
    
    // Jika install.php masih ada dan config belum diisi
    if (file_exists($install_file) && DatabaseConfig::DB_NAME == 'wedding_digital') {
        if (!isset($_GET['page']) || $_GET['page'] != 'install') {
            header('Location: ' . BASE_URL . '?page=install');
            exit;
        }
    }
}

// Initialize
checkInstallation();

// Global database connection
try {
    $GLOBALS['db'] = DatabaseConfig::getConnection();
} catch (Exception $e) {
    // Jika database belum ada, tampilkan pesan install
    if (file_exists(ROOT_PATH . 'install.php')) {
        header('Location: ' . BASE_URL . 'install.php');
        exit;
    } else {
        die("Database error: " . $e->getMessage());
    }
}
?>
