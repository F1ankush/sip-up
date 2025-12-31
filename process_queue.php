<?php
/**
 * Queue Processor - Run via cron every 5 minutes
 * Cron command: */5 * * * * php /path/to/process_queue.php
 */

require_once 'includes/config.php';
require_once 'includes/queue_manager.php';

class QueueProcessor {
    
    public static function process() {
        $startTime = time();
        $maxDuration = 240; // 4 minutes max execution
        
        echo "[" . date('Y-m-d H:i:s') . "] Starting queue processor\n";
        
        try {
            // Process pending jobs
            $processed = 0;
            
            while ((time() - $startTime) < $maxDuration) {
                $count = QueueManager::processPending(10);
                
                if ($count === 0) {
                    // No more jobs to process
                    break;
                }
                
                $processed += $count;
                
                // Small delay to prevent CPU spike
                sleep(1);
            }
            
            // Get stats
            $stats = QueueManager::getStats();
            
            // Clean old completed jobs (older than 7 days)
            $cleaned = QueueManager::cleanup(7);
            
            echo "[" . date('Y-m-d H:i:s') . "] Queue processor completed\n";
            echo "  - Processed: $processed jobs\n";
            echo "  - Pending: " . $stats['pending'] . " jobs\n";
            echo "  - Processing: " . $stats['processing'] . " jobs\n";
            echo "  - Completed: " . $stats['completed'] . " jobs\n";
            echo "  - Failed: " . $stats['failed'] . " jobs\n";
            echo "  - Cleaned: $cleaned old jobs\n";
            echo "  - Duration: " . (time() - $startTime) . " seconds\n";
            
        } catch (Exception $e) {
            echo "[ERROR] " . date('Y-m-d H:i:s') . " - " . $e->getMessage() . "\n";
            error_log("Queue processor error: " . $e->getMessage());
        }
    }
}

// Run if executed from CLI
if (php_sapi_name() === 'cli') {
    QueueProcessor::process();
} else {
    // Can also be called from web
    QueueProcessor::process();
}
?>
