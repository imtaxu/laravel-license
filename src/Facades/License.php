<?php

namespace ImTaxu\LaravelLicense\Facades;

// Bu satır sadece IDE için yardımcı olması amacıyla eklenmiştir
if (!class_exists('Illuminate\\Support\\Facades\\Facade')) {
    class_alias('stdClass', 'Illuminate\\Support\\Facades\\Facade');
}

use Illuminate\Support\Facades\Facade;
use ImTaxu\LaravelLicense\LicenseChecker;

class License extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return LicenseChecker::class;
    }
}
