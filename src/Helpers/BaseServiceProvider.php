<?php

namespace ImTaxu\LaravelLicense\Helpers;

/**
 * Laravel ServiceProvider için temel sınıf
 */
class BaseServiceProvider
{
    /**
     * Servis sağlayıcısı kayıt metodu
     *
     * @return void
     */
    public function register()
    {
        // Boş implementasyon
    }

    /**
     * Servis sağlayıcısı boot metodu
     *
     * @return void
     */
    public function boot()
    {
        // Boş implementasyon
    }

    /**
     * Konfigürasyon dosyalarını birleştir
     *
     * @param string $path
     * @param string $key
     * @return void
     */
    public function mergeConfigFrom($path, $key)
    {
        // Boş implementasyon
    }

    /**
     * Dosyaları yayınla
     *
     * @param array $paths
     * @param string|array|null $groups
     * @return void
     */
    public function publishes($paths, $groups = null)
    {
        // Boş implementasyon
    }

    /**
     * View dosyalarını yükle
     *
     * @param string $path
     * @param string $namespace
     * @return void
     */
    public function loadViewsFrom($path, $namespace)
    {
        // Boş implementasyon
    }

    /**
     * Çeviri dosyalarını yükle
     *
     * @param string $path
     * @param string $namespace
     * @return void
     */
    public function loadTranslationsFrom($path, $namespace)
    {
        // Boş implementasyon
    }

    /**
     * Route dosyalarını yükle
     *
     * @param string $path
     * @return void
     */
    public function loadRoutesFrom($path)
    {
        // Boş implementasyon
    }
}
