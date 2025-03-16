<?php
/**
 * Admin kullanıcısı oluşturma scripti
 */

require_once 'config.php';
require_once 'functions.php';
require_once 'auth.php';

// Veritabanı bağlantısı
$db = getDbConnection();

// Admin kullanıcısı var mı kontrol et
$stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE email = 'admin@example.com'");
$stmt->execute();
$adminExists = (int)$stmt->fetchColumn() > 0;

if ($adminExists) {
    echo "Admin kullanıcısı zaten mevcut.\n";
} else {
    // Auth sınıfını başlat
    $auth = new Auth();
    
    // Admin kullanıcısını ekle
    $adminData = [
        'name' => 'Admin',
        'email' => 'admin@example.com',
        'password' => 'password',
        'role' => 'admin'
    ];
    
    $result = $auth->addUser($adminData);
    
    if ($result) {
        echo "Admin kullanıcısı başarıyla oluşturuldu.\n";
        echo "E-posta: admin@example.com\n";
        echo "Şifre: password\n";
    } else {
        echo "Admin kullanıcısı oluşturulurken bir hata oluştu.\n";
    }
}

// Kullanıcılar tablosunu kontrol et
try {
    $stmt = $db->prepare("SHOW TABLES LIKE 'users'");
    $stmt->execute();
    $tableExists = $stmt->rowCount() > 0;
    
    if (!$tableExists) {
        echo "Kullanıcılar tablosu bulunamadı. Tablo oluşturuluyor...\n";
        
        // Kullanıcılar tablosunu oluştur
        $sql = "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            role ENUM('admin', 'user') NOT NULL DEFAULT 'user',
            last_login DATETIME NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            INDEX (email),
            INDEX (role)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        
        $db->exec($sql);
        
        echo "Kullanıcılar tablosu oluşturuldu. Lütfen scripti tekrar çalıştırın.\n";
    } else {
        echo "Kullanıcılar tablosu mevcut.\n";
    }
    
    // Login logs tablosunu kontrol et
    $stmt = $db->prepare("SHOW TABLES LIKE 'login_logs'");
    $stmt->execute();
    $tableExists = $stmt->rowCount() > 0;
    
    if (!$tableExists) {
        echo "Giriş logları tablosu bulunamadı. Tablo oluşturuluyor...\n";
        
        // Giriş logları tablosunu oluştur
        $sql = "CREATE TABLE IF NOT EXISTS login_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NULL,
            ip_address VARCHAR(45) NOT NULL,
            user_agent VARCHAR(255) NOT NULL,
            success TINYINT(1) NOT NULL DEFAULT 0,
            username VARCHAR(100) NULL,
            created_at DATETIME NOT NULL,
            INDEX (user_id),
            INDEX (ip_address),
            INDEX (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        
        $db->exec($sql);
        
        echo "Giriş logları tablosu oluşturuldu.\n";
    } else {
        echo "Giriş logları tablosu mevcut.\n";
    }
} catch (PDOException $e) {
    echo "Veritabanı hatası: " . $e->getMessage() . "\n";
}
