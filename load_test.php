<?php
/**
 * Load Testing Script
 * Tests system performance under concurrent load
 * 
 * Usage:
 * php load_test.php 100    // Test with 100 concurrent requests
 * php load_test.php 1000   // Test with 1000 concurrent requests
 */

class LoadTester {
    private $baseUrl = 'http://localhost/top1';
    private $results = [];
    private $totalTime = 0;
    private $successCount = 0;
    private $errorCount = 0;
    
    public function __construct($baseUrl = null) {
        if ($baseUrl) {
            $this->baseUrl = $baseUrl;
        }
    }
    
    /**
     * Test a single endpoint
     */
    public function testEndpoint($url, $method = 'GET', $data = null) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        
        if ($method === 'POST' && $data) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        }
        
        $startTime = microtime(true);
        $response = curl_exec($ch);
        $duration = (microtime(true) - $startTime) * 1000; // Convert to ms
        
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        return [
            'url' => $url,
            'method' => $method,
            'duration' => $duration,
            'statusCode' => $httpCode,
            'success' => ($httpCode >= 200 && $httpCode < 300),
            'error' => $error
        ];
    }
    
    /**
     * Run concurrent requests
     */
    public function runConcurrentTest($endpoint, $concurrentRequests = 100, $iterations = 10) {
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "LOAD TEST: $endpoint\n";
        echo "Concurrent Requests: $concurrentRequests\n";
        echo "Iterations: $iterations\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        
        $times = [];
        $statusCodes = [];
        
        for ($i = 0; $i < $iterations; $i++) {
            echo "Running iteration " . ($i + 1) . " of $iterations...\n";
            
            // Use curl_multi for true concurrency
            $mh = curl_multi_init();
            $handles = [];
            
            for ($j = 0; $j < $concurrentRequests; $j++) {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $this->baseUrl . $endpoint);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
                
                curl_multi_add_handle($mh, $ch);
                $handles[] = $ch;
            }
            
            // Execute all requests
            $running = null;
            do {
                curl_multi_exec($mh, $running);
                usleep(10000); // 10ms delay to reduce CPU usage
            } while ($running > 0);
            
            // Collect results
            foreach ($handles as $ch) {
                $duration = curl_getinfo($ch, CURLINFO_TOTAL_TIME) * 1000;
                $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                
                $times[] = $duration;
                $statusCodes[] = $statusCode;
                
                if ($statusCode >= 200 && $statusCode < 300) {
                    $this->successCount++;
                } else {
                    $this->errorCount++;
                }
                
                curl_multi_remove_handle($mh, $ch);
                curl_close($ch);
            }
            
            curl_multi_close($mh);
        }
        
        // Calculate statistics
        $this->printResults($times, $statusCodes, $concurrentRequests * $iterations);
    }
    
    /**
     * Test specific pages
     */
    public function testPages() {
        echo "\n\n";
        echo "╔════════════════════════════════════════════════════════╗\n";
        echo "║         INDIVIDUAL PAGE LOAD TESTS                    ║\n";
        echo "╚════════════════════════════════════════════════════════╝\n\n";
        
        $endpoints = [
            '/index.php' => 'Homepage',
            '/pages/products.php' => 'Products Page',
            '/pages/dashboard.php' => 'Dashboard',
            '/pages/orders.php' => 'Orders Page',
            '/admin/dashboard.php' => 'Admin Dashboard',
        ];
        
        foreach ($endpoints as $path => $name) {
            echo "\n📄 Testing: $name ($path)\n";
            echo "─────────────────────────────────────────────────────────\n";
            
            $times = [];
            for ($i = 0; $i < 20; $i++) {
                $result = $this->testEndpoint($this->baseUrl . $path);
                $times[] = $result['duration'];
                
                echo ".";
                if (($i + 1) % 10 === 0) echo " " . ($i + 1) . "/20\n";
            }
            
            $this->printPageStats($name, $times);
        }
    }
    
    /**
     * Print results statistics
     */
    private function printResults($times, $statusCodes, $totalRequests) {
        sort($times);
        
        $avgTime = array_sum($times) / count($times);
        $minTime = min($times);
        $maxTime = max($times);
        $p95 = $times[floor(count($times) * 0.95)];
        $p99 = $times[floor(count($times) * 0.99)];
        
        $statusDistribution = array_count_values($statusCodes);
        
        echo "\n✅ RESULTS:\n";
        echo "─────────────────────────────────────────────────────────\n";
        echo "Total Requests: " . number_format($totalRequests) . "\n";
        echo "Successful: " . $this->successCount . " (" . round($this->successCount / $totalRequests * 100, 2) . "%)\n";
        echo "Failed: " . $this->errorCount . " (" . round($this->errorCount / $totalRequests * 100, 2) . "%)\n";
        echo "\n📊 Response Times:\n";
        echo "  Min:     " . round($minTime, 2) . "ms\n";
        echo "  Avg:     " . round($avgTime, 2) . "ms\n";
        echo "  P95:     " . round($p95, 2) . "ms\n";
        echo "  P99:     " . round($p99, 2) . "ms\n";
        echo "  Max:     " . round($maxTime, 2) . "ms\n";
        
        echo "\n📈 Status Code Distribution:\n";
        foreach ($statusDistribution as $code => $count) {
            echo "  $code: " . $count . " (" . round($count / $totalRequests * 100, 2) . "%)\n";
        }
        
        // Performance verdict
        echo "\n🎯 Performance Assessment:\n";
        if ($avgTime < 200) {
            echo "  ✅ EXCELLENT: Response times under 200ms\n";
        } elseif ($avgTime < 500) {
            echo "  ✅ GOOD: Response times under 500ms\n";
        } elseif ($avgTime < 1000) {
            echo "  ⚠️  WARNING: Response times under 1000ms\n";
        } else {
            echo "  ❌ CRITICAL: Response times over 1000ms\n";
        }
        
        if ($this->successCount / $totalRequests > 0.99) {
            echo "  ✅ EXCELLENT: >99% success rate\n";
        } elseif ($this->successCount / $totalRequests > 0.95) {
            echo "  ✅ GOOD: >95% success rate\n";
        } else {
            echo "  ⚠️  WARNING: <95% success rate\n";
        }
    }
    
    /**
     * Print page-specific stats
     */
    private function printPageStats($pageName, $times) {
        sort($times);
        
        $avg = array_sum($times) / count($times);
        $min = min($times);
        $max = max($times);
        
        echo "\nResults for $pageName:\n";
        echo "  Min: " . round($min, 2) . "ms\n";
        echo "  Avg: " . round($avg, 2) . "ms\n";
        echo "  Max: " . round($max, 2) . "ms\n";
        
        if ($avg < 200) {
            echo "  ✅ Excellent performance\n";
        } elseif ($avg < 500) {
            echo "  ✅ Good performance\n";
        } elseif ($avg < 1000) {
            echo "  ⚠️  Average performance\n";
        } else {
            echo "  ❌ Poor performance - Needs optimization\n";
        }
    }
}

