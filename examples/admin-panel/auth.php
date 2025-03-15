<?php
/**
 * Lisans Yönetim Paneli Kimlik Doğrulama Sınıfı
 */

class Auth {
    private $db;
    
    /**
     * Yapıcı metod
     */
    public function __construct() {
        $this->db = getDbConnection();
    }
    
    /**
     * Kullanıcı girişi yapar
     * 
     * @param string $username Kullanıcı adı
     * @param string $password Şifre
     * @return bool
     */
    public function login($username, $password) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
            $stmt->execute(['username' => $username]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                // Kullanıcı bilgilerini session'a kaydet
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['last_activity'] = time();
                
                // Giriş logunu kaydet
                $this->logLogin($user['id'], true);
                
                return true;
            }
            
            // Başarısız giriş logunu kaydet
            $this->logLogin(0, false, $username);
            
            return false;
        } catch (PDOException $e) {
            error_log("Giriş hatası: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Kullanıcı çıkışı yapar
     */
    public function logout() {
        // Session'ı temizle
        session_unset();
        session_destroy();
        
        // Yeni session başlat
        session_start();
    }
    
    /**
     * Kullanıcının giriş yapmış olup olmadığını kontrol eder
     * 
     * @return bool
     */
    public function isLoggedIn() {
        // Session'da kullanıcı bilgisi var mı?
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['last_activity'])) {
            return false;
        }
        
        // Session timeout kontrolü
        if (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT) {
            $this->logout();
            return false;
        }
        
        // Son aktivite zamanını güncelle
        $_SESSION['last_activity'] = time();
        
        return true;
    }
    
    /**
     * Giriş denemelerini loglar
     * 
     * @param int $userId Kullanıcı ID
     * @param bool $success Başarılı mı?
     * @param string $username Kullanıcı adı (başarısız giriş için)
     */
    private function logLogin($userId, $success, $username = '') {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO login_logs (user_id, ip_address, user_agent, success, username, created_at)
                VALUES (:user_id, :ip_address, :user_agent, :success, :username, NOW())
            ");
            
            $stmt->execute([
                'user_id' => $userId,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                'success' => $success ? 1 : 0,
                'username' => $username
            ]);
        } catch (PDOException $e) {
            error_log("Giriş logu hatası: " . $e->getMessage());
        }
    }
    
    /**
     * Kullanıcı bilgilerini getirir
     * 
     * @param int $userId Kullanıcı ID
     * @return array|false
     */
    public function getUserInfo($userId = null) {
        if ($userId === null) {
            $userId = $_SESSION['user_id'] ?? 0;
        }
        
        if (!$userId) {
            return false;
        }
        
        try {
            $stmt = $this->db->prepare("SELECT id, username, name, email, last_login, created_at FROM users WHERE id = :id LIMIT 1");
            $stmt->execute(['id' => $userId]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Kullanıcı bilgisi hatası: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Giriş yapmış kullanıcının ID'sini döndürür
     * 
     * @return int|null Kullanıcı ID veya null
     */
    public function getUserId() {
        if ($this->isLoggedIn()) {
            return $_SESSION['user_id'] ?? null;
        }
        
        return null;
    }
}
