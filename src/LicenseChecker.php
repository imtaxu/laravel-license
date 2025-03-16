<?php

namespace ImTaxu\LaravelLicense;

use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;
use ImTaxu\LaravelLicense\Services\ConfigIntegrityService;
use ImTaxu\LaravelLicense\Services\RateLimiterService;
use ImTaxu\LaravelLicense\Services\CacheService;
use ImTaxu\LaravelLicense\Services\NotificationService;
use ImTaxu\LaravelLicense\Services\HardwareIdService;

// IDE helpers for functions provided by Laravel
if (!function_exists('app')) {
    function app() {
        return new class {
            public function environment(...$args) { return in_array('local', $args); }
            public function basePath($path = '') { return __DIR__ . '/../../' . $path; }
            public function make($class) { return null; }
        };
    }
}

// IDE helper for Laravel Facades
namespace Illuminate\Support\Facades {
    if (!class_exists('Log')) {
        class Log {
            public static function error($message) {}
            public static function info($message) {}
            public static function warning($message) {}
            public static function debug($message) {}
        }
    }
    
    if (!class_exists('Cache')) {
        class Cache {
            public static function get($key, $default = null) { return $default; }
            public static function put($key, $value, $ttl = null) {}
            public static function has($key) { return false; }
            public static function forget($key) {}
            public static function remember($key, $ttl, $callback) { return $callback(); }
            public static function pull($key, $default = null) { return $default; }
            
            // Carbon benzeri metotlar
            public static function addDays($days) { return new \DateTime(); }
        }
    }
    
    if (!class_exists('Http')) {
        class Http {
            public static function get($url, $query = []) { 
                return new class {
                    public $statusCode = 200;
                    private $data = [];
                    
                    public function successful() { return $this->statusCode >= 200 && $this->statusCode < 300; }
                    
                    public function json($key = null, $default = null) {
                        if ($key === null) return $this->data;
                        return $this->data[$key] ?? $default;
                    }
                };
            }
            
            public static function post($url, $data = []) { 
                return new class {
                    public $statusCode = 200;
                    private $data = [];
                    
                    public function successful() { return $this->statusCode >= 200 && $this->statusCode < 300; }
                    
                    public function json($key = null, $default = null) {
                        if ($key === null) return $this->data;
                        return $this->data[$key] ?? $default;
                    }
                };
            }
        }
    }
    
    if (!class_exists('Lang')) {
        class Lang {
            public static function get($key, $replace = [], $locale = null) { return $key; }
            public static function has($key, $locale = null) { return false; }
            public static function choice($key, $number, $replace = [], $locale = null) { return $key; }
        }
    }
}

// IDE helper for service classes
namespace ImTaxu\LaravelLicense\Services {
    if (!class_exists('ConfigIntegrityService')) {
        class ConfigIntegrityService {
            public function verifyConfigIntegrity($configPath, $vendorBackupPath) { return true; }
            public function fetchExcludedIpsFromServer($licenseKey, $variables) { return []; }
            public function verifyConfigWithServer($licenseKey, $config) { return true; }
        }
    }
    
    if (!class_exists('RateLimiterService')) {
        class RateLimiterService {
            public function isRateLimited($key, $maxAttempts, $decayMinutes) { return false; }
            public function increment($key, $decayMinutes = 1) {}
            public function clear($key) {}
        }
    }
    
    if (!class_exists('CacheService')) {
        class CacheService {
            public function getLicenseData($licenseKey) { return []; }
            public function setLicenseData($licenseKey, $data, $ttl = null) {}
            public function clearLicenseData($licenseKey) {}
        }
    }
    
    if (!class_exists('NotificationService')) {
        class NotificationService {
            public function getNotifications() { return []; }
            public function addNotification($type, $message, $ttl = null) {}
            public function clearNotifications() {}
        }
    }
    
    if (!class_exists('HardwareIdService')) {
        class HardwareIdService {
            public function getHardwareId() { return ''; }
            public function getHardwareFingerprint() { return []; }
        }
    }
}

// IDE helper for Illuminate\Support\Str
namespace Illuminate\Support {
    if (!class_exists('Str')) {
        class Str {
            public static function random($length = 16) { return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)))), 1, $length); }
        }
    }
}

