<?php
session_start();

// Check if already installed
if (file_exists('../config/installed.lock')) {
    die('System already installed. Please remove install directory.');
}

// Installation steps
$steps = [
    'welcome' => 'Welcome',
    'requirements' => 'System Requirements',
    'database' => 'Database Setup',
    'admin' => 'Admin Account',
    'settings' => 'Wedding Settings',
    'finish' => 'Installation Complete'
];

$current_step = $_GET['step'] ?? 'welcome';
$error = '';
$success = '';

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($current_step) {
        case 'database':
            $result = processDatabaseStep();
            if ($result['success']) {
                header('Location: ?step=admin');
                exit;
            } else {
                $error = $result['error'];
            }
            break;
            
        case 'admin':
            $result = processAdminStep();
            if ($result['success']) {
                header('Location: ?step=settings');
                exit;
            } else {
                $error = $result['error'];
            }
            break;
            
        case 'settings':
            $result = processSettingsStep();
            if ($result['success']) {
                header('Location: ?step=finish');
                exit;
            } else {
                $error = $result['error'];
            }
            break;
    }
}

function checkRequirements() {
    $requirements = [
        'php_version' => [
            'required' => '7.4',
            'current' => PHP_VERSION,
            'passed' => version_compare(PHP_VERSION, '7.4.0', '>=')
        ],
        'pdo_mysql' => [
            'required' => 'Enabled',
            'current' => extension_loaded('pdo_mysql') ? 'Enabled' : 'Disabled',
            'passed' => extension_loaded('pdo_mysql')
        ],
        'gd' => [
            'required' => 'Enabled',
            'current' => extension_loaded('gd') ? 'Enabled' : 'Disabled',
            'passed' => extension_loaded('gd')
        ],
        'json' => [
            'required' => 'Enabled',
            'current' => extension_loaded('json') ? 'Enabled' : 'Disabled',
            'passed' => extension_loaded('json')
        ],
        'mbstring' => [
            'required' => 'Enabled',
            'current' => extension_loaded('mbstring') ? 'Enabled' : 'Disabled',
            'passed' => extension_loaded('mbstring')
        ],
        'openssl' => [
            'required' => 'Enabled',
            'current' => extension_loaded('openssl') ? 'Enabled' : 'Disabled',
            'passed' => extension_loaded('openssl')
        ],
        'file_uploads' => [
            'required' => 'On',
            'current' => ini_get('file_uploads') ? 'On' : 'Off',
            'passed' => ini_get('file_uploads')
        ],
        'upload_max_filesize' => [
            'required' => '10M',
            'current' => ini_get('upload_max_filesize'),
            'passed' => intval(ini_get('upload_max_filesize')) >= 10
        ],
        'post_max_size' => [
            'required' => '20M',
            'current' => ini_get('post_max_size'),
            'passed' => intval(ini_get('post_max_size')) >= 20
        ],
        'writable_config' => [
            'required' => 'Writable',
            'current' => is_writable('../config') ? 'Writable' : 'Not Writable',
            'passed' => is_writable('../config')
        ],
        'writable_uploads' => [
            'required' => 'Writable',
            'current' => is_writable('../uploads') ? 'Writable' : 'Not Writable',
            'passed' => is_writable('../uploads')
        ],
        'writable_assets' => [
            'required' => 'Writable',
            'current' => is_writable('../assets') ? 'Writable' : 'Not Writable',
            'passed' => is_writable('../assets')
        ]
    ];
    
    $all_passed = true;
    foreach ($requirements as $req) {
        if (!$req['passed']) {
            $all_passed = false;
            break;
        }
    }
    
    return [
        'requirements' => $requirements,
        'all_passed' => $all_passed
    ];
}

