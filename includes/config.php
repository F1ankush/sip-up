<?php
// ============================================================================
// SCALABLE B2B PLATFORM CONFIGURATION - 10K-20K USERS SUPPORT
// ============================================================================

// DATABASE CONFIGURATION - OPTIMIZED FOR SCALE
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'Karan@1903');
define('DB_NAME', 'b2b_billing_system');

// Database Connection Pool Settings
define('DB_POOL_SIZE', 50);              // Connection pool size
define('DB_MAX_CONNECTIONS', 100);       // Maximum connections
define('DB_CONNECTION_TIMEOUT', 5);      // 5 seconds timeout
define('DB_READ_TIMEOUT', 30);           // 30 seconds for queries
define('DB_WRITE_TIMEOUT', 30);          // 30 seconds for writes

// SITE CONFIGURATION
define('SITE_URL', 'http://localhost/top1/');
define('SITE_NAME', 'B2B Retailer Platform');
define('COMPANY_NAME', 'sipup');
define('COMPANY_GST', '27AABCU1234B2Z5');
define('COMPANY_PHONE', '+91 9876543210');
define('COMPANY_EMAIL', 'support@retailerplatform.com');
define('COMPANY_ADDRESS', 'Bangalore, Karnataka, India');

// FOOTER CONFIGURATION
define('FOOTER_COMPANY', 'sipup');
define('FOOTER_DEVELOPER', 'Growcell IT Architect');
define('FOOTER_WEBSITE', 'https://growcell.in');
define('FOOTER_YEAR', date('Y'));

// ============================================================================
// CACHING CONFIGURATION - Redis/Memcached Support
// ============================================================================
define('CACHE_ENABLED', true);
define('CACHE_TYPE', 'file');             // 'file', 'redis', 'memcached'
define('CACHE_HOST', 'localhost');
define('CACHE_PORT', 6379);
define('CACHE_TTL', 3600);                // Default cache 1 hour
define('CACHE_DIR', __DIR__ . '/../cache/');

// Cache strategies for different data types
define('CACHE_PRODUCT_TTL', 1800);        // 30 minutes
define('CACHE_USER_TTL', 600);            // 10 minutes
define('CACHE_ORDER_TTL', 300);           // 5 minutes
define('CACHE_DASHBOARD_TTL', 120);       // 2 minutes

// ============================================================================
// SESSION CONFIGURATION - Distributed Sessions
// ============================================================================
define('SESSION_TIMEOUT', 1800);          // 30 minutes
define('SESSION_STORAGE', 'database');    // 'database', 'redis', 'file'
define('SESSION_HANDLER', 'user');        // Use custom handler
define('SESSION_REVALIDATE', 300);        // Revalidate every 5 min
define('SESSION_SECURE', false);          // Set true for HTTPS
define('SESSION_HTTP_ONLY', true);        // Prevent JS access
define('SESSION_SAME_SITE', 'Lax');       // CSRF protection

// ============================================================================
// SECURITY CONFIGURATION
// ============================================================================
define('ADMIN_SETUP_KEY', 'Karan');
define('MAX_FILE_SIZE', 5242880);         // 5MB
define('ALLOWED_FILE_TYPES', array('image/jpeg', 'image/png'));
define('ALLOWED_EXTENSIONS', array('jpg', 'jpeg', 'png'));
define('UPLOAD_DIR', __DIR__ . '/../uploads/');

// Security headers and protection
define('CSRF_TOKEN_LIFETIME', 3600);      // 1 hour
define('RATE_LIMIT_ENABLED', true);
define('RATE_LIMIT_REQUESTS', 100);       // 100 requests
define('RATE_LIMIT_WINDOW', 60);          // Per 60 seconds
define('RATE_LIMIT_IP', true);            // Limit per IP
define('RATE_LIMIT_USER', true);          // Limit per user

// ============================================================================
// PAGINATION & PERFORMANCE
// ============================================================================
define('ITEMS_PER_PAGE', 20);             // Increased for better UX
define('MAX_ITEMS_PER_PAGE', 100);
define('LAZY_LOAD_ENABLED', true);
define('LAZY_LOAD_LIMIT', 50);

// Query optimization
define('QUERY_CACHE_ENABLED', true);
define('QUERY_LOG_SLOW', true);
define('SLOW_QUERY_THRESHOLD', 1);        // 1 second
define('BATCH_SIZE', 1000);               // Batch operations

// ============================================================================
// API & REQUEST HANDLING
// ============================================================================
define('API_RATE_LIMIT', 1000);           // Per user per hour
define('API_TIMEOUT', 30);                // 30 seconds
define('API_MAX_PAYLOAD', 10485760);      // 10MB
define('ASYNC_PROCESSING', true);
define('QUEUE_ENABLED', true);
define('QUEUE_DRIVER', 'file');           // 'file', 'database', 'redis'

// ============================================================================
// UPI CONFIGURATION
// ============================================================================
define('UPI_MERCHANT_ID', 'YourUPIMerchantID');
define('UPI_TIMEOUT', 30);

// ============================================================================
// ERROR REPORTING & LOGGING
// ============================================================================
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../error_log.txt');

// Logging configuration
define('LOG_LEVEL', 'info');              // 'debug', 'info', 'warning', 'error'
define('LOG_DIR', __DIR__ . '/../logs/');
define('LOG_MAX_SIZE', 104857600);        // 100MB per file
define('LOG_RETENTION_DAYS', 30);
define('LOG_SLOW_QUERIES', true);
define('LOG_PERFORMANCE', true);

// ============================================================================
// OPTIMIZATION SETTINGS
// ============================================================================

// Memory and execution
ini_set('memory_limit', '512M');          // Increased from default
ini_set('max_execution_time', 300);       // 5 minutes
ini_set('default_socket_timeout', 60);

// Database optimization
ini_set('mysqli.max_connections', 100);
ini_set('mysqli.max_persistent', 50);
ini_set('mysqli.default_port', 3306);

// Output buffering
ini_set('output_buffering', 'on');
ini_set('output_handler', 'gzip');

// Compression
ini_set('zlib.output_compression', 'on');
ini_set('zlib.output_compression_level', 6);

// ============================================================================
// MONITORING & METRICS
// ============================================================================
define('MONITORING_ENABLED', true);
define('METRICS_COLLECTION', true);
define('PERFORMANCE_TRACKING', true);
define('UPTIME_MONITORING', true);
define('HEALTH_CHECK_INTERVAL', 300);     // Every 5 minutes

// ============================================================================
// SESSION INITIALIZATION - DISTRIBUTED
// ============================================================================

// Configure session settings for distributed systems
ini_set('session.gc_maxlifetime', 1800);
ini_set('session.gc_probability', 1);
ini_set('session.gc_divisor', 100);
ini_set('session.name', 'SIPUP_SESSIONID');
ini_set('session.cookie_secure', false);  // Set true for HTTPS
ini_set('session.cookie_httponly', true);
ini_set('session.cookie_samesite', 'Lax');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