// IDE helper for Illuminate\Console\Command
namespace Illuminate\Console {
    if (!class_exists('Command')) {
        class Command {
            public function error($message) {}
            public function info($message) {}
            public function line($message) {}
        }
    }
    
    // Service sınıfları için IDE helper'lar
    if (!class_exists('ConfigIntegrityService')) {
        class ConfigIntegrityService {
            public function verifyIntegrity() { return true; }
            public function compareWithVendorBackup() { return true; }
            public function decryptConfig() { return []; }
        }
    }
    
    if (!class_exists('RateLimiterService')) {
        class RateLimiterService {
            public function isRateLimited() { return false; }
            public function increment() { return 0; }
            public function clear() {}
            public function check() { return true; }
            public function reset() {}
        }
    }
    
    if (!class_exists('CacheService')) {
        class CacheService {
            public function getLicenseData() { return []; }
            public function setLicenseData() {}
            public function clearLicenseData() {}
            public function cacheLicenseStatus() {}
            /**
             * Önbellek ayarlarını günceller
             * 
             * @param array $config Önbellek ayarları
             * @return void
             */
            public function setConfig(array $config): void {}
        }
    }
    
    if (!class_exists('NotificationService')) {
        class NotificationService {
            public function getNotifications() { return []; }
            public function addNotification() {}
            public function clearNotifications() {}
            public function shouldShowNotification() { return false; }
            public function dismissNotification() {}
            public function getLicenseStatus() { return 'active'; }
        }
    }
    
    if (!class_exists('HardwareIdService')) {
        class HardwareIdService {
            public function getHardwareId() { return ''; }
            public function getHardwareFingerprint() { return []; }
            public function verifyHardwareId() { return true; }
            public function generateHardwareId() { return ''; }
        }
    }
    
    // Laravel Facades için IDE helper'lar
    if (!class_exists('Log')) {
        class Log {
            public static function error($message) {}
            public static function info($message) {}
            public static function warning($message) {}
            public static function debug($message) {}
        }
    }
    
    if (!class_exists('Lang')) {
        class Lang {
            public static function get($key, $replace = [], $locale = null) { return $key; }
            public static function has($key, $locale = null) { return false; }
            public static function choice($key, $number, $replace = [], $locale = null) { return $key; }
        }
    }
    
    if (!class_exists('Cache')) {
        class Cache {
            public static function get($key, $default = null) { return $default; }
            public static function put($key, $value, $ttl = null) { return true; }
            public static function has($key) { return false; }
            public static function forget($key) { return true; }
            public static function remember($key, $ttl, $callback) { return $callback(); }
            public static function pull($key, $default = null) { return $default; }
            public static function addDays($days) { return new \DateTime(); }
        }
    }
    
    if (!class_exists('Http')) {
        class Http {
            public static function get($url, $query = []) { 
                return new class {
                    public $statusCode = 200;
                    private $data = [];
                    
                    public function successful() { return $this->statusCode >= 200 && $this->statusCode < 300; }
                    
                    public function json($key = null, $default = null) {
                        if ($key === null) return $this->data;
                        return $this->data[$key] ?? $default;
                    }
                };
            }
            
            public static function post($url, $data = []) { 
                return new class {
                    public $statusCode = 200;
                    private $data = [];
                    
                    public function successful() { return $this->statusCode >= 200 && $this->statusCode < 300; }
                    
                    public function json($key = null, $default = null) {
                        if ($key === null) return $this->data;
                        return $this->data[$key] ?? $default;
                    }
                };
            }
        }
    }
    
    if (!class_exists('Exception')) {
        class Exception {
            public function getMessage() { return ''; }
        }
    }
}

if (!function_exists('now')) {
    function now() {
        return new class {
            public function addSeconds($seconds) { return $this; }
        };
    }
}

// IDE helpers for Laravel classes
if (!class_exists('Illuminate\Support\Facades\Cache')) {
    class_alias('ImTaxu\LaravelLicense\Helpers\CacheHelper', 'Illuminate\Support\Facades\Cache');
}

if (!class_exists('Illuminate\Support\Facades\Http')) {
    class_alias('ImTaxu\LaravelLicense\Helpers\HttpHelper', 'Illuminate\Support\Facades\Http');
}

if (!class_exists('Illuminate\Support\Facades\Log')) {
    class_alias('ImTaxu\LaravelLicense\Helpers\LogHelper', 'Illuminate\Support\Facades\Log');
}

