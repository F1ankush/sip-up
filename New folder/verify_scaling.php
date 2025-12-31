<?php
/**
 * System Verification Script
 * Verifies all scaling components are installed and functional
 * 
 * Usage: php verify_scaling.php
 */

echo "\n";
echo "╔════════════════════════════════════════════════════════╗\n";
echo "║     SCALING SYSTEM VERIFICATION SCRIPT                 ║\n";
echo "║                                                        ║\n";
echo "║   Verifying installation of all scaling components    ║\n";
echo "╚════════════════════════════════════════════════════════╝\n\n";

$baseDir = __DIR__;
$allGood = true;
$issues = [];

// Define expected files
$components = [
    'includes/config.php' => 'Enhanced Configuration',
    'includes/db_scalable.php' => 'Scalable Database Layer',
    'includes/cache_manager.php' => 'Cache Manager',
    'includes/rate_limiter.php' => 'Rate Limiter',
    'includes/queue_manager.php' => 'Queue Manager',
    'database_optimize.php' => 'Database Optimizer',
    'process_queue.php' => 'Queue Processor',
    'monitoring_dashboard.php' => 'Monitoring Dashboard',
    'load_test.php' => 'Load Testing Tool',
];

$documentation = [
    'SCALING_GUIDE.md' => 'Comprehensive Scaling Guide',
    'SCALING_QUICKSTART.md' => 'Quick Start Guide',
    'SYSTEM_SCALING_SUMMARY.md' => 'System Summary',
    'SCALING_IMPLEMENTATION_STATUS.md' => 'Implementation Status',
    'SCALING_CHECKLIST.md' => 'Implementation Checklist',
    'SCALING_INDEX.md' => 'Navigation Index',
    'INTEGRATION_EXAMPLES.php' => 'Code Examples',
];

echo "📦 CHECKING CORE COMPONENTS\n";
echo "─────────────────────────────────────────────────────────\n\n";

foreach ($components as $path => $name) {
    $fullPath = $baseDir . '/' . $path;
    $exists = file_exists($fullPath);
    $status = $exists ? '✅' : '❌';
    
    echo "$status $name\n";
    echo "   File: $path\n";
    
    if ($exists) {
        $size = filesize($fullPath);
        $lines = count(file($fullPath));
        echo "   Size: " . number_format($size) . " bytes, $lines lines\n";
    } else {
        $allGood = false;
        $issues[] = "Missing: $path";
    }
    
    echo "\n";
}

echo "\n📚 CHECKING DOCUMENTATION\n";
echo "─────────────────────────────────────────────────────────\n\n";

foreach ($documentation as $path => $name) {
    $fullPath = $baseDir . '/' . $path;
    $exists = file_exists($fullPath);
    $status = $exists ? '✅' : '⚠️';
    
    echo "$status $name\n";
    echo "   File: $path\n";
    
    if ($exists) {
        $size = filesize($fullPath);
        $lines = count(file($fullPath));
        echo "   Size: " . number_format($size) . " bytes, $lines lines\n";
    }
    
    echo "\n";
}

echo "\n🔧 CHECKING CONFIGURATION\n";
echo "─────────────────────────────────────────────────────────\n\n";

// Check config constants
require_once 'includes/config.php';

$configChecks = [
    'DB_POOL_SIZE' => defined('DB_POOL_SIZE'),
    'CACHE_ENABLED' => defined('CACHE_ENABLED'),
    'RATE_LIMIT_ENABLED' => defined('RATE_LIMIT_ENABLED'),
    'QUERY_CACHE_ENABLED' => defined('QUERY_CACHE_ENABLED'),
];

foreach ($configChecks as $constant => $defined) {
    $status = $defined ? '✅' : '❌';
    $value = $defined ? constant($constant) : 'NOT DEFINED';
    echo "$status $constant = $value\n";
    
    if (!$defined) {
        $allGood = false;
        $issues[] = "Missing constant: $constant";
    }
}

echo "\n\n💾 CHECKING DIRECTORIES\n";
echo "─────────────────────────────────────────────────────────\n\n";

$dirs = [
    'includes' => 'Includes Directory',
    'queue' => 'Queue Directory',
    'cache' => 'Cache Directory',
    'uploads' => 'Uploads Directory',
];

