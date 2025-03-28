<?php

namespace Imtaxu\LaravelLicense;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class LicenseManager
{
    /**
     * The application instance.
     *
     * @var Application
     */
    protected $app;

    /**
     * License verification status.
     *
     * @var bool
     */
    protected bool $verified = false;

    /**
     * License key.
     *
     * @var string|null
     */
    protected ?string $licenseKey = null;

    /**
     * License data.
     *
     * @var array|null
     */
    protected ?array $licenseData = null;

    /**
     * Create a new License Manager instance.
     *
     * @param Application $app
     * @return void
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->licenseKey = config('license.key');
        $this->loadLicenseFromCache();

        // Check for file integrity if enabled
        if (config('license.integrity_check', true)) {
            $this->checkIntegrity();
        }
    }

    /**
     * Get the verification URL.
     *
     * @return string
     */
    protected function getVerificationUrl(): string
    {
        // Hard-coded endpoint definition for security
        $serverUrl = rtrim(config('license.server_url', 'https://your-domain.com/license-admin'), '/');
        $endpoint = '/api.php'; // Or whatever endpoint you're using

        // Optionally encode or obfuscate the endpoint
        return $serverUrl . $endpoint;
    }

    /**
     * Get the activation URL.
     *
     * @return string
     */
    protected function getActivationUrl(): string
    {
        // If activation_url is explicitly set, use it
        if (!empty(config('license.activation_url'))) {
            return config('license.activation_url');
        }

        // Otherwise, build URL from server_url
        $serverUrl = rtrim(config('license.server_url', 'https://your-domain.com/license-admin'), '/');
        return $serverUrl . '/api.php';
    }

    /**
     * Get the deactivation URL.
     *
     * @return string
     */
    protected function getDeactivationUrl(): string
    {
        // If deactivation_url is explicitly set, use it
        if (!empty(config('license.deactivation_url'))) {
            return config('license.deactivation_url');
        }

        // Otherwise, build URL from server_url
        $serverUrl = rtrim(config('license.server_url', 'https://your-domain.com/license-admin'), '/');
        return $serverUrl . '/index.php';
    }

    /**
     * Get the error URL.
     *
     * @return string
     */
    protected function getErrorUrl(): string
    {
        // If error_url is explicitly set, use it
        if (!empty(config('license.error_url'))) {
            return config('license.error_url');
        }

        // Otherwise, build URL from server_url
        $serverUrl = rtrim(config('license.server_url', 'https://your-domain.com/license-admin'), '/');
        return $serverUrl . '/index.php';
    }

    /**
     * Load license data from cache.
     *
     * @return void
     */
    protected function loadLicenseFromCache(): void
    {
        $licenseData = Cache::get('license_data');
        if ($licenseData) {
            $this->licenseData = $licenseData;
            $this->verified = $licenseData['verified'] ?? false;
        }
    }

    /**
     * Check if the license is valid.
     *
     * @return bool
     */
    public function isValid(): bool
    {
        if ($this->verified && $this->licenseData) {
            // Check if license has expired
            if (isset($this->licenseData['expires_at']) && now()->greaterThan($this->licenseData['expires_at'])) {
                return false;
            }

            // Check if we need to re-verify based on check interval
            $lastChecked = $this->licenseData['last_checked'] ?? null;
            $checkInterval = config('license.check_interval', 1440);

            if (!$lastChecked || now()->diffInMinutes($lastChecked) >= $checkInterval) {
                // Re-verify license
                return $this->verify();
            }

            return true;
        }

        return $this->verify();
    }

    /**
     * Clean response body from any HTML warnings or errors
     *
     * @param string $response
     * @return string
     */
    private function cleanResponseBody(string $response): string
    {
        // Remove any HTML content before JSON
        if (preg_match('/{.*}/s', $response, $matches)) {
            return $matches[0];
        }
        return $response;
    }

