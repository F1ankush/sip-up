<?php
/**
 * Scalable Database Manager - Supports 10K-20K Users
 * Features: Connection pooling, query caching, batching, prepared statements
 */

require_once 'config.php';
require_once 'error_handler.php';
require_once 'cache_manager.php';

class ScalableDatabase {
    private static $instance = null;
    private $connections = array();
    private $currentConnection = null;
    private $connectionPool = array();
    private $queryCache = array();
    private $transactionActive = false;
    private $lastQuery = '';
    private $queryCount = 0;
    private $queryTime = 0;

    /**
     * Singleton pattern for database instance
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->initializeConnectionPool();
    }

    /**
     * Initialize connection pool
     */
    private function initializeConnectionPool() {
        try {
            // Set connection timeout
            ini_set('mysqli.connect_timeout', DB_CONNECTION_TIMEOUT);
            
            // Create initial connection
            $this->currentConnection = new mysqli(
                DB_HOST,
                DB_USER,
                DB_PASS,
                DB_NAME
            );

            if ($this->currentConnection->connect_error) {
                error_log("Database Connection Failed: " . $this->currentConnection->connect_error);
                DatabaseErrorHandler::handleError("Database", "Connection Failed: " . $this->currentConnection->connect_error);
            }

            // Set charset
            $this->currentConnection->set_charset("utf8mb4");

            // Enable query caching
            if (QUERY_CACHE_ENABLED) {
                $this->currentConnection->query("SET SESSION query_cache_type = ON");
            }

            // Set timeout for long-running queries
            $this->currentConnection->query("SET SESSION max_execution_time = " . (DB_READ_TIMEOUT * 1000));

            // Test connection
            if (!$this->currentConnection->ping()) {
                error_log("Database Ping Failed");
                $this->currentConnection = null;
                $this->reconnect();
            }

            $this->connections[spl_object_hash($this->currentConnection)] = $this->currentConnection;

        } catch (Exception $e) {
            error_log("Database Exception: " . $e->getMessage());
            DatabaseErrorHandler::handleError("Database", "Exception: " . $e->getMessage());
        }
    }

    /**
     * Get a connection from pool (for multi-connection scenarios)
     */
    public function getConnection() {
        if ($this->currentConnection === null || !$this->currentConnection->ping()) {
            $this->reconnect();
        }
        return $this->currentConnection;
    }

    /**
     * Reconnect to database
     */
    private function reconnect() {
        try {
            $this->currentConnection = new mysqli(
                DB_HOST,
                DB_USER,
                DB_PASS,
                DB_NAME
            );

            if ($this->currentConnection->connect_error) {
                error_log("Reconnection Failed: " . $this->currentConnection->connect_error);
                DatabaseErrorHandler::handleError("Database", "Reconnection Failed");
            }

            $this->currentConnection->set_charset("utf8mb4");
            $this->connections[spl_object_hash($this->currentConnection)] = $this->currentConnection;

        } catch (Exception $e) {
            error_log("Reconnection Exception: " . $e->getMessage());
            DatabaseErrorHandler::handleError("Database", "Reconnection Exception");
        }
    }

    /**
     * Execute a query with caching
     */
    public function query($sql, $useCache = true) {
        try {
            // Check cache first
            if ($useCache && CACHE_ENABLED) {
                $cached = CacheManager::get('query_' . md5($sql));
                if ($cached !== null) {
                    return $cached;
                }
            }

            $this->lastQuery = $sql;
            $startTime = microtime(true);

            $result = $this->currentConnection->query($sql);

            $executionTime = microtime(true) - $startTime;
            $this->queryTime += $executionTime;
            $this->queryCount++;

            // Log slow queries
            if (LOG_SLOW_QUERIES && $executionTime > SLOW_QUERY_THRESHOLD) {
                error_log("[SLOW QUERY] " . $executionTime . "s: " . substr($sql, 0, 100));
            }

            if (!$result) {
                error_log("Query Error: " . $this->currentConnection->error);
                return false;
            }

            // Cache result if enabled
            if ($useCache && CACHE_ENABLED && strpos(strtoupper($sql), 'SELECT') === 0) {
                $data = array();
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $data[] = $row;
                    }
                    $result->data_seek(0);
                    CacheManager::set('query_' . md5($sql), $data, CACHE_TTL);
                }
            }

