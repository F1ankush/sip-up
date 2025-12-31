<?php
/**
 * Async Queue Manager - Handle high request volume
 * Processes long-running operations asynchronously
 */

require_once 'config.php';
require_once 'cache_manager.php';

class QueueManager {
    private static $queueDir = null;
    private static $db = null;

    public static function initialize() {
        self::$queueDir = __DIR__ . '/../queue/';
        
        if (!is_dir(self::$queueDir)) {
            mkdir(self::$queueDir, 0755, true);
        }
    }

    /**
     * Add job to queue
     */
    public static function addJob($jobType, $data, $priority = 'normal', $delaySeconds = 0) {
        self::initialize();

        $job = array(
            'id' => uniqid('job_', true),
            'type' => $jobType,
            'data' => $data,
            'priority' => $priority,
            'status' => 'pending',
            'created_at' => time(),
            'scheduled_at' => time() + $delaySeconds,
            'attempts' => 0,
            'max_attempts' => 3,
            'error' => null
        );

        $filename = self::$queueDir . $job['id'] . '.json';
        
        if (file_put_contents($filename, json_encode($job))) {
            // Also cache for quick access
            CacheManager::set('job_' . $job['id'], $job, 86400);
            return $job['id'];
        }

        return false;
    }

    /**
     * Get job status
     */
    public static function getJobStatus($jobId) {
        self::initialize();

        // Try cache first
        $cached = CacheManager::get('job_' . $jobId);
        if ($cached !== null) {
            return $cached;
        }

        $filename = self::$queueDir . $jobId . '.json';
        
        if (file_exists($filename)) {
            $job = json_decode(file_get_contents($filename), true);
            CacheManager::set('job_' . $jobId, $job, 86400);
            return $job;
        }

        return null;
    }

    /**
     * Process pending jobs
     */
    public static function processPending($maxJobs = 10) {
        self::initialize();

        $processed = 0;
        $files = glob(self::$queueDir . '*.json');

        usort($files, function($a, $b) {
            $jobA = json_decode(file_get_contents($a), true);
            $jobB = json_decode(file_get_contents($b), true);
            
            // Priority order: high > normal > low
            $priorityMap = array('high' => 3, 'normal' => 2, 'low' => 1);
            
            $priorityA = $priorityMap[$jobA['priority']] ?? 2;
            $priorityB = $priorityMap[$jobB['priority']] ?? 2;
            
            if ($priorityA !== $priorityB) {
                return $priorityB <=> $priorityA;
            }
            
            return $jobA['created_at'] <=> $jobB['created_at'];
        });

        foreach ($files as $file) {
            if ($processed >= $maxJobs) {
                break;
            }

            $job = json_decode(file_get_contents($file), true);

            // Skip if not scheduled yet
            if ($job['scheduled_at'] > time()) {
                continue;
            }

            // Skip if already processed
            if ($job['status'] !== 'pending') {
                continue;
            }

            if (self::executeJob($job)) {
                $processed++;
            }
        }

        return $processed;
    }

    /**
     * Execute a single job
     */
    private static function executeJob(&$job) {
        try {
            $job['status'] = 'processing';
            $job['attempts']++;

            // Save updated job
            file_put_contents(
                self::$queueDir . $job['id'] . '.json',
                json_encode($job)
            );

            $result = self::handleJobType($job['type'], $job['data']);

            if ($result) {
                $job['status'] = 'completed';
                $job['completed_at'] = time();
                $job['result'] = $result;
            } else {
                throw new Exception('Job execution failed');
            }

            // Save completed job
            file_put_contents(
                self::$queueDir . $job['id'] . '.json',
                json_encode($job)
            );

            CacheManager::set('job_' . $job['id'], $job, 86400);
            return true;

        } catch (Exception $e) {
            $job['error'] = $e->getMessage();

            if ($job['attempts'] < $job['max_attempts']) {
                $job['status'] = 'pending';
                $job['scheduled_at'] = time() + (60 * $job['attempts']); // Exponential backoff
            } else {
                $job['status'] = 'failed';
            }

            file_put_contents(
                self::$queueDir . $job['id'] . '.json',
                json_encode($job)
            );

            error_log("Job failed: " . $job['id'] . " - " . $e->getMessage());
            return false;
        }
    }

    /**
     * Handle different job types
     */
    private static function handleJobType($type, $data) {
        switch ($type) {
            case 'send_email':
                return self::sendEmail($data);

            case 'generate_report':
                return self::generateReport($data);

            case 'process_payment':
                return self::processPayment($data);

            case 'update_inventory':
                return self::updateInventory($data);

            case 'backup_database':
                return self::backupDatabase($data);

            case 'cleanup_cache':
                return self::cleanupCache($data);

            default:
                error_log("Unknown job type: $type");
                return false;
        }
    }

    /**
     * Job handlers
     */
    private static function sendEmail($data) {
        // Implement email sending logic
        error_log("Processing email job: " . $data['email']);
        return true;
    }

    private static function generateReport($data) {
        error_log("Generating report: " . $data['report_type']);
        return true;
    }

    private static function processPayment($data) {
        error_log("Processing payment: " . $data['payment_id']);
        return true;
    }

    private static function updateInventory($data) {
        error_log("Updating inventory: " . $data['product_id']);
        return true;
    }

    private static function backupDatabase($data) {
        error_log("Backing up database");
        return true;
    }

    private static function cleanupCache($data) {
        CacheManager::flush();
        error_log("Cache cleaned up");
        return true;
    }

    /**
     * Get queue stats
     */
    public static function getStats() {
        self::initialize();

        $files = glob(self::$queueDir . '*.json');
        $stats = array(
            'total' => 0,
            'pending' => 0,
            'processing' => 0,
            'completed' => 0,
            'failed' => 0
        );

        foreach ($files as $file) {
            $job = json_decode(file_get_contents($file), true);
            $stats['total']++;
            $stats[$job['status']]++;
        }

        return $stats;
    }

    /**
     * Clean old completed jobs
     */
    public static function cleanup($daysOld = 7) {
        self::initialize();

        $cutoffTime = time() - ($daysOld * 86400);
        $files = glob(self::$queueDir . '*.json');
        $cleaned = 0;

        foreach ($files as $file) {
            $job = json_decode(file_get_contents($file), true);
            
            if ($job['status'] === 'completed' && $job['completed_at'] < $cutoffTime) {
                unlink($file);
                CacheManager::delete('job_' . $job['id']);
                $cleaned++;
            }
        }

        return $cleaned;
    }
}

// Initialize on include
QueueManager::initialize();
?>