// Main execution
if (php_sapi_name() === 'cli') {
    $concurrentUsers = isset($argv[1]) ? intval($argv[1]) : 100;
    
    echo "\n";
    echo "╔════════════════════════════════════════════════════════╗\n";
    echo "║        SCALING SYSTEM LOAD TEST SUITE                  ║\n";
    echo "║                                                        ║\n";
    echo "║   Testing system capacity and performance metrics      ║\n";
    echo "╚════════════════════════════════════════════════════════╝\n";
    
    $tester = new LoadTester();
    
    // Test main pages
    $tester->testPages();
    
    // Test concurrent load
    echo "\n\n";
    echo "╔════════════════════════════════════════════════════════╗\n";
    echo "║         CONCURRENT LOAD TEST                           ║\n";
    echo "║   Testing system with concurrent users                 ║\n";
    echo "╚════════════════════════════════════════════════════════╝\n";
    
    $tester->runConcurrentTest('/index.php', $concurrentUsers, 5);
    
    echo "\n\n";
    echo "╔════════════════════════════════════════════════════════╗\n";
    echo "║        LOAD TEST COMPLETED                             ║\n";
    echo "║                                                        ║\n";
    echo "║   For detailed results, check monitoring_dashboard.php ║\n";
    echo "╚════════════════════════════════════════════════════════╝\n\n";
    
} else {
    echo "This script must be run from command line.\n";
    echo "Usage: php load_test.php [concurrent_users]\n";
    echo "Example: php load_test.php 100\n";
}
?>
