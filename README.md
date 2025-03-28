# Laravel License Manager

A license manager package for Laravel applications that provides robust license verification, activation, and obfuscation capabilities.

## Features

- License key validation and verification
- License activation and deactivation
- File integrity checks
- Code obfuscation for security
- Admin dashboard for license management
- Command-line tools for license operations
- Customizable license verification API
- Performance optimization with caching
- Customizable error page
- Automatic license checking with middleware
- Exemption settings for specific routes and IP addresses
- **Advanced Security:** Config file encryption and integrity checking
- **Manipulation Protection:** Automatic detection of changes made to the config file
- **Multi-language Support:** Built-in support for multiple languages
- **Hardware ID Verification:** Optional hardware fingerprinting for enhanced security
- **Rate Limiting:** Protection against API abuse

## Requirements

- PHP 8.2 or higher
- Laravel 11.0 or higher

## Installation

You can install the package via composer:

```bash
composer require imtaxu/laravel-license
```

After installation, run the following command to publish the configuration file:

```bash
php artisan vendor:publish --tag=license-config
```

If you want to customize the view file, run the following command:

```bash
php artisan vendor:publish --tag=license-views
```

To publish language files:

```bash
php artisan vendor:publish --tag=license-translations
```

## Configuration

Add the following variables to your `.env` file:

```
APP_LICENSE=your-license-key
APP_VERSION=your-app-version
```

You can customize the variables to be sent for license checking and other settings by opening the `config/license.php` file.

### Encrypting the Config File

After editing your license configuration file, run the following command to encrypt and protect the file:

```bash
php artisan license:obfuscate
```

This command obfuscates your configuration and route files, making them difficult to modify or tamper with. If the file content is altered or deleted after encryption, the system will automatically redirect to the license error page.

## Usage

### Middleware Usage

To protect routes with license checking, add the middleware to your routes:

```php
// In a route file
Route::middleware('license')->group(function () {
    // Your protected routes
});

// Or in a controller
public function __construct()
{
    $this->middleware('license');
}
```

### Facade Usage

You can use the License facade to check license status manually:

```php
use Imtaxu\LaravelLicense\Facades\License;

if (License::isValid()) {
    // License is valid
} else {
    // License is invalid
}
```

### License Activation

Activate a license key through the command line:

```bash
php artisan license:activate YOUR-LICENSE-KEY
```

Or use the provided activation form at `/license/activate`.

### Exemption Settings

You can configure exemptions for specific routes or IP addresses in the config file:

```php
'exemptions' => [
    'routes' => [
        'login',
        'register',
        'password/*',
    ],
    'ips' => [
        '127.0.0.1',
        '::1',
    ],
],
```

## Commands

- `php artisan license:activate` - Activate a license key
- `php artisan license:deactivate` - Deactivate the current license
- `php artisan license:verify` - Verify license status
- `php artisan license:obfuscate` - Obfuscate protected files

## Admin Dashboard

The package includes a license management dashboard. To set it up:

