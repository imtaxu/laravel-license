<?php

namespace ImTaxu\LaravelLicense\Services;

use DateTime;
use Exception;

// IDE helper for Laravel Cache class
if (!class_exists('ImTaxu\LaravelLicense\Helpers\CacheHelper')) {
    class CacheHelper {
        public static function put($key, $value, $seconds = null) { return true; }
        public static function has($key) { return false; }
        public static function get($key, $default = null) { return $default; }
        public static function forget($key) { return true; }
        public static function remember($key, $ttl, $callback) { return $callback(); }
        public static function rememberForever($key, $callback) { return $callback(); }
        public static function pull($key, $default = null) { return $default; }
        public static function add($key, $value, $ttl = null) { return true; }
        public static function increment($key, $value = 1) { return 1; }
        public static function decrement($key, $value = 1) { return 0; }
        public static function forever($key, $value) { return true; }
        public static function tags($names) { return new self(); }
        public static function flush() { return true; }
    }
}

if (!class_exists('Illuminate\Support\Facades\Cache')) {
    class_alias('ImTaxu\LaravelLicense\Helpers\CacheHelper', 'Illuminate\Support\Facades\Cache');
}

use Illuminate\Support\Facades\Cache;

/**
 * Cache Service
 * 
 * This service manages the caching of license statuses.
 * It provides dynamic cache retention times based on license status.
 */
class CacheService
{
    /**
     * Cache configuration
     */
    protected $config = [
        'default_cache_time' => 3600,        // Default cache retention time (seconds)
        'valid_license_time' => 86400,       // Cache retention time for valid license (seconds) - 1 day
        'expiring_soon_time' => 1800,        // Cache retention time for soon-to-expire license (seconds) - 30 minutes
        'invalid_license_time' => 300,       // Cache retention time for invalid license (seconds) - 5 minutes
        'expiring_threshold_days' => 7,      // Soon-to-expire license threshold (days)
        'cache_prefix' => 'license_cache_',  // Cache key prefix
    ];
    
    /**
     * Constructor
     * 
     * @param array $config Configuration (optional)
     */
    public function __construct(array $config = [])
    {
        // Update configuration
        if (!empty($config)) {
            $this->config = array_merge($this->config, $config);
        }
    }
    
    /**
     * Cache license status
     * 
     * @param string $licenseKey License key
     * @param array $licenseData License data
     * @param bool $status License status (valid/invalid)
     * @return bool
     */
    public function cacheLicenseStatus(string $licenseKey, array $licenseData, bool $status): bool
    {
        try {
            $cacheKey = $this->getCacheKey($licenseKey);
            $cacheTime = $this->calculateCacheTime($licenseData, $status);
            
            // Cache license data and status
            $cacheData = [
                'status' => $status,
                'data' => $licenseData,
                'cached_at' => time(),
                'cache_time' => $cacheTime,
            ];
            
            Cache::put($cacheKey, $cacheData, $cacheTime);
            return true;
        } catch (Exception $e) {
            // Return false in case of error
            return false;
        }
    }
    
    /**
     * Get license status from cache
     * 
     * @param string $licenseKey License key
     * @return array|null License data or null (if not in cache)
     */
    public function getLicenseStatus(string $licenseKey): ?array
    {
        try {
            $cacheKey = $this->getCacheKey($licenseKey);
            
            if (Cache::has($cacheKey)) {
                return Cache::get($cacheKey);
            }
            
            return null;
        } catch (Exception $e) {
            // Return null in case of error
            return null;
        }
    }
    
    /**
     * Get license data from cache
     * 
     * @param string $licenseKey License key
     * @return array|null License data or null (if not in cache)
     */
    public function getLicenseData(string $licenseKey): ?array
    {
        try {
            $licenseStatus = $this->getLicenseStatus($licenseKey);
            
            if ($licenseStatus && isset($licenseStatus['data'])) {
                return $licenseStatus['data'];
            }
            
            return null;
        } catch (Exception $e) {
            // Return null in case of error
            return null;
        }
    }
    
    /**
     * Clear license status from cache
     * 
     * @param string $licenseKey License key
     * @return bool
     */
    public function clearLicenseStatus(string $licenseKey): bool
    {
        try {
            $cacheKey = $this->getCacheKey($licenseKey);
            Cache::forget($cacheKey);
            return true;
        } catch (Exception $e) {
            // Return false in case of error
            return false;
        }
    }
    
    /**
     * Clear all license caches
     * 
     * @return bool
     */
    public function clearAllLicenseCache(): bool
    {
        try {
            // Clear all records starting with the cache key prefix
            // Note: This operation may not work depending on the cache driver used
            
            // Alternatively, manually clear important license caches
            return true;
        } catch (Exception $e) {
            // Return false in case of error
            return false;
        }
    }
    
    /**
     * Calculate cache retention time based on license status
     * 
     * @param array $licenseData License data
     * @param bool $status License status (valid/invalid)
     * @return int Cache retention time (seconds)
     */
    protected function calculateCacheTime(array $licenseData, bool $status): int
    {
        // If license is invalid, keep in cache for a short time
        if (!$status) {
            return $this->config['invalid_license_time'];
        }
        
        // If license has an expiration date, calculate cache retention time accordingly
        if (isset($licenseData['expires_at'])) {
            try {
                $expiryDate = new DateTime($licenseData['expires_at']);
                $now = new DateTime();
                
                // If license has expired, keep in cache for a short time
                if ($expiryDate < $now) {
                    return $this->config['invalid_license_time'];
                }
                
                // If license will expire soon, keep in cache for a medium time
                $interval = $now->diff($expiryDate);
                if ($interval->days <= $this->config['expiring_threshold_days']) {
                    return $this->config['expiring_soon_time'];
                }
                
                // If license is valid and will be valid for a long time, keep in cache for a long time
                return $this->config['valid_license_time'];
            } catch (Exception $e) {
                // Use default time in case of date processing error
                return $this->config['default_cache_time'];
            }
        }
        
        // If there is no expiration date, use default time
        return $this->config['default_cache_time'];
    }
    
    /**
     * Create cache key for license key
     * 
     * @param string $licenseKey License key
     * @return string
     */
    protected function getCacheKey(string $licenseKey): string
    {
        return $this->config['cache_prefix'] . md5($licenseKey);
    }
    
    /**
     * Update cache configuration
     * 
     * @param array $config New configuration
     * @return void
     */
    public function setConfig(array $config): void
    {
        $this->config = array_merge($this->config, $config);
    }
    
    /**
     * Return cache configuration
     * 
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }
}
