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

## Kurulum

Composer aracılığıyla paketi projenize ekleyin:

```bash
composer require vendor/laravel-license
```

Kurulumdan sonra, yapılandırma dosyasını yayınlamak için aşağıdaki komutu çalıştırın:

```bash
php artisan vendor:publish --tag=license-config
```

Görünüm dosyasını özelleştirmek isterseniz, aşağıdaki komutu çalıştırın:

```bash
php artisan vendor:publish --tag=license-views
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
