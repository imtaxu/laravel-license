<?php

namespace Imtaxu\LaravelLicense\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Imtaxu\LaravelLicense\Services\LicenseVerificationService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

class LicenseController extends Controller
{
    /**
     * License verification service
     */
    protected $licenseService;

    /**
     * Constructor
     */
    public function __construct(LicenseVerificationService $licenseService)
    {
        $this->licenseService = $licenseService;
    }

    /**
     * Show license error page
     */
    public function showError()
    {
        return view('license-manager::error', [
            'licenseKey' => Config::get('license.key'),
            'domain' => request()->getHost(),
            'supportEmail' => Config::get('license.support_email', 'your@email.com')
        ]);
    }

    /**
     * Show license activation page
     */
    public function showActivate()
    {
        return view('license-manager::activate', [
            'licenseKey' => Config::get('license.key'),
            'domain' => request()->getHost()
        ]);
    }

    /**
     * Activate license
     */
    public function activate(Request $request)
    {
        $request->validate([
            'license_key' => 'required|string'
        ]);

        $licenseKey = $request->input('license_key');
        $domain = request()->getHost();

        try {
            $response = Http::post(Config::get('license.api_url'), [
                'page' => 'licenses',
                'action' => 'activate',
                'license_key' => $licenseKey,
                'domain' => $domain,
            ]);

            if ($response->successful()) {
                $data = $response->json();

                if ($data['status'] === 'success') {
                    // Save license key to config
                    $this->updateLicenseConfig($licenseKey);

                    // Clear cache
                    $this->licenseService->clearCache();

                    // Re-store file hashes
                    $this->licenseService->storeFileHashes();

                    return redirect()->route('home')->with('success', 'Lisans başarıyla aktifleştirildi!');
                }

                return back()->with('error', $data['message'] ?? 'Lisans aktifleştirilemedi.');
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Lisans sunucusuyla bağlantı kurulamadı: ' . $e->getMessage());
        }

        return back()->with('error', 'Lisans aktivasyonu başarısız oldu.');
    }

    /**
     * Update license settings
     */
    private function updateLicenseConfig($licenseKey)
    {
        $configPath = config_path('license.php');
        $config = file_get_contents($configPath);

        // Update license key
        $updatedConfig = preg_replace(
            "/'key'\s*=>\s*'[^']*'/",
            "'key' => '{$licenseKey}'",
            $config
        );

        file_put_contents($configPath, $updatedConfig);
    }

    /**
     * Manually check license
     */
    public function checkLicense()
    {
        $isValid = $this->licenseService->isValid();

        return response()->json([
            'status' => $isValid ? 'active' : 'inactive',
            'license_key' => Config::get('license.key'),
            'domain' => request()->getHost(),
            'file_integrity' => $this->licenseService->verifyFileIntegrity(),
            'license_status' => $this->licenseService->verifyLicenseStatus(),
        ]);
    }

    /**
     * Clear license cache
     */
    public function clearCache()
    {
        $this->licenseService->clearCache();
        return redirect()->back()->with('success', 'Lisans önbelleği temizlendi.');
    }

    /**
     * Show license status page
     */
    public function status()
    {
        return view('license-manager::status', [
            'licenseKey' => Config::get('license.key'),
            'domain' => request()->getHost(),
            'isValid' => $this->licenseService->isValid(),
            'fileIntegrity' => $this->licenseService->verifyFileIntegrity(),
            'licenseStatus' => $this->licenseService->verifyLicenseStatus(),
        ]);
    }

    /**
     * Deactivate license
     */
    public function deactivate(Request $request)
    {
        try {
            $response = Http::post(Config::get('license.api_url'), [
                'page' => 'licenses',
                'action' => 'deactivate',
                'license_key' => Config::get('license.key'),
                'domain' => request()->getHost(),
            ]);

            if ($response->successful()) {
                $data = $response->json();

                if ($data['status'] === 'success') {
                    // Clear cache
                    $this->licenseService->clearCache();

                    return back()->with('success', 'Lisans başarıyla devre dışı bırakıldı!');
                }

                return back()->with('error', $data['message'] ?? 'Lisans devre dışı bırakılamadı.');
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Lisans sunucusuyla bağlantı kurulamadı: ' . $e->getMessage());
        }

        return back()->with('error', 'Lisans devre dışı bırakma işlemi başarısız oldu.');
    }
}
