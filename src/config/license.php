<?php

// env fonksiyonu Laravel tarafından sağlanır
// Bu satır sadece IDE için yardımcı olması amacıyla eklenmiştir
if (!function_exists('env')) {
    function env($key, $default = null) {
        return $default;
    }
}

return [
    /*
    |--------------------------------------------------------------------------
    | Lisans API Adresi
    |--------------------------------------------------------------------------
    |
    | Lisans kontrolünün yapılacağı API adresi
    |
    */
    'api_url' => env('LICENSE_API_URL', 'https://license.example.com/api/verify'),

    /*
    |--------------------------------------------------------------------------
    | Lisans Anahtarı
    |--------------------------------------------------------------------------
    |
    | Uygulamanızın lisans anahtarı
    |
    */
    'license_key' => env('LICENSE_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Lisans Kontrolü İçin Gönderilecek Değişkenler
    |--------------------------------------------------------------------------
    |
    | Lisans kontrolü sırasında API'ye gönderilecek değişkenler
    |
    */
    'variables' => [
        'domain' => $_SERVER['SERVER_NAME'] ?? env('APP_URL', 'localhost'),
        'ip' => $_SERVER['SERVER_ADDR'] ?? '127.0.0.1',
        'app_name' => env('APP_NAME', 'Laravel'),
        // Diğer değişkenler buraya eklenebilir
    ],

    /*
    |--------------------------------------------------------------------------
    | Lisans Kontrol Sıklığı (saniye)
    |--------------------------------------------------------------------------
    |
    | Lisans kontrolünün ne sıklıkla yapılacağı (saniye cinsinden)
    | Varsayılan: 1 gün (86400 saniye)
    |
    */
    'check_frequency' => env('LICENSE_CHECK_FREQUENCY', 86400),

    /*
    |--------------------------------------------------------------------------
    | Lisans Hatası Durumunda Yönlendirilecek Sayfa
    |--------------------------------------------------------------------------
    |
    | Lisans geçersiz olduğunda kullanıcının yönlendirileceği sayfa
    |
    */
    'error_route' => 'license.error',

    /*
    |--------------------------------------------------------------------------
    | Lisans Kontrolünden Muaf Tutulacak Rotalar
    |--------------------------------------------------------------------------
    |
    | Bu rotalarda lisans kontrolü yapılmayacaktır
    |
    */
    'excluded_routes' => [
        'license.error',
        'login',
        'logout',
        // Diğer muaf rotalar buraya eklenebilir
    ],

    /*
    |--------------------------------------------------------------------------
    | Lisans Kontrolünden Muaf Tutulacak IP Adresleri
    |--------------------------------------------------------------------------
    |
    | Bu IP adreslerinden gelen isteklerde lisans kontrolü yapılmayacaktır
    |
    */
    'excluded_ips' => [
        '127.0.0.1',
        // Diğer muaf IP adresleri buraya eklenebilir
    ],
];