// Use statements moved to the top

class LicenseChecker
{
    /**
     * License configuration
     *
     * @var array
     */
    protected $config;
    
    /**
     * Is fail-safe mode active?
     *
     * @var bool
     */
    protected $failSafeMode = false;
    
    /**
     * Config integrity service
     *
     * @var ConfigIntegrityService
     */
    protected $integrityService;
    
    /**
     * Rate limiter service
     *
     * @var RateLimiterService|null
     */
    protected $rateLimiter;
    
    /**
     * Cache service
     *
     * @var CacheService
     */
    protected $cacheService;
    
    /**
     * Notification service
     *
     * @var NotificationService
     */
    protected $notificationService;
    
    /**
     * Hardware ID service
     *
     * @var HardwareIdService
     */
    protected $hardwareIdService;

    /**
     * LicenseChecker constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->integrityService = new ConfigIntegrityService();
        
        // If config is encrypted, decrypt it
        if (isset($config['_encrypted']) && $config['_encrypted'] === true) {
            $this->config = $this->integrityService->decryptConfig($config);
        } else {
            $this->config = $config;
        }
        
        // Fill fail-safe settings with default values
        if (!isset($this->config['fail_safe'])) {
            $this->config['fail_safe'] = [
                'enabled' => true,                // Is fail-safe mode active?
                'grace_period' => 86400,         // Grace period when server is unreachable (seconds) - 24 hours
                'last_valid_check_key' => 'license_last_valid_check',  // Cache key for last valid check
                'check_attempts_key' => 'license_check_attempts'       // Cache key for failed check attempts
            ];
        }
        
        // Initialize rate limiter service
        if (isset($this->config['rate_limiting']) && is_array($this->config['rate_limiting'])) {
            $this->rateLimiter = new RateLimiterService($this->config['rate_limiting']);
        } else {
            // Initialize with default settings
            $this->rateLimiter = new RateLimiterService();
        }
        
        // Initialize cache service
        if (isset($this->config['cache']) && is_array($this->config['cache'])) {
            $this->cacheService = new CacheService($this->config['cache']);
        } else {
            // Initialize with default settings
            $this->cacheService = new CacheService();
        }
        
        // Initialize notification service
        if (isset($this->config['notification']) && is_array($this->config['notification'])) {
            $this->notificationService = new NotificationService($this->config['notification']);
        } else {
            // Initialize with default settings
            $this->notificationService = new NotificationService();
        }
        
        // Initialize hardware ID service
        if (isset($this->config['hardware_id']) && is_array($this->config['hardware_id'])) {
            $this->hardwareIdService = new HardwareIdService($this->config['hardware_id']);
        } else {
            // Initialize with default settings (disabled by default)
            $this->hardwareIdService = new HardwareIdService();
        }
    }

    /**
     * Perform license check
     *
     * @return bool
     */
    public function check(): bool
    {
        // Check config integrity - This step prevents manipulation
        if (!$this->verifyConfigIntegrity()) {
            Log::error('License configuration file failed integrity check.');
            $this->cacheLicenseStatus(false);
            return false;
        }
        
        // Rate limiting check
        if ($this->rateLimiter) {
            $ipAddress = $this->config['variables']['ip'] ?? '0.0.0.0';
            $licenseKey = $this->config['license_key'];
            $rateLimitKey = "license_check:{$licenseKey}";
            
            $rateLimitCheck = $this->rateLimiter->check($rateLimitKey, $ipAddress);
            
            // If rate limit is exceeded
            if (is_array($rateLimitCheck) && isset($rateLimitCheck['limited']) && $rateLimitCheck['limited']) {
                Log::warning("Rate limit exceeded. IP: {$ipAddress}, License: {$licenseKey}");
                $this->cacheLicenseStatus(false);
                return false;
            }
        }
        
        // Use license status from cache if available
        $licenseStatus = $this->cacheService->getLicenseData($this->config['license_key']);
        if ($licenseStatus !== null) {
            // Use status from cache
            return $licenseStatus['status'];
        }

        try {
            // Generate client signature - This is used to detect manipulation
            $clientSignature = $this->generateClientSignature();
            
            // Prepare API request
            $requestData = $this->prepareData();
            $requestData['client_signature'] = $clientSignature;
            
            // Add hardware ID if hardware ID check is active
            if ($this->hardwareIdService && $this->config['hardware_id']['enabled'] ?? false) {
                $hardwareId = $this->hardwareIdService->generateHardwareId();
                if ($hardwareId) {
                    $requestData['hardware_id'] = $hardwareId;
                }
            }
            
            // Fail-safe: Check number of failed attempts
            $failSafeEnabled = $this->config['fail_safe']['enabled'] ?? true;
            $checkAttemptsKey = $this->config['fail_safe']['check_attempts_key'];
            $failedAttempts = Cache::get($checkAttemptsKey, 0);
            
            // Send API request
            $response = Http::post($this->config['api_url'], $requestData);
            
            // Successful response check
            if ($response->successful()) {
                $responseData = $response->json();
                
                // Use excluded_ips list from server
                if (isset($responseData['data']['excluded_ips'])) {
                    // Use list from server, not local config
                    $this->config['excluded_ips'] = $responseData['data']['excluded_ips'];
                }
                
                // Use rate_limiting settings from server
                if (isset($responseData['data']['rate_limiting']) && is_array($responseData['data']['rate_limiting'])) {
                    $this->config['rate_limiting'] = $responseData['data']['rate_limiting'];
                    // Update rate limiter service
                    $this->rateLimiter->setConfig($this->config['rate_limiting']);
                }
                
                // Check if license is valid
                if ($responseData['status'] === 'success') {
                    // Reset rate limiter after successful verification
                    if ($this->rateLimiter) {
                        $ipAddress = $this->config['variables']['ip'] ?? '0.0.0.0';
                        $licenseKey = $this->config['license_key'];
                        $rateLimitKey = "license_check:{$licenseKey}";
                        $this->rateLimiter->reset($rateLimitKey, $ipAddress);
                    }
                    
                    // Cache license information
                    $this->cacheService->cacheLicenseStatus(
                        $this->config['license_key'],
                        $responseData['data'] ?? [],
                        true
                    );
                    
                    // Fail-safe: Save last valid check time and reset failed attempt count
                    if ($failSafeEnabled) {
                        $lastValidCheckKey = $this->config['fail_safe']['last_valid_check_key'];
                        Cache::put($lastValidCheckKey, time(), 30 * 24 * 60 * 60); // Store for 30 days (in seconds)
                        Cache::put($checkAttemptsKey, 0, 30 * 24 * 60 * 60); // Reset failed attempts (in seconds)
                        $this->failSafeMode = false; // Turn off fail-safe mode
                    }
                    
                    // Use check_frequency value from server
                    if (isset($responseData['data']['check_frequency'])) {
                        $this->config['check_frequency'] = $responseData['data']['check_frequency'];
                        $this->cacheService->setConfig(['default_cache_time' => $responseData['data']['check_frequency']]);
                    }
                    
                    // Use cache settings from server
                    if (isset($responseData['data']['cache']) && is_array($responseData['data']['cache'])) {
                        $this->config['cache'] = $responseData['data']['cache'];
                        // Update cache service
                        $this->cacheService->setConfig($this->config['cache']);
                    }
                    
                    // Use fail-safe settings from server
                    if (isset($responseData['data']['fail_safe']) && is_array($responseData['data']['fail_safe'])) {
                        $this->config['fail_safe'] = array_merge($this->config['fail_safe'], $responseData['data']['fail_safe']);
                    }
                    
                    // Use hardware ID settings from server
                    if (isset($responseData['data']['hardware_id']) && is_array($responseData['data']['hardware_id'])) {
                        if (!isset($this->config['hardware_id'])) {
                            $this->config['hardware_id'] = [];
                        }
                        $this->config['hardware_id'] = array_merge($this->config['hardware_id'], $responseData['data']['hardware_id']);
                        // Update hardware ID service
                        $this->hardwareIdService->setConfig($this->config['hardware_id']);
                    }
                    
                    // Hardware ID verification
                    if ($this->hardwareIdService && ($this->config['hardware_id']['enabled'] ?? false) && isset($responseData['data']['stored_hardware_id'])) {
                        $storedHardwareId = $responseData['data']['stored_hardware_id'];
                        if (!$this->hardwareIdService->verifyHardwareId($storedHardwareId)) {
                            Log::error(Lang::get('license::messages.hardware_id_verification_failed'));
                            $this->cacheService->cacheLicenseStatus(
                                $this->config['license_key'],
                                $responseData['data'] ?? [],
                                false
                            );
                            return false;
                        }
                    }
                    
                    return true;
                }
            }
            
            // In case of failed response
            Log::error(Lang::get('license::messages.license_invalid') . ': ' . ($response->json('message') ?? 'Unknown error'));
            
            // Cache license information (as invalid)
            $this->cacheService->cacheLicenseStatus(
                $this->config['license_key'],
                $response->json('data') ?? [],
                false
            );
            
            // Fail-safe: Reset failed attempt counter (this is a valid response, just the license is invalid)
            if ($failSafeEnabled) {
                Cache::put($checkAttemptsKey, 0, 30 * 24 * 60 * 60); // 30 gün (saniye cinsinden)
                $this->failSafeMode = false; // Turn off fail-safe mode
            }
            
            return false;
        } catch (Exception $e) {
            Log::error(Lang::get('license::messages.license_check_error', ['message' => $e->getMessage()]));
            
            // When API is unreachable or an error occurs
            // Consider license valid if in development environment
            if (app()->environment('local', 'development', 'testing')) {
                return true;
            }
            
            // Fail-safe mode check
            if ($failSafeEnabled) {
                // Increment failed attempt counter
                $failedAttempts++;
                Cache::put($checkAttemptsKey, $failedAttempts, 30 * 24 * 60 * 60); // 30 gün (saniye cinsinden)
                
                // Get last valid check time
                $lastValidCheckKey = $this->config['fail_safe']['last_valid_check_key'];
                $lastValidCheck = Cache::get($lastValidCheckKey);
                
                // If there was a valid check before and we're within the grace period
                if ($lastValidCheck !== null) {
                    $gracePeriod = $this->config['fail_safe']['grace_period'];
                    $graceEndTime = $lastValidCheck + $gracePeriod;
                    
                    if (time() < $graceEndTime) {
                        // We're within grace period, consider license valid
                        Log::warning('Fail-safe mode active: License server unreachable, temporarily considering license valid. Time remaining: ' . ($graceEndTime - time()) . ' seconds');
                        $this->failSafeMode = true;
                        return true;
                    }
                }
            }
            
            // In production environment and fail-safe mode not valid, consider license invalid
            $this->cacheService->cacheLicenseStatus(
                $this->config['license_key'],
                ['error' => $e->getMessage()],
                false
            );
            
            return false;
        }
    }
    
