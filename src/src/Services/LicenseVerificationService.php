<?php

namespace Imtaxu\LaravelLicense\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Artisan;

class LicenseVerificationService
{
    /**
     * License server API URL
     */
    protected $apiUrl;

    /**
     * License key
     */
    protected $licenseKey;

    /**
     * Files to verify integrity
     */
    protected $filesToVerify = [
        'config/license.php',
        'vendor/imtaxu/laravel-license/src/Middleware/LicenseMiddleware.php',
        'vendor/imtaxu/laravel-license/src/Services/LicenseVerificationService.php',
        'vendor/imtaxu/laravel-license/src/Controllers/LicenseController.php',
        'vendor/imtaxu/laravel-license/routes/web.php',
        'vendor/imtaxu/laravel-license/src/LicenseServiceProvider.php',
    ];

    /**
     * Cache key for file hashes
     */
    protected const FILE_HASH_CACHE_KEY = 'imtaxu_license_file_hashes';

    /**
     * Cache key for license status
     */
    protected const LICENSE_STATUS_CACHE_KEY = 'imtaxu_license_status';

    /**
     * Cache duration in minutes
     */
    protected const CACHE_DURATION = 60;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->apiUrl = config('license.api_url', 'https://your-domain.com/license-admin/api.php');
        $this->licenseKey = config('license.key');
    }

    /**
     * Verify license status from remote server
     *
     * @return bool True if license is active, false otherwise
     */
    public function verifyLicenseStatus()
    {
        // Check from cache first
        if (Cache::has(self::LICENSE_STATUS_CACHE_KEY)) {
            $status = Cache::get(self::LICENSE_STATUS_CACHE_KEY);

            // Only 'active' status is valid
            return $status === 'active';
        }

        try {
            // Burada POST kullanın ve parametreleri sadeleştirin
            $response = Http::post($this->apiUrl, [
                'license_key' => $this->licenseKey,
                'domain' => request()->getHost(),
                'ip' => request()->ip(),
                'action' => 'verify'
            ]);

            if ($response->successful()) {
                $data = $response->json();

                // Başarılı yanıt varsa status değerini ayarlayın
                if (isset($data['status']) && $data['status'] === 'success') {
                    // Cache'e 'active' olarak kaydedin
                    Cache::put(self::LICENSE_STATUS_CACHE_KEY, 'active', self::CACHE_DURATION);
                    return true;
                } else {
                    $status = 'inactive';
                    Cache::put(self::LICENSE_STATUS_CACHE_KEY, $status, self::CACHE_DURATION);
                    $this->handleInvalidLicense('License is not active: ' . $status);
                    return false;
                }
            }
        } catch (\Exception $e) {
            Log::error('License verification error: ' . $e->getMessage());
        }

        return false;
    }

    /**
     * Verify file integrity
     *
     * @return bool True if all files are verified, false otherwise
     */
    public function verifyFileIntegrity()
    {
        // On first use, store file hashes
        if (!$this->hasStoredHashes()) {
            $this->storeFileHashes();
            return true;
        }

        $storedHashes = Cache::get(self::FILE_HASH_CACHE_KEY, []);

        foreach ($this->filesToVerify as $filePath) {
            $fullPath = base_path($filePath);

            // If file doesn't exist
            if (!File::exists($fullPath)) {
                $this->handleInvalidLicense("License file missing: {$filePath}");
                return false;
            }

            // If file hash has changed
            $currentHash = hash_file('sha256', $fullPath);
            if (!isset($storedHashes[$filePath]) || $storedHashes[$filePath] !== $currentHash) {
                $this->handleInvalidLicense("License file modified: {$filePath}");
                return false;
            }
        }

        return true;
    }

    /**
     * Check if we have stored hashes
     *
     * @return bool True if hashes are stored, false otherwise
     */
    public function hasStoredHashes()
    {
        return Cache::has(self::FILE_HASH_CACHE_KEY);
    }

    /**
     * Store file hashes
     */
    public function storeFileHashes()
    {
        $hashes = [];

        foreach ($this->filesToVerify as $filePath) {
            $fullPath = base_path($filePath);

            if (File::exists($fullPath)) {
                $hashes[$filePath] = hash_file('sha256', $fullPath);
            }
        }

        Cache::put(self::FILE_HASH_CACHE_KEY, $hashes, self::CACHE_DURATION * 24); // Keep for 24 hours
    }

    /**
     * Handle invalid license (file tampered or license inactive)
     *
     * @param string $reason Reason for invalidity
     */
    protected function handleInvalidLicense($reason)
    {
        Log::warning("License validation failed: {$reason}");

        // Clear application cache to ensure changes take effect
        try {
            Artisan::call('cache:clear');
        } catch (\Exception $e) {
            Log::error('Failed to clear cache: ' . $e->getMessage());
        }
    }

    /**
     * General validation check
     *
     * @return bool True if license and files are valid, false otherwise
     */
    public function isValid()
    {
        // First check file integrity - this is faster than network request
        $fileIntegrityValid = $this->verifyFileIntegrity();

        // Only check license status if files are valid
        if ($fileIntegrityValid) {
            return $this->verifyLicenseStatus();
        }

        return false;
    }

    /**
     * Clear cache
     */
    public function clearCache()
    {
        Cache::forget(self::LICENSE_STATUS_CACHE_KEY);
        Cache::forget(self::FILE_HASH_CACHE_KEY);
    }

    /**
     * Force license recheck by clearing cache and checking again
     *
     * @return bool True if license is valid, false otherwise
     */
    public function forceRecheck()
    {
        $this->clearCache();
        return $this->isValid();
    }
}
