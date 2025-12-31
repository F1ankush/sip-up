<?php
/**
 * Database Optimization Script - Run periodically
 * Creates indexes, analyzes tables, optimizes structure
 */

// This script should be run via cron: php database_optimize.php

require_once 'includes/config.php';

class DatabaseOptimizer {
    private $db;

    public function __construct() {
        $this->db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($this->db->connect_error) {
            die("Connection failed: " . $this->db->connect_error);
        }
    }

    /**
     * Create all necessary indexes
     */
    public function createIndexes() {
        echo "[*] Creating indexes...\n";

        $indexes = array(
            // Users table
            "ALTER TABLE users ADD INDEX idx_email (email)",
            "ALTER TABLE users ADD INDEX idx_phone (phone)",
            "ALTER TABLE users ADD INDEX idx_status (status)",
            "ALTER TABLE users ADD INDEX idx_created_at (created_at)",

            // Products table
            "ALTER TABLE products ADD INDEX idx_category (category)",
            "ALTER TABLE products ADD INDEX idx_status (status)",
            "ALTER TABLE products ADD INDEX idx_price (price)",
            "ALTER TABLE products ADD INDEX idx_created_at (created_at)",

            // Orders table
            "ALTER TABLE orders ADD INDEX idx_user_id (user_id)",
            "ALTER TABLE orders ADD INDEX idx_status (status)",
            "ALTER TABLE orders ADD INDEX idx_created_at (created_at)",
            "ALTER TABLE orders ADD INDEX idx_user_created (user_id, created_at)",

            // Payments table
            "ALTER TABLE payments ADD INDEX idx_user_id (user_id)",
            "ALTER TABLE payments ADD INDEX idx_status (status)",
            "ALTER TABLE payments ADD INDEX idx_payment_method (payment_method)",
            "ALTER TABLE payments ADD INDEX idx_created_at (created_at)",

            // Bills table
            "ALTER TABLE bills ADD INDEX idx_user_id (user_id)",
            "ALTER TABLE bills ADD INDEX idx_order_id (order_id)",
            "ALTER TABLE bills ADD INDEX idx_status (status)",
            "ALTER TABLE bills ADD INDEX idx_due_date (due_date)",

            // Contact messages
            "ALTER TABLE contact_messages ADD INDEX idx_status (status)",
            "ALTER TABLE contact_messages ADD INDEX idx_email (email)",
            "ALTER TABLE contact_messages ADD INDEX idx_created_at (created_at)",

            // Applications
            "ALTER TABLE retailer_applications ADD INDEX idx_status (status)",
            "ALTER TABLE retailer_applications ADD INDEX idx_company_name (company_name)",
            "ALTER TABLE retailer_applications ADD INDEX idx_created_at (created_at)"
        );

        foreach ($indexes as $index) {
            try {
                $this->db->query($index);
                echo "  [✓] " . substr($index, 0, 50) . "...\n";
            } catch (Exception $e) {
                echo "  [!] Failed: " . substr($index, 0, 50) . "...\n";
            }
        }
    }

    /**
     * Analyze all tables for optimization
     */
    public function analyzeTables() {
        echo "\n[*] Analyzing tables...\n";

        $result = $this->db->query("SHOW TABLES");
        
        while ($row = $result->fetch_row()) {
            $table = $row[0];
            $this->db->query("ANALYZE TABLE $table");
            echo "  [✓] Analyzed: $table\n";
        }
    }

    /**
     * Optimize all tables
     */
    public function optimizeTables() {
        echo "\n[*] Optimizing tables...\n";

        $result = $this->db->query("SHOW TABLES");
        
        while ($row = $result->fetch_row()) {
            $table = $row[0];
            $this->db->query("OPTIMIZE TABLE $table");
            echo "  [✓] Optimized: $table\n";
        }
    }

