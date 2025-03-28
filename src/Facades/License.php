<?php

namespace Imtaxu\LaravelLicense\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static bool isValid()
 * @method static bool verify()
 * @method static bool activate(string $licenseKey)
 * @method static bool deactivate()
 * @method static array|null getLicenseData()
 * @method static string|null getLicenseKey()
 * @method static void reportError(string $message)
 *
 * @see \Imtaxu\LaravelLicense\LicenseManager
 */
class License extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'license';
    }
}
