<?php
/**
 * Lisans Bildirim Yöneticisi
 * 
 * Bu sınıf, lisans süresinin dolmasına belirli süreler kala
 * kullanıcıya bildirim göstermek için kullanılır.
 */
class LicenseNotifications
{
    /**
     * Bildirim eşik değerleri (gün cinsinden)
     */
    private $thresholds = [30, 15, 7, 3, 1];
    
    /**
     * Veritabanı bağlantısı
     */
    private $db;
    
    /**
     * Kullanıcı ID'si
     */
    private $userId;
    
    /**
     * Yapıcı metod
     * 
     * @param PDO $db Veritabanı bağlantısı
     * @param int $userId Kullanıcı ID'si
     */
    public function __construct($db, $userId)
    {
        $this->db = $db;
        $this->userId = $userId;
    }
    
    /**
     * Süresi dolmak üzere olan lisansları kontrol eder ve bildirim gösterir
     * 
     * @return array|null Bildirim bilgileri veya null
     */
    public function checkExpiringLicenses()
    {
        try {
            // Kullanıcının daha önce gördüğü bildirimleri al
            $dismissedNotifications = $this->getDismissedNotifications();
            
            // Süresi dolmak üzere olan lisansları getir
            $expiringLicenses = $this->getExpiringLicenses();
            
            if (empty($expiringLicenses)) {
                return null;
            }
            
            // Her bir lisans için en uygun bildirim eşiğini bul
            $notifications = [];
            foreach ($expiringLicenses as $license) {
                $expiryDate = new DateTime($license['expires_at']);
                $now = new DateTime();
                $daysRemaining = $now->diff($expiryDate)->days;
                
                // Lisans süresi dolmuşsa bildirim gösterme
                if ($expiryDate < $now) {
                    continue;
                }
                
                // En uygun eşik değerini bul
                $threshold = $this->findBestThreshold($daysRemaining);
                
                // Bu eşik değeri için bildirim daha önce kapatılmış mı kontrol et
                $notificationKey = $license['id'] . '_' . $threshold;
                if (in_array($notificationKey, $dismissedNotifications)) {
                    continue;
                }
                
                // Bildirim ekle
                $notifications[] = [
                    'license_id' => $license['id'],
                    'license_key' => $license['license_key'],
                    'domain' => $license['domain'],
                    'expires_at' => $license['expires_at'],
                    'days_remaining' => $daysRemaining,
                    'threshold' => $threshold,
                    'notification_key' => $notificationKey
                ];
            }
            
            return $notifications;
        } catch (Exception $e) {
            error_log("Lisans bildirim hatası: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Kalan gün sayısına göre en uygun eşik değerini bulur
     * 
     * @param int $daysRemaining Kalan gün sayısı
     * @return int Eşik değeri
     */
    private function findBestThreshold($daysRemaining)
    {
        foreach ($this->thresholds as $threshold) {
            if ($daysRemaining <= $threshold) {
                return $threshold;
            }
        }
        
        return $this->thresholds[0]; // Varsayılan olarak ilk eşik değerini döndür
    }
    
    /**
     * Süresi dolmak üzere olan lisansları getirir
     * 
     * @return array Lisans listesi
     */
    private function getExpiringLicenses()
    {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM licenses 
                WHERE status = 'active' 
                AND expires_at BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 30 DAY)
                ORDER BY expires_at ASC
            ");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Süresi dolacak lisansları getirme hatası: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Kullanıcının daha önce gördüğü ve kapattığı bildirimleri getirir
     * 
     * @return array Kapatılmış bildirim anahtarları
     */
    private function getDismissedNotifications()
    {
        try {
            $stmt = $this->db->prepare("
                SELECT notification_key FROM dismissed_notifications 
                WHERE user_id = :user_id AND dismissed_until > NOW()
            ");
            $stmt->execute(['user_id' => $this->userId]);
            
            $dismissed = [];
            while ($row = $stmt->fetch()) {
                $dismissed[] = $row['notification_key'];
            }
            
            return $dismissed;
        } catch (PDOException $e) {
            error_log("Kapatılmış bildirimleri getirme hatası: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Bildirimi kapatır
     * 
     * @param string $notificationKey Bildirim anahtarı
     * @param int $nextThreshold Bir sonraki gösterilecek eşik değeri (gün)
     * @return bool İşlem başarılı mı?
     */
    public function dismissNotification($notificationKey, $nextThreshold = null)
    {
        try {
            // Mevcut bildirimi sil
            $stmtDelete = $this->db->prepare("
                DELETE FROM dismissed_notifications 
                WHERE user_id = :user_id AND notification_key = :notification_key
            ");
            $stmtDelete->execute([
                'user_id' => $this->userId,
                'notification_key' => $notificationKey
            ]);
            
            // Yeni bildirim kaydı ekle
            $dismissedUntil = new DateTime();
            
            // Bir sonraki eşik değeri belirtilmişse, o zamana kadar ertele
            if ($nextThreshold !== null) {
                $currentDays = explode('_', $notificationKey)[1]; // notification_key: license_id_threshold
                $daysToAdd = $currentDays - $nextThreshold;
                $dismissedUntil->add(new DateInterval("P{$daysToAdd}D"));
            } else {
                // Belirtilmemişse varsayılan olarak 1 ay ertele
                $dismissedUntil->add(new DateInterval("P30D"));
            }
            
            $stmtInsert = $this->db->prepare("
                INSERT INTO dismissed_notifications 
                (user_id, notification_key, dismissed_until, created_at) 
                VALUES (:user_id, :notification_key, :dismissed_until, NOW())
            ");
            
            return $stmtInsert->execute([
                'user_id' => $this->userId,
                'notification_key' => $notificationKey,
                'dismissed_until' => $dismissedUntil->format('Y-m-d H:i:s')
            ]);
        } catch (PDOException $e) {
            error_log("Bildirim kapatma hatası: " . $e->getMessage());
            return false;
        }
    }
}
