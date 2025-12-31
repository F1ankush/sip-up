<?php
require_once 'config.php';
require_once 'error_handler.php';

class Database {
    private $connection;
    private $isConnected = false;
    
    public function __construct() {
        try {
            // Set connection timeout to prevent hanging connections
            ini_set('mysqli.connect_timeout', 5);
            
            $this->connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            
            if ($this->connection->connect_error) {
                error_log("Database Connection Failed: " . $this->connection->connect_error);
                DatabaseErrorHandler::handleError("Database", "Connection Failed: " . $this->connection->connect_error);
            }
            
            // Set charset
            $this->connection->set_charset("utf8mb4");
            
            // Test connection
            if (!$this->connection->ping()) {
                error_log("Database Ping Failed");
                DatabaseErrorHandler::handleError("Database", "Ping failed");
            }
            
            $this->isConnected = true;
        } catch (Exception $e) {
            error_log("Database Exception: " . $e->getMessage());
            DatabaseErrorHandler::handleError("Database", "Exception: " . $e->getMessage());
        }
    }
    
    public function getConnection() {
        if (!$this->isConnected) {
            DatabaseErrorHandler::handleError("Database", "Connection lost");
        }
        return $this->connection;
    }
    
    public function query($sql) {
        return $this->connection->query($sql);
    }
    
    public function prepare($sql) {
        return $this->connection->prepare($sql);
    }
    
    public function escape($str) {
        return $this->connection->real_escape_string($str);
    }
    
    public function getLastId() {
        return $this->connection->insert_id;
    }
    
    public function affectedRows() {
        return $this->connection->affected_rows;
    }
    
    public function close() {
        $this->connection->close();
    }
}

// Create global database instance
$db = new Database();
?>