    /**
     * Check the integrity of the config file
     *
     * @return bool
     */
    protected function verifyConfigIntegrity(): bool
    {
        // Skip integrity check in development environment
        if (app()->environment('local', 'development', 'testing')) {
            return true;
        }
        
        // Check config integrity
        if (!$this->integrityService->verifyIntegrity()) {
            return false;
        }
        
        // Compare with vendor backup file
        if (!$this->integrityService->compareWithVendorBackup()) {
            return false;
        }
        
        return true;
    }

    /**
     * Cache license status
     * 
     * @deprecated This method is no longer used, use CacheService::cacheLicenseStatus() instead
     * @param bool $status
     * @return void
     */
    protected function cacheLicenseStatus(bool $status): void
    {
        // Kept for backward compatibility
        Cache::put('license_status', $status, now()->addSeconds($this->config['check_frequency']));
    }
    
    /**
     * Is fail-safe mode active?
     * 
     * @return bool
     */
    public function isInFailSafeMode(): bool
    {
        return $this->failSafeMode;
    }
    
    /**
     * Returns fail-safe mode information
     * 
     * @return array|null
     */
    public function getFailSafeInfo(): ?array
    {
        if (!$this->failSafeMode) {
            return null;
        }
        
        $lastValidCheckKey = $this->config['fail_safe']['last_valid_check_key'];
        $lastValidCheck = Cache::get($lastValidCheckKey);
        $gracePeriod = $this->config['fail_safe']['grace_period'];
        $graceEndTime = $lastValidCheck + $gracePeriod;
        $remainingTime = $graceEndTime - time();
        
        return [
            'active' => true,
            'last_valid_check' => $lastValidCheck,
            'grace_period' => $gracePeriod,
            'grace_end_time' => $graceEndTime,
            'remaining_time' => $remainingTime > 0 ? $remainingTime : 0,
            'remaining_hours' => round($remainingTime / 3600, 1)
        ];
    }
    
