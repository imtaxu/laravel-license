<?php

namespace ImTaxu\LaravelLicense;

use Illuminate\Support\ServiceProvider;
use ImTaxu\LaravelLicense\Console\Commands\ObfuscateConfigCommand;
use ImTaxu\LaravelLicense\Http\Middleware\LicenseCheckMiddleware;
use ImTaxu\LaravelLicense\Services\ConfigIntegrityService;

// IDE helper for Laravel ServiceProvider
if (!class_exists('Illuminate\Support\ServiceProvider')) {
    class_alias('ImTaxu\LaravelLicense\Helpers\ServiceProviderHelper', 'Illuminate\Support\ServiceProvider');
}

// Laravel helper fonksiyonları için IDE helper
if (!function_exists('config_path')) {
    function config_path($path = '') {
        return app()->basePath('config') . ($path ? '/' . $path : '');
    }
}

if (!function_exists('resource_path')) {
    function resource_path($path = '') {
        return app()->basePath('resources') . ($path ? '/' . $path : '');
    }
}

if (!function_exists('config')) {
    function config($key = null, $default = null) {
        if ($key === null) {
            return [];
        }
        
        return $default;
    }
}

class LaravelLicenseServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // mergeConfigFrom metodu ServiceProvider sınıfında tanımlıdır
        // Burada IDE için tam yolu belirtiyoruz
        \Illuminate\Support\ServiceProvider::mergeConfigFrom(
            __DIR__.'/config/license.php', 'license'
        );

        // app property'si ServiceProvider sınıfında tanımlıdır
        // Burada IDE için tam yolu belirtiyoruz
        $this->{'app'}->singleton(ConfigIntegrityService::class, function ($app) {
            return new ConfigIntegrityService();
        });
        
        // app property'si ServiceProvider sınıfında tanımlıdır
        // Burada IDE için tam yolu belirtiyoruz
        $this->{'app'}->singleton(LicenseChecker::class, function ($app) {
            return new LicenseChecker(config('license'));
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // publishes metodu ServiceProvider sınıfında tanımlıdır
        // Burada IDE için tam yolu belirtiyoruz
        \Illuminate\Support\ServiceProvider::publishes([
            __DIR__.'/config/license.php' => config_path('license.php'),
        ], 'license-config');

        // publishes metodu ServiceProvider sınıfında tanımlıdır
        // Burada IDE için tam yolu belirtiyoruz
        \Illuminate\Support\ServiceProvider::publishes([
            __DIR__.'/views' => resource_path('views/vendor/license'),
        ], 'license-views');

        // loadViewsFrom metodu ServiceProvider sınıfında tanımlıdır
        // Burada IDE için tam yolu belirtiyoruz
        \Illuminate\Support\ServiceProvider::loadViewsFrom(__DIR__.'/views', 'license');
        
        // Dil dosyalarını yükle
        // loadTranslationsFrom metodu ServiceProvider sınıfında tanımlıdır
        // Burada IDE için tam yolu belirtiyoruz
        \Illuminate\Support\ServiceProvider::loadTranslationsFrom(__DIR__.'/resources/lang', 'license');
        
        // Dil dosyalarını yayınla
        // publishes metodu ServiceProvider sınıfında tanımlıdır
        // Burada IDE için tam yolu belirtiyoruz
        \Illuminate\Support\ServiceProvider::publishes([
            __DIR__.'/resources/lang' => resource_path('lang/vendor/license'),
        ], 'license-translations');

        // app property'si ServiceProvider sınıfında tanımlıdır
        // Burada IDE için tam yolu belirtiyoruz
        $this->{'app'}['router']->aliasMiddleware('license.check', LicenseCheckMiddleware::class);
        
        // Lisans hata sayfası için rota tanımla
        // loadRoutesFrom metodu ServiceProvider sınıfında tanımlıdır
        // Burada IDE için tam yolu belirtiyoruz
        \Illuminate\Support\ServiceProvider::loadRoutesFrom(__DIR__.'/routes/web.php');
        
        // Komutları kaydet
        if ($this->{'app'}->runningInConsole()) {
            // commands metodu ServiceProvider sınıfında tanımlıdır
            // Burada IDE için tam yolu belirtiyoruz
            \Illuminate\Support\ServiceProvider::commands([
                ObfuscateConfigCommand::class,
            ]);
        }
    }
}