    /**
     * Verify license.
     *
     * @return bool
     */
    public function verify(): bool
    {
        if (empty($this->licenseKey)) {
            Log::warning('License key not provided.');
            return false;
        }

        try {
            $verificationUrl = $this->getVerificationUrl();
            Log::info("Verification URL: " . $verificationUrl);

            $requestData = [
                'license_key' => $this->licenseKey,
                'domain' => request()->getHost(),
                'ip' => request()->ip(),
                'version' => config('license.version', '1.0.0'),
                'instance_id' => $this->getInstanceId(),
                'action' => 'verify'
            ];
            Log::info("Request data: " . json_encode($requestData));

            $response = Http::timeout(5)->post($verificationUrl, $requestData);

            Log::info("Response status: " . $response->status());
            Log::info("Response body: " . $response->body());

            if ($response->successful()) {
                // Clean the response body before parsing as JSON
                $cleanBody = $this->cleanResponseBody($response->body());
                Log::info("Cleaned response body: " . $cleanBody);

                // Parse cleaned JSON
                $data = json_decode($cleanBody, true);
                Log::info("Parsed response data: " . json_encode($data));

                if (is_array($data) && isset($data['status']) && $data['status'] === 'success') {
                    $this->verified = true;
                    $this->licenseData = $data['data'] ?? [];
                    $this->licenseData['last_checked'] = now();
                    $this->licenseData['verified'] = true;

                    // Cache the license data
                    $cacheDuration = now()->addDays(config('license.cache_duration', 1));
                    Cache::put('license_data', $this->licenseData, $cacheDuration);

                    return true;
                }

                Log::warning('License verification failed: ' . ($data['message'] ?? 'Unknown error'));
            } else {
                Log::error('License server communication failed: ' . $response->status());
            }
        } catch (\Exception $e) {
            Log::error("Verification exception: " . $e->getMessage());
        }

        return false;
    }

    /**
     * Activate license.
     *
     * @return bool
     */
    public function activate(string $licenseKey): bool
    {
        try {
            $activationUrl = $this->getActivationUrl();
            Log::info("Activation URL: " . $activationUrl);

            $requestData = [
                'license_key' => $licenseKey,
                'domain' => request()->getHost(),
                'ip' => request()->ip(),
                'version' => config('license.version', '1.0.0'),
                'instance_id' => $this->getInstanceId(),
                'action' => 'activate'
            ];
            Log::info("Activation request data: " . json_encode($requestData));

            $response = Http::post($activationUrl, $requestData);

            Log::info("Response status: " . $response->status());
            Log::info("Response body: " . $response->body());

            if ($response->successful()) {
                // Clean the response body before parsing as JSON
                $cleanBody = $this->cleanResponseBody($response->body());
                Log::info("Cleaned response body: " . $cleanBody);

                // Parse cleaned JSON
                $data = json_decode($cleanBody, true);
                Log::info("Parsed response data: " . json_encode($data));

                if (is_array($data) && isset($data['status']) && $data['status'] === 'success') {
                    // Set license data on successful activation
                    $this->licenseKey = $licenseKey;
                    $this->verified = true;
                    $this->licenseData = $data['data'] ?? [];
                    $this->licenseData['last_checked'] = now();
                    $this->licenseData['verified'] = true;

                    // Explicitly set status to active
                    $this->licenseData['status'] = 'active';

                    // Cache the license data
                    $cacheDuration = now()->addDays(config('license.cache_duration', 1));
                    Cache::put('license_data', $this->licenseData, $cacheDuration);
                    return true;
                }

                Log::warning('License activation failed: ' . ($data['message'] ?? 'Unknown error'));
            } else {
                Log::error('License server communication failed: ' . $response->status());
            }
        } catch (\Exception $e) {
            Log::error("Activation exception: " . $e->getMessage());
        }

        return false;
    }

    /**
     * Deactivate license.
     *
     * @return bool
     */
    public function deactivate(): bool
    {
        if (empty($this->licenseKey)) {
            Log::warning('No license key found for deactivation.');
            return false;
        }

        try {
            $response = Http::post($this->getDeactivationUrl(), [
                'license_key' => $this->licenseKey,
                'domain' => request()->getHost(),
                'ip' => request()->ip(),
                'instance_id' => $this->getInstanceId(),
                'action' => 'deactivate'
            ]);

            if ($response->successful()) {
                $data = $response->json();

                if ($data['status'] === 'success') {
                    $this->verified = false;
                    $this->licenseData = null;

                    // Clear the cached license data
                    Cache::forget('license_data');

                    return true;
                }

                Log::warning('License deactivation failed: ' . ($data['message'] ?? 'Unknown error'));
            } else {
                Log::error('License server communication failed: ' . $response->status());
            }
        } catch (\Exception $e) {
            Log::error('License deactivation exception: ' . $e->getMessage());
        }

        return false;
    }

