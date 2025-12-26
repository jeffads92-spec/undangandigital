<?php
/**
 * BACKUP CLASS
 * Sistem backup & restore database dan file
 */

class Backup {
    private $db;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->db = new Database();
    }
    
    /**
     * Create full backup
     */
    public function createBackup($type = 'full', $description = '') {
        $timestamp = date('Y-m-d_H-i-s');
        $filename = "backup_{$type}_{$timestamp}.zip";
        $backupPath = BACKUPS_PATH . $filename;
        
        // Create backups directory if not exists
        if (!is_dir(BACKUPS_PATH)) {
            mkdir(BACKUPS_PATH, 0755, true);
        }
        
        // Create ZIP archive
        $zip = new ZipArchive();
        
        if ($zip->open($backupPath, ZipArchive::CREATE) !== TRUE) {
            return ['success' => false, 'message' => 'Gagal membuat file backup'];
        }
        
        try {
            // Backup database
            if ($type == 'full' || $type == 'database') {
                $this->backupDatabase($zip);
            }
            
            // Backup files
            if ($type == 'full' || $type == 'files') {
                $this->backupFiles($zip);
            }
            
            // Backup configuration
            if ($type == 'full' || $type == 'config') {
                $this->backupConfig($zip);
            }
            
            // Add backup info
            $info = [
                'date' => date('Y-m-d H:i:s'),
                'type' => $type,
                'description' => $description,
                'version' => '2.0',
                'tables_backed_up' => $this->getTableCount(),
                'files_backed_up' => $zip->numFiles
            ];
            
            $zip->addFromString('backup_info.json', json_encode($info, JSON_PRETTY_PRINT));
            
            $zip->close();
            
            // Save backup record
            $backupId = $this->saveBackupRecord($filename, $type, $description, filesize($backupPath));
            
            return [
                'success' => true,
                'filename' => $filename,
                'path' => $backupPath,
                'size' => filesize($backupPath),
                'id' => $backupId,
                'info' => $info
            ];
            
        } catch (Exception $e) {
            if ($zip) {
                $zip->close();
            }
            
            if (file_exists($backupPath)) {
                unlink($backupPath);
            }
            
            return ['success' => false, 'message' => 'Backup gagal: ' . $e->getMessage()];
        }
    }
    
    /**
     * Backup database
     */
    private function backupDatabase($zip) {
        // Get all tables
        $tables = $this->db->fetchAll("SHOW TABLES");
        
        $sqlContent = "-- Wedding Digital Database Backup\n";
        $sqlContent .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
        $sqlContent .= "-- Database: " . DatabaseConfig::DB_NAME . "\n\n";
        
        foreach ($tables as $tableRow) {
            $tableName = reset($tableRow);
            
            // Skip backup logs table
            if ($tableName == 'backup_logs') {
                continue;
            }
            
            // Get table structure
            $createTable = $this->db->fetch("SHOW CREATE TABLE `{$tableName}`");
            $sqlContent .= "DROP TABLE IF EXISTS `{$tableName}`;\n\n";
            $sqlContent .= $createTable['Create Table'] . ";\n\n";
            
            // Get table data
            $rows = $this->db->fetchAll("SELECT * FROM `{$tableName}`");
            
            if (!empty($rows)) {
                $columns = array_keys($rows[0]);
                $columnList = '`' . implode('`, `', $columns) . '`';
                
                $sqlContent .= "INSERT INTO `{$tableName}` ({$columnList}) VALUES\n";
                
                $insertValues = [];
                foreach ($rows as $row) {
                    $values = [];
                    foreach ($row as $value) {
                        if (is_null($value)) {
                            $values[] = 'NULL';
                        } else {
                            $values[] = "'" . $this->db->escape($value) . "'";
                        }
                    }
                    $insertValues[] = "(" . implode(', ', $values) . ")";
                }
                
                $sqlContent .= implode(",\n", $insertValues) . ";\n\n";
            }
        }
        
        // Add to ZIP
        $zip->addFromString('database_backup.sql', $sqlContent);
    }
    
    /**
     * Backup files
     */
    private function backupFiles($zip) {
        // Backup configuration files
        $configFiles = [
            'config/database.php',
            'config/constants.php',
            'config/autoload.php',
            '.htaccess',
            'index.php'
        ];
        
        foreach ($configFiles as $file) {
            if (file_exists(ROOT_PATH . $file)) {
                $zip->addFile(ROOT_PATH . $file, $file);
            }
        }
        
        // Backup core classes
        $this->addDirectoryToZip($zip, 'core/', 'core/');
        
        // Backup templates (only structure, not assets)
        $this->addDirectoryToZip($zip, 'templates/', 'templates/', ['assets']);
        
        // Backup uploads (optional - could be large)
        $uploadsSize = $this->getDirectorySize(UPLOADS_PATH);
        if ($uploadsSize < 10485760) { // 10MB limit
            $this->addDirectoryToZip($zip, 'assets/uploads/', 'uploads/');
        }
    }
    
    /**
     * Backup configuration
     */
    private function backupConfig($zip) {
        $config = [
            'site' => [
                'name' => SITE_NAME,
                'url' => BASE_URL,
                'timezone' => date_default_timezone_get()
            ],
            'database' => [
                'host' => DatabaseConfig::DB_HOST,
                'name' => DatabaseConfig::DB_NAME,
                'user' => DatabaseConfig::DB_USER
            ],
            'settings' => $this->getAllSettings()
        ];
        
        $zip->addFromString('config.json', json_encode($config, JSON_PRETTY_PRINT));
    }
    
    /**
     * Add directory to ZIP
     */
    private function addDirectoryToZip($zip, $dirPath, $zipPath, $excludeDirs = []) {
        $dirPath = ROOT_PATH . $dirPath;
        
        if (!is_dir($dirPath)) {
            return;
        }
        
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dirPath),
            RecursiveIteratorIterator::LEAVES_ONLY
        );
        
        foreach ($files as $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen(ROOT_PATH));
                
                // Check if in excluded directory
                $exclude = false;
                foreach ($excludeDirs as $excludeDir) {
                    if (strpos($relativePath, $excludeDir) !== false) {
                        $exclude = true;
                        break;
                    }
                }
                
                if (!$exclude) {
                    $zip->addFile($filePath, $relativePath);
                }
            }
        }
    }
    
    /**
     * Restore from backup
     */
    public function restoreBackup($filename) {
        $backupPath = BACKUPS_PATH . $filename;
        
        if (!file_exists($backupPath)) {
            return ['success' => false, 'message' => 'File backup tidak ditemukan'];
        }
        
        // Extract backup
        $zip = new ZipArchive();
        
        if ($zip->open($backupPath) !== TRUE) {
            return ['success' => false, 'message' => 'Gagal membuka file backup'];
        }
        
        $this->db->beginTransaction();
        
        try {
            // Extract to temp directory
            $tempDir = sys_get_temp_dir() . '/wedding_restore_' . time();
            mkdir($tempDir, 0755, true);
            
            $zip->extractTo($tempDir);
            $zip->close();
            
            // Read backup info
            $infoFile = $tempDir . '/backup_info.json';
            if (file_exists($infoFile)) {
                $info = json_decode(file_get_contents($infoFile), true);
            }
            
            // Restore database if exists
            $sqlFile = $tempDir . '/database_backup.sql';
            if (file_exists($sqlFile)) {
                $this->restoreDatabase($sqlFile);
            }
            
            // Restore files if exists
            $this->restoreFiles($tempDir);
            
            $this->db->commit();
            
            // Clean up temp directory
            $this->deleteDirectory($tempDir);
            
            // Log restore
            $this->logRestore($filename, $info['type'] ?? 'unknown');
            
            return ['success' => true, 'message' => 'Backup berhasil direstore'];
            
        } catch (Exception $e) {
            $this->db->rollback();
            
            if (isset($tempDir) && is_dir($tempDir)) {
                $this->deleteDirectory($tempDir);
            }
            
            return ['success' => false, 'message' => 'Restore gagal: ' . $e->getMessage()];
        }
    }
    
    /**
     * Restore database
     */
    private function restoreDatabase($sqlFile) {
        $sql = file_get_contents($sqlFile);
        
        // Split SQL by semicolons
        $queries = explode(';', $sql);
        
        foreach ($queries as $query) {
            $query = trim($query);
            
            if (!empty($query)) {
                try {
                    $this->db->prepare($query)->execute();
                } catch (Exception $e) {
                    // Skip errors on DROP TABLE IF EXISTS
                    if (strpos($query, 'DROP TABLE IF EXISTS') === false) {
                        throw $e;
                    }
                }
            }
        }
    }
    
    /**
     * Restore files
     */
    private function restoreFiles($tempDir) {
        // Restore configuration files
        $configFiles = ['config/database.php', 'config/constants.php', '.htaccess'];
        
        foreach ($configFiles as $file) {
            $source = $tempDir . '/' . $file;
            $dest = ROOT_PATH . $file;
            
            if (file_exists($source) && file_exists(dirname($dest))) {
                copy($source, $dest);
            }
        }
    }
    
    /**
     * Save backup record
     */
    private function saveBackupRecord($filename, $type, $description, $size) {
        return $this->db->insert('backup_logs', [
            'filename' => $filename,
            'type' => $type,
            'description' => $description,
            'size' => $size,
            'status' => 'success',
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Log restore
     */
    private function logRestore($filename, $type) {
        $auth = new Auth();
        $user = $auth->getCurrentUser();
        
        $this->db->insert('restore_logs', [
            'filename' => $filename,
            'type' => $type,
            'restored_by' => $user ? $user['id'] : null,
            'restored_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Get all backups
     */
    public function getAllBackups() {
        return $this->db->fetchAll(
            "SELECT * FROM backup_logs 
             WHERE status = 'success' 
             ORDER BY created_at DESC"
        );
    }
    
    /**
     * Get backup info
     */
    public function getBackupInfo($filename) {
        $backupPath = BACKUPS_PATH . $filename;
        
        if (!file_exists($backupPath)) {
            return null;
        }
        
        $zip = new ZipArchive();
        
        if ($zip->open($backupPath) !== TRUE) {
            return null;
        }
        
        $info = [
            'filename' => $filename,
            'size' => filesize($backupPath),
            'created' => date('Y-m-d H:i:s', filemtime($backupPath)),
            'file_count' => $zip->numFiles
        ];
        
        // Read backup info
        $infoContent = $zip->getFromName('backup_info.json');
        if ($infoContent) {
            $backupInfo = json_decode($infoContent, true);
            $info = array_merge($info, $backupInfo);
        }
        
        $zip->close();
        
        return $info;
    }
    
    /**
     * Delete backup
     */
    public function deleteBackup($filename) {
        $backupPath = BACKUPS_PATH . $filename;
        
        if (!file_exists($backupPath)) {
            return ['success' => false, 'message' => 'File backup tidak ditemukan'];
        }
        
        if (unlink($backupPath)) {
            // Update database record
            $this->db->update('backup_logs', 
                ['status' => 'deleted', 'deleted_at' => date('Y-m-d H:i:s')],
                'filename = ?', [$filename]
            );
            
            return ['success' => true, 'message' => 'Backup berhasil dihapus'];
        }
        
        return ['success' => false, 'message' => 'Gagal menghapus file backup'];
    }
    
    /**
     * Auto backup scheduler
     */
    public function autoBackup() {
        // Check if auto backup is enabled
        $autoBackup = $this->db->fetch(
            "SELECT key_value FROM settings WHERE key_name = 'auto_backup'"
        );
        
        if (!$autoBackup || $autoBackup['key_value'] != '1') {
            return false;
        }
        
        // Check last backup time
        $lastBackup = $this->db->fetch(
            "SELECT created_at FROM backup_logs 
             WHERE type = 'auto' 
             ORDER BY created_at DESC LIMIT 1"
        );
        
        // Backup if never backed up or more than 24 hours ago
        if (!$lastBackup || strtotime($lastBackup['created_at']) < time() - 86400) {
            return $this->createBackup('auto', 'Auto backup ' . date('Y-m-d'));
        }
        
        return false;
    }
    
    /**
     * Get directory size
     */
    private function getDirectorySize($dir) {
        $size = 0;
        
        if (!is_dir($dir)) {
            return 0;
        }
        
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir),
            RecursiveIteratorIterator::LEAVES_ONLY
        );
        
        foreach ($files as $file) {
            if ($file->isFile()) {
                $size += $file->getSize();
            }
        }
        
        return $size;
    }
    
    /**
     * Delete directory recursively
     */
    private function deleteDirectory($dir) {
        if (!is_dir($dir)) {
            return;
        }
        
        $files = array_diff(scandir($dir), ['.', '..']);
        
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            
            if (is_dir($path)) {
                $this->deleteDirectory($path);
            } else {
                unlink($path);
            }
        }
        
        rmdir($dir);
    }
    
    /**
     * Get all settings
     */
    private function getAllSettings() {
        $settings = $this->db->fetchAll("SELECT key_name, key_value FROM settings");
        
        $result = [];
        foreach ($settings as $setting) {
            $result[$setting['key_name']] = $setting['key_value'];
        }
        
        return $result;
    }
    
    /**
     * Get table count
     */
    private function getTableCount() {
        return $this->db->fetchColumn("SELECT COUNT(*) FROM information_schema.tables 
                                      WHERE table_schema = ?", [DatabaseConfig::DB_NAME]);
    }
    
    /**
     * Database escape (for backup SQL)
     */
    private function dbEscape($value) {
        if (is_null($value)) {
            return 'NULL';
        }
        
        return str_replace(
            ["\\", "\0", "\n", "\r", "'", '"', "\x1a"],
            ["\\\\", "\\0", "\\n", "\\r", "\\'", '\\"', "\\Z"],
            $value
        );
    }
}
?>
