<?php

namespace ImTaxu\LaravelLicense\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Exception;

// IDE helpers for functions provided by Laravel
if (!function_exists('app')) {
    function app() {
        return new class {
            public function basePath($path = '') { return __DIR__ . '/../../../' . $path; }
            public function environment(...$args) { return in_array('local', $args); }
            public function make($class) { return null; }
        };
    }
}

// IDE helper for Laravel Facades
namespace Illuminate\Support\Facades {
    if (!class_exists('File')) {
        class File {
            public static function exists($path) { return file_exists($path); }
            public static function get($path) { return file_get_contents($path); }
            public static function put($path, $contents) { return file_put_contents($path, $contents); }
        }
    }
    
    if (!class_exists('Log')) {
        class Log {
            public static function error($message) {}
            public static function info($message) {}
            public static function warning($message) {}
            public static function debug($message) {}
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
}

// Global namespace functions
namespace {
    if (!function_exists('app')) {
        function app() {
            return new class {
                public function basePath($path = '') { return __DIR__ . '/../../../' . $path; }
                public function environment(...$args) { return in_array('local', $args); }
                public function make($class) { return null; }
            };
        }
    }
    
    if (!function_exists('config_path')) {
        function config_path($path = '') {
            return app()->basePath('config') . ($path ? '/' . $path : '');
        }
    }
}

// IDE helper for Exception
namespace {
    if (!class_exists('\Exception')) {
        class Exception extends \Exception {}
    }
}

if (!function_exists('config')) {
    function config($key = null, $default = null) {
        if ($key === 'license.api_url') {
            return 'https://license.example.com/api/verify';
        }
        return $default;
    }
}

// IDE helpers for Laravel classes
if (!class_exists('Illuminate\Support\Facades\File')) {
    class_alias('ImTaxu\LaravelLicense\Helpers\FileHelper', 'Illuminate\Support\Facades\File');
}

if (!class_exists('Illuminate\Support\Facades\Log')) {
    class_alias('ImTaxu\LaravelLicense\Helpers\LogHelper', 'Illuminate\Support\Facades\Log');
}

if (!class_exists('Illuminate\Support\Facades\Http')) {
    class_alias('ImTaxu\LaravelLicense\Helpers\HttpHelper', 'Illuminate\Support\Facades\Http');
}

namespace ImTaxu\LaravelLicense\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Exception;

class ConfigIntegrityService
{
    /**
     * Config dosyasının bütünlüğünü kontrol et
     *
     * @return bool
     */
    public function verifyIntegrity(): bool
    {
        $configPath = config_path('license.php');
        
        // Config dosyası yoksa veya okunamıyorsa
        if (!File::exists($configPath) || !is_readable($configPath)) {
            Log::error('Lisans yapılandırma dosyası bulunamadı veya okunamadı.');
            return false;
        }
        
        // Config dosyasını yükle
        $config = require $configPath;
        
        // Şifrelenmiş config değilse, bütünlük kontrolü yapma
        if (!isset($config['_encrypted']) || $config['_encrypted'] !== true) {
            // Şifrelenmemiş config dosyası için bütünlük kontrolü yapmıyoruz
            // Bu durumda kullanıcı henüz şifreleme yapmamış olabilir
            return true;
        }
        
        // Gerekli alanlar eksikse
        if (!isset($config['_data']) || !isset($config['_checksum']) || !isset($config['_key'])) {
            Log::error('Lisans yapılandırma dosyası bozuk veya eksik.');
            return false;
        }
        
        // Checksum kontrolü yap
        $calculatedChecksum = md5($config['_data'] . $config['_key']);
        
        if ($calculatedChecksum !== $config['_checksum']) {
            Log::error('Lisans yapılandırma dosyası değiştirilmiş veya bozulmuş.');
            return false;
        }
        
        return true;
    }
    
    /**
     * Şifrelenmiş config'i çöz
     *
     * @param array $encryptedConfig
     * @return array
     */
    public function decryptConfig(array $encryptedConfig): array
    {
        // Şifrelenmiş değilse, olduğu gibi döndür
        if (!isset($encryptedConfig['_encrypted']) || $encryptedConfig['_encrypted'] !== true) {
            return $encryptedConfig;
        }
        
        // Şifrelenmiş veriyi ve anahtarı al
        $encryptedData = $encryptedConfig['_data'];
        $key = $encryptedConfig['_key'];
        
        // Base64 decode
        $decoded = base64_decode($encryptedData);
        
        // XOR şifre çözme
        $decrypted = '';
        $keyLength = strlen($key);
        
        for ($i = 0; $i < strlen($decoded); $i++) {
            $decrypted .= $decoded[$i] ^ $key[$i % $keyLength];
        }
        
        // JSON'dan array'e çevir
        $configArray = json_decode($decrypted, true);
        
        // Çözme başarısız olursa
        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('Lisans yapılandırma dosyası çözülemedi: ' . json_last_error_msg());
            return [];
        }
        
        return $configArray;
    }
    
    /**
     * Vendor klasöründeki yedek config ile karşılaştır
     *
     * @return bool
     */
    public function compareWithVendorBackup(): bool
    {
        $configPath = config_path('license.php');
        $vendorBackupPath = __DIR__ . '/../../config/license.php.obfuscated';
        
        // Vendor yedek dosyası yoksa
        if (!File::exists($vendorBackupPath)) {
            // İlk kurulum olabilir, bu durumda karşılaştırma yapmıyoruz
            return true;
        }
        
        // Config dosyası yoksa
        if (!File::exists($configPath)) {
            Log::error('Lisans yapılandırma dosyası bulunamadı.');
            return false;
        }
        
        // İki dosyanın içeriğini karşılaştır
        $configContent = File::get($configPath);
        $vendorBackupContent = File::get($vendorBackupPath);
        
        // Eğer dosyalar farklıysa
        if ($configContent !== $vendorBackupContent) {
            // Config dosyası değiştirilmiş olabilir
            $config = require $configPath;
            
            // Şifrelenmiş config değilse, kullanıcı henüz şifreleme yapmamış olabilir
            if (!isset($config['_encrypted']) || $config['_encrypted'] !== true) {
                return true;
            }
            
            // Şifrelenmiş ama farklıysa, dosya değiştirilmiş demektir
            Log::error('Lisans yapılandırma dosyası değiştirilmiş.');
            return false;
        }
        
        return true;
    }
    
    /**
     * Sunucu tarafından excluded_ips listesini al
     *
     * @param string $licenseKey
     * @param array $variables
     * @return array|null
     */
    public function fetchExcludedIpsFromServer(string $licenseKey, array $variables): ?array
    {
        try {
            // Sunucuya istek gönder
            $response = Http::post(config('license.api_url'), [
                'license_key' => $licenseKey,
                'action' => 'get_excluded_ips',
                'variables' => $variables
            ]);
            
            // Başarılı yanıt kontrolü
            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['status']) && $data['status'] === 'success' && isset($data['data']['excluded_ips'])) {
                    return $data['data']['excluded_ips'];
                }
            }
            
