<?php

namespace ImTaxu\LaravelLicense\Helpers;

/**
 * Laravel Str sınıfı için IDE helper
 */
class StrHelper
{
    /**
     * Rastgele bir string oluştur
     *
     * @param int $length
     * @return string
     */
    public static function random(int $length = 16): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        
        return $randomString;
    }
}