1. Copy the admin directory from the [Laravel License Admin Panel](https://github.com/imtaxu/laravel-license-admin) repository to your public server
2. Configure access to the dashboard through `config/license.php`
3. Access the dashboard at `https://your-domain.com/license-admin`

## License Server Setup

To create your own license server:

1. Copy the admin directory from the [Laravel License Admin Panel](https://github.com/imtaxu/laravel-license-admin) repository to your web server
2. Set up a database for storing license information
3. Update the admin configuration as needed
4. Secure the admin area with appropriate authentication

A license server implementation is available in the [Laravel License Admin Panel](https://github.com/imtaxu/laravel-license-admin) repository. You can use this as a starting point for creating your own license server.

## License Features Field

The `features` field in the license system is a **completely optional** feature that allows you to dynamically manage license features by storing data in JSON format. This field facilitates feature-based license management for your applications.

### Use Cases

- **Create different license packages**: Define different levels such as basic, premium, enterprise
- **Module access control**: Determine which modules each license can access
- **Set usage limits**: Define limits such as user count, storage space, process limits
- **Custom client configurations**: Define custom settings for each client

### Example JSON Format

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

### Feature Definitions and Usage Examples

| Feature | Description | Usage Example |
|----------|------------|---------------|
| `premium_access` | Permission to access premium features | `if ($license->hasFeature('premium_access')) { // Show premium features }` |
| `max_users` | Maximum number of users that can be added to the system | `if (count($users) < $license->hasFeature('max_users', 10)) { // Add new user }` |
| `modules` | List of modules with access permission | `if ($license->hasModuleAccess('reporting')) { // Show reporting module }` |
| `storage_limit` | Storage space limit | `if ($fileSize + $currentUsage < parseSize($license->hasFeature('storage_limit', '1GB'))) { // Upload file }` |
| `api_rate_limit` | API request limit | `if ($requestCount < $license->hasFeature('api_rate_limit', 100)) { // Process API request }` |

### Usage

```php
// Get all features
$features = License::getLicenseFeatures();

// Check a specific feature
$maxUsers = License::hasFeature('max_users', 10); // Default value: 10

// Check module access
if (License::hasModuleAccess('reporting')) {
    // Has access to reporting module
} else {
    // No access to reporting module
}
```

## Integration with Permission Systems

The package supports various permission systems to control admin access:

```php
// config/license.php
'admin_check' => 'role', // Options: 'role', 'permission', 'is_admin', 'custom'
'admin_role' => 'admin',
'admin_permission' => 'manage_licenses',
```

You can extend `AdminLicenseMiddleware` for custom authorization logic.

## Security

This package includes several security features:

- Code obfuscation
- Config file integrity checking
- Encrypted storage of license information
- Hardware ID verification
- Rate limiting for API requests
- Domain validation
- XOR encryption with unique keys
- Dynamic variable name scrambling

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This package is open-sourced software licensed under the MIT license.

---

# Laravel Lisans Yöneticisi

Laravel uygulamaları için güçlü lisans doğrulama, aktivasyon ve kod karıştırma özellikleri sunan bir lisans yönetim paketidir.

## Özellikler

- Lisans anahtarı doğrulama ve kontrol
- Lisans aktivasyonu ve deaktivasyonu
- Dosya bütünlüğü kontrolleri
- Güvenlik için kod karıştırma
- Lisans yönetimi için admin paneli
- Lisans işlemleri için komut satırı araçları
- Özelleştirilebilir lisans doğrulama API'si
- Önbelleğe alma ile performans optimizasyonu
- Özelleştirilebilir hata sayfası
- Middleware ile otomatik lisans kontrolü
- Belirli rotalar ve IP adresleri için muafiyet ayarları
- **Gelişmiş Güvenlik:** Config dosyasını şifreleme ve bütünlük kontrolü
- **Manipülasyon Koruması:** Config dosyasında yapılan değişiklikleri otomatik tespit etme
- **Çoklu Dil Desteği:** Birden fazla dil için yerleşik destek
- **Donanım ID Doğrulama:** Gelişmiş güvenlik için isteğe bağlı donanım parmak izi
- **Hız Sınırlama:** API kötüye kullanımına karşı koruma

## Gereksinimler

- PHP 8.2 veya üstü
- Laravel 11.0 veya üstü

## Kurulum

Composer aracılığıyla paketi projenize ekleyin:

```bash
composer require imtaxu/laravel-license
```

Kurulumdan sonra, yapılandırma dosyasını yayınlamak için aşağıdaki komutu çalıştırın:

```bash
php artisan vendor:publish --tag=license-config
```

Görünüm dosyasını özelleştirmek isterseniz, aşağıdaki komutu çalıştırın:

```bash
php artisan vendor:publish --tag=license-views
```

Dil dosyalarını yayınlamak için:

```bash
php artisan vendor:publish --tag=license-translations
```

## Yapılandırma

`.env` dosyanıza aşağıdaki değişkenleri ekleyin:

```
APP_LICENSE=your-license-key
APP_VERSION=your-app-version
```

`config/license.php` dosyasını açarak lisans kontrolü için gönderilecek değişkenleri ve diğer ayarları özelleştirebilirsiniz.

### Config Dosyasını Şifreleme

Lisans yapılandırma dosyanızı düzenledikten sonra, dosyayı şifrelemek ve korumak için aşağıdaki komutu çalıştırın:

```bash
php artisan license:obfuscate
```

Bu komut, yapılandırma ve rota dosyalarınızı karıştırarak değiştirilmesini veya kurcalanmasını zorlaştırır. Şifreleme işlemi sonrasında, dosya içeriği değiştirilirse veya silinirse, sistem otomatik olarak lisans hata sayfasına yönlendirecektir.

## Kullanım

### Middleware Kullanımı

Rotaları lisans kontrolü ile korumak için, middleware'i rotalarınıza ekleyin:

```php
// Bir rota dosyasında
Route::middleware('license')->group(function () {
    // Korunan rotalarınız
});

// Veya bir controller'da
public function __construct()
{
    $this->middleware('license');
}
```

### Facade Kullanımı

Lisans durumunu manuel olarak kontrol etmek için License facade'ini kullanabilirsiniz:

```php
use Imtaxu\LaravelLicense\Facades\License;

if (License::isValid()) {
    // Lisans geçerli
} else {
    // Lisans geçersiz
}
```

### Lisans Aktivasyonu

Komut satırından bir lisans anahtarını aktifleştirin:

```bash
php artisan license:activate LİSANS-ANAHTARINIZ
```

Veya `/license/activate` adresindeki aktivasyon formunu kullanın.

### Muafiyet Ayarları

Belirli rotalar veya IP adresleri için muafiyetleri yapılandırma dosyasında ayarlayabilirsiniz:

```php
'exemptions' => [
    'routes' => [
        'login',
        'register',
        'password/*',
    ],
    'ips' => [
        '127.0.0.1',
        '::1',
    ],
],
```

## Komutlar

- `php artisan license:activate` - Lisans anahtarını aktifleştirir
- `php artisan license:deactivate` - Mevcut lisansı devre dışı bırakır
- `php artisan license:verify` - Lisans durumunu doğrular
- `php artisan license:obfuscate` - Korumalı dosyaları karıştırır

## Admin Paneli

Paket, lisans yönetim paneli içerir. Kurulum için:

1. [Laravel License Admin Panel](https://github.com/imtaxu/laravel-license-admin) deposundaki admin dizinini genel sunucunuza kopyalayın
2. `config/license.php` üzerinden panele erişimi yapılandırın
3. Panele `https://your-domain.com/license-admin` adresinden erişin

## Lisans Sunucusu Kurulumu

Kendi lisans sunucunuzu oluşturmak için:

1. [Laravel License Admin Panel](https://github.com/imtaxu/laravel-license-admin) deposundaki admin dizinini web sunucunuza kopyalayın
2. Lisans bilgilerini saklamak için bir veritabanı kurun
3. Admin yapılandırmasını gerektiği gibi güncelleyin
4. Admin alanını uygun kimlik doğrulama ile güvence altına alın

[Laravel License Admin Panel](https://github.com/imtaxu/laravel-license-admin) deposunda bir lisans sunucusu uygulaması bulunmaktadır. Kendi lisans sunucunuzu oluşturmak için bunu başlangıç noktası olarak kullanabilirsiniz.

## Lisans Özellikleri (Features) Alanı

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

### Kullanım

```php
// Tüm özellikleri al
$features = License::getLicenseFeatures();

// Belirli bir özelliği kontrol et
$maxUsers = License::hasFeature('max_users', 10); // Varsayılan değer: 10

// Modül erişimini kontrol et
if (License::hasModuleAccess('reporting')) {
    // Raporlama modülüne erişim var
} else {
    // Raporlama modülüne erişim yok
}
```

## İzin Sistemleri ile Entegrasyon

Paket, admin erişimini kontrol etmek için çeşitli izin sistemlerini destekler:

```php
// config/license.php
'admin_check' => 'role', // Seçenekler: 'role', 'permission', 'is_admin', 'custom'
'admin_role' => 'admin',
'admin_permission' => 'manage_licenses',
```

Özel yetkilendirme mantığı için `AdminLicenseMiddleware` sınıfını genişletebilirsiniz.

## Güvenlik

Bu paket şu güvenlik özelliklerini içerir:

- Kod karıştırma
- Config dosyası bütünlük kontrolü
- Lisans bilgilerinin şifrelenmiş depolaması
- Donanım ID doğrulama
- API istekleri için hız sınırlama
- Domain doğrulama
- Benzersiz anahtarlarla XOR şifreleme
- Dinamik değişken adı karıştırma

## Katkıda Bulunma

Katkılarınızı bekliyoruz! Lütfen bir Pull Request göndermekten çekinmeyin.

## Lisans

Bu paket, MIT lisansı altında lisanslanmış açık kaynaklı bir yazılımdır.