    /**
     * Create partitions for large tables (for 10K+ users)
     */
    public function createPartitions() {
        echo "\n[*] Creating table partitions...\n";

        // Partition orders by month
        $partitionOrders = "
            ALTER TABLE orders
            PARTITION BY RANGE(YEAR(created_at) * 100 + MONTH(created_at)) (
                PARTITION p202401 VALUES LESS THAN (202402),
                PARTITION p202402 VALUES LESS THAN (202403),
                PARTITION p202403 VALUES LESS THAN (202404),
                PARTITION p202404 VALUES LESS THAN (202405),
                PARTITION p202405 VALUES LESS THAN (202406),
                PARTITION p202406 VALUES LESS THAN (202407),
                PARTITION p202407 VALUES LESS THAN (202408),
                PARTITION p202408 VALUES LESS THAN (202409),
                PARTITION p202409 VALUES LESS THAN (202410),
                PARTITION p202410 VALUES LESS THAN (202411),
                PARTITION p202411 VALUES LESS THAN (202412),
                PARTITION p202412 VALUES LESS THAN (202501),
                PARTITION pmax VALUES LESS THAN MAXVALUE
            )
        ";

        try {
            $this->db->query($partitionOrders);
            echo "  [✓] Partitioned orders table by month\n";
        } catch (Exception $e) {
            echo "  [!] Partition creation failed: " . $e->getMessage() . "\n";
        }
    }

    /**
     * Update table statistics
     */
    public function updateStatistics() {
        echo "\n[*] Updating statistics...\n";

        $result = $this->db->query("SHOW TABLES");
        
        while ($row = $result->fetch_row()) {
            $table = $row[0];
            
            // Get row count
            $countResult = $this->db->query("SELECT COUNT(*) as cnt FROM $table");
            $countRow = $countResult->fetch_assoc();
            $count = $countRow['cnt'];
            
            echo "  [✓] $table: $count rows\n";
        }
    }

    /**
     * Set up auto-increment for better distribution
     */
    public function configureAutoIncrement() {
        echo "\n[*] Configuring auto-increment...\n";

        $tables = array('users', 'products', 'orders', 'payments', 'bills');

        foreach ($tables as $table) {
            try {
                $result = $this->db->query("SELECT AUTO_INCREMENT FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = '$table'");
                $row = $result->fetch_assoc();
                
                if ($row) {
                    echo "  [✓] $table: AUTO_INCREMENT = " . $row['AUTO_INCREMENT'] . "\n";
                }
            } catch (Exception $e) {
                echo "  [!] Failed to check $table\n";
            }
        }
    }

    /**
     * Generate optimization report
     */
    public function generateReport() {
        echo "\n[*] Database Optimization Report\n";
        echo "==================================\n";

        $result = $this->db->query("
            SELECT 
                ROUND(SUM(data_length) / 1024 / 1024, 2) as size_mb,
                COUNT(*) as table_count
            FROM information_schema.TABLES
            WHERE table_schema = '" . DB_NAME . "'
        ");

        $row = $result->fetch_assoc();
        echo "Database Size: " . $row['size_mb'] . " MB\n";
        echo "Table Count: " . $row['table_count'] . "\n";

        echo "\nTable Sizes:\n";
        $result = $this->db->query("
            SELECT 
                TABLE_NAME,
                ROUND(((data_length + index_length) / 1024 / 1024), 2) as size_mb,
                TABLE_ROWS as row_count
            FROM information_schema.TABLES
            WHERE table_schema = '" . DB_NAME . "'
            ORDER BY size_mb DESC
        ");

        while ($row = $result->fetch_assoc()) {
            echo "  " . str_pad($row['TABLE_NAME'], 30) . 
                 " Size: " . str_pad($row['size_mb'] . " MB", 10) . 
                 " Rows: " . $row['row_count'] . "\n";
        }
    }

    /**
     * Run all optimizations
     */
    public function runAll() {
        echo "Starting Database Optimization...\n\n";
        
        $this->updateStatistics();
        $this->createIndexes();
        $this->analyzeTables();
        $this->optimizeTables();
        $this->configureAutoIncrement();
        $this->generateReport();

        echo "\n[✓] Database optimization completed!\n";
        echo "Recommendation: Run this script weekly via cron job\n";
        echo "Cron command: 0 2 * * 0 php /path/to/database_optimize.php\n";
    }

    public function __destruct() {
        $this->db->close();
    }
}

// Run if executed directly
if (php_sapi_name() === 'cli') {
    $optimizer = new DatabaseOptimizer();
    $optimizer->runAll();
}
?>
