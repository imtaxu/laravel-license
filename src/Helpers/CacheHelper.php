<?php

namespace ImTaxu\LaravelLicense\Helpers;

/**
 * Laravel Cache Facade için IDE helper
 */
class CacheHelper
{
    private static $cache = [];
    
    /**
     * Cache'de bir değerin olup olmadığını kontrol et
     *
     * @param string $key
     * @return bool
     */
    public static function has(string $key): bool
    {
        return isset(self::$cache[$key]);
    }
    
    /**
     * Cache'den bir değer al
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        return self::has($key) ? self::$cache[$key] : $default;
    }
    
    /**
     * Cache'e bir değer kaydet
     *
     * @param string $key
     * @param mixed $value
     * @param mixed $ttl
     * @return bool
     */
    public static function put(string $key, $value, $ttl = null): bool
    {
        self::$cache[$key] = $value;
        return true;
    }
    
    /**
     * Cache'den bir değeri sil
     *
     * @param string $key
     * @return bool
     */
    public static function forget(string $key): bool
    {
        if (self::has($key)) {
            unset(self::$cache[$key]);
            return true;
        }
        
        return false;
    }
}
