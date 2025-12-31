<?php
/**
 * Cache Manager - Supports File, Redis, and Memcached
 * Optimized for 10K-20K concurrent users
 */

require_once 'config.php';

class CacheManager {
    private static $instance = null;
    private static $cacheDriver = null;
    private static $stats = array('hits' => 0, 'misses' => 0, 'sets' => 0);

    public static function initialize() {
        if (self::$cacheDriver !== null) {
            return;
        }

        if (!CACHE_ENABLED) {
            return;
        }

        try {
            if (CACHE_TYPE === 'redis' && extension_loaded('redis')) {
                self::$cacheDriver = new Redis();
                self::$cacheDriver->connect(CACHE_HOST, CACHE_PORT);
                self::$cacheDriver->select(0);
            } elseif (CACHE_TYPE === 'memcached' && extension_loaded('memcached')) {
                self::$cacheDriver = new Memcached();
                self::$cacheDriver->addServer(CACHE_HOST, CACHE_PORT);
            } else {
                // Default to file-based cache
                self::$cacheDriver = 'file';
                if (!is_dir(CACHE_DIR)) {
                    mkdir(CACHE_DIR, 0755, true);
                }
            }
        } catch (Exception $e) {
            error_log("Cache initialization failed: " . $e->getMessage());
            self::$cacheDriver = 'file';
        }
    }

    /**
     * Get value from cache
     */
    public static function get($key) {
        self::initialize();

        if (!CACHE_ENABLED) {
            return null;
        }

        try {
            if (self::$cacheDriver === 'file') {
                $file = CACHE_DIR . md5($key) . '.cache';
                if (file_exists($file)) {
                    $data = unserialize(file_get_contents($file));
                    if ($data['expires'] > time()) {
                        self::$stats['hits']++;
                        return $data['value'];
                    } else {
                        unlink($file);
                    }
                }
            } elseif (self::$cacheDriver instanceof Redis) {
                $value = self::$cacheDriver->get($key);
                if ($value !== false) {
                    self::$stats['hits']++;
                    return unserialize($value);
                }
            } elseif (self::$cacheDriver instanceof Memcached) {
                $value = self::$cacheDriver->get($key);
                if (self::$cacheDriver->getResultCode() !== Memcached::RES_NOTFOUND) {
                    self::$stats['hits']++;
                    return $value;
                }
            }

            self::$stats['misses']++;
            return null;

        } catch (Exception $e) {
            error_log("Cache get error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Set value in cache
     */
    public static function set($key, $value, $ttl = null) {
        self::initialize();

        if (!CACHE_ENABLED) {
            return false;
        }

        if ($ttl === null) {
            $ttl = CACHE_TTL;
        }

        try {
            self::$stats['sets']++;

            if (self::$cacheDriver === 'file') {
                $file = CACHE_DIR . md5($key) . '.cache';
                $data = array(
                    'value' => $value,
                    'expires' => time() + $ttl,
                    'created' => time()
                );
                return file_put_contents($file, serialize($data)) !== false;

            } elseif (self::$cacheDriver instanceof Redis) {
                return self::$cacheDriver->setex($key, $ttl, serialize($value));

            } elseif (self::$cacheDriver instanceof Memcached) {
                return self::$cacheDriver->set($key, $value, time() + $ttl);
            }

            return false;

        } catch (Exception $e) {
            error_log("Cache set error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete from cache
     */
    public static function delete($key) {
        self::initialize();

        if (!CACHE_ENABLED) {
            return false;
        }

        try {
            if (self::$cacheDriver === 'file') {
                $file = CACHE_DIR . md5($key) . '.cache';
                if (file_exists($file)) {
                    return unlink($file);
                }
            } elseif (self::$cacheDriver instanceof Redis) {
                return self::$cacheDriver->del($key) > 0;
            } elseif (self::$cacheDriver instanceof Memcached) {
                return self::$cacheDriver->delete($key);
            }

            return false;

        } catch (Exception $e) {
            error_log("Cache delete error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Clear all cache
     */
    public static function flush() {
        self::initialize();

        if (!CACHE_ENABLED) {
            return false;
        }

        try {
            if (self::$cacheDriver === 'file') {
                $files = glob(CACHE_DIR . '*.cache');
                foreach ($files as $file) {
                    unlink($file);
                }
                return true;

            } elseif (self::$cacheDriver instanceof Redis) {
                return self::$cacheDriver->flushDB();

            } elseif (self::$cacheDriver instanceof Memcached) {
                return self::$cacheDriver->flush();
            }

            return false;

        } catch (Exception $e) {
            error_log("Cache flush error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get cache statistics
     */
    public static function getStats() {
        self::initialize();

        return array(
            'hits' => self::$stats['hits'],
            'misses' => self::$stats['misses'],
            'sets' => self::$stats['sets'],
            'hitRate' => self::$stats['hits'] + self::$stats['misses'] > 0 
                ? (self::$stats['hits'] / (self::$stats['hits'] + self::$stats['misses'])) * 100 
                : 0,
            'driver' => self::$cacheDriver === 'file' ? 'file' : get_class(self::$cacheDriver)
        );
    }

    /**
     * Cache multiple values
     */
    public static function setMultiple($values, $ttl = null) {
        foreach ($values as $key => $value) {
            self::set($key, $value, $ttl);
        }
    }

    /**
     * Get multiple values
     */
    public static function getMultiple($keys) {
        $result = array();
        foreach ($keys as $key) {
            $result[$key] = self::get($key);
        }
        return $result;
    }

    /**
     * Check if key exists
     */
    public static function exists($key) {
        return self::get($key) !== null;
    }
}

// Initialize cache on first use
CacheManager::initialize();
?>
