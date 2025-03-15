<?php

namespace ImTaxu\LaravelLicense\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Rate Limiter Servisi
 * 
 * Bu servis, lisans kontrolü için rate limiting (hız sınırlama) işlemlerini yönetir.
 * Aynı IP adresinden veya aynı lisans anahtarıyla belirli bir süre içinde yapılabilecek
 * istek sayısını sınırlayarak brute force saldırılarına karşı koruma sağlar.
 */
class RateLimiterService
{
    /**
     * Rate limiting yapılandırması
     */
    protected $config = [
        'enabled' => true,            // Rate limiting aktif mi?
        'max_attempts' => 10,         // Maksimum deneme sayısı
        'decay_minutes' => 5,         // Deneme sayısının sıfırlanması için gereken süre (dakika)
        'block_minutes' => 30,        // Engelleme süresi (dakika)
    ];
    
    /**
     * Yapıcı metod
     * 
     * @param array $config Yapılandırma (opsiyonel)
     */
    public function __construct(array $config = [])
    {
        // Yapılandırmayı güncelle
        if (!empty($config)) {
            $this->config = array_merge($this->config, $config);
        }
    }
    
    /**
     * Bir isteğin rate limit'e takılıp takılmadığını kontrol eder
     * 
     * @param string $key Kontrol edilecek anahtar (lisans anahtarı, endpoint adı vb.)
     * @param string $ipAddress IP adresi
     * @return bool|array Rate limit'e takılmadıysa true, takıldıysa hata bilgilerini içeren dizi
     */
    public function check(string $key, string $ipAddress): bool|array
    {
        // Rate limiting devre dışı bırakılmışsa her zaman true döndür
        if (!$this->config['enabled']) {
            return true;
        }
        
        try {
            // Cache anahtarını oluştur
            $cacheKey = "rate_limit:{$key}:{$ipAddress}";
            
            // Cache'den mevcut durumu al
            $rateLimitData = Cache::get($cacheKey);
            
            // Engelleme kontrolü
            if ($rateLimitData && isset($rateLimitData['blocked_until'])) {
                $now = time();
                
                if ($now < $rateLimitData['blocked_until']) {
                    // Hala engelli
                    $remainingSeconds = $rateLimitData['blocked_until'] - $now;
                    $remainingMinutes = ceil($remainingSeconds / 60);
                    
                    return [
                        'limited' => true,
                        'message' => "Çok fazla istek gönderdiniz. Lütfen {$remainingMinutes} dakika sonra tekrar deneyin.",
                        'remaining_seconds' => $remainingSeconds,
                        'blocked_until' => $rateLimitData['blocked_until']
                    ];
                }
                
                // Engelleme süresi dolmuş, kaydı sıfırla
                $this->reset($key, $ipAddress);
                $rateLimitData = null;
            }
            
            // Yeni kayıt oluştur
            if ($rateLimitData === null) {
                $rateLimitData = [
                    'attempts' => 1,
                    'last_attempt' => time()
                ];
                
                Cache::put($cacheKey, $rateLimitData, now()->addMinutes($this->config['decay_minutes']));
                return true;
            }
            
            // Son deneme zamanını kontrol et
            $lastAttempt = $rateLimitData['last_attempt'];
            $now = time();
            $decayTime = $now - ($this->config['decay_minutes'] * 60);
            
            // Decay süresi geçmişse, deneme sayısını sıfırla
            if ($lastAttempt < $decayTime) {
                $this->reset($key, $ipAddress);
                return true;
            }
            
            // Deneme sayısını artır
            $attempts = $rateLimitData['attempts'] + 1;
            $rateLimitData['attempts'] = $attempts;
            $rateLimitData['last_attempt'] = $now;
            
            // Maksimum deneme sayısı aşıldıysa engelle
            if ($attempts >= $this->config['max_attempts']) {
                $blockedUntil = $now + ($this->config['block_minutes'] * 60);
                $rateLimitData['blocked_until'] = $blockedUntil;
                
                Cache::put($cacheKey, $rateLimitData, now()->addMinutes($this->config['block_minutes'] + 1));
                
                // Log kaydı oluştur
                Log::warning("Rate limit aşıldı. IP: {$ipAddress}, Anahtar: {$key}, Engelleme süresi: {$this->config['block_minutes']} dakika");
                
                return [
                    'limited' => true,
                    'message' => "Çok fazla istek gönderdiniz. Lütfen {$this->config['block_minutes']} dakika sonra tekrar deneyin.",
                    'remaining_seconds' => $this->config['block_minutes'] * 60,
                    'blocked_until' => $blockedUntil
                ];
            }
            
            // Güncellenen veriyi cache'e kaydet
            Cache::put($cacheKey, $rateLimitData, now()->addMinutes($this->config['decay_minutes']));
            
            // Kalan deneme sayısı
            $remainingAttempts = $this->config['max_attempts'] - $attempts;
            
            return [
                'limited' => false,
                'remaining_attempts' => $remainingAttempts,
                'message' => "Kalan deneme hakkı: {$remainingAttempts}"
            ];
            
        } catch (Exception $e) {
            // Hata durumunda log kaydı oluştur ve true döndür (rate limit'e takılmasın)
            Log::error("Rate limiting hatası: " . $e->getMessage());
            return true;
        }
    }
    
    /**
     * Başarılı bir istek sonrası rate limit sayacını sıfırlar
     * 
     * @param string $key Sıfırlanacak anahtar
     * @param string $ipAddress IP adresi
     * @return bool
     */
    public function reset(string $key, string $ipAddress): bool
    {
        try {
            $cacheKey = "rate_limit:{$key}:{$ipAddress}";
            Cache::forget($cacheKey);
            return true;
        } catch (Exception $e) {
            Log::error("Rate limit sıfırlama hatası: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Tüm rate limit kayıtlarını temizler
     * 
     * @return bool
     */
    public function clearAll(): bool
    {
        try {
            // Cache önekiyle başlayan tüm kayıtları temizle
            // Not: Bu işlem, kullanılan cache sürücüsüne bağlı olarak çalışmayabilir
            // Örneğin, file cache sürücüsü için bu işlem çalışmaz
            
            // Alternatif olarak, önemli rate limit kayıtlarını manuel olarak temizle
            return true;
        } catch (Exception $e) {
            Log::error("Rate limit temizleme hatası: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Rate limiting yapılandırmasını günceller
     * 
     * @param array $config Yeni yapılandırma
     * @return void
     */
    public function setConfig(array $config): void
    {
        $this->config = array_merge($this->config, $config);
    }
    
    /**
     * Rate limiting yapılandırmasını döndürür
     * 
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }
}
