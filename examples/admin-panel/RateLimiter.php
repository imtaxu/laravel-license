<?php
/**
 * Lisans Yönetim Paneli - Rate Limiter Sınıfı
 * 
 * Bu sınıf, lisans kontrolü için rate limiting (hız sınırlama) işlemlerini yönetir.
 * Aynı IP adresinden veya aynı lisans anahtarıyla belirli bir süre içinde yapılabilecek
 * istek sayısını sınırlayarak brute force saldırılarına karşı koruma sağlar.
 */

class RateLimiter
{
    /**
     * Veritabanı bağlantısı
     */
    private $db;
    
    /**
     * Rate limiting yapılandırması
     */
    private $config = [
        'max_attempts' => 10,         // Maksimum deneme sayısı
        'decay_minutes' => 5,         // Deneme sayısının sıfırlanması için gereken süre (dakika)
        'block_minutes' => 30,        // Engelleme süresi (dakika)
        'ip_header' => 'REMOTE_ADDR', // IP adresinin alınacağı HTTP header
    ];
    
    /**
     * Yapıcı metod
     * 
     * @param PDO $db Veritabanı bağlantısı
     * @param array $config Yapılandırma (opsiyonel)
     */
    public function __construct($db, array $config = [])
    {
        $this->db = $db;
        
        // Yapılandırmayı güncelle
        if (!empty($config)) {
            $this->config = array_merge($this->config, $config);
        }
        
        // Rate limits tablosunu oluştur (eğer yoksa)
        $this->createRateLimitsTable();
    }
    
