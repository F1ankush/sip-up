<?php
/**
 * Rate Limiter - Handle 10K-20K users
 * Prevents abuse and ensures fair resource distribution
 */

require_once 'config.php';
require_once 'cache_manager.php';

class RateLimiter {
    private static $limiters = array();

    /**
     * Check if request should be allowed
     */
    public static function check($identifier, $limit = null, $window = null) {
        if (!RATE_LIMIT_ENABLED) {
            return true;
        }

        $limit = $limit ?? RATE_LIMIT_REQUESTS;
        $window = $window ?? RATE_LIMIT_WINDOW;

        $key = 'rate_limit_' . $identifier;
        $current = CacheManager::get($key);

        if ($current === null) {
            $current = array('count' => 0, 'reset_time' => time() + $window);
        }

        // Reset if window expired
        if (time() > $current['reset_time']) {
            $current = array('count' => 0, 'reset_time' => time() + $window);
        }

        $current['count']++;
        CacheManager::set($key, $current, $window);

        if ($current['count'] > $limit) {
            error_log("Rate limit exceeded for: " . $identifier);
            return false;
        }

        return true;
    }

    /**
     * Get remaining requests
     */
    public static function getRemaining($identifier, $limit = null) {
        if (!RATE_LIMIT_ENABLED) {
            return -1; // Unlimited
        }

        $limit = $limit ?? RATE_LIMIT_REQUESTS;
        $key = 'rate_limit_' . $identifier;
        $current = CacheManager::get($key);

        if ($current === null) {
            return $limit;
        }

        return max(0, $limit - $current['count']);
    }

    /**
     * Get reset time
     */
    public static function getResetTime($identifier) {
        $key = 'rate_limit_' . $identifier;
        $current = CacheManager::get($key);

        if ($current === null) {
            return time() + RATE_LIMIT_WINDOW;
        }

        return $current['reset_time'];
    }

    /**
     * Check by IP address
     */
    public static function checkByIP($limit = null, $window = null) {
        if (!RATE_LIMIT_IP) {
            return true;
        }

        $ip = self::getClientIP();
        return self::check('ip_' . $ip, $limit, $window);
    }

    /**
     * Check by user ID
     */
    public static function checkByUser($userId, $limit = null, $window = null) {
        if (!RATE_LIMIT_USER || !$userId) {
            return true;
        }

        $limit = $limit ?? API_RATE_LIMIT;
        $window = $window ?? 3600; // Per hour for API

        return self::check('user_' . $userId, $limit, $window);
    }

    /**
     * Check by endpoint
     */
    public static function checkEndpoint($endpoint, $limit = null, $window = null) {
        $ip = self::getClientIP();
        $identifier = 'endpoint_' . $endpoint . '_' . $ip;
        
        return self::check($identifier, $limit, $window);
    }

    /**
     * Get client IP address
     */
    private static function getClientIP() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        }
        
        return preg_replace('/[^0-9a-f:.]/', '', $ip);
    }

    /**
     * Reset rate limit for identifier
     */
    public static function reset($identifier) {
        $key = 'rate_limit_' . $identifier;
        return CacheManager::delete($key);
    }

    /**
     * Set response headers for rate limiting
     */
    public static function setHeaders($identifier, $limit = null, $window = null) {
        if (!RATE_LIMIT_ENABLED) {
            return;
        }

        $limit = $limit ?? RATE_LIMIT_REQUESTS;
        $remaining = self::getRemaining($identifier, $limit);
        $resetTime = self::getResetTime($identifier);

        header('X-RateLimit-Limit: ' . $limit);
        header('X-RateLimit-Remaining: ' . $remaining);
        header('X-RateLimit-Reset: ' . $resetTime);
    }
}

// Initialize cache for rate limiter
CacheManager::initialize();
?>
