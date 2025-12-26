<?php
/**
 * DATABASE CLASS
 * PDO Wrapper dengan method CRUD lengkap
 */

class Database {
    private $pdo;
    private $error;
    private $stmt;
    private $connected = false;
    
    /**
     * Constructor - Connect to database
     */
    public function __construct() {
        $config = require_once 'config/database.php';
        
        try {
            $dsn = "mysql:host=" . DatabaseConfig::DB_HOST . 
                   ";dbname=" . DatabaseConfig::DB_NAME . 
                   ";charset=utf8mb4";
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
                PDO::ATTR_PERSISTENT => true
            ];
            
            $this->pdo = new PDO($dsn, DatabaseConfig::DB_USER, DatabaseConfig::DB_PASS, $options);
            $this->connected = true;
            
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            $this->connected = false;
            
            // Log error
            error_log("Database Connection Failed: " . $this->error);
            
            // Show user-friendly message
            if (strpos($this->error, "Unknown database") !== false) {
                die("Database '" . DatabaseConfig::DB_NAME . "' tidak ditemukan. 
                     Silakan buat database terlebih dahulu atau jalankan installer.");
            } else {
                die("Koneksi database gagal. Error: " . htmlspecialchars($this->error));
            }
        }
    }
    
    /**
     * Prepare statement
     */
    public function prepare($sql) {
        try {
            $this->stmt = $this->pdo->prepare($sql);
            return $this;
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            throw new Exception("Prepare failed: " . $this->error);
        }
    }
    
    /**
     * Bind parameters
     */
    public function bind($param, $value, $type = null) {
        if (is_null($type)) {
            switch (true) {
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
            }
        }
        
        $this->stmt->bindValue($param, $value, $type);
        return $this;
    }
    
    /**
     * Execute statement
     */
    public function execute() {
        try {
            return $this->stmt->execute();
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            throw new Exception("Execute failed: " . $this->error);
        }
    }
    
    /**
     * Execute and fetch single row
     */
    public function fetch($sql = null, $params = []) {
        if ($sql) {
            $this->prepare($sql);
        }
        
        if (!empty($params)) {
            foreach ($params as $key => $value) {
                $this->bind($key + 1, $value);
            }
        }
        
        $this->execute();
        return $this->stmt->fetch();
    }
    
    /**
     * Execute and fetch all rows
     */
    public function fetchAll($sql = null, $params = []) {
        if ($sql) {
            $this->prepare($sql);
        }
        
        if (!empty($params)) {
            foreach ($params as $key => $value) {
                $this->bind($key + 1, $value);
            }
        }
        
        $this->execute();
        return $this->stmt->fetchAll();
    }
    
    /**
     * Execute and fetch column
     */
    public function fetchColumn($sql = null, $params = [], $column = 0) {
        if ($sql) {
            $this->prepare($sql);
        }
        
        if (!empty($params)) {
            foreach ($params as $key => $value) {
                $this->bind($key + 1, $value);
            }
        }
        
        $this->execute();
        return $this->stmt->fetchColumn($column);
    }
    
    /**
     * Insert data
     */
    public function insert($table, $data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        $this->prepare($sql);
        
        foreach ($data as $key => $value) {
            $this->bind(':' . $key, $value);
        }
        
        $this->execute();
        return $this->pdo->lastInsertId();
    }
    
    /**
     * Update data
     */
    public function update($table, $data, $where, $whereParams = []) {
        $setParts = [];
        foreach ($data as $key => $value) {
            $setParts[] = "{$key} = :{$key}";
        }
        $setClause = implode(', ', $setParts);
        
        $sql = "UPDATE {$table} SET {$setClause} WHERE {$where}";
        $this->prepare($sql);
        
        // Bind data values
        foreach ($data as $key => $value) {
            $this->bind(':' . $key, $value);
        }
        
        // Bind where parameters
        foreach ($whereParams as $key => $value) {
            $this->bind(':' . $key, $value);
        }
        
        return $this->execute();
    }
    
    /**
     * Delete data
     */
    public function delete($table, $where, $params = []) {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        $this->prepare($sql);
        
        foreach ($params as $key => $value) {
            $this->bind(':' . $key, $value);
        }
        
        return $this->execute();
    }
    
    /**
     * Begin transaction
     */
    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }
    
    /**
     * Commit transaction
     */
    public function commit() {
        return $this->pdo->commit();
    }
    
    /**
     * Rollback transaction
     */
    public function rollback() {
        return $this->pdo->rollBack();
    }
    
    /**
     * Get row count
     */
    public function rowCount() {
        return $this->stmt->rowCount();
    }
    
    /**
     * Get last insert ID
     */
    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }
    
    /**
     * Check if table exists
     */
    public function tableExists($table) {
        try {
            $result = $this->fetch("SHOW TABLES LIKE ?", [$table]);
            return !empty($result);
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Get table columns
     */
    public function getTableColumns($table) {
        $sql = "SHOW COLUMNS FROM {$table}";
        $columns = $this->fetchAll($sql);
        
        $result = [];
        foreach ($columns as $column) {
            $result[] = $column['Field'];
        }
        
        return $result;
    }
    
    /**
     * Backup database
     */
    public function backup($filename = null) {
        if (!$filename) {
            $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
        }
        
        $backupPath = BACKUPS_PATH . $filename;
        
        // Get all tables
        $tables = $this->fetchAll("SHOW TABLES");
        
        $backupContent = "-- Wedding Digital Database Backup\n";
        $backupContent .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
        $backupContent .= "-- Database: " . DatabaseConfig::DB_NAME . "\n\n";
        
        foreach ($tables as $tableRow) {
            $tableName = reset($tableRow);
            
            // Drop table if exists
            $backupContent .= "DROP TABLE IF EXISTS `{$tableName}`;\n\n";
            
            // Create table structure
            $createTable = $this->fetch("SHOW CREATE TABLE `{$tableName}`");
            $backupContent .= $createTable['Create Table'] . ";\n\n";
            
            // Get table data
            $rows = $this->fetchAll("SELECT * FROM `{$tableName}`");
            
            if (!empty($rows)) {
                $columns = array_keys($rows[0]);
                $columnList = '`' . implode('`, `', $columns) . '`';
                
                $backupContent .= "INSERT INTO `{$tableName}` ({$columnList}) VALUES\n";
                
                $insertValues = [];
                foreach ($rows as $row) {
                    $values = [];
                    foreach ($row as $value) {
                        if (is_null($value)) {
                            $values[] = 'NULL';
                        } else {
                            $values[] = "'" . addslashes($value) . "'";
                        }
                    }
                    $insertValues[] = "(" . implode(', ', $values) . ")";
                }
                
                $backupContent .= implode(",\n", $insertValues) . ";\n\n";
            }
        }
        
        // Save to file
        if (!is_dir(BACKUPS_PATH)) {
            mkdir(BACKUPS_PATH, 0755, true);
        }
        
        file_put_contents($backupPath, $backupContent);
        
        // Log backup
        $this->insert('backup_logs', [
            'filename' => $filename,
            'size' => filesize($backupPath),
            'status' => 'success',
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        return $backupPath;
    }
    
    /**
     * Get error message
     */
    public function getError() {
        return $this->error;
    }
    
    /**
     * Check if connected
     */
    public function isConnected() {
        return $this->connected;
    }
    
    /**
     * Close connection
     */
    public function close() {
        $this->pdo = null;
        $this->connected = false;
    }
    
    /**
     * Destructor
     */
    public function __destruct() {
        $this->close();
    }
}
?>
