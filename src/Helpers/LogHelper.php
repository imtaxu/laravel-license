<?php

namespace ImTaxu\LaravelLicense\Helpers;

/**
 * Laravel Log Facade için IDE helper
 */
class LogHelper
{
    /**
     * Hata mesajı logla
     *
     * @param string $message
     * @return void
     */
    public static function error(string $message): void
    {
        error_log("[ERROR] $message");
    }

    /**
     * Bilgi mesajı logla
     *
     * @param string $message
     * @return void
     */
    public static function info(string $message): void
    {
        error_log("[INFO] $message");
    }

    /**
     * Uyarı mesajı logla
     *
     * @param string $message
     * @return void
     */
    public static function warning(string $message): void
    {
        error_log("[WARNING] $message");
    }

    /**
     * Debug mesajı logla
     *
     * @param string $message
     * @return void
     */
    public static function debug(string $message): void
    {
        error_log("[DEBUG] $message");
    }
}
