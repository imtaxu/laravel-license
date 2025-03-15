<?php

namespace ImTaxu\LaravelLicense\Helpers;

/**
 * Laravel Command sınıfı için IDE helper
 */
class CommandHelper
{
    /**
     * Komut imzası
     *
     * @var string
     */
    protected $signature;

    /**
     * Komut açıklaması
     *
     * @var string
     */
    protected $description;

    /**
     * Hata mesajı yazdır
     *
     * @param string $message
     * @return void
     */
    public function error(string $message): void
    {
        echo "[ERROR] $message" . PHP_EOL;
    }

    /**
     * Bilgi mesajı yazdır
     *
     * @param string $message
     * @return void
     */
    public function info(string $message): void
    {
        echo "[INFO] $message" . PHP_EOL;
    }

    /**
     * Satır yazdır
     *
     * @param string $message
     * @return void
     */
    public function line(string $message): void
    {
        echo "$message" . PHP_EOL;
    }
}
