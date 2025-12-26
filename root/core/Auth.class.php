<?php
/**
 * AUTHENTICATION CLASS
 * Sistem login, session management, dan keamanan
 */

class Auth {
    private $db;
    private $session_timeout = 3600; // 1 hour
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->db = new Database();
        
        // Start session if not started
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        // Check session timeout
        $this->checkSessionTimeout();
    }
    
    /**
     * Login user
     */
    public function login($username, $password, $remember = false) {
        // Clean input
        $username = trim($username);
        $password = trim($password);
        
        // Validate
        if (empty($username) || empty($password)) {
            return ['success' => false, 'message' => 'Username dan password harus diisi'];
        }
        
        // Check brute force protection
        if ($this->isBruteForce($username)) {
            return ['success' => false, 'message' => 'Terlalu banyak percobaan login. Coba lagi nanti.'];
        }
        
        // Get user from database
        $user = $this->db->fetch(
            "SELECT * FROM admin_users WHERE username = ? AND is_active = 1",
            [$username]
        );
        
        if (!$user) {
            $this->logFailedAttempt($username);
            return ['success' => false, 'message' => 'Username atau password salah'];
        }
        
        // Verify password
        if (!password_verify($password, $user['password_hash'])) {
            $this->logFailedAttempt($username);
            return ['success' => false, 'message' => 'Username atau password salah'];
        }
        
        // Update last login
        $this->db->update('admin_users', [
            'last_login' => date('Y-m-d H:i:s'),
            'login_ip' => $_SERVER['REMOTE_ADDR']
        ], 'id = ?', [$user['id']]);
        
        // Set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['login_time'] = time();
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        
        // Set remember me cookie
        if ($remember) {
            $token = bin2hex(random_bytes(32));
            $expiry = time() + (30 * 24 * 60 * 60); // 30 days
            
            setcookie('remember_token', $token, $expiry, '/');
            
            // Save token in database
            $this->db->insert('remember_tokens', [
                'user_id' => $user['id'],
                'token' => password_hash($token, PASSWORD_DEFAULT),
                'expires_at' => date('Y-m-d H:i:s', $expiry),
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }
        
        // Log activity
        $this->logActivity($user['id'], 'login', 'User logged in');
        
        return ['success' => true, 'user' => $user];
    }
    
    /**
     * Check if user is logged in
     */
    public function isLoggedIn() {
        if (isset($_SESSION['user_id']) && isset($_SESSION['login_time'])) {
            // Check session timeout
            if (time() - $_SESSION['login_time'] > $this->session_timeout) {
                $this->logout();
                return false;
            }
            
            // Update session time
            $_SESSION['login_time'] = time();
            return true;
        }
        
        // Check remember me cookie
        if (isset($_COOKIE['remember_token'])) {
            return $this->checkRememberToken($_COOKIE['remember_token']);
        }
        
        return false;
    }
    
    /**
     * Check remember token
     */
    private function checkRememberToken($token) {
        $tokens = $this->db->fetchAll(
            "SELECT * FROM remember_tokens WHERE expires_at > NOW()"
        );
        
        foreach ($tokens as $storedToken) {
            if (password_verify($token, $storedToken['token'])) {
                // Get user
                $user = $this->db->fetch(
                    "SELECT * FROM admin_users WHERE id = ? AND is_active = 1",
                    [$storedToken['user_id']]
                );
                
                if ($user) {
                    // Set session
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['full_name'] = $user['full_name'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['login_time'] = time();
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                    
                    return true;
                }
            }
        }
        
        // Invalid token, clear cookie
        setcookie('remember_token', '', time() - 3600, '/');
        return false;
    }
    
    /**
     * Logout user
     */
    public function logout() {
        // Remove remember token
        if (isset($_COOKIE['remember_token'])) {
            // Delete from database
            $this->db->delete('remember_tokens', 'token = ?', [
                password_hash($_COOKIE['remember_token'], PASSWORD_DEFAULT)
            ]);
            
            // Clear cookie
            setcookie('remember_token', '', time() - 3600, '/');
        }
        
        // Clear session
        $_SESSION = [];
        
        // Destroy session
        if (session_status() == PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        
        return true;
    }
    
    /**
     * Get current user
     */
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        return $this->db->fetch(
            "SELECT * FROM admin_users WHERE id = ?",
            [$_SESSION['user_id']]
        );
    }
    
    /**
     * Check if user has permission
     */
    public function hasPermission($requiredRole) {
        if (!$this->isLoggedIn()) {
            return false;
        }
        
        $userRole = $_SESSION['role'];
        
        $roleHierarchy = [
            'superadmin' => 3,
            'admin' => 2,
            'editor' => 1
        ];
        
        if (!isset($roleHierarchy[$userRole]) || !isset($roleHierarchy[$requiredRole])) {
            return false;
        }
        
        return $roleHierarchy[$userRole] >= $roleHierarchy[$requiredRole];
    }
    
    /**
     * Change password
     */
    public function changePassword($userId, $currentPassword, $newPassword) {
        // Get user
        $user = $this->db->fetch(
            "SELECT * FROM admin_users WHERE id = ?",
            [$userId]
        );
        
        if (!$user) {
            return ['success' => false, 'message' => 'User tidak ditemukan'];
        }
        
        // Verify current password
        if (!password_verify($currentPassword, $user['password_hash'])) {
            return ['success' => false, 'message' => 'Password saat ini salah'];
        }
        
        // Validate new password
        if (strlen($newPassword) < 6) {
            return ['success' => false, 'message' => 'Password minimal 6 karakter'];
        }
        
        // Update password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $this->db->update('admin_users', [
            'password_hash' => $hashedPassword,
            'updated_at' => date('Y-m-d H:i:s')
        ], 'id = ?', [$userId]);
        
        // Log activity
        $this->logActivity($userId, 'change_password', 'Password changed');
        
        return ['success' => true, 'message' => 'Password berhasil diubah'];
    }
    
    /**
     * Create new user
     */
    public function createUser($data) {
        // Validate
        if (empty($data['username']) || empty($data['password']) || empty($data['full_name'])) {
            return ['success' => false, 'message' => 'Data tidak lengkap'];
        }
        
        // Check if username exists
        $existing = $this->db->fetch(
            "SELECT id FROM admin_users WHERE username = ?",
            [$data['username']]
        );
        
        if ($existing) {
            return ['success' => false, 'message' => 'Username sudah digunakan'];
        }
        
        // Hash password
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        
        // Insert user
        $userId = $this->db->insert('admin_users', [
            'username' => $data['username'],
            'password_hash' => $hashedPassword,
            'full_name' => $data['full_name'],
            'email' => $data['email'] ?? '',
            'role' => $data['role'] ?? 'admin',
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        // Log activity
        $currentUser = $this->getCurrentUser();
        $this->logActivity($currentUser['id'], 'create_user', "Created user: {$data['username']}");
        
        return ['success' => true, 'user_id' => $userId];
    }
    
    /**
     * Check brute force attack
     */
    private function isBruteForce($username) {
        $time = time() - (15 * 60); // 15 minutes ago
        
        $attempts = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM login_attempts 
             WHERE username = ? AND attempt_time > ?",
            [$username, $time]
        );
        
        return $attempts >= 5; // 5 attempts in 15 minutes
    }
    
    /**
     * Log failed login attempt
     */
    private function logFailedAttempt($username) {
        $this->db->insert('login_attempts', [
            'username' => $username,
            'ip_address' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'attempt_time' => time()
        ]);
    }
    
    /**
     * Log activity
     */
    public function logActivity($userId, $action, $details = '') {
        return $this->db->insert('activities', [
            'admin_id' => $userId,
            'action' => $action,
            'details' => $details,
            'ip_address' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Check session timeout
     */
    private function checkSessionTimeout() {
        if (isset($_SESSION['login_time'])) {
            $inactive = time() - $_SESSION['login_time'];
            
            if ($inactive > $this->session_timeout) {
                $this->logout();
                header('Location: ' . BASE_URL . 'admin/login.php?timeout=1');
                exit;
            }
        }
    }
    
    /**
     * Validate CSRF token
     */
    public function validateCSRF($token) {
        if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Get CSRF token
     */
    public function getCSRFToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Generate password reset token
     */
    public function generateResetToken($email) {
        // Get user by email
        $user = $this->db->fetch(
            "SELECT id FROM admin_users WHERE email = ? AND is_active = 1",
            [$email]
        );
        
        if (!$user) {
            return ['success' => false, 'message' => 'Email tidak ditemukan'];
        }
        
        // Generate token
        $token = bin2hex(random_bytes(32));
        $expires = time() + (60 * 60); // 1 hour
        
        // Save token
        $this->db->insert('password_resets', [
            'user_id' => $user['id'],
            'token' => password_hash($token, PASSWORD_DEFAULT),
            'expires_at' => date('Y-m-d H:i:s', $expires),
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        return ['success' => true, 'token' => $token, 'user_id' => $user['id']];
    }
    
    /**
     * Reset password with token
     */
    public function resetPassword($token, $newPassword) {
        // Get valid token
        $reset = $this->db->fetch(
            "SELECT * FROM password_resets 
             WHERE expires_at > NOW() 
             ORDER BY created_at DESC LIMIT 1"
        );
        
        if (!$reset) {
            return ['success' => false, 'message' => 'Token tidak valid atau sudah kadaluarsa'];
        }
        
        // Verify token
        if (!password_verify($token, $reset['token'])) {
            return ['success' => false, 'message' => 'Token tidak valid'];
        }
        
        // Update password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $this->db->update('admin_users', [
            'password_hash' => $hashedPassword,
            'updated_at' => date('Y-m-d H:i:s')
        ], 'id = ?', [$reset['user_id']]);
        
        // Delete used token
        $this->db->delete('password_resets', 'id = ?', [$reset['id']]);
        
        // Log activity
        $this->logActivity($reset['user_id'], 'password_reset', 'Password reset via token');
        
        return ['success' => true, 'message' => 'Password berhasil direset'];
    }
}
?>
