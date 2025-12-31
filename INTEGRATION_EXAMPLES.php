<?php
/**
 * Integration Helper
 * Copy-paste snippets for integrating scaling components into existing pages
 * 
 * This file contains ready-to-use code examples for common integration scenarios
 */

// ============================================================================
// EXAMPLE 1: Cache Product Listings
// ============================================================================

/*
Location: pages/products.php

Add this at the top:
*/

require_once '../includes/cache_manager.php';

// Before your database query:
$cacheKey = 'products_page_' . ($currentPage ?? 1);
$cachedProducts = CacheManager::get($cacheKey);

if (!$cachedProducts) {
    // Fetch from database
    $query = "SELECT id, name, price, category, image FROM products 
              WHERE status = 'active' 
              LIMIT 20 OFFSET " . (($currentPage - 1) * 20);
    
    $result = $db->query($query);
    $cachedProducts = $result->fetch_all(MYSQLI_ASSOC);
    
    // Store in cache for 30 minutes
    CacheManager::set($cacheKey, $cachedProducts, 1800);
}

// Use $cachedProducts to display products


// ============================================================================
// EXAMPLE 2: Rate Limiting on Login Page
// ============================================================================

/*
Location: admin/login.php or pages/login.php

Add this after form submission check:
*/

require_once '../includes/rate_limiter.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check rate limit (10 attempts per 5 minutes)
    if (!RateLimiter::checkByIP(10, 300)) {
        $error = "Too many login attempts. Please try again later.";
        // Set response headers for client
        RateLimiter::setHeaders($_SERVER['REMOTE_ADDR'], 10, 300);
        exit;
    }
    
    // Proceed with login validation
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // ... rest of login code ...
}


// ============================================================================
// EXAMPLE 3: Queue Email Sending
// ============================================================================

/*
Location: pages/apply.php or any page that sends email

Replace mail() calls with queue:
*/

require_once '../includes/queue_manager.php';

// Instead of:
// mail($to, $subject, $body, $headers);

// Use:
$jobId = QueueManager::addJob('send_email', [
    'to' => $applicant_email,
    'subject' => 'Application Received',
    'body' => $emailBody,
    'headers' => ['Content-Type' => 'text/html; charset=UTF-8']
], 'high'); // high priority for important emails

echo "Your application has been submitted successfully!";
// Email will be sent asynchronously


// ============================================================================
// EXAMPLE 4: Cache Dashboard Data
// ============================================================================

/*
Location: pages/dashboard.php or admin/dashboard.php

Add this at the top:
*/

require_once '../includes/cache_manager.php';

$userId = $_SESSION['user_id'] ?? 0;
$cacheKey = 'dashboard_' . $userId;

$dashboardData = CacheManager::get($cacheKey);

if (!$dashboardData) {
    // Build dashboard data
    $dashboardData = [
        'totalOrders' => getTotalOrders($userId),
        'totalAmount' => getTotalAmount($userId),
        'pendingOrders' => getPendingOrders($userId),
        'recentOrders' => getRecentOrders($userId, 5),
        'stats' => getMonthlyStats($userId)
    ];
    
    // Cache for 2 minutes
    CacheManager::set($cacheKey, $dashboardData, 120);
}

// Use $dashboardData to display dashboard


// ============================================================================
// EXAMPLE 5: Cache User Data
// ============================================================================

/*
Location: Any page that loads user information

Use this function to load user data:
*/

function getUserData($userId) {
    require_once 'cache_manager.php';
    
    $cacheKey = 'user_' . $userId;
    $userData = CacheManager::get($cacheKey);
    
    if (!$userData) {
        global $db;
        $query = "SELECT id, name, email, phone, status FROM users WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $userData = $stmt->get_result()->fetch_assoc();
        
        // Cache for 10 minutes
        CacheManager::set($cacheKey, $userData, 600);
    }
    
    return $userData;
}


// ============================================================================
// EXAMPLE 6: Monitor Query Performance
// ============================================================================

/*
Location: admin/dashboard.php

Add this section to display database stats:
*/

require_once '../includes/db_scalable.php';

$db = ScalableDatabase::getInstance();
$stats = $db->getStats();

echo "Database Statistics:";
echo "Total Queries: " . $stats['queryCount'];
echo "Average Time: " . round($stats['averageTime'], 2) . "ms";
echo "Total Time: " . round($stats['totalTime'], 2) . "ms";
echo "Slow Queries (>1s): " . count($stats['slowQueries'] ?? []);


// ============================================================================
// EXAMPLE 7: Queue Report Generation
// ============================================================================

/*
Location: pages/reports.php or admin/reports.php

Queue long-running reports:
*/

require_once '../includes/queue_manager.php';

if ($_POST['generateReport'] ?? false) {
    $jobId = QueueManager::addJob('generate_report', [
        'type' => 'sales',
        'startDate' => $_POST['startDate'],
        'endDate' => $_POST['endDate'],
        'email' => $_SESSION['user_email'],
        'format' => 'pdf'
    ], 'normal');
    
    echo "Report generation started. You'll receive email when ready.";
    echo "<a href='check_report.php?id=$jobId'>Check Status</a>";
}


