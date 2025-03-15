<?php
/**
 * Lisans Yönetim Paneli Yapılandırma Dosyası
 */

// Veritabanı bağlantı bilgileri
define('DB_HOST', 'localhost');
define('DB_NAME', 'license_manager');
define('DB_USER', 'root');
define('DB_PASS', '');

// Uygulama ayarları
define('APP_NAME', 'Lisans Yönetim Paneli');
define('APP_URL', 'http://localhost/license-manager');
define('APP_VERSION', '1.0.0');

// Güvenlik ayarları
define('HASH_SALT', 'lisans-yonetim-gizli-anahtar-degistirin');
define('SESSION_TIMEOUT', 3600); // 1 saat

// Lisans ayarları
define('DEFAULT_LICENSE_DURATION', 365); // Gün cinsinden
define('MAX_INVALID_ATTEMPTS', 5); // Maksimum geçersiz lisans kontrolü denemesi

// Veritabanı bağlantısı
function getDbConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        
        return new PDO($dsn, DB_USER, DB_PASS, $options);
    } catch (PDOException $e) {
        die("Veritabanı bağlantı hatası: " . $e->getMessage());
    }
}