    /**
     * Lisans özelliklerini (features) alır
     * 
     * Bu metot, lisansın JSON formatında saklanmış özelliklerini (features) döndürür.
     * Özellikler alanı tamamen opsiyoneldir ve lisansın özel yeteneklerini tanımlamak için kullanılır.
     * 
     * Örnek özellikler:
     * - premium_access: Premium özelliklere erişim izni (boolean)
     * - max_users: Sisteme eklenebilecek maksimum kullanıcı sayısı (integer)
     * - modules: Erişim izni olan modüllerin listesi (array)
     * - storage_limit: Depolama alanı limiti (string)
     * - api_rate_limit: API istek limiti (integer)
     * 
     * @return array|null JSON formatındaki lisans özellikleri veya null (özellikler tanımlanmamışsa)
     */
    public function getLicenseFeatures(): ?array
    {
        // Lisans verilerini önbellekten al
        $licenseData = $this->cacheService->getLicenseData($this->config['license_key']);
        
        // Lisans verileri yoksa veya geçersizse null döndür
        if (!$licenseData || !isset($licenseData['data']) || !isset($licenseData['data']['features'])) {
            return null;
        }
        
        // JSON formatındaki features alanını diziye dönüştür
        $features = $licenseData['data']['features'];
        if (is_string($features)) {
            $features = json_decode($features, true);
        }
        
        return $features;
    }
    