    /**
     * Rate limits tablosunu oluşturur
     */
    private function createRateLimitsTable()
    {
        try {
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS `rate_limits` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `key` varchar(255) NOT NULL,
                    `ip_address` varchar(45) NOT NULL,
                    `attempts` int(11) NOT NULL DEFAULT 0,
                    `blocked_until` datetime DEFAULT NULL,
                    `last_attempt_at` datetime NOT NULL,
                    `created_at` datetime NOT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `key_ip` (`key`, `ip_address`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");
        } catch (PDOException $e) {
            error_log('Rate limits tablosu oluşturma hatası: ' . $e->getMessage());
        }
    }
    
    /**
     * Bir isteğin rate limit'e takılıp takılmadığını kontrol eder
     * 
     * @param string $key Kontrol edilecek anahtar (lisans anahtarı, endpoint adı vb.)
     * @param string $ipAddress IP adresi (null ise otomatik algılanır)
     * @return bool|array Rate limit'e takılmadıysa true, takıldıysa hata bilgilerini içeren dizi
     */
    public function check(string $key, string $ipAddress = null)
    {
        // IP adresi belirtilmemişse otomatik algıla
        if ($ipAddress === null) {
            $ipAddress = $_SERVER[$this->config['ip_header']] ?? '0.0.0.0';
        }
        
        // Rate limit kaydını al
        $record = $this->getRateLimitRecord($key, $ipAddress);
        
        // Engelleme kontrolü
        if ($record && $record['blocked_until'] !== null) {
            $blockedUntil = new DateTime($record['blocked_until']);
            $now = new DateTime();
            
            if ($blockedUntil > $now) {
                // Hala engelli
                $remainingSeconds = $blockedUntil->getTimestamp() - $now->getTimestamp();
                $remainingMinutes = ceil($remainingSeconds / 60);
                
                return [
                    'limited' => true,
                    'message' => "Çok fazla istek gönderdiniz. Lütfen {$remainingMinutes} dakika sonra tekrar deneyin.",
                    'remaining_seconds' => $remainingSeconds,
                    'blocked_until' => $record['blocked_until']
                ];
            }
            
            // Engelleme süresi dolmuş, kaydı sıfırla
            $this->resetRateLimit($key, $ipAddress);
            $record = null;
        }
        
        // Yeni kayıt oluştur veya mevcut kaydı güncelle
        if ($record === null) {
            $this->createRateLimitRecord($key, $ipAddress);
            return true;
        }
        
        // Son deneme zamanını kontrol et
        $lastAttempt = new DateTime($record['last_attempt_at']);
        $now = new DateTime();
        $decayTime = new DateTime();
        $decayTime->sub(new DateInterval('PT' . ($this->config['decay_minutes'] * 60) . 'S'));
        
        // Decay süresi geçmişse, deneme sayısını sıfırla
        if ($lastAttempt < $decayTime) {
            $this->resetRateLimit($key, $ipAddress);
            return true;
        }
        
        // Deneme sayısını artır
        $attempts = $record['attempts'] + 1;
        $this->updateRateLimitRecord($key, $ipAddress, $attempts);
        
        // Maksimum deneme sayısı aşıldıysa engelle
        if ($attempts >= $this->config['max_attempts']) {
            $blockedUntil = new DateTime();
            $blockedUntil->add(new DateInterval('PT' . ($this->config['block_minutes'] * 60) . 'S'));
            
            $this->blockRateLimit($key, $ipAddress, $blockedUntil->format('Y-m-d H:i:s'));
            
            return [
                'limited' => true,
                'message' => "Çok fazla istek gönderdiniz. Lütfen {$this->config['block_minutes']} dakika sonra tekrar deneyin.",
                'remaining_seconds' => $this->config['block_minutes'] * 60,
                'blocked_until' => $blockedUntil->format('Y-m-d H:i:s')
            ];
        }
        
        // Kalan deneme sayısı
        $remainingAttempts = $this->config['max_attempts'] - $attempts;
        
        return [
            'limited' => false,
            'remaining_attempts' => $remainingAttempts,
            'message' => "Kalan deneme hakkı: {$remainingAttempts}"
        ];
    }
    
    /**
     * Başarılı bir istek sonrası rate limit sayacını sıfırlar
     * 
     * @param string $key Sıfırlanacak anahtar
     * @param string $ipAddress IP adresi
     * @return bool
     */
    public function reset(string $key, string $ipAddress = null)
    {
        // IP adresi belirtilmemişse otomatik algıla
        if ($ipAddress === null) {
            $ipAddress = $_SERVER[$this->config['ip_header']] ?? '0.0.0.0';
        }
        
        return $this->resetRateLimit($key, $ipAddress);
    }
    
    /**
     * Rate limit kaydını veritabanından alır
     * 
     * @param string $key Anahtar
     * @param string $ipAddress IP adresi
     * @return array|null
     */
    private function getRateLimitRecord(string $key, string $ipAddress)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM rate_limits 
                WHERE `key` = :key AND ip_address = :ip_address
                LIMIT 1
            ");
            
            $stmt->execute([
                'key' => $key,
                'ip_address' => $ipAddress
            ]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Rate limit kaydı alma hatası: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Yeni bir rate limit kaydı oluşturur
     * 
     * @param string $key Anahtar
     * @param string $ipAddress IP adresi
     * @return bool
     */
    private function createRateLimitRecord(string $key, string $ipAddress)
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO rate_limits (`key`, ip_address, attempts, last_attempt_at, created_at)
                VALUES (:key, :ip_address, 1, NOW(), NOW())
            ");
            
            return $stmt->execute([
                'key' => $key,
                'ip_address' => $ipAddress
            ]);
        } catch (PDOException $e) {
            error_log('Rate limit kaydı oluşturma hatası: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Rate limit kaydını günceller
     * 
     * @param string $key Anahtar
     * @param string $ipAddress IP adresi
     * @param int $attempts Deneme sayısı
     * @return bool
     */
    private function updateRateLimitRecord(string $key, string $ipAddress, int $attempts)
    {
        try {
            $stmt = $this->db->prepare("
                UPDATE rate_limits 
                SET attempts = :attempts, last_attempt_at = NOW()
                WHERE `key` = :key AND ip_address = :ip_address
            ");
            
            return $stmt->execute([
                'attempts' => $attempts,
                'key' => $key,
                'ip_address' => $ipAddress
            ]);
        } catch (PDOException $e) {
            error_log('Rate limit kaydı güncelleme hatası: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Rate limit kaydını engeller
     * 
     * @param string $key Anahtar
     * @param string $ipAddress IP adresi
     * @param string $blockedUntil Engelleme bitiş tarihi
     * @return bool
     */
    private function blockRateLimit(string $key, string $ipAddress, string $blockedUntil)
    {
        try {
            $stmt = $this->db->prepare("
                UPDATE rate_limits 
                SET blocked_until = :blocked_until
                WHERE `key` = :key AND ip_address = :ip_address
            ");
            
            return $stmt->execute([
                'blocked_until' => $blockedUntil,
                'key' => $key,
                'ip_address' => $ipAddress
            ]);
        } catch (PDOException $e) {
            error_log('Rate limit engelleme hatası: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Rate limit kaydını sıfırlar
     * 
     * @param string $key Anahtar
     * @param string $ipAddress IP adresi
     * @return bool
     */
    private function resetRateLimit(string $key, string $ipAddress)
    {
        try {
            $stmt = $this->db->prepare("
                UPDATE rate_limits 
                SET attempts = 0, blocked_until = NULL, last_attempt_at = NOW()
                WHERE `key` = :key AND ip_address = :ip_address
            ");
            
            return $stmt->execute([
                'key' => $key,
                'ip_address' => $ipAddress
            ]);
        } catch (PDOException $e) {
            error_log('Rate limit sıfırlama hatası: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Rate limit istatistiklerini alır
     * 
     * @return array
     */
    public function getStats()
    {
        try {
            // Toplam kayıt sayısı
            $stmtTotal = $this->db->query("SELECT COUNT(*) as total FROM rate_limits");
            $totalRecords = $stmtTotal->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Engellenen kayıt sayısı
            $stmtBlocked = $this->db->query("SELECT COUNT(*) as blocked FROM rate_limits WHERE blocked_until IS NOT NULL AND blocked_until > NOW()");
            $blockedRecords = $stmtBlocked->fetch(PDO::FETCH_ASSOC)['blocked'];
            
            // Son 24 saatteki deneme sayısı
            $stmt24h = $this->db->query("SELECT SUM(attempts) as attempts FROM rate_limits WHERE last_attempt_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)");
            $attempts24h = $stmt24h->fetch(PDO::FETCH_ASSOC)['attempts'];
            
            return [
                'total_records' => $totalRecords,
                'blocked_records' => $blockedRecords,
                'attempts_24h' => $attempts24h ?? 0
            ];
        } catch (PDOException $e) {
            error_log('Rate limit istatistikleri alma hatası: ' . $e->getMessage());
            return [
                'total_records' => 0,
                'blocked_records' => 0,
                'attempts_24h' => 0
            ];
        }
    }
    
    /**
     * Engellenen IP'leri listeler
     * 
     * @param int $limit Maksimum kayıt sayısı
     * @return array
     */
    public function getBlockedIps(int $limit = 50)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM rate_limits 
                WHERE blocked_until IS NOT NULL AND blocked_until > NOW()
                ORDER BY blocked_until DESC
                LIMIT :limit
            ");
            
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Engellenen IP listesi alma hatası: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Eski rate limit kayıtlarını temizler
     * 
     * @param int $days Gün sayısı
     * @return int Silinen kayıt sayısı
     */
    public function cleanup(int $days = 30)
    {
        try {
            $stmt = $this->db->prepare("
                DELETE FROM rate_limits 
                WHERE last_attempt_at < DATE_SUB(NOW(), INTERVAL :days DAY)
                AND (blocked_until IS NULL OR blocked_until < NOW())
            ");
            
            $stmt->bindValue(':days', $days, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->rowCount();
        } catch (PDOException $e) {
            error_log('Rate limit temizleme hatası: ' . $e->getMessage());
            return 0;
        }
    }
}
