<?php
/**
 * Veritabanı kurulum scripti
 */

// Veritabanı bağlantı bilgilerini al
require_once 'config.php';

try {
    // Veritabanı bağlantısı (veritabanı olmadan)
    $dsn = "mysql:host=" . DB_HOST . ";charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    
    // Veritabanı var mı kontrol et
    $stmt = $pdo->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '" . DB_NAME . "'");
    $dbExists = $stmt->fetchColumn();
    
    if (!$dbExists) {
        echo "Veritabanı oluşturuluyor: " . DB_NAME . "<br/>";
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        echo "Veritabanı oluşturuldu.<br/>";
    } else {
        echo "Veritabanı zaten mevcut.<br/>";
    }
    
    // Veritabanını seç
    $pdo->exec("USE `" . DB_NAME . "`");
    
    // Kullanıcılar tablosunu oluştur
    echo "Kullanıcılar tablosu oluşturuluyor...<br/>";
    $pdo->exec("CREATE TABLE IF NOT EXISTS `users` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(100) NOT NULL,
        `email` VARCHAR(100) NOT NULL UNIQUE,
        `password` VARCHAR(255) NOT NULL,
        `role` ENUM('admin', 'user') NOT NULL DEFAULT 'user',
        `last_login` DATETIME NULL,
        `created_at` DATETIME NOT NULL,
        `updated_at` DATETIME NOT NULL,
        INDEX (`email`),
        INDEX (`role`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "Kullanıcılar tablosu oluşturuldu.<br/>";
    
    // Giriş logları tablosunu oluştur
    echo "Giriş logları tablosu oluşturuluyor...<br/>";
    $pdo->exec("CREATE TABLE IF NOT EXISTS `login_logs` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT NULL,
        `ip_address` VARCHAR(45) NOT NULL,
        `user_agent` VARCHAR(255) NOT NULL,
        `success` TINYINT(1) NOT NULL DEFAULT 0,
        `username` VARCHAR(100) NULL,
        `created_at` DATETIME NOT NULL,
        INDEX (`user_id`),
        INDEX (`ip_address`),
        INDEX (`created_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "Giriş logları tablosu oluşturuldu.<br/>";
    
    // Lisanslar tablosunu oluştur
    echo "Lisanslar tablosu oluşturuluyor...<br/>";
    $pdo->exec("CREATE TABLE IF NOT EXISTS `licenses` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `license_key` VARCHAR(100) NOT NULL UNIQUE,
        `domain` VARCHAR(255) NULL,
        `owner_email` VARCHAR(255) NULL,
        `status` ENUM('active', 'inactive', 'suspended', 'expired') NOT NULL DEFAULT 'active',
        `expires_at` DATE NULL,
        `max_instances` INT NOT NULL DEFAULT 1,
        `features` TEXT NULL,
        `excluded_ips` TEXT NULL,
        `created_at` DATETIME NOT NULL,
        `updated_at` DATETIME NOT NULL,
        INDEX (`license_key`),
        INDEX (`domain`),
        INDEX (`owner_email`),
        INDEX (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "Lisanslar tablosu oluşturuldu.<br/>";
    
    // Geçersiz istekler tablosunu oluştur
    echo "Geçersiz istekler tablosu oluşturuluyor...<br/>";
    $pdo->exec("CREATE TABLE IF NOT EXISTS `invalid_requests` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `ip_address` VARCHAR(45) NOT NULL,
        `license_key` VARCHAR(100) NULL,
        `domain` VARCHAR(255) NULL,
        `reason` VARCHAR(255) NOT NULL,
        `created_at` DATETIME NOT NULL,
        INDEX (`ip_address`),
        INDEX (`license_key`),
        INDEX (`domain`),
        INDEX (`created_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "Geçersiz istekler tablosu oluşturuldu.<br/>";
    
    // Bildirimler tablosunu oluştur
    echo "Bildirimler tablosu oluşturuluyor...<br/>";
    $pdo->exec("CREATE TABLE IF NOT EXISTS `dismissed_notifications` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT NOT NULL,
        `notification_key` VARCHAR(100) NOT NULL,
        `dismissed_until` DATETIME NOT NULL,
        `created_at` DATETIME NOT NULL,
        INDEX (`user_id`),
        INDEX (`notification_key`),
        UNIQUE KEY (`user_id`, `notification_key`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "Bildirimler tablosu oluşturuldu.<br/>";
    
    echo "Veritabanı kurulumu tamamlandı.<br/>";
    
} catch (PDOException $e) {
    die("Veritabanı hatası: " . $e->getMessage() . "<br/>");
}
