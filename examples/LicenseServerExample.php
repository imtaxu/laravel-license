<?php

/**
 * Bu dosya, lisans sunucusu tarafında nasıl bir kontrol yapılabileceğini gösteren bir örnektir.
 * Gerçek bir uygulamada, bu kod bir Laravel veya başka bir framework uygulamasında API endpoint olarak kullanılabilir.
 * 
 * Veritabanı yapısı örneği:
 * 
 * CREATE TABLE `licenses` (
 *   `id` int(11) NOT NULL AUTO_INCREMENT,
 *   `license_key` varchar(255) NOT NULL,
 *   `status` enum('active','inactive','suspended') NOT NULL DEFAULT 'active',
 *   `domain` varchar(255) NOT NULL,
 *   `client_ip` varchar(45) DEFAULT NULL,
 *   `owner_email` varchar(255) NOT NULL,
 *   `expires_at` datetime NOT NULL,
 *   `created_at` datetime NOT NULL,
 *   `updated_at` datetime NOT NULL,
 *   `max_instances` int(11) NOT NULL DEFAULT 1,
 *   `client_signature` varchar(255) DEFAULT NULL,
 *   `excluded_ips` text DEFAULT NULL,
 *   `features` text DEFAULT NULL,
 *   PRIMARY KEY (`id`),
 *   UNIQUE KEY `license_key` (`license_key`)
 * ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
 */

namespace Examples;

use PDO;
use DateTime;
use Exception;

class LicenseServerExample
{
    /**
     * Veritabanı bağlantısı
     */
    private $db;
    
    /**
     * Rate Limiter nesnesi
     */
    private $rateLimiter;
    
    /**
     * Yapılandırma
     */
    private $config = [
        'db_host' => 'localhost',
        'db_name' => 'license_server',
        'db_user' => 'license_user',
        'db_pass' => 'license_password',
        'check_frequency' => 86400, // 1 gün (saniye cinsinden)
        'rate_limit' => [
            'enabled' => true,
            'max_attempts' => 10,         // Maksimum deneme sayısı
            'decay_minutes' => 5,         // Deneme sayısının sıfırlanması için gereken süre (dakika)
            'block_minutes' => 30,        // Engelleme süresi (dakika)
            'ip_header' => 'REMOTE_ADDR', // IP adresinin alınacağı HTTP header
        ],
    ];
    
    /**
     * Örnek lisans kayıtları (gerçek uygulamada veritabanından gelecek)
     */
    private $licenses = [
        'LICENSE-KEY-123456' => [
            'status' => 'active',
            'expires_at' => '2025-12-31',
            'domain' => 'example.com',
            'client_ip' => '123.123.123.123',
            'owner_email' => 'client@example.com',
            'excluded_ips' => [], // Bu IP'ler sunucu tarafında tutulur, istemci tarafında değil
            'max_instances' => 1,
            'features' => ['feature1', 'feature2'],
            'client_signature' => 'abc123', // İstemcinin benzersiz imzası
        ]
    ];

    /**
     * Yapıcı metod - Veritabanı bağlantısını başlatır ve Rate Limiter'ı yapılandırır
     */
    public function __construct()
    {
        try {
            // Gerçek uygulamada veritabanı bağlantısı burada kurulur
            // $this->db = new PDO(
            //     "mysql:host={$this->config['db_host']};dbname={$this->config['db_name']}",
            //     $this->config['db_user'],
            //     $this->config['db_pass'],
            //     [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            // );
            
            // Rate Limiter'ı başlat (eğer etkinse)
            if ($this->config['rate_limit']['enabled'] && isset($this->db)) {
                // RateLimiter sınıfını dahil et
                // require_once 'RateLimiter.php';
                
                // Rate Limiter nesnesini oluştur
                // $this->rateLimiter = new RateLimiter($this->db, $this->config['rate_limit']);
            }
        } catch (Exception $e) {
            // Hata günlüğüne kaydet
            error_log('Başlatma hatası: ' . $e->getMessage());
        }
    }
    