            Log::warning('Sunucudan excluded_ips listesi alınamadı: ' . ($response->json('message') ?? 'Bilinmeyen hata'));
            return null;
        } catch (Exception $e) {
            Log::error('Sunucudan excluded_ips listesi alınırken hata: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Config dosyasının sunucu tarafında doğrulanması
     *
     * @param string $licenseKey
     * @param array $config
     * @return bool
     */
    public function verifyConfigWithServer(string $licenseKey, array $config): bool
    {
        try {
            // Config'in hash'ini oluştur
            $configHash = md5(json_encode($config));
            
            // Sunucuya istek gönder
            $response = Http::post(config('license.api_url'), [
                'license_key' => $licenseKey,
                'action' => 'verify_config',
                'config_hash' => $configHash
            ]);
            
            // Başarılı yanıt kontrolü
            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['status']) && $data['status'] === 'success' && isset($data['data']['valid']) && $data['data']['valid'] === true) {
                    return true;
                }
            }
            
            Log::warning('Config dosyası sunucu tarafında doğrulanamadı: ' . ($response->json('message') ?? 'Bilinmeyen hata'));
            return false;
        } catch (Exception $e) {
            Log::error('Config dosyası sunucu tarafında doğrulanırken hata: ' . $e->getMessage());
            return false;
        }
    }
}