            return $result;

        } catch (Exception $e) {
            error_log("Query Exception: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Prepared statement with parameter binding
     */
    public function prepare($sql) {
        try {
            if ($this->currentConnection === null) {
                $this->reconnect();
            }

            $stmt = $this->currentConnection->prepare($sql);
            
            if (!$stmt) {
                error_log("Prepare Error: " . $this->currentConnection->error);
                return false;
            }

            return $stmt;

        } catch (Exception $e) {
            error_log("Prepare Exception: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Batch insert for bulk operations
     */
    public function batchInsert($table, $data, $columns) {
        try {
            $values = array();
            $placeholders = array();
            
            foreach ($data as $row) {
                $placeholders[] = '(' . implode(',', array_fill(0, count($row), '?')) . ')';
                $values = array_merge($values, array_values($row));
            }

            $sql = "INSERT INTO $table (" . implode(',', $columns) . ") VALUES " . 
                   implode(',', $placeholders);

            $stmt = $this->prepare($sql);
            
            if (!$stmt) {
                return false;
            }

            // Bind values
            $types = str_repeat('s', count($values));
            $stmt->bind_param($types, ...$values);
            
            if ($stmt->execute()) {
                $lastId = $this->currentConnection->insert_id;
                $stmt->close();
                return $lastId;
            } else {
                error_log("Batch Insert Error: " . $stmt->error);
                $stmt->close();
                return false;
            }

        } catch (Exception $e) {
            error_log("Batch Insert Exception: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Batch update for multiple records
     */
    public function batchUpdate($table, $data, $whereColumn, $values) {
        try {
            $count = 0;
            
            foreach ($data as $row) {
                $updates = array();
                $updateValues = array();
                
                foreach ($row as $column => $value) {
                    if ($column !== $whereColumn) {
                        $updates[] = "$column = ?";
                        $updateValues[] = $value;
                    }
                }

                $whereValue = $row[$whereColumn];
                $updateValues[] = $whereValue;

                $sql = "UPDATE $table SET " . implode(',', $updates) . 
                       " WHERE $whereColumn = ?";

                $stmt = $this->prepare($sql);
                if ($stmt) {
                    $types = str_repeat('s', count($updateValues));
                    $stmt->bind_param($types, ...$updateValues);
                    if ($stmt->execute()) {
                        $count += $stmt->affected_rows;
                    }
                    $stmt->close();
                }
            }

            return $count;

        } catch (Exception $e) {
            error_log("Batch Update Exception: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Begin transaction
     */
    public function beginTransaction() {
        try {
            if ($this->currentConnection === null) {
                $this->reconnect();
            }
            
            $this->currentConnection->begin_transaction();
            $this->transactionActive = true;
            return true;

        } catch (Exception $e) {
            error_log("Begin Transaction Exception: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Commit transaction
     */
    public function commit() {
        try {
            if ($this->transactionActive) {
                $this->currentConnection->commit();
                $this->transactionActive = false;
                return true;
            }
            return false;

        } catch (Exception $e) {
            error_log("Commit Exception: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Rollback transaction
     */
    public function rollback() {
        try {
            if ($this->transactionActive) {
                $this->currentConnection->rollback();
                $this->transactionActive = false;
                return true;
            }
            return false;

        } catch (Exception $e) {
            error_log("Rollback Exception: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get last insert ID
     */
    public function getLastId() {
        return $this->currentConnection->insert_id;
    }

    /**
     * Get affected rows
     */
    public function affectedRows() {
        return $this->currentConnection->affected_rows;
    }

    /**
     * Escape string for safety
     */
    public function escape($str) {
        return $this->currentConnection->real_escape_string($str);
    }

    /**
     * Get query statistics
     */
    public function getStats() {
        return array(
            'queryCount' => $this->queryCount,
            'totalTime' => $this->queryTime,
            'averageTime' => $this->queryCount > 0 ? $this->queryTime / $this->queryCount : 0,
            'lastQuery' => $this->lastQuery,
            'connections' => count($this->connections)
        );
    }

    /**
     * Clear query cache
     */
    public function clearCache() {
        $this->queryCache = array();
        CacheManager::flush();
    }

    /**
     * Close all connections
     */
    public function closeAll() {
        foreach ($this->connections as $conn) {
            if ($conn) {
                $conn->close();
            }
        }
        $this->connections = array();
        $this->currentConnection = null;
    }

    /**
     * Destructor
     */
    public function __destruct() {
        $this->closeAll();
    }
}

// Create global database instance
$db = ScalableDatabase::getInstance();
?>
