<?php

namespace ImTaxu\LaravelLicense\Helpers;

/**
 * Laravel File Facade için IDE helper
 */
class FileHelper
{
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
     * Dosyanın okunabilir olup olmadığını kontrol et
     *
     * @param string $path
     * @return bool
     */
    public static function isReadable(string $path): bool
    {
        return is_readable($path);
    }

    /**
     * Dosya içeriğini oku
     *
     * @param string $path
     * @return string
     */
    public static function get(string $path): string
    {
        return file_get_contents($path);
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
     * Dosyayı kopyala
     *
     * @param string $source
     * @param string $destination
     * @return bool
     */
    public static function copy(string $source, string $destination): bool
    {
        return copy($source, $destination);
    }

    /**
     * Dosyayı sil
     *
     * @param string $path
     * @return bool
     */
    public static function delete(string $path): bool
    {
        return unlink($path);
    }
}
