<?php

namespace ImTaxu\LaravelLicense\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Lisans yenileme bildirimleri için servis sınıfı
 */
class NotificationService
{
    /**
     * Bildirim eşik değerleri (gün cinsinden)
     *
     * @var array
     */
    protected $thresholds = [30, 15, 7, 3, 1];
    
    /**
     * Yapılandırma
     *
     * @var array
     */
    protected $config;
    
    /**
     * NotificationService constructor.
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->config = $config;
        
        // Varsayılan ayarları doldur
        if (!isset($this->config['notification_thresholds'])) {
            $this->config['notification_thresholds'] = $this->thresholds;
        } else {
            $this->thresholds = $this->config['notification_thresholds'];
        }
        
        if (!isset($this->config['cache_key_prefix'])) {
            $this->config['cache_key_prefix'] = 'license_notification_';
        }
        
        if (!isset($this->config['dismissed_cache_key'])) {
            $this->config['dismissed_cache_key'] = 'license_dismissed_notifications';
        }
    }
    
    /**
     * Lisans süresinin dolmasına kaç gün kaldığını kontrol eder
     *
     * @param string $expiryDate Lisans bitiş tarihi (Y-m-d H:i:s formatında)
     * @return int|null Kalan gün sayısı veya null (lisans süresi dolmuşsa)
     */
    public function getDaysRemaining(string $expiryDate): ?int
    {
        try {
            $expiry = strtotime($expiryDate);
            $now = time();
            
            // Lisans süresi dolmuşsa null döndür
            if ($expiry <= $now) {
                return null;
            }
            
            // Kalan gün sayısını hesapla
            $daysRemaining = ceil(($expiry - $now) / (60 * 60 * 24));
            return (int) $daysRemaining;
        } catch (Exception $e) {
            Log::error('Lisans süresi hesaplama hatası: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Lisans için bildirim gösterilmeli mi?
     *
     * @param string $licenseKey Lisans anahtarı
     * @param string $expiryDate Lisans bitiş tarihi (Y-m-d H:i:s formatında)
     * @return array|null Bildirim bilgileri veya null (bildirim gösterilmeyecekse)
     */
    public function shouldShowNotification(string $licenseKey, string $expiryDate): ?array
    {
        try {
            // Kalan gün sayısını hesapla
            $daysRemaining = $this->getDaysRemaining($expiryDate);
            
            // Lisans süresi dolmuşsa veya kalan gün sayısı eşik değerlerinden büyükse bildirim gösterme
            if ($daysRemaining === null || $daysRemaining > max($this->thresholds)) {
                return null;
            }
            
            // En uygun eşik değerini bul
            $threshold = $this->findBestThreshold($daysRemaining);
            
            // Bu eşik değeri için bildirim daha önce kapatılmış mı kontrol et
            $notificationKey = $this->getNotificationKey($licenseKey, $threshold);
            
            if ($this->isNotificationDismissed($notificationKey)) {
                return null;
            }
            
            // Bildirim bilgilerini döndür
            return [
                'license_key' => $licenseKey,
                'days_remaining' => $daysRemaining,
                'threshold' => $threshold,
                'notification_key' => $notificationKey,
                'expiry_date' => $expiryDate
            ];
        } catch (Exception $e) {
            Log::error('Lisans bildirim kontrolü hatası: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Kalan gün sayısına göre en uygun eşik değerini bulur
     *
     * @param int $daysRemaining Kalan gün sayısı
     * @return int Eşik değeri
     */
    protected function findBestThreshold(int $daysRemaining): int
    {
        foreach ($this->thresholds as $threshold) {
            if ($daysRemaining <= $threshold) {
                return $threshold;
            }
        }
        
        return $this->thresholds[0]; // Varsayılan olarak ilk eşik değerini döndür
    }
    
    /**
     * Bildirim anahtarı oluşturur
     *
     * @param string $licenseKey Lisans anahtarı
     * @param int $threshold Eşik değeri
     * @return string Bildirim anahtarı
     */
    protected function getNotificationKey(string $licenseKey, int $threshold): string
    {
        return $this->config['cache_key_prefix'] . md5($licenseKey) . '_' . $threshold;
    }
    
    /**
     * Bildirimin daha önce kapatılıp kapatılmadığını kontrol eder
     *
     * @param string $notificationKey Bildirim anahtarı
     * @return bool Bildirim kapatılmış mı?
     */
    public function isNotificationDismissed(string $notificationKey): bool
    {
        $dismissedNotifications = Cache::get($this->config['dismissed_cache_key'], []);
        
        if (!is_array($dismissedNotifications)) {
            return false;
        }
        
        foreach ($dismissedNotifications as $dismissed) {
            if (isset($dismissed['key']) && $dismissed['key'] === $notificationKey) {
                // Kapatılma süresi dolmuş mu kontrol et
                if (isset($dismissed['until']) && time() < $dismissed['until']) {
                    return true;
                }
            }
        }
        
        return false;
    }
    
    /**
     * Bildirimi kapatır
     *
     * @param string $notificationKey Bildirim anahtarı
     * @param int|null $nextThreshold Bir sonraki gösterilecek eşik değeri (gün)
     * @return bool İşlem başarılı mı?
     */
    public function dismissNotification(string $notificationKey, ?int $nextThreshold = null): bool
    {
        try {
            $dismissedNotifications = Cache::get($this->config['dismissed_cache_key'], []);
            
            if (!is_array($dismissedNotifications)) {
                $dismissedNotifications = [];
            }
            
            // Mevcut bildirimi listeden çıkar
            $dismissedNotifications = array_filter($dismissedNotifications, function ($item) use ($notificationKey) {
                return !isset($item['key']) || $item['key'] !== $notificationKey;
            });
            
            // Yeni bildirim kaydı ekle
            $dismissedUntil = time() + 86400 * 30; // Varsayılan olarak 30 gün
            
            // Bir sonraki eşik değeri belirtilmişse, o zamana kadar ertele
            if ($nextThreshold !== null) {
                $currentThreshold = (int) substr($notificationKey, strrpos($notificationKey, '_') + 1);
                $daysToAdd = $currentThreshold - $nextThreshold;
                
                if ($daysToAdd > 0) {
                    $dismissedUntil = time() + 86400 * $daysToAdd; // Gün sayısı * 1 gün (saniye)
                }
            }
            
            $dismissedNotifications[] = [
                'key' => $notificationKey,
                'until' => $dismissedUntil,
                'dismissed_at' => time()
            ];
            
            // Güncellenmiş listeyi önbelleğe kaydet
            Cache::put($this->config['dismissed_cache_key'], $dismissedNotifications, now()->addDays(30));
            
            return true;
        } catch (Exception $e) {
            Log::error('Bildirim kapatma hatası: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Tüm bildirimleri temizler
     *
     * @return bool İşlem başarılı mı?
     */
    public function clearAllNotifications(): bool
    {
        try {
            Cache::forget($this->config['dismissed_cache_key']);
            return true;
        } catch (Exception $e) {
            Log::error('Bildirim temizleme hatası: ' . $e->getMessage());
            return false;
        }
    }
}