// ============================================================================
// EXAMPLE 8: Batch Insert Products
// ============================================================================

/*
Location: admin/import_products.php

Use batch insert for bulk operations:
*/

require_once '../includes/db_scalable.php';

$db = ScalableDatabase::getInstance();

// Prepare product data
$productData = [
    [1, 'Product 1', 'Category A', 99.99],
    [2, 'Product 2', 'Category B', 149.99],
    [3, 'Product 3', 'Category A', 199.99],
    // ... up to 1000 products
];

// Batch insert (much faster than individual inserts)
$db->batchInsert('products', $productData, ['id', 'name', 'category', 'price']);

echo "Imported " . count($productData) . " products";


// ============================================================================
// EXAMPLE 9: API Rate Limiting
// ============================================================================

/*
Location: api/endpoint.php

Add API rate limiting:
*/

require_once '../includes/rate_limiter.php';

// API rate limiting (1000 requests per hour per user)
if (isset($userId)) {
    if (!RateLimiter::checkByUser($userId, 1000, 3600)) {
        header('HTTP/1.1 429 Too Many Requests');
        json_encode(['error' => 'API rate limit exceeded']);
        exit;
    }
    
    // Set rate limit headers
    RateLimiter::setHeaders('user_' . $userId, 1000, 3600);
}

// Process API request...


// ============================================================================
// EXAMPLE 10: Cache Invalidation
// ============================================================================

/*
Location: admin/edit_product.php

Clear cache when data changes:
*/

require_once '../includes/cache_manager.php';

if ($_POST['updateProduct'] ?? false) {
    // Update product in database
    $productId = $_POST['productId'];
    $name = $_POST['name'];
    
    $query = "UPDATE products SET name = ? WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->bind_param('si', $name, $productId);
    $stmt->execute();
    
    // Clear related caches
    CacheManager::delete('products_page_1');
    CacheManager::delete('products_page_2');
    CacheManager::delete('product_' . $productId);
    
    echo "Product updated successfully";
}


// ============================================================================
// EXAMPLE 11: Transaction Support
// ============================================================================

/*
Location: pages/checkout.php

Use transactions for data consistency:
*/

require_once '../includes/db_scalable.php';

$db = ScalableDatabase::getInstance();

try {
    $db->beginTransaction();
    
    // Create order
    $insertOrder = "INSERT INTO orders (user_id, total) VALUES (?, ?)";
    $stmt = $db->prepare($insertOrder);
    $stmt->bind_param('id', $userId, $total);
    $stmt->execute();
    $orderId = $stmt->insert_id;
    
    // Insert order items
    foreach ($cartItems as $item) {
        $insertItem = "INSERT INTO order_items (order_id, product_id, quantity) VALUES (?, ?, ?)";
        $stmt = $db->prepare($insertItem);
        $stmt->bind_param('iii', $orderId, $item['productId'], $item['quantity']);
        $stmt->execute();
    }
    
    // Update inventory
    foreach ($cartItems as $item) {
        $updateInventory = "UPDATE products SET stock = stock - ? WHERE id = ?";
        $stmt = $db->prepare($updateInventory);
        $stmt->bind_param('ii', $item['quantity'], $item['productId']);
        $stmt->execute();
    }
    
    // All queries succeeded
    $db->commit();
    echo "Order created successfully";
    
} catch (Exception $e) {
    // Any error - rollback all changes
    $db->rollback();
    echo "Order creation failed: " . $e->getMessage();
}


// ============================================================================
// HELPER FUNCTIONS FOR INTEGRATION
// ============================================================================

/**
 * Clear all cache for a user
 */
function clearUserCache($userId) {
    require_once 'cache_manager.php';
    CacheManager::delete('user_' . $userId);
    CacheManager::delete('dashboard_' . $userId);
}

/**
 * Invalidate product cache
 */
function invalidateProductCache() {
    require_once 'cache_manager.php';
    // Clear all product caches
    for ($page = 1; $page <= 10; $page++) {
        CacheManager::delete('products_page_' . $page);
    }
}

/**
 * Check if rate limit exceeded
 */
function isRateLimitExceeded($identifier, $limit = 100, $window = 60) {
    require_once 'rate_limiter.php';
    return !RateLimiter::check($identifier, $limit, $window);
}

/**
 * Queue a job and return job ID
 */
function queueJob($type, $data, $priority = 'normal') {
    require_once 'queue_manager.php';
    return QueueManager::addJob($type, $data, $priority);
}

/**
 * Get database statistics
 */
function getDatabaseStats() {
    require_once 'db_scalable.php';
    $db = ScalableDatabase::getInstance();
    return $db->getStats();
}

/**
 * Get cache statistics
 */
function getCacheStats() {
    require_once 'cache_manager.php';
    return CacheManager::getStats();
}

/**
 * Measure query execution time
 */
function measureQuery($query) {
    require_once 'db_scalable.php';
    $db = ScalableDatabase::getInstance();
    $startTime = microtime(true);
    $result = $db->query($query);
    $duration = (microtime(true) - $startTime) * 1000;
    return ['result' => $result, 'duration' => $duration . 'ms'];
}
?>