    /**
     * Lisans doğrulama işlemi
     *
     * @param string $licenseKey Lisans anahtarı
     * @param array $data İstemciden gelen veriler
     * @return array
     */
    public function verifyLicense(string $licenseKey, array $data): array
    {
        // IP adresini al
        $ipAddress = $_SERVER[$this->config['rate_limit']['ip_header']] ?? $data['ip'] ?? '0.0.0.0';
        
        // Rate limiting kontrolü
        if ($this->rateLimiter) {
            // Lisans anahtarı ve IP adresi kombinasyonu için bir anahtar oluştur
            $rateLimitKey = 'verify:' . $licenseKey . ':' . $ipAddress;
            $rateLimitCheck = $this->rateLimiter->check($rateLimitKey, $ipAddress);
            
            // Rate limit'e takıldıysa hata döndür
            if (is_array($rateLimitCheck) && isset($rateLimitCheck['limited']) && $rateLimitCheck['limited']) {
                // Geçersiz istek logla
                $this->logInvalidRequest($licenseKey, $ipAddress, $data['domain'] ?? '', $rateLimitCheck['message'], $data);
                
                return $this->errorResponse($rateLimitCheck['message']);
            }
        }
        
        // Gerçek uygulamada veritabanından lisans bilgilerini çek
        // $stmt = $this->db->prepare("SELECT * FROM licenses WHERE license_key = ?");
        // $stmt->execute([$licenseKey]);
        // $license = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Örnek için sabit verileri kullan
        if (!isset($this->licenses[$licenseKey])) {
            // Rate limit sayacını artır (başarısız deneme)
            // Geçersiz istek logla
            $this->logInvalidRequest($licenseKey, $ipAddress, $data['domain'] ?? '', 'Geçersiz lisans anahtarı.', $data);
            
            return $this->errorResponse('Geçersiz lisans anahtarı.');
        }

        $license = $this->licenses[$licenseKey];

        // Lisans süresi kontrolü
        if (strtotime($license['expires_at']) < time()) {
            return $this->errorResponse('Lisans süresi dolmuş.');
        }

        // Lisans durumu kontrolü
        if ($license['status'] !== 'active') {
            return $this->errorResponse('Lisans aktif değil.');
        }

        // Domain kontrolü - Akıllı domain kontrolü
        $domain = $data['domain'] ?? '';
        if (!$this->isDomainValid($domain, $license['domain'])) {
            return $this->errorResponse('Bu domain için lisans geçerli değil.');
        }

        // IP kontrolü
        $ip = $data['ip'] ?? '';
        if (!empty($license['client_ip']) && $license['client_ip'] !== $ip) {
            // IP değişikliği olabilir, ancak domain doğruysa izin ver
            // Log tut ve bildirim gönder
            error_log("IP değişikliği tespit edildi: {$license['client_ip']} -> {$ip}");
        }

        // Excluded IP kontrolü - Bu liste sunucu tarafında tutulur
        $excludedIps = is_array($license['excluded_ips']) ? $license['excluded_ips'] : json_decode($license['excluded_ips'] ?? '[]', true);
        if (!empty($excludedIps) && in_array($ip, $excludedIps)) {
            return $this->errorResponse('Bu IP adresi lisans kontrolünden muaf tutulmuştur.');
        }

        // İstemci imzası kontrolü - Manipülasyonu engellemek için
        $clientSignature = $data['client_signature'] ?? '';
        if ($license['client_signature'] !== $clientSignature) {
            return $this->errorResponse('İstemci imzası geçersiz. Lisans manipüle edilmiş olabilir.');
        }

        // Maksimum instance sayısı kontrolü
        // Gerçek uygulamada, aynı lisans anahtarı ile kaç farklı IP'den erişim yapıldığı kontrol edilebilir
        
        // Tüm kontrollerden geçildi, lisans geçerli
        
        // Başarılı doğrulama sonrası rate limit sayacını sıfırla
        if ($this->rateLimiter) {
            $rateLimitKey = 'verify:' . $licenseKey . ':' . $ipAddress;
            $this->rateLimiter->reset($rateLimitKey, $ipAddress);
        }
        
        return [
            'status' => 'success',
            'message' => 'Lisans geçerli.',
            'data' => [
                'expires_at' => $license['expires_at'],
                'features' => is_array($license['features']) ? $license['features'] : json_decode($license['features'] ?? '[]', true),
                'check_frequency' => $this->config['check_frequency'],
                'excluded_ips' => is_array($license['excluded_ips']) ? $license['excluded_ips'] : json_decode($license['excluded_ips'] ?? '[]', true),
                'owner_email' => $license['owner_email'] ?? '',
                'max_instances' => $license['max_instances'] ?? 1
            ]
        ];
    }

