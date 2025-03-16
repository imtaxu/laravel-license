# License Management Panel

This panel is a simple web interface for license management for the Laravel License package. With this panel, you can create licenses, manage existing licenses, and track invalid license requests.

## Features

-   Secure login system
-   Modern and user-friendly interface
-   License creation and management
-   License status tracking
-   View invalid license requests
-   License renewal and domain change capabilities
-   Rate limiting protection against brute force attacks
-   Detailed analytics and reporting
-   Multi-user support with role-based access control

## Installation

1. Create the database tables:

```sql
-- Users table
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

-- Licenses table
CREATE TABLE `licenses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `license_key` varchar(100) NOT NULL,
  `domain` varchar(255) NULL,
  `owner_email` varchar(255) NULL,
  `status` enum('active','inactive','suspended','expired') NOT NULL DEFAULT 'active',
  `expires_at` date NULL,
  `max_instances` int(11) NOT NULL DEFAULT 1,
  `features` text NULL,
  `excluded_ips` text NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `license_key` (`license_key`),
  INDEX (`domain`),
  INDEX (`owner_email`),
  INDEX (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Invalid requests table
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

-- Login logs table
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

-- Rate limits table
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

2. Create an admin user:

```sql
INSERT INTO `users` (`username`, `password`, `name`, `email`, `created_at`, `updated_at`)
VALUES ('admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin User', 'admin@example.com', NOW(), NOW());
```

Note: The password above is set to "password". It is recommended to use a more secure password in a production environment.

3. Update the database connection information in the `config.php` file.

4. Run the panel on your web server.

## Security Measures

-   All passwords are hashed with bcrypt
-   Failed login attempts are logged
-   Session timeout control
-   Login actions are recorded with IP address and user agent
-   Rate limiting protection against brute force attacks
-   Temporary IP blocking after a certain number of failed attempts
-   CSRF protection for all forms
-   Input validation and sanitization

## API Integration

This panel works integrated with the API in the `LicenseServerExample.php` file. License verification requests are made through the API and the results are saved to the database.

## License

This software is distributed under the MIT license.

---

# Lisans Yönetim Paneli

Bu panel, Laravel License paketi için lisans yönetimini sağlayan basit bir web arayüzüdür. Panel sayesinde lisans oluşturma, lisansları yönetme ve geçersiz lisans isteklerini takip etme işlemlerini gerçekleştirebilirsiniz.

## Özellikler

-   Güvenli giriş sistemi
-   Modern ve kullanıcı dostu arayüz
-   Lisans oluşturma ve yönetme
-   Lisans durumlarını takip etme
-   Geçersiz lisans isteklerini görüntüleme
-   Lisans yenileme ve domain değişikliği yapabilme
-   Rate Limiting (hız sınırlama) ile brute force saldırılarına karşı koruma
-   Detaylı analitik ve raporlama
-   Rol tabanlı erişim kontrolü ile çok kullanıcılı destek

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
  `license_key` varchar(100) NOT NULL,
  `domain` varchar(255) NULL,
  `owner_email` varchar(255) NULL,
  `status` enum('active','inactive','suspended','expired') NOT NULL DEFAULT 'active',
  `expires_at` date NULL,
  `max_instances` int(11) NOT NULL DEFAULT 1,
  `features` text NULL,
  `excluded_ips` text NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `license_key` (`license_key`),
  INDEX (`domain`),
  INDEX (`owner_email`),
  INDEX (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
VALUES ('admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin Kullanıcı', 'admin@example.com', NOW(), NOW());
```

Not: Yukarıdaki şifre "password" olarak ayarlanmıştır. Gerçek ortamda daha güvenli bir şifre kullanmanız önerilir.

3. `config.php` dosyasında veritabanı bağlantı bilgilerini güncelleyin.

4. Paneli web sunucunuzda çalıştırın.

## Güvenlik Önlemleri

-   Tüm şifreler bcrypt ile hashlenir
-   Başarısız giriş denemeleri loglanır
-   Oturum zaman aşımı kontrolü yapılır
-   Giriş işlemleri IP adresi ve user agent ile kaydedilir
-   Rate Limiting ile brute force saldırılarına karşı koruma sağlanır
-   Belirli sayıda başarısız deneme sonrası IP adresi geçici olarak engellenir
-   Tüm formlar için CSRF koruması
-   Girdi doğrulama ve temizleme

## Özellikler (Features) Alanı

Lisans sisteminde `features` alanı, **tamamen opsiyonel** bir özelliktir ve JSON formatında veri saklayarak lisansların özelliklerini dinamik olarak yönetmenize olanak tanır. Bu alan, uygulamalarınızın lisans bazlı özellik yönetimini kolaylaştırır.

### Kullanım Alanları

-   **Farklı lisans paketleri oluşturma**: Temel, premium, kurumsal gibi farklı seviyeler tanımlayabilirsiniz
-   **Modül erişimi kontrolü**: Hangi lisansın hangi modüllere erişebileceğini belirleyebilirsiniz
-   **Kullanım sınırları belirleme**: Kullanıcı sayısı, depolama alanı, işlem limitleri gibi sınırlar tanımlayabilirsiniz
-   **Özel müşteri yapılandırmaları**: Her müşteriye özel ayarlar tanımlayabilirsiniz

### Örnek JSON Formatı

```json
{
  "premium_access": true,
  "max_users": 50,
  "modules": ["reporting", "analytics", "export"],
  "storage_limit": "10GB",
  "api_rate_limit": 1000,
  "custom_settings": {
    "theme": "dark",
    "notification_channels": ["email", "sms"],
    "data_retention_days": 90
  }
}
```

### Özellik Tanımları ve Kullanım Örnekleri

| Özellik | Açıklama | Kullanım Örneği |
|----------|------------|---------------|
| `premium_access` | Premium özelliklere erişim izni | `if ($license->hasFeature('premium_access')) { // Premium özellikleri göster }` |
| `max_users` | Sisteme eklenebilecek maksimum kullanıcı sayısı | `if (count($users) < $license->hasFeature('max_users', 10)) { // Yeni kullanıcı ekle }` |
| `modules` | Erişim izni olan modüllerin listesi | `if ($license->hasModuleAccess('reporting')) { // Raporlama modülünü göster }` |
| `storage_limit` | Depolama alanı limiti | `if ($fileSize + $currentUsage < parseSize($license->hasFeature('storage_limit', '1GB'))) { // Dosyayı yükle }` |
| `api_rate_limit` | API istek limiti | `if ($requestCount < $license->hasFeature('api_rate_limit', 100)) { // API isteğini işle }` |

### Uygulama Entegrasyonu

Features alanı, uygulamalarınızda şu şekilde kullanılabilir:

1. **Kullanıcı Sayısı Kontrolü**: `max_users` değeri, sisteme kaydedilebilecek maksimum kullanıcı sayısını belirler. Yeni kullanıcı ekleme işlemlerinde bu değer kontrol edilmelidir.

2. **Modül Erişimi**: `modules` dizisi, lisansın hangi modüllere erişebileceğini belirler. Menü öğelerini gösterirken veya sayfa erişimlerini kontrol ederken kullanılabilir.

3. **Depolama Alanı**: `storage_limit` değeri, kullanıcının toplam depolama alanını belirler. Dosya yükleme işlemlerinde bu limit kontrol edilmelidir.

4. **API Limitleri**: `api_rate_limit` değeri, belirli bir süre içinde yapılabilecek API isteği sayısını belirler.

Bu yapı, veritabanı şemanızı değiştirmeden yeni özellikler eklemenize olanak tanır ve lisans sisteminin gelecekteki ihtiyaçlara göre kolayca genişletilebilmesini sağlar.

## API Entegrasyonu

Bu panel, `LicenseServerExample.php` dosyasında bulunan API ile entegre çalışır. Lisans doğrulama istekleri API üzerinden yapılır ve sonuçlar veritabanına kaydedilir.

## Lisans

Bu yazılım MIT lisansı altında dağıtılmaktadır.