    /**
     * Belirli bir lisans özelliğinin değerini kontrol eder
     * 
     * Bu metot, belirli bir lisans özelliğinin değerini döndürür. Eğer özellik bulunamazsa
     * veya lisansın features alanı tanımlanmamışsa, varsayılan değer döndürülür.
     * 
     * Kullanım örnekleri:
     * 1. Kullanıcı sayısı kontrolü:
     *    `if (count($users) < $license->hasFeature('max_users', 10)) { // Yeni kullanıcı ekle }`
     * 
     * 2. Depolama alanı kontrolü:
     *    `if ($fileSize + $currentUsage < parseSize($license->hasFeature('storage_limit', '1GB'))) { // Dosyayı yükle }`
     * 
     * 3. Premium erişim kontrolü:
     *    `if ($license->hasFeature('premium_access')) { // Premium özellikleri göster }`
     * 
     * @param string $featureName Özellik adı (max_users, storage_limit, premium_access vb.)
     * @param mixed $defaultValue Özellik bulunamazsa döndürülecek varsayılan değer
     * @return mixed Özellik değeri veya varsayılan değer
     */
    public function hasFeature(string $featureName, $defaultValue = false)
    {
        $features = $this->getLicenseFeatures();
        
        if ($features === null) {
            return $defaultValue;
        }
        
        return $features[$featureName] ?? $defaultValue;
    }
    