    /**
     * Domain kontrolü yapar
     * Ana domain ve www subdomain için geçerli, diğer subdomain'ler için geçersiz
     *
     * @param string $requestDomain İstemciden gelen domain
     * @param string $licenseDomain Lisanstaki domain
     * @return bool
     */
    private function isDomainValid(string $requestDomain, string $licenseDomain): bool
    {
        // Domain'leri temizle (http://, https://, www. gibi önekleri kaldır)
        $requestDomain = $this->cleanDomain($requestDomain);
        $licenseDomain = $this->cleanDomain($licenseDomain);
        
        // Tam eşleşme varsa geçerli
        if ($requestDomain === $licenseDomain) {
            return true;
        }
        
        // www. ile başlayan versiyonu kontrol et
        if ($requestDomain === 'www.' . $licenseDomain) {
            return true;
        }
        
        // Lisans www. ile başlıyorsa, www. olmadan da geçerli olmalı
        if (strpos($licenseDomain, 'www.') === 0 && substr($licenseDomain, 4) === $requestDomain) {
            return true;
        }
        
        // Diğer subdomain'ler için geçersiz
        // Örnek: blog.example.com, test.example.com gibi
        $requestParts = explode('.', $requestDomain);
        $licenseParts = explode('.', $licenseDomain);
        
        // İstek domain'i daha fazla parçaya sahipse (yani bir subdomain içeriyorsa)
        // ve bu www. değilse, geçersiz kabul et
        if (count($requestParts) > count($licenseParts) && $requestParts[0] !== 'www') {
            return false;
        }
        
        return false;
    }
    
    /**
     * Domain'i temizler (http://, https://, www. gibi önekleri kaldırır)
     *
     * @param string $domain
     * @return string
     */
    private function cleanDomain(string $domain): string
    {
        // HTTP ve HTTPS protokollerini kaldır
        $domain = str_replace(['http://', 'https://'], '', $domain);
        
        // Sondaki / işaretini kaldır
        $domain = rtrim($domain, '/');
        
        // Olası port numarasını kaldır (örn: example.com:8080)
        $domain = preg_replace('/:\d+$/', '', $domain);
        
        return strtolower($domain);
    }

    /**
     * Hata yanıtı oluşturur
     *
     * @param string $message Hata mesajı
     * @return array
     */
    private function errorResponse(string $message): array
    {
        return [
            'status' => 'error',
            'message' => $message,
            'data' => null
        ];
    }
    /**
     * Excluded IP listesini günceller
     *
     * @param string $licenseKey Lisans anahtarı
     * @param array $excludedIps Muaf tutulan IP'ler
     * @return array
     */
    public function updateExcludedIps(string $licenseKey, array $excludedIps): array
    {
        // Gerçek uygulamada veritabanında güncelleme yapılır
        // $stmt = $this->db->prepare("UPDATE licenses SET excluded_ips = ? WHERE license_key = ?");
        // $stmt->execute([json_encode($excludedIps), $licenseKey]);
        
        // Örnek için sabit verileri kullan
        if (!isset($this->licenses[$licenseKey])) {
            return $this->errorResponse('Geçersiz lisans anahtarı.');
        }
        
        // Excluded IP'leri güncelle
        $this->licenses[$licenseKey]['excluded_ips'] = $excludedIps;
        
        return [
            'status' => 'success',
            'message' => 'Excluded IP listesi güncellendi.',
            'data' => [
                'excluded_ips' => $excludedIps
            ]
        ];
    }
    
    /**
     * Config hash'ini doğrular
     *
     * @param string $licenseKey Lisans anahtarı
     * @param string $configHash Config dosyasının hash'i
     * @return array
     */
    public function verifyConfigHash(string $licenseKey, string $configHash): array
    {
        // IP adresini al
        $ipAddress = $_SERVER[$this->config['rate_limit']['ip_header']] ?? '0.0.0.0';
        
        // Rate limiting kontrolü
        if ($this->rateLimiter) {
            $rateLimitKey = 'verify_config:' . $licenseKey . ':' . $ipAddress;
            $rateLimitCheck = $this->rateLimiter->check($rateLimitKey, $ipAddress);
            
            // Rate limit'e takıldıysa hata döndür
            if (is_array($rateLimitCheck) && isset($rateLimitCheck['limited']) && $rateLimitCheck['limited']) {
                return $this->errorResponse($rateLimitCheck['message']);
            }
        }
        
        // Gerçek uygulamada veritabanında son geçerli hash kontrolü yapılır
        // $stmt = $this->db->prepare("SELECT config_hash FROM license_configs WHERE license_key = ?");
        // $stmt->execute([$licenseKey]);
        // $storedHash = $stmt->fetchColumn();
        
        // Başarılı doğrulama sonrası rate limit sayacını sıfırla
        if ($this->rateLimiter) {
            $rateLimitKey = 'verify_config:' . $licenseKey . ':' . $ipAddress;
            $this->rateLimiter->reset($rateLimitKey, $ipAddress);
        }
        
        // Örnek için her zaman geçerli kabul et
        return [
            'status' => 'success',
            'message' => 'Config hash doğrulandı.',
            'data' => [
                'valid' => true
            ]
        ];
    }
    
