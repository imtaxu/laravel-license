<?php

namespace ImTaxu\LaravelLicense\Helpers;

/**
 * Laravel Storage Facade için IDE helper
 */
class StorageHelper
{
    /**
     * Dosya içeriğini oku
     *
     * @param string $path
     * @return string|null
     */
    public static function get(string $path): ?string
    {
        return file_exists($path) ? file_get_contents($path) : null;
    }

    /**
     * Dosyaya içerik yaz
     *
     * @param string $path
     * @param string $content
     * @return bool
     */
    public static function put(string $path, string $content): bool
    {
        return file_put_contents($path, $content) !== false;
    }

    /**
     * Dosyanın var olup olmadığını kontrol et
     *
     * @param string $path
     * @return bool
     */
    public static function exists(string $path): bool
    {
        return file_exists($path);
    }

    /**
     * Dosyayı sil
     *
     * @param string $path
     * @return bool
     */
    public static function delete(string $path): bool
    {
        return file_exists($path) ? unlink($path) : false;
    }
}
