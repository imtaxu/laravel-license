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
     * @param string $email Kullanıcı e-posta adresi
     * @param string $password Şifre
     * @return bool
     */
    public function login($email, $password) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                // Kullanıcı bilgilerini session'a kaydet
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['last_activity'] = time();
                
                // Giriş logunu kaydet
                $this->logLogin($user['id'], true);
                
                return true;
            }
            
            // Başarısız giriş logunu kaydet
            $this->logLogin(0, false, $email);
            
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
            $stmt = $this->db->prepare("SELECT id, name, email, role, last_login, created_at FROM users WHERE id = :id LIMIT 1");
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
    
    /**
     * Kullanıcı profil bilgilerini günceller
     * 
     * @param int $userId Kullanıcı ID
     * @param array $data Güncellenecek veriler
     * @return bool
     */
    public function updateUserProfile($userId, $data) {
        try {
            $updateFields = [];
            $params = ['id' => $userId];
            
            // Güncellenecek alanları belirle
            if (isset($data['name']) && !empty($data['name'])) {
                $updateFields[] = "name = :name";
                $params['name'] = $data['name'];
            }
            
            if (isset($data['email']) && !empty($data['email'])) {
                // E-posta benzersiz olmalı
                if ($this->isEmailUnique($data['email'], $userId)) {
                    $updateFields[] = "email = :email";
                    $params['email'] = $data['email'];
                } else {
                    return false; // E-posta zaten kullanımda
                }
            }
            
            // Şifre güncelleme
            if (isset($data['new_password']) && !empty($data['new_password'])) {
                $updateFields[] = "password = :password";
                $params['password'] = password_hash($data['new_password'], PASSWORD_DEFAULT);
            }
            
            if (empty($updateFields)) {
                return false; // Güncellenecek alan yok
            }
            
            $sql = "UPDATE users SET " . implode(", ", $updateFields) . ", updated_at = NOW() WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute($params);
            
            // Session bilgilerini güncelle
            if ($result && isset($params['name'])) {
                $_SESSION['name'] = $params['name'];
            }
            
            if ($result && isset($params['email'])) {
                $_SESSION['email'] = $params['email'];
            }
            
            return $result;
        } catch (PDOException $e) {
            error_log("Profil güncelleme hatası: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Kullanıcının mevcut şifresini kontrol eder
     * 
     * @param int $userId Kullanıcı ID
     * @param string $password Kontrol edilecek şifre
     * @return bool
     */
    public function verifyPassword($userId, $password) {
        try {
            $stmt = $this->db->prepare("SELECT password FROM users WHERE id = :id LIMIT 1");
            $stmt->execute(['id' => $userId]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                return true;
            }
            
            return false;
        } catch (PDOException $e) {
            error_log("Şifre doğrulama hatası: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * E-posta adresinin benzersiz olup olmadığını kontrol eder
     * 
     * @param string $email E-posta adresi
     * @param int $excludeUserId Hariç tutulacak kullanıcı ID
     * @return bool
     */
    public function isEmailUnique($email, $excludeUserId = 0) {
        try {
            $sql = "SELECT COUNT(*) FROM users WHERE email = :email";
            $params = ['email' => $email];
            
            if ($excludeUserId > 0) {
                $sql .= " AND id != :id";
                $params['id'] = $excludeUserId;
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $count = $stmt->fetchColumn();
            
            return ($count == 0);
        } catch (PDOException $e) {
            error_log("E-posta kontrol hatası: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Tüm kullanıcıları getirir
     * 
     * @return array|false
     */
    public function getAllUsers() {
        try {
            $stmt = $this->db->prepare("SELECT id, name, email, role, last_login, created_at FROM users ORDER BY name");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Kullanıcı listesi hatası: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Yeni kullanıcı ekler
     * 
     * @param array $data Kullanıcı verileri
     * @return int|false Eklenen kullanıcı ID veya false
     */
    public function addUser($data) {
        try {
            // E-posta benzersiz mi kontrol et
            if (!$this->isEmailUnique($data['email'])) {
                return false; // E-posta zaten kullanımda
            }
            
            $stmt = $this->db->prepare("
                INSERT INTO users (name, email, password, role, created_at, updated_at)
                VALUES (:name, :email, :password, :role, NOW(), NOW())
            ");
            
            $stmt->execute([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => password_hash($data['password'], PASSWORD_DEFAULT),
                'role' => $data['role'] ?? 'user'
            ]);
            
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Kullanıcı ekleme hatası: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Kullanıcı günceller
     * 
     * @param int $userId Kullanıcı ID
     * @param array $data Güncellenecek veriler
     * @return bool
     */
    public function updateUser($userId, $data) {
        try {
            $updateFields = [];
            $params = ['id' => $userId];
            
            // Güncellenecek alanları belirle
            if (isset($data['name'])) {
                $updateFields[] = "name = :name";
                $params['name'] = $data['name'];
            }
            
            if (isset($data['email'])) {
                // E-posta benzersiz olmalı
                if ($this->isEmailUnique($data['email'], $userId)) {
                    $updateFields[] = "email = :email";
                    $params['email'] = $data['email'];
                } else {
                    return false; // E-posta zaten kullanımda
                }
            }
            
            if (isset($data['role'])) {
                $updateFields[] = "role = :role";
                $params['role'] = $data['role'];
            }
            
            // Şifre güncelleme
            if (isset($data['password']) && !empty($data['password'])) {
                $updateFields[] = "password = :password";
                $params['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            }
            
            if (empty($updateFields)) {
                return false; // Güncellenecek alan yok
            }
            
            $sql = "UPDATE users SET " . implode(", ", $updateFields) . ", updated_at = NOW() WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Kullanıcı güncelleme hatası: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Kullanıcı siler
     * 
     * @param int $userId Kullanıcı ID
     * @return bool
     */
    public function deleteUser($userId) {
        // Kendini silmeyi engelle
        if ($userId == $this->getUserId()) {
            return false;
        }
        
        try {
            $stmt = $this->db->prepare("DELETE FROM users WHERE id = :id");
            return $stmt->execute(['id' => $userId]);
        } catch (PDOException $e) {
            error_log("Kullanıcı silme hatası: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Kullanıcının yönetici olup olmadığını kontrol eder
     * 
     * @return bool
     */
    public function isAdmin() {
        return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    }
}