    /**
     * Geçersiz lisans isteğini loglar
     *
     * @param string $licenseKey Lisans anahtarı
     * @param string $ipAddress IP adresi
     * @param string $domain Domain
     * @param string $reason Sebep
     * @param array $requestData İstek verileri
     * @return bool
     */
    private function logInvalidRequest(string $licenseKey, string $ipAddress, string $domain, string $reason, array $requestData = []): bool
    {
        try {
            // Gerçek uygulamada veritabanına kaydet
            // $stmt = $this->db->prepare("
            //     INSERT INTO invalid_requests (license_key, ip_address, domain, reason, request_data, user_agent, created_at)
            //     VALUES (?, ?, ?, ?, ?, ?, NOW())
            // ");
            // 
            // return $stmt->execute([
            //     $licenseKey,
            //     $ipAddress,
            //     $domain,
            //     $reason,
            //     json_encode($requestData),
            //     $_SERVER['HTTP_USER_AGENT'] ?? ''
            // ]);
            
            // Örnek için log dosyasına yaz
            $logMessage = sprintf(
                "[%s] Geçersiz lisans isteği: %s, IP: %s, Domain: %s, Sebep: %s",
                date('Y-m-d H:i:s'),
                $licenseKey,
                $ipAddress,
                $domain,
                $reason
            );
            
            error_log($logMessage);
            
            return true;
        } catch (Exception $e) {
            error_log('Geçersiz istek loglama hatası: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Rate limit istatistiklerini alır
     *
     * @return array
     */
    public function getRateLimitStats(): array
    {
        if (!$this->rateLimiter) {
            return [
                'status' => 'error',
                'message' => 'Rate Limiter aktif değil.',
                'data' => null
            ];
        }
        
        return [
            'status' => 'success',
            'message' => 'Rate limit istatistikleri alındı.',
            'data' => $this->rateLimiter->getStats()
        ];
    }
    
    /**
     * Engellenen IP'leri listeler
     *
     * @param int $limit Maksimum kayıt sayısı
     * @return array
     */
    public function getBlockedIps(int $limit = 50): array
    {
        if (!$this->rateLimiter) {
            return [
                'status' => 'error',
                'message' => 'Rate Limiter aktif değil.',
                'data' => null
            ];
        }
        
        return [
            'status' => 'success',
            'message' => 'Engellenen IP listesi alındı.',
            'data' => [
                'blocked_ips' => $this->rateLimiter->getBlockedIps($limit)
            ]
        ];
    }
    
    /**
     * Eski rate limit kayıtlarını temizler
     *
     * @param int $days Gün sayısı
     * @return array
     */
    public function cleanupRateLimits(int $days = 30): array
    {
        if (!$this->rateLimiter) {
            return [
                'status' => 'error',
                'message' => 'Rate Limiter aktif değil.',
                'data' => null
            ];
        }
        
        $deletedCount = $this->rateLimiter->cleanup($days);
        
        return [
            'status' => 'success',
            'message' => "{$deletedCount} adet eski rate limit kaydı temizlendi.",
            'data' => [
                'deleted_count' => $deletedCount
            ]
        ];
    }
}

/**
 * API endpoint örnekleri (Laravel veya başka bir framework kullanılabilir)
 */
/*
// Lisans doğrulama endpoint'i
Route::post('/api/verify', function (Request $request) {
    $licenseKey = $request->input('license_key');
    $data = $request->only(['domain', 'ip', 'client_signature']);
    
    $licenseServer = new LicenseServerExample();
    $response = $licenseServer->verifyLicense($licenseKey, $data);
    
    return response()->json($response);
});

// Excluded IP listesi güncelleme endpoint'i
Route::post('/api/update-excluded-ips', function (Request $request) {
    $licenseKey = $request->input('license_key');
    $excludedIps = $request->input('excluded_ips', []);
    
    $licenseServer = new LicenseServerExample();
    $response = $licenseServer->updateExcludedIps($licenseKey, $excludedIps);
    
    return response()->json($response);
});

// Config hash doğrulama endpoint'i
Route::post('/api/verify-config', function (Request $request) {
    $licenseKey = $request->input('license_key');
    $configHash = $request->input('config_hash');
    
    $licenseServer = new LicenseServerExample();
    $response = $licenseServer->verifyConfigHash($licenseKey, $configHash);
    
    return response()->json($response);
});
*/
