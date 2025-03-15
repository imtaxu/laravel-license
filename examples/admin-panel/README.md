# Lisans Yönetim Paneli

Bu panel, Laravel License paketi için lisans yönetimini sağlayan basit bir web arayüzüdür. Panel sayesinde lisans oluşturma, lisansları yönetme ve geçersiz lisans isteklerini takip etme işlemlerini gerçekleştirebilirsiniz.

## Özellikler

- Güvenli giriş sistemi
- Modern ve kullanıcı dostu arayüz
- Lisans oluşturma ve yönetme
- Lisans durumlarını takip etme
- Geçersiz lisans isteklerini görüntüleme
- Lisans yenileme ve domain değişikliği yapabilme
- Rate Limiting (hız sınırlama) ile brute force saldırılarına karşı koruma

## Kurulum

1. Veritabanı tablolarını oluşturun:

```sql
-- Kullanıcılar tablosu
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `last_login` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Lisanslar tablosu
CREATE TABLE `licenses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `license_key` varchar(255) NOT NULL,
  `status` enum('active','inactive','suspended') NOT NULL DEFAULT 'active',
  `domain` varchar(255) NOT NULL,
  `client_ip` varchar(45) DEFAULT NULL,
  `owner_email` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `max_instances` int(11) NOT NULL DEFAULT 1,
  `client_signature` varchar(255) DEFAULT NULL,
  `excluded_ips` text DEFAULT NULL,
  `features` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `license_key` (`license_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Geçersiz istekler tablosu
CREATE TABLE `invalid_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `license_key` varchar(255) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `domain` varchar(255) NOT NULL,
  `reason` varchar(255) NOT NULL,
  `request_data` text DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Giriş logları tablosu
CREATE TABLE `login_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `success` tinyint(1) NOT NULL DEFAULT 0,
  `username` varchar(50) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Rate limits tablosu
CREATE TABLE `rate_limits` (
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
```

2. Admin kullanıcısı oluşturun:

```sql
INSERT INTO `users` (`username`, `password`, `name`, `email`, `created_at`, `updated_at`) 
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin Kullanıcı', 'admin@example.com', NOW(), NOW());
```
Not: Yukarıdaki şifre "password" olarak ayarlanmıştır. Gerçek ortamda daha güvenli bir şifre kullanmanız önerilir.

3. `config.php` dosyasında veritabanı bağlantı bilgilerini güncelleyin.

4. Paneli web sunucunuzda çalıştırın.

## Güvenlik Önlemleri

- Tüm şifreler bcrypt ile hashlenir
- Başarısız giriş denemeleri loglanır
- Oturum zaman aşımı kontrolü yapılır
- Giriş işlemleri IP adresi ve user agent ile kaydedilir
- Rate Limiting ile brute force saldırılarına karşı koruma sağlanır
- Belirli sayıda başarısız deneme sonrası IP adresi geçici olarak engellenir

## API Entegrasyonu

Bu panel, `LicenseServerExample.php` dosyasında bulunan API ile entegre çalışır. Lisans doğrulama istekleri API üzerinden yapılır ve sonuçlar veritabanına kaydedilir.

## Lisans

Bu yazılım MIT lisansı altında dağıtılmaktadır.
