<?php

namespace ImTaxu\LaravelLicense\Helpers;

/**
 * Laravel Lang Facade için IDE helper
 */
class LangHelper
{
    /**
     * Bir çeviri anahtarını çevirir
     *
     * @param string $key
     * @param array $replace
     * @param string|null $locale
     * @return string
     */
    public static function get(string $key, array $replace = [], string $locale = null): string
    {
        // Çeviri anahtarından varsayılan bir metin oluştur
        $parts = explode('.', $key);
        $lastPart = end($parts);
        
        // Değişkenleri yerleştir
        $text = $lastPart;
        foreach ($replace as $search => $value) {
            $text = str_replace(':' . $search, $value, $text);
        }
        
        return $text;
    }
    
    /**
     * Bir çeviri anahtarını çevirir (get metodunun takma adı)
     *
     * @param string $key
     * @param array $replace
     * @param string|null $locale
     * @return string
     */
    public static function trans(string $key, array $replace = [], string $locale = null): string
    {
        return self::get($key, $replace, $locale);
    }
    
    /**
     * Bir çeviri anahtarının var olup olmadığını kontrol eder
     *
     * @param string $key
     * @param string|null $locale
     * @return bool
     */
    public static function has(string $key, string $locale = null): bool
    {
        return true;
    }
}
