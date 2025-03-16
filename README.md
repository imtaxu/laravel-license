# Laravel License Package

This package provides a license verification system for your Laravel 11 and Laravel 12 applications.

## Features

- Customizable license verification API
- Customizable variables for license checks
- Performance optimization with caching
- Customizable error page
- Automatic license checking with middleware
- Exemption settings for specific routes and IP addresses
- **Advanced Security:** Config file encryption and integrity checking
- **Manipulation Protection:** Automatic detection of changes made to the config file
- **Multi-language Support:** Built-in support for multiple languages
- **Hardware ID Verification:** Optional hardware fingerprinting for enhanced security
- **Rate Limiting:** Protection against API abuse

## Installation

Add the package to your project via Composer:

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
LICENSE_API_URL=https://license.example.com/api/verify
LICENSE_KEY=your-license-key
LICENSE_CHECK_FREQUENCY=86400
```

You can customize the variables to be sent for license checking and other settings by opening the `config/license.php` file.

### Encrypting the Config File

After editing your license configuration file, run the following command to encrypt and protect the file:

```bash
php artisan license:obfuscate
```

### Middleware Usage

To protect routes with license checking, add the middleware to your routes:

```php
// In a route file
Route::middleware('license.check')->group(function () {
    // Your protected routes
});

// Or in a controller
public function __construct()
{
    $this->middleware('license.check');
}
```

### Facade Usage

You can use the License facade to check license status manually:

```php
use ImTaxu\LaravelLicense\Facades\License;

if (License::isValid()) {
    // License is valid
} else {
    // License is invalid
}
```

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

## Server Implementation

An example server implementation is included in the `examples` directory. You can use this as a starting point for creating your own license server.

## Security

This package includes several security features:

- Config file integrity checking
- Encrypted storage of license information
- Hardware ID verification
- Rate limiting for API requests
- Domain validation

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This package is open-sourced software licensed under the MIT license.

---

# Laravel Lisans Paketi

Bu paket, Laravel 11 ve Laravel 12 uygulamalarınız için lisans doğrulama sistemi sağlar.

## Özellikler

- Özelleştirilebilir lisans doğrulama API'si
- Lisans kontrolü için özelleştirilebilir değişkenler
- Önbelleğe alma ile performans optimizasyonu
- Özelleştirilebilir hata sayfası
- Middleware ile otomatik lisans kontrolü
- Belirli rotalar ve IP adresleri için muafiyet ayarları
- **Gelişmiş Güvenlik:** Config dosyasını şifreleme ve bütünlük kontrolü
- **Manipülasyon Koruması:** Config dosyasında yapılan değişiklikleri otomatik tespit etme
- **Çoklu Dil Desteği:** Birden fazla dil için yerleşik destek
- **Donanım ID Doğrulama:** Gelişmiş güvenlik için isteğe bağlı donanım parmak izi
- **Hız Sınırlama:** API kötüye kullanımına karşı koruma

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
LICENSE_API_URL=https://license.example.com/api/verify
LICENSE_KEY=your-license-key
LICENSE_CHECK_FREQUENCY=86400
```

`config/license.php` dosyasını açarak lisans kontrolü için gönderilecek değişkenleri ve diğer ayarları özelleştirebilirsiniz.

### Config Dosyasını Şifreleme

Lisans yapılandırma dosyanızı düzenledikten sonra, dosyayı şifrelemek ve korumak için aşağıdaki komutu çalıştırın:

```bash
php artisan license:obfuscate
```

### Middleware Kullanımı

Rotaları lisans kontrolü ile korumak için, middleware'i rotalarınıza ekleyin:

```php
// Bir rota dosyasında
Route::middleware('license.check')->group(function () {
    // Korunan rotalarınız
});

// Veya bir controller'da
public function __construct()
{
    $this->middleware('license.check');
}
```

### Facade Kullanımı

Lisans durumunu manuel olarak kontrol etmek için License facade'ini kullanabilirsiniz:

```php
use ImTaxu\LaravelLicense\Facades\License;

if (License::isValid()) {
    // Lisans geçerli
} else {
    // Lisans geçersiz
}
```

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

## Sunucu Uygulaması

`examples` dizininde örnek bir sunucu uygulaması bulunmaktadır. Kendi lisans sunucunuzu oluşturmak için bunu başlangıç noktası olarak kullanabilirsiniz.

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

## Güvenlik

Bu paket şu güvenlik özelliklerini içerir:

- Config dosyası bütünlük kontrolü
- Lisans bilgilerinin şifrelenmiş depolaması
- Donanım ID doğrulama
- API istekleri için hız sınırlama
- Domain doğrulama

## Katkıda Bulunma

Katkılarınızı bekliyoruz! Lütfen bir Pull Request göndermekten çekinmeyin.

## Lisans

Bu paket, MIT lisansı altında lisanslanmış açık kaynaklı bir yazılımdır.

Bu komut, config dosyanızı şifreleyecek ve manipülasyona karşı koruyacaktır. Şifreleme işlemi sonrasında, dosya içeriği değiştirilirse veya silinirse, sistem otomatik olarak lisans hata sayfasına yönlendirecektir.

## Kullanım

### Middleware ile Kullanım

Rotalarınızı lisans kontrolüne tabi tutmak için `license.check` middleware'ini kullanabilirsiniz:

```php
// Tek bir rota için
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware('license.check');

// Rota grubu için
Route::middleware('license.check')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    });
    
    Route::get('/settings', function () {
        return view('settings');
    });
});
```

### Facade ile Manuel Kontrol

Kodunuzun herhangi bir yerinde lisans kontrolü yapmak için `License` facade'ini kullanabilirsiniz:

```php
use Vendor\LaravelLicense\Facades\License;

if (License::check()) {
    // Lisans geçerli
} else {
    // Lisans geçersiz
}
```

### Lisans Hata Sayfası

Lisans geçersiz olduğunda kullanıcılar otomatik olarak bir hata sayfasına yönlendirilir. Bu sayfayı özelleştirmek için, önce görünüm dosyalarını yayınlayın ve ardından `resources/views/vendor/license/error.blade.php` dosyasını düzenleyin.

## Lisans Kontrolü İçin Rota Tanımlama

Servis sağlayıcınızda aşağıdaki rotayı tanımlayın:

```php
// app/Providers/RouteServiceProvider.php içinde
Route::get('/license-error', [\Vendor\LaravelLicense\Http\Controllers\LicenseController::class, 'showError'])
    ->name('license.error');
```

## Güvenlik Özellikleri

### Config Dosyası Şifreleme ve Bütünlük Kontrolü

Bu paket, lisans yapılandırma dosyanızı şifreleyerek ve bütünlüğünü kontrol ederek, lisans atlatma girişimlerine karşı koruma sağlar:

1. **Şifreleme:** `license:obfuscate` komutu, config dosyanızı XOR şifreleme ve Base64 kodlama kullanarak şifreler.
2. **Checksum Doğrulama:** Her istekte, config dosyasının bütünlüğü checksum ile kontrol edilir.
3. **Vendor Yedek Karşılaştırması:** Şifrelenmiş config dosyası, vendor klasöründeki bir yedekle karşılaştırılarak değişiklikler tespit edilir.
4. **Otomatik Yönlendirme:** Herhangi bir manipülasyon tespit edildiğinde, kullanıcı otomatik olarak lisans hata sayfasına yönlendirilir.

## Lisans

MIT