    /**
     * Lisansın belirli bir modüle erişimi olup olmadığını kontrol eder
     * 
     * Bu metot, lisansın belirli bir modüle erişimi olup olmadığını kontrol eder.
     * Modül erişimi iki şekilde tanımlanabilir:
     * 
     * 1. `modules` dizisi içinde modül adının bulunması:
     *    ```json
     *    {
     *      "modules": ["reporting", "analytics", "export"]
     *    }
     *    ```
     * 
     * 2. Modül adının doğrudan bir özellik olarak tanımlanması ve değerinin true olması:
     *    ```json
     *    {
     *      "reporting": true,
     *      "analytics": false
     *    }
     *    ```
     * 
     * Kullanım örneği:
     * ```php
     * if ($license->hasModuleAccess('reporting')) {
     *     // Raporlama modülünü göster
     * } else {
     *     // Erişim yok mesajı göster
     * }
     * ```
     * 
     * @param string $moduleName Modül adı (reporting, analytics, export vb.)
     * @return bool Erişim izni var mı?
     */
    public function hasModuleAccess(string $moduleName): bool
    {
        $features = $this->getLicenseFeatures();
        
        if ($features === null) {
            return false;
        }
        
        // Modüller dizisi varsa kontrol et
        if (isset($features['modules']) && is_array($features['modules'])) {
            return in_array($moduleName, $features['modules']);
        }
        
        // Modül adında bir özellik varsa ve true ise erişim ver
        if (isset($features[$moduleName]) && $features[$moduleName] === true) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Checks how many days are left until the license expires and returns notification information if a notification should be displayed
     * 
     * @return array|null Notification information or null (if no notification should be displayed)
     */
    public function getLicenseExpiryNotification(): ?array
    {
        // Return null if notification service doesn't exist
        if (!$this->notificationService) {
            return null;
        }
        
        try {
            // Get license information from cache
            $licenseData = $this->cacheService->getLicenseData($this->config['license_key']);
            
            // Return null if license information doesn't exist or is invalid
            if (!$licenseData || !isset($licenseData['expires_at'])) {
                return null;
            }
            
            // Check if notification should be displayed
            $result = $this->notificationService->shouldShowNotification(
                $this->config['license_key'],
                $licenseData['expires_at']
            );
            
            return $result ? (array)$result : null;
        } catch (Exception $e) {
            Log::error('Error during license expiry check: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Dismiss license renewal notification
     * 
     * @param string $notificationKey Notification key
     * @param int|null $nextThreshold Next threshold to display (days)
     * @return bool Was the operation successful?
     */
    public function dismissLicenseNotification(string $notificationKey, ?int $nextThreshold = null): bool
    {
        if (!$this->notificationService) {
            return false;
        }
        
        $result = $this->notificationService->dismissNotification($notificationKey, $nextThreshold);
        return $result === true || $result === 1 || $result === '1';
    }

    /**
     * Prepare data to be sent for license check
     *
     * @return array
     */
    protected function prepareData(): array
    {
        $data = [
            'license_key' => $this->config['license_key'],
            'domain' => $this->config['variables']['domain'] ?? '',
            'ip' => $this->config['variables']['ip'] ?? '',
            'client_signature' => $this->generateClientSignature(),
        ];
        
        // Add other variables
        foreach ($this->config['variables'] as $key => $value) {
            if (!in_array($key, ['domain', 'ip'])) {
                $data[$key] = $value;
            }
        }
        
        return $data;
    }

    /**
     * Generate a unique signature for the client
     * This signature is used to detect license manipulation
     * 
     * @return string
     */
    protected function generateClientSignature(): string
    {
        // Generate a unique signature for the client
        // This consists of the config file hash, domain, IP address and other information
        $configHash = md5(json_encode($this->config));
        $domainInfo = $this->cleanDomain($this->config['variables']['domain'] ?? '');
        $ipInfo = $this->config['variables']['ip'] ?? '';
        $appInfo = $this->config['variables']['app_name'] ?? '';
        
        // If hardware ID check is active, include hardware ID in the signature
        $hardwareInfo = '';
        if ($this->hardwareIdService && ($this->config['hardware_id']['enabled'] ?? false)) {
            $hardwareId = $this->hardwareIdService->generateHardwareId();
            if ($hardwareId) {
                $hardwareInfo = $hardwareId;
            }
        }
        
        // Create a unique signature
        $signature = hash('sha256', $configHash . $domainInfo . $ipInfo . $appInfo . $hardwareInfo);
        
        return $signature;
    }
    
    /**
     * Cleans the domain (removes prefixes like http://, https://, www.)
     *
     * @param string $domain
     * @return string
     */
    protected function cleanDomain(string $domain): string
    {
        // Remove HTTP and HTTPS protocols
        $domain = str_replace(['http://', 'https://'], '', $domain);
        
        // Remove trailing slash
        $domain = rtrim($domain, '/');
        
        // Remove possible port number (e.g., example.com:8080)
        $domain = preg_replace('/:\d+$/', '', $domain);
        
        return strtolower($domain);
    }

    /**
     * Check if the current IP address is excluded
     *
     * @param string $ip
     * @return bool
     */
    public function isExcludedIp(string $ip): bool
    {
        // Use the excluded_ips list from the server
        // This prevents the user from changing excluded_ips locally
        return in_array($ip, $this->config['excluded_ips']);
    }

    /**
     * Check if the current route is excluded
     *
     * @param string $routeName
     * @return bool
     */
    public function isExcludedRoute(string $routeName): bool
    {
        return in_array($routeName, $this->config['excluded_routes']);
    }
}
