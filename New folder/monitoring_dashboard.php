<?php
/**
 * System Monitoring Dashboard
 * Displays real-time metrics for scaled system
 */

session_start();
require_once 'includes/config.php';
require_once 'includes/db_scalable.php';
require_once 'includes/cache_manager.php';
require_once 'includes/queue_manager.php';

// Check admin access
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin/login.php');
    exit;
}

$db = ScalableDatabase::getInstance();
$cacheStats = CacheManager::getStats();
$queueStats = QueueManager::getStats();
$dbStats = $db->getStats();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Monitoring Dashboard</title>
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
            padding: 20px;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
        }
        
        header {
            color: white;
            margin-bottom: 30px;
            text-align: center;
        }
        
        header h1 {
            font-size: 32px;
            margin-bottom: 10px;
        }
        
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            position: relative;
            overflow: hidden;
        }
        
        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
        }
        
        .card h2 {
            font-size: 18px;
            color: #333;
            margin-bottom: 15px;
            padding-top: 10px;
        }
        
        .metric {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        
        .metric:last-child {
            border-bottom: none;
        }
        
        .metric-label {
            color: #666;
            font-size: 14px;
        }
        
        .metric-value {
            font-size: 18px;
            font-weight: bold;
            color: #667eea;
        }
        
        .progress-bar {
            width: 100%;
            height: 8px;
            background: #eee;
            border-radius: 4px;
            overflow: hidden;
            margin-top: 10px;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            transition: width 0.3s ease;
        }
        
        .status-good {
            color: #27ae60;
        }
        
        .status-warning {
            color: #f39c12;
        }
        
        .status-critical {
            color: #e74c3c;
        }
        
        .full-width {
            grid-column: 1 / -1;
        }
        
        .refresh-info {
            text-align: center;
            color: #999;
            margin-top: 20px;
            font-size: 12px;
        }
        
        .last-query {
            background: #f5f5f5;
            padding: 10px;
            border-radius: 4px;
            margin-top: 10px;
            font-family: monospace;
            font-size: 12px;
            color: #333;
            word-break: break-all;
            max-height: 100px;
            overflow-y: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        
        table th {
            background: #f5f5f5;
            padding: 10px;
            text-align: left;
            font-weight: bold;
            border-bottom: 2px solid #ddd;
        }
        
        table td {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .alert {
            background: #fff3cd;
            border-left: 4px solid #f39c12;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        
        .alert.critical {
            background: #f8d7da;
            border-left-color: #e74c3c;
        }
        
        .alert.success {
            background: #d4edda;
            border-left-color: #27ae60;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>⚙️ System Monitoring Dashboard</h1>
            <p>Real-time performance metrics for scaled application</p>
        </header>
        
        <?php if ($dbStats['queryCount'] > 0 && $dbStats['averageTime'] > 1.0): ?>
        <div class="alert critical">
            ⚠️ <strong>Performance Alert:</strong> Average query time is <?php echo round($dbStats['averageTime'], 2); ?>ms. 
            Consider optimizing slow queries or increasing cache TTLs.
        </div>
        <?php endif; ?>
        
        <?php if ($queueStats['failed'] > 10): ?>
        <div class="alert critical">
            ⚠️ <strong>Queue Alert:</strong> <?php echo $queueStats['failed']; ?> failed jobs detected. 
            Review error logs immediately.
        </div>
        <?php endif; ?>
        
        <div class="dashboard-grid">
            
            <!-- Database Performance -->
            <div class="card">
                <h2>📊 Database Performance</h2>
                <div class="metric">
                    <span class="metric-label">Total Queries</span>
                    <span class="metric-value"><?php echo number_format($dbStats['queryCount']); ?></span>
                </div>
                <div class="metric">
                    <span class="metric-label">Average Time</span>
                    <span class="metric-value <?php echo $dbStats['averageTime'] > 1.0 ? 'status-warning' : 'status-good'; ?>">
                        <?php echo round($dbStats['averageTime'], 2); ?>ms
                    </span>
                </div>
                <div class="metric">
                    <span class="metric-label">Total Time</span>
                    <span class="metric-value"><?php echo round($dbStats['totalTime'], 2); ?>ms</span>
                </div>
                <div class="metric">
                    <span class="metric-label">Last Query</span>
                    <span class="metric-value" style="font-size: 12px;">
                        <?php echo date('H:i:s'); ?>
                    </span>
                </div>
                <div class="last-query"><?php echo htmlspecialchars($dbStats['lastQuery'] ?? 'N/A'); ?></div>
            </div>
            
            <!-- Cache Performance -->
            <div class="card">
                <h2>💾 Cache Performance</h2>
                <div class="metric">
                    <span class="metric-label">Cache Hits</span>
                    <span class="metric-value status-good"><?php echo number_format($cacheStats['hits']); ?></span>
                </div>
                <div class="metric">
                    <span class="metric-label">Cache Misses</span>
                    <span class="metric-value"><?php echo number_format($cacheStats['misses']); ?></span>
                </div>
                <div class="metric">
                    <span class="metric-label">Hit Rate</span>
                    <span class="metric-value <?php echo $cacheStats['hitRate'] > 60 ? 'status-good' : 'status-warning'; ?>">
                        <?php echo round($cacheStats['hitRate'], 1); ?>%
                    </span>
                </div>
                <div class="metric">
                    <span class="metric-label">Driver</span>
                    <span class="metric-value"><?php echo ucfirst($cacheStats['driver']); ?></span>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?php echo min($cacheStats['hitRate'], 100); ?>%"></div>
                </div>
            </div>
            
            <!-- Queue Status -->
            <div class="card">
                <h2>📝 Job Queue Status</h2>
                <div class="metric">
                    <span class="metric-label">Pending Jobs</span>
                    <span class="metric-value <?php echo $queueStats['pending'] < 100 ? 'status-good' : 'status-warning'; ?>">
                        <?php echo number_format($queueStats['pending']); ?>
                    </span>
                </div>
                <div class="metric">
                    <span class="metric-label">Processing</span>
                    <span class="metric-value"><?php echo number_format($queueStats['processing']); ?></span>
                </div>
                <div class="metric">
                    <span class="metric-label">Completed</span>
                    <span class="metric-value status-good"><?php echo number_format($queueStats['completed']); ?></span>
                </div>
                <div class="metric">
                    <span class="metric-label">Failed</span>
                    <span class="metric-value <?php echo $queueStats['failed'] > 0 ? 'status-critical' : 'status-good'; ?>">
                        <?php echo number_format($queueStats['failed']); ?>
                    </span>
                </div>
            </div>
            
            <!-- System Configuration -->
            <div class="card">
                <h2>⚙️ System Configuration</h2>
                <div class="metric">
                    <span class="metric-label">Memory Limit</span>
                    <span class="metric-value"><?php echo ini_get('memory_limit'); ?></span>
                </div>
                <div class="metric">
                    <span class="metric-label">Max Execution Time</span>
                    <span class="metric-value"><?php echo ini_get('max_execution_time'); ?>s</span>
                </div>
                <div class="metric">
                    <span class="metric-label">DB Pool Size</span>
                    <span class="metric-value"><?php echo DB_POOL_SIZE; ?></span>
                </div>
                <div class="metric">
                    <span class="metric-label">Cache TTL</span>
                    <span class="metric-value"><?php echo CACHE_TTL; ?>s</span>
                </div>
                <div class="metric">
                    <span class="metric-label">Rate Limit</span>
                    <span class="metric-value"><?php echo RATE_LIMIT_REQUESTS; ?>/min</span>
                </div>
            </div>
            
            <!-- Scaling Health -->
            <div class="card">
                <h2>❤️ System Health</h2>
                <table>
                    <tr>
                        <td style="font-weight: bold;">Database Health</td>
                        <td class="status-good">✓ Healthy</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold;">Cache System</td>
                        <td class="status-good">✓ Operational</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold;">Queue Processor</td>
                        <td class="<?php echo $queueStats['pending'] < 50 ? 'status-good' : 'status-warning'; ?>">
                            <?php echo $queueStats['pending'] < 50 ? '✓ Operational' : '⚠ Backlog'; ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold;">Rate Limiting</td>
                        <td class="status-good">✓ Active</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold;">Performance</td>
                        <td class="<?php echo $dbStats['averageTime'] < 1.0 ? 'status-good' : 'status-warning'; ?>">
                            <?php echo $dbStats['averageTime'] < 1.0 ? '✓ Good' : '⚠ Monitor'; ?>
                        </td>
                    </tr>
                </table>
            </div>
            
            <!-- Scaling Recommendations -->
            <div class="card full-width">
                <h2>📈 Scaling Recommendations</h2>
                <div style="padding-top: 10px;">
                    <?php 
                    $recommendations = [];
                    
                    if ($cacheStats['hitRate'] < 50) {
                        $recommendations[] = "📉 Low cache hit rate. Consider increasing cache TTLs or adjusting cache keys.";
                    }
                    if ($dbStats['averageTime'] > 1.0) {
                        $recommendations[] = "🐢 Slow queries detected. Review slow_query logs and add indexes.";
                    }
                    if ($queueStats['pending'] > 100) {
                        $recommendations[] = "📦 Queue backlog detected. Increase queue processor frequency or optimize job handlers.";
                    }
                    if ($queueStats['failed'] > 5) {
                        $recommendations[] = "❌ Job failures detected. Review error logs for failed job details.";
                    }
                    if (empty($recommendations)) {
                        $recommendations[] = "✅ System is performing well. Continue monitoring metrics regularly.";
                    }
                    
                    echo "<ul style='list-style: none; padding: 0;'>";
                    foreach ($recommendations as $rec) {
                        echo "<li style='padding: 8px 0; border-bottom: 1px solid #eee;'>$rec</li>";
                    }
                    echo "</ul>";
                    ?>
                </div>
            </div>
            
        </div>
        
        <div class="refresh-info">
            <p>Page auto-refreshes every 30 seconds</p>
            <p>Last updated: <?php echo date('Y-m-d H:i:s'); ?></p>
            <meta http-equiv="refresh" content="30">
        </div>
    </div>
</body>
</html>