function processDatabaseStep() {
    $host = $_POST['db_host'] ?? 'localhost';
    $port = $_POST['db_port'] ?? '3306';
    $name = $_POST['db_name'] ?? '';
    $user = $_POST['db_user'] ?? '';
    $pass = $_POST['db_pass'] ?? '';
    
    if (empty($name) || empty($user)) {
        return ['success' => false, 'error' => 'Database name and username are required'];
    }
    
    try {
        // Test database connection
        $dsn = "mysql:host=$host;port=$port;charset=utf8mb4";
        $pdo = new PDO($dsn, $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Create database if not exists
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `$name`");
        
        // Store database config in session
        $_SESSION['db_config'] = [
            'host' => $host,
            'port' => $port,
            'name' => $name,
            'user' => $user,
            'pass' => $pass
        ];
        
        return ['success' => true];
        
    } catch (PDOException $e) {
        return ['success' => false, 'error' => 'Database connection failed: ' . $e->getMessage()];
    }
}

function processAdminStep() {
    $username = $_POST['admin_username'] ?? '';
    $password = $_POST['admin_password'] ?? '';
    $email = $_POST['admin_email'] ?? '';
    $fullname = $_POST['admin_fullname'] ?? '';
    
    if (empty($username) || empty($password) || empty($email)) {
        return ['success' => false, 'error' => 'All fields are required'];
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'error' => 'Invalid email address'];
    }
    
    if (strlen($password) < 8) {
        return ['success' => false, 'error' => 'Password must be at least 8 characters'];
    }
    
    $_SESSION['admin_config'] = [
        'username' => $username,
        'password' => password_hash($password, PASSWORD_DEFAULT),
        'email' => $email,
        'fullname' => $fullname
    ];
    
    return ['success' => true];
}

function processSettingsStep() {
    $couple_name = $_POST['couple_name'] ?? '';
    $wedding_date = $_POST['wedding_date'] ?? '';
    $wedding_time = $_POST['wedding_time'] ?? '';
    $venue = $_POST['venue'] ?? '';
    $theme = $_POST['theme'] ?? 'royal-elegance';
    
    if (empty($couple_name) || empty($wedding_date) || empty($venue)) {
        return ['success' => false, 'error' => 'Required fields are missing'];
    }
    
    $_SESSION['wedding_config'] = [
        'couple_name' => $couple_name,
        'wedding_date' => $wedding_date,
        'wedding_time' => $wedding_time,
        'venue' => $venue,
        'theme' => $theme
    ];
    
    // Now install everything
    return installSystem();
}