    /**
     * Get license data.
     *
     * @return array|null
     */
    public function getLicenseData(): ?array
    {
        return $this->licenseData;
    }

    /**
     * Get license key.
     *
     * @return string|null
     */
    public function getLicenseKey(): ?string
    {
        return $this->licenseKey;
    }

    /**
     * Generate a unique instance ID for this installation.
     *
     * @return string
     */
    protected function getInstanceId(): string
    {
        $instanceId = Cache::get('license_instance_id');

        if (!$instanceId) {
            // Generate a unique ID based on server information
            $instanceId = md5(
                implode('|', [
                    config('app.key'),
                    request()->getHost(),
                    $_SERVER['SERVER_ADDR'] ?? '',
                    $_SERVER['SERVER_NAME'] ?? '',
                    php_uname('n'),
                    Str::random(40)
                ])
            );

            Cache::forever('license_instance_id', $instanceId);
        }

        return $instanceId;
    }

    /**
     * Check package file integrity.
     *
     * @return bool
     */
    protected function checkIntegrity(): bool
    {
        // List of critical files to check
        $criticalFiles = [
            __FILE__,
            __DIR__ . '/LicenseServiceProvider.php',
            __DIR__ . '/../config/license.php',
            base_path('routes/web.php'),
            // Add more critical files here as needed
        ];

        // Path to the checksums file
        $checksumPath = storage_path('app/license_checksums.php');

        // If checksums file exists, verify files against stored checksums
        if (File::exists($checksumPath)) {
            try {
                $expectedChecksums = require $checksumPath;

                foreach ($criticalFiles as $file) {
                    if (!File::exists($file)) {
                        $this->handleIntegrityFailure("Critical file not found: {$file}");
                        return false;
                    }

                    $fileKey = $this->getFileKey($file);

                    // If we have an expected checksum for this file, verify it
                    if (isset($expectedChecksums[$fileKey])) {
                        $actualChecksum = md5_file($file);
                        if ($actualChecksum !== $expectedChecksums[$fileKey]) {
                            $this->handleIntegrityFailure("File integrity check failed for: {$file}");
                            return false;
                        }
                    }
                }
            } catch (\Exception $e) {
                $this->handleIntegrityFailure("Integrity check exception: " . $e->getMessage());
                return false;
            }
        } else {
            // If checksums file doesn't exist yet, just check file existence
            foreach ($criticalFiles as $file) {
                if (!File::exists($file)) {
                    $this->handleIntegrityFailure("Critical file not found: {$file}");
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Generate a normalized key for a file path
     *
     * @param string $filePath
     * @return string
     */
    private function getFileKey(string $filePath): string
    {
        // Generate a consistent key from file path for checksum lookup
        return str_replace(['\\', '/', '.', ':', ' '], '_', $filePath);
    }

    /**
     * Handle integrity check failure.
     *
     * @param string $reason
     * @return void
     */
    protected function handleIntegrityFailure(string $reason): void
    {
        Log::error("License integrity check failed: {$reason}");

        if (config('license.redirect_on_error', true)) {
            // Clear cached data to force re-verification
            Cache::forget('license_data');
            $this->verified = false;

            // Forcefully invalidate the license when integrity check fails
            $this->licenseData = null;

            // Option to trigger a security notification
            $this->reportError("Integrity violation detected: {$reason}");

            // Handle redirect logic either through middleware or directly
            if (app()->runningInHttp()) {
                // First check if route exists, otherwise redirect to custom error url
                if (Route::has('license.error')) {
                    abort(redirect()->route('license.error'));
                } else {
                    // Fallback to a direct URL if route doesn't exist
                    abort(redirect(config('license.routes_prefix', 'license') . '/error'));
                }
            }
        }
    }

    /**
     * Report license error to server.
     *
     * @param string $message
     * @return void
     */
    public function reportError(string $message): void
    {
        try {
            Http::post($this->getErrorUrl(), [
                'license_key' => $this->licenseKey,
                'domain' => request()->getHost(),
                'ip' => request()->ip(),
                'instance_id' => $this->getInstanceId(),
                'message' => $message,
                'timestamp' => now()->toIso8601String(),
                'action' => 'reportError'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to report license error: ' . $e->getMessage());
        }
    }
}