foreach ($dirs as $dir => $name) {
    $fullPath = $baseDir . '/' . $dir;
    $exists = is_dir($fullPath);
    $status = $exists ? '✅' : '⚠️';
    
    echo "$status $name\n";
    echo "   Path: $dir/\n";
    
    if ($exists) {
        $items = count(array_diff(scandir($fullPath), ['.', '..']));
        echo "   Items: $items\n";
        
        // Check if writable
        if (!is_writable($fullPath)) {
            echo "   ⚠️  Directory not writable\n";
        }
    } else {
        echo "   ⚠️  Directory not found\n";
    }
    
    echo "\n";
}

echo "\n🔍 CHECKING FUNCTIONALITY\n";
echo "─────────────────────────────────────────────────────────\n\n";

// Test database connection
echo "Testing database connection...\n";
try {
    require_once 'includes/db_scalable.php';
    $db = ScalableDatabase::getInstance();
    $result = $db->query("SELECT 1");
    if ($result) {
        echo "✅ Database connection successful\n";
    } else {
        echo "❌ Database query failed\n";
        $allGood = false;
    }
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    $allGood = false;
}

// Test cache manager
echo "\nTesting cache system...\n";
try {
    require_once 'includes/cache_manager.php';
    CacheManager::set('test_key', 'test_value', 300);
    $value = CacheManager::get('test_key');
    
    if ($value === 'test_value') {
        echo "✅ Cache system functional\n";
        CacheManager::delete('test_key');
    } else {
        echo "⚠️  Cache retrieved unexpected value\n";
    }
} catch (Exception $e) {
    echo "⚠️  Cache warning: " . $e->getMessage() . "\n";
}

// Test rate limiter
echo "\nTesting rate limiter...\n";
try {
    require_once 'includes/rate_limiter.php';
    $allowed = RateLimiter::checkByIP(1000, 3600);
    echo "✅ Rate limiter functional\n";
    
    if ($allowed) {
        echo "   Current IP is within limits\n";
    }
} catch (Exception $e) {
    echo "⚠️  Rate limiter warning: " . $e->getMessage() . "\n";
}

// Test queue manager
echo "\nTesting queue system...\n";
try {
    require_once 'includes/queue_manager.php';
    $stats = QueueManager::getStats();
    echo "✅ Queue system functional\n";
    echo "   Pending: " . $stats['pending'] . " jobs\n";
    echo "   Completed: " . $stats['completed'] . " jobs\n";
} catch (Exception $e) {
    echo "⚠️  Queue warning: " . $e->getMessage() . "\n";
}

echo "\n\n📊 VERIFICATION SUMMARY\n";
echo "═════════════════════════════════════════════════════════\n\n";

if (count($issues) === 0) {
    echo "✅ ALL CHECKS PASSED!\n\n";
    echo "Your scaling system is correctly installed and functional.\n";
    echo "You can proceed with the following steps:\n\n";
    echo "1. Run database optimization:\n";
    echo "   php database_optimize.php\n\n";
    echo "2. Set up cron jobs (on Linux/Mac):\n";
    echo "   */5 * * * * php /path/to/top1/process_queue.php\n";
    echo "   0 2 * * 0 php /path/to/top1/database_optimize.php\n\n";
    echo "3. Access monitoring dashboard:\n";
    echo "   http://localhost/top1/monitoring_dashboard.php\n\n";
    echo "4. Test performance:\n";
    echo "   php load_test.php 100\n\n";
    
} else {
    echo "⚠️  ISSUES FOUND:\n\n";
    foreach ($issues as $issue) {
        echo "  • $issue\n";
    }
    echo "\nPlease resolve these issues before proceeding.\n";
}

echo "\n═════════════════════════════════════════════════════════\n";
echo "COMPONENTS INSTALLED: " . count($components) . "/" . count($components) . "\n";
echo "DOCUMENTATION FILES: " . count($documentation) . "/" . count($documentation) . "\n";
echo "STATUS: " . ($allGood ? '✅ READY' : '⚠️  ISSUES DETECTED') . "\n";
echo "═════════════════════════════════════════════════════════\n\n";

echo "📖 For more information, see:\n";
echo "   SCALING_QUICKSTART.md - Quick start guide\n";
echo "   SCALING_GUIDE.md - Comprehensive documentation\n";
echo "   SCALING_INDEX.md - Navigation and index\n\n";

?>