function installSystem() {
    // Get config from session
    $db_config = $_SESSION['db_config'] ?? null;
    $admin_config = $_SESSION['admin_config'] ?? null;
    $wedding_config = $_SESSION['wedding_config'] ?? null;
    
    if (!$db_config || !$admin_config || !$wedding_config) {
        return ['success' => false, 'error' => 'Configuration missing'];
    }
    
    try {
        // Connect to database
        $dsn = "mysql:host={$db_config['host']};port={$db_config['port']};dbname={$db_config['name']};charset=utf8mb4";
        $pdo = new PDO($dsn, $db_config['user'], $db_config['pass']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Read and execute SQL file
        $sql_file = file_get_contents('database/schema.sql');
        $sql_statements = array_filter(array_map('trim', explode(';', $sql_file)));
        
        foreach ($sql_statements as $statement) {
            if (!empty($statement)) {
                $pdo->exec($statement);
            }
        }
        
        // Insert admin user
        $stmt = $pdo->prepare("INSERT INTO admins (username, password, email, full_name, role, is_active, created_at) 
                               VALUES (?, ?, ?, ?, 'superadmin', 1, NOW())");
        $stmt->execute([
            $admin_config['username'],
            $admin_config['password'],
            $admin_config['email'],
            $admin_config['fullname']
        ]);
        
        // Insert wedding data
        $stmt = $pdo->prepare("INSERT INTO weddings (couple_name, wedding_date, venue, title, theme, is_active, created_at) 
                               VALUES (?, ?, ?, ?, ?, 1, NOW())");
        $stmt->execute([
            $wedding_config['couple_name'],
            $wedding_config['wedding_date'] . ' ' . $wedding_config['wedding_time'],
            $wedding_config['venue'],
            'Pernikahan ' . $wedding_config['couple_name'],
            $wedding_config['theme']
        ]);
        
        // Insert default template settings
        $themes = ['royal-elegance', 'classic-romance', 'garden-bliss'];
        foreach ($themes as $theme) {
            $colors = getDefaultColors($theme);
            $fonts = getDefaultFonts($theme);
            
            $stmt = $pdo->prepare("INSERT INTO template_settings (template_name, colors, fonts, custom_css, is_active) 
                                   VALUES (?, ?, ?, '', 1)");
            $stmt->execute([
                $theme,
                json_encode($colors),
                json_encode($fonts)
            ]);
        }
        
        // Create config file
        $config_content = generateConfigFile($db_config);
        file_put_contents('../config/database.php', $config_content);
        
        // Create .htaccess if not exists
        if (!file_exists('../.htaccess')) {
            $htaccess = generateHtaccess();
            file_put_contents('../.htaccess', $htaccess);
        }
        
        // Create installed lock file
        file_put_contents('../config/installed.lock', date('Y-m-d H:i:s'));
        
        // Set file permissions
        chmod('../config/database.php', 0644);
        chmod('../uploads', 0755);
        chmod('../assets', 0755);
        
        return ['success' => true];
        
    } catch (PDOException $e) {
        return ['success' => false, 'error' => 'Installation failed: ' . $e->getMessage()];
    }
}

function getDefaultColors($theme) {
    $colors = [
        'royal-elegance' => [
            'primary' => '#8B4513',
            'secondary' => '#D4AF37',
            'accent' => '#C19A6B',
            'text_dark' => '#333333',
            'text_light' => '#FFFFFF',
            'bg_light' => '#F8F4EC',
            'bg_dark' => '#2C1810'
        ],
        'classic-romance' => [
            'primary' => '#C2185B',
            'secondary' => '#F8BBD0',
            'accent' => '#E91E63',
            'text_dark' => '#333333',
            'text_light' => '#FFFFFF',
            'bg_light' => '#FFF9FB',
            'bg_dark' => '#880E4F'
        ],
        'garden-bliss' => [
            'primary' => '#2E7D32',
            'secondary' => '#81C784',
            'accent' => '#4CAF50',
            'text_dark' => '#2E2E2E',
            'text_light' => '#FFFFFF',
            'bg_light' => '#F1F8E9',
            'bg_dark' => '#1B5E20'
        ]
    ];
    
    return $colors[$theme] ?? $colors['royal-elegance'];
}

function getDefaultFonts($theme) {
    $fonts = [
        'royal-elegance' => [
            'heading' => 'Playfair Display',
            'body' => 'Crimson Text'
        ],
        'classic-romance' => [
            'heading' => 'Great Vibes',
            'body' => 'Dancing Script'
        ],
        'garden-bliss' => [
            'heading' => 'Sacramento',
            'body' => 'Lora'
        ]
    ];
    
    return $fonts[$theme] ?? $fonts['royal-elegance'];
}

function generateConfigFile($db_config) {
    return '<?php
// Database Configuration
define("DB_HOST", "' . $db_config['host'] . '");
define("DB_PORT", "' . $db_config['port'] . '");
define("DB_NAME", "' . $db_config['name'] . '");
define("DB_USER", "' . $db_config['user'] . '");
define("DB_PASS", "' . $db_config['pass'] . '");

// Application Configuration
define("BASE_URL", "http://' . $_SERVER['HTTP_HOST'] . str_replace('/installer', '', dirname($_SERVER['PHP_SELF'])) . '");
define("SITE_NAME", "Undangan Digital");
define("TIMEZONE", "Asia/Jakarta");
define("DEBUG_MODE", false);

// Security
define("ENCRYPTION_KEY", "' . bin2hex(random_bytes(16)) . '");
define("JWT_SECRET", "' . bin2hex(random_bytes(32)) . '");

// File Uploads
define("MAX_UPLOAD_SIZE", 5242880); // 5MB
define("ALLOWED_IMAGE_TYPES", ["image/jpeg", "image/png", "image/gif", "image/webp"]);
define("UPLOAD_PATH", dirname(__DIR__) . "/uploads/");

// Session
ini_set("session.cookie_httponly", 1);
ini_set("session.use_only_cookies", 1);
ini_set("session.cookie_secure", isset($_SERVER["HTTPS"]));

// Error Reporting
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
} else {
    error_reporting(0);
    ini_set("display_errors", 0);
}

// Timezone
date_default_timezone_set(TIMEZONE);

// Create database connection
try {
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $conn = new PDO($dsn, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Helper function for base_url
function base_url($path = "") {
    return BASE_URL . "/" . ltrim($path, "/");
}
?>';
}

function generateHtaccess() {
    return '# Enable rewrite engine
RewriteEngine On

# Force HTTPS (uncomment if using SSL)
# RewriteCond %{HTTPS} off
# RewriteRule ^(.*)$ https://%{HTTP_HOST}/$1 [R=301,L]

# Remove index.php from URL
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php?/$1 [L,QSA]

# Security headers
Header set X-Content-Type-Options "nosniff"
Header set X-Frame-Options "SAMEORIGIN"
Header set X-XSS-Protection "1; mode=block"
Header set Referrer-Policy "strict-origin-when-cross-origin"

# CORS headers
Header set Access-Control-Allow-Origin "*"
Header set Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS"
Header set Access-Control-Allow-Headers "Content-Type, Authorization"

# Compression
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript application/json
</IfModule>

# Cache control
<FilesMatch "\.(ico|pdf|flv|jpg|jpeg|png|gif|js|css|swf)$">
    Header set Cache-Control "max-age=31536000, public"
</FilesMatch>

<FilesMatch "\.(html|htm|php)$">
    Header set Cache-Control "max-age=3600, private, must-revalidate"
</FilesMatch>

# Protect sensitive files
<FilesMatch "^(\.env|composer\.json|composer\.lock|package\.json|\.gitignore|README\.md|database\.sql)$">
    Order allow,deny
    Deny from all
</FilesMatch>

# Protect directories
RedirectMatch 403 ^/installer/?$
RedirectMatch 403 ^/config/?$
RedirectMatch 403 ^/includes/?$

# Custom error pages
ErrorDocument 404 /error/404.html
ErrorDocument 500 /error/500.html

# Prevent directory listing
Options -Indexes';
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installer - Undangan Digital</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .installer-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 1000px;
            overflow: hidden;
        }

        .installer-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .installer-header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        .installer-header p {
            opacity: 0.9;
            font-size: 1.1rem;
        }

        .installer-body {
            display: flex;
            min-height: 500px;
        }

        .sidebar {
            width: 250px;
            background: #f8f9fa;
            border-right: 1px solid #e9ecef;
            padding: 30px 0;
        }

        .step-list {
            list-style: none;
        }

        .step-item {
            padding: 15px 30px;
            display: flex;
            align-items: center;
            gap: 15px;
            cursor: default;
            border-left: 4px solid transparent;
            transition: all 0.3s ease;
        }

        .step-item.active {
            background: white;
            border-left-color: #667eea;
            color: #667eea;
            font-weight: 600;
        }

        .step-item.completed {
            color: #28a745;
        }

        .step-item i {
            font-size: 1.2rem;
            width: 24px;
        }

        .step-number {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 30px;
            height: 30px;
            background: #e9ecef;
            border-radius: 50%;
            font-weight: bold;
        }

        .step-item.active .step-number {
            background: #667eea;
            color: white;
        }

        .step-item.completed .step-number {
            background: #28a745;
            color: white;
        }

        .content-area {
            flex: 1;
            padding: 40px;
            overflow-y: auto;
        }

        .step-content {
            display: none;
        }

        .step-content.active {
            display: block;
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .step-title {
            font-size: 1.8rem;
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #555;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-success {
            background: #28a745;
            color: white;
        }

        .btn-success:hover {
            background: #218838;
        }

        .form-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #f0f0f0;
        }

        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-error {
            background: #fee;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }

        .alert-success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }

        .alert i {
            font-size: 1.2rem;
        }

        .requirements-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        .requirements-table th,
        .requirements-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }

        .requirements-table th {
            background: #f8f9fa;
            font-weight: 600;
        }

        .status-pass {
            color: #28a745;
            font-weight: 600;
        }

        .status-fail {
            color: #dc3545;
            font-weight: 600;
        }

        .welcome-content {
            text-align: center;
            max-width: 600px;
            margin: 0 auto;
        }

        .welcome-content i {
            font-size: 4rem;
            color: #667eea;
            margin-bottom: 20px;
        }

        .welcome-content h2 {
            color: #333;
            margin-bottom: 15px;
        }

        .welcome-content p {
            color: #666;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }

        .feature {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }

        .feature i {
            font-size: 2rem;
            color: #667eea;
            margin-bottom: 10px;
        }

        .finish-content {
            text-align: center;
            padding: 40px 0;
        }

        .finish-content i {
            font-size: 5rem;
            color: #28a745;
            margin-bottom: 20px;
            animation: bounce 1s infinite alternate;
        }

        @keyframes bounce {
            from { transform: translateY(0); }
            to { transform: translateY(-10px); }
        }

        .finish-content h2 {
            color: #333;
            margin-bottom: 15px;
        }

        .finish-content p {
            color: #666;
            margin-bottom: 30px;
        }

        .credentials {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            text-align: left;
            margin: 30px auto;
            max-width: 500px;
        }

        .credential-item {
            margin: 10px 0;
            display: flex;
            justify-content: space-between;
        }

        .credential-item strong {
            color: #333;
        }

        .credential-item span {
            color: #666;
            font-family: monospace;
        }

        @media (max-width: 768px) {
            .installer-body {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
                border-right: none;
                border-bottom: 1px solid #e9ecef;
            }
            
            .step-list {
                display: flex;
                overflow-x: auto;
                padding: 10px;
            }
            
            .step-item {
                flex-direction: column;
                text-align: center;
                min-width: 100px;
                padding: 10px;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .features {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="installer-container">
        <div class="installer-header">
            <h1><i class="fas fa-heart"></i> Undangan Digital</h1>
            <p>Premium Wedding Invitation System - Installer Wizard</p>
        </div>
        
        <div class="installer-body">
            <div class="sidebar">
                <ul class="step-list">
                    <?php foreach ($steps as $key => $label): ?>
                        <?php
                        $active = $key === $current_step ? 'active' : '';
                        $completed = array_search($key, array_keys($steps)) < array_search($current_step, array_keys($steps)) ? 'completed' : '';
                        ?>
                        <li class="step-item <?= $active ?> <?= $completed ?>">
                            <div class="step-number"><?= array_search($key, array_keys($steps)) + 1 ?></div>
                            <i class="fas fa-<?= getStepIcon($key) ?>"></i>
                            <span><?= $label ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            
            <div class="content-area">
                <?php if ($error): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <?= htmlspecialchars($success) ?>
                    </div>
                <?php endif; ?>
                
                <!-- Step 1: Welcome -->
                <div class="step-content <?= $current_step === 'welcome' ? 'active' : '' ?>">
                    <div class="welcome-content">
                        <i class="fas fa-heart"></i>
                        <h2>Selamat Datang di Installer</h2>
                        <p>Terima kasih telah memilih <strong>Undangan Digital</strong>. Sistem ini akan membantu Anda membuat undangan pernikahan digital yang profesional dengan fitur lengkap.</p>
                        
                        <div class="features">
                            <div class="feature">
                                <i class="fas fa-qrcode"></i>
                                <h4>QRIS Payment</h4>
                                <p>Sistem pembayaran digital lengkap</p>
                            </div>
                            <div class="feature">
                                <i class="fab fa-whatsapp"></i>
                                <h4>WhatsApp Integration</h4>
                                <p>Kirim undangan & konfirmasi otomatis</p>
                            </div>
                            <div class="feature">
                                <i class="fas fa-chart-line"></i>
                                <h4>Admin Dashboard</h4>
                                <p>Kelola tamu & analitik real-time</p>
                            </div>
                            <div class="feature">
                                <i class="fas fa-palette"></i>
                                <h4>Multiple Themes</h4>
                                <p>3 template premium siap pakai</p>
                            </div>
                        </div>
                        
                        <p>Instalasi akan memakan waktu sekitar 5 menit. Pastikan Anda memiliki informasi database dan detail pernikahan yang diperlukan.</p>
                        
                        <div class="form-actions">
                            <div></div>
                            <a href="?step=requirements" class="btn btn-primary">
                                Mulai Instalasi <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Step 2: Requirements -->
                <div class="step-content <?= $current_step === 'requirements' ? 'active' : '' ?>">
                    <?php $requirements = checkRequirements(); ?>
                    <h2 class="step-title">System Requirements Check</h2>
                    <p>Pastikan server Anda memenuhi semua persyaratan sistem berikut:</p>
                    
                    <table class="requirements-table">
                        <thead>
                            <tr>
                                <th>Requirement</th>
                                <th>Required</th>
                                <th>Current</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($requirements['requirements'] as $key => $req): ?>
                                <tr>
                                    <td><?= ucfirst(str_replace('_', ' ', $key)) ?></td>
                                    <td><?= $req['required'] ?></td>
                                    <td><?= $req['current'] ?></td>
                                    <td class="<?= $req['passed'] ? 'status-pass' : 'status-fail' ?>">
                                        <?= $req['passed'] ? '✓ PASS' : '✗ FAIL' ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <?php if (!$requirements['all_passed']): ?>
                        <div class="alert alert-error">
                            <i class="fas fa-exclamation-triangle"></i>
                            Beberapa persyaratan sistem tidak terpenuhi. Harap hubungi hosting provider Anda untuk mengatasi masalah ini sebelum melanjutkan.
                        </div>
                    <?php endif; ?>
                    
                    <div class="form-actions">
                        <a href="?step=welcome" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                        <a href="?step=database" class="btn btn-primary" <?= !$requirements['all_passed'] ? 'style="opacity:0.5; pointer-events:none;"' : '' ?>>
                            Lanjut <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
                
                <!-- Step 3: Database Setup -->
                <div class="step-content <?= $current_step === 'database' ? 'active' : '' ?>">
                    <h2 class="step-title">Database Configuration</h2>
                    <p>Masukkan informasi koneksi database MySQL Anda. Database akan dibuat otomatis jika belum ada.</p>
                    
                    <form method="POST">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="db_host">Database Host *</label>
                                <input type="text" id="db_host" name="db_host" class="form-control" value="localhost" required>
                                <small>Biasanya "localhost"</small>
                            </div>
                            <div class="form-group">
                                <label for="db_port">Database Port *</label>
                                <input type="text" id="db_port" name="db_port" class="form-control" value="3306" required>
                                <small>Default MySQL port: 3306</small>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="db_name">Database Name *</label>
                            <input type="text" id="db_name" name="db_name" class="form-control" required placeholder="undangan_digital">
                            <small>Nama database yang akan dibuat/digunakan</small>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="db_user">Database Username *</label>
                                <input type="text" id="db_user" name="db_user" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="db_pass">Database Password</label>
                                <input type="password" id="db_pass" name="db_pass" class="form-control">
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <a href="?step=requirements" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </a>
                            <button type="submit" class="btn btn-primary">
                                Test Connection & Lanjut <i class="fas fa-arrow-right"></i>
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Step 4: Admin Account -->
                <div class="step-content <?= $current_step === 'admin' ? 'active' : '' ?>">
                    <h2 class="step-title">Admin Account Setup</h2>
                    <p>Buat akun administrator untuk mengelola website undangan digital.</p>
                    
                    <form method="POST">
                        <div class="form-group">
                            <label for="admin_username">Username *</label>
                            <input type="text" id="admin_username" name="admin_username" class="form-control" required placeholder="admin">
                        </div>
                        
                        <div class="form-group">
                            <label for="admin_password">Password *</label>
                            <input type="password" id="admin_password" name="admin_password" class="form-control" required minlength="8">
                            <small>Minimal 8 karakter</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="admin_email">Email *</label>
                            <input type="email" id="admin_email" name="admin_email" class="form-control" required placeholder="admin@example.com">
                        </div>
                        
                        <div class="form-group">
                            <label for="admin_fullname">Nama Lengkap</label>
                            <input type="text" id="admin_fullname" name="admin_fullname" class="form-control" placeholder="Administrator">
                        </div>
                        
                        <div class="form-actions">
                            <a href="?step=database" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </a>
                            <button type="submit" class="btn btn-primary">
                                Simpan & Lanjut <i class="fas fa-arrow-right"></i>
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Step 5: Wedding Settings -->
                <div class="step-content <?= $current_step === 'settings' ? 'active' : '' ?>">
                    <h2 class="step-title">Wedding Information</h2>
                    <p>Masukkan informasi dasar pernikahan Anda. Ini akan ditampilkan di website undangan.</p>
                    
                    <form method="POST">
                        <div class="form-group">
                            <label for="couple_name">Nama Pasangan *</label>
                            <input type="text" id="couple_name" name="couple_name" class="form-control" required placeholder="John & Jane">
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="wedding_date">Tanggal Pernikahan *</label>
                                <input type="date" id="wedding_date" name="wedding_date" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="wedding_time">Waktu Acara</label>
                                <input type="time" id="wedding_time" name="wedding_time" class="form-control" value="14:00">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="venue">Lokasi Acara *</label>
                            <input type="text" id="venue" name="venue" class="form-control" required placeholder="Grand Ballroom Hotel Indonesia">
                        </div>
                        
                        <div class="form-group">
                            <label for="theme">Pilih Tema *</label>
                            <select id="theme" name="theme" class="form-control" required>
                                <option value="royal-elegance" selected>Royal Elegance (Elegant & Minimalist)</option>
                                <option value="classic-romance">Classic Romance (Romantic & Traditional)</option>
                                <option value="garden-bliss">Garden Bliss (Natural & Outdoor)</option>
                            </select>
                        </div>
                        
                        <div class="form-actions">
                            <a href="?step=admin" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </a>
                            <button type="submit" class="btn btn-primary">
                                Install System <i class="fas fa-cogs"></i>
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Step 6: Installation Complete -->
                <div class="step-content <?= $current_step === 'finish' ? 'active' : '' ?>">
                    <div class="finish-content">
                        <i class="fas fa-check-circle"></i>
                        <h2>Installation Complete!</h2>
                        <p>Sistem Undangan Digital berhasil diinstal. Website Anda siap digunakan.</p>
                        
                        <?php
                        $admin_config = $_SESSION['admin_config'] ?? null;
                        ?>
                        
                        <?php if ($admin_config): ?>
                            <div class="credentials">
                                <h3>Admin Login Details</h3>
                                <div class="credential-item">
                                    <strong>Admin URL:</strong>
                                    <span><?= htmlspecialchars(dirname($_SERVER['PHP_SELF'])) ?>/../admin/</span>
                                </div>
                                <div class="credential-item">
                                    <strong>Username:</strong>
                                    <span><?= htmlspecialchars($_POST['admin_username'] ?? 'admin') ?></span>
                                </div>
                                <div class="credential-item">
                                    <strong>Password:</strong>
                                    <span>********</span>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <div class="alert alert-success">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>PENTING:</strong> Hapus folder <code>installer</code> dari server Anda untuk keamanan!
                        </div>
                        
                        <div class="form-actions">
                            <a href="../" class="btn btn-success">
                                <i class="fas fa-eye"></i> Lihat Website
                            </a>
                            <a href="../admin/" class="btn btn-primary">
                                <i class="fas fa-cogs"></i> Buka Admin Panel
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Auto-fill wedding date with tomorrow's date
        document.addEventListener('DOMContentLoaded', function() {
            const weddingDateInput = document.getElementById('wedding_date');
            if (weddingDateInput) {
                const tomorrow = new Date();
                tomorrow.setDate(tomorrow.getDate() + 1);
                weddingDateInput.min = tomorrow.toISOString().split('T')[0];
                weddingDateInput.value = tomorrow.toISOString().split('T')[0];
            }
            
            // Password strength indicator
            const passwordInput = document.getElementById('admin_password');
            if (passwordInput) {
                passwordInput.addEventListener('input', function() {
                    const strength = checkPasswordStrength(this.value);
                    const indicator = this.parentElement.querySelector('.strength-indicator') || 
                                    document.createElement('div');
                    indicator.className = 'strength-indicator';
                    indicator.innerHTML = `Strength: <span class="strength-${strength.level}">${strength.text}</span>`;
                    
                    if (!this.parentElement.querySelector('.strength-indicator')) {
                        this.parentElement.appendChild(indicator);
                    }
                });
            }
        });
        
        function checkPasswordStrength(password) {
            let score = 0;
            
            // Length check
            if (password.length >= 8) score += 1;
            if (password.length >= 12) score += 1;
            
            // Complexity checks
            if (/[A-Z]/.test(password)) score += 1;
            if (/[a-z]/.test(password)) score += 1;
            if (/[0-9]/.test(password)) score += 1;
            if (/[^A-Za-z0-9]/.test(password)) score += 1;
            
            if (score >= 5) return { level: 'strong', text: 'Strong' };
            if (score >= 3) return { level: 'medium', text: 'Medium' };
            return { level: 'weak', text: 'Weak' };
        }
    </script>
</body>
</html>

<?php
function getStepIcon($step) {
    $icons = [
        'welcome' => 'home',
        'requirements' => 'check-circle',
        'database' => 'database',
        'admin' => 'user-shield',
        'settings' => 'heart',
        'finish' => 'check'
    ];
    return $icons[$step] ?? 'circle';
}
?>
