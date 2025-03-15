<?php

namespace ImTaxu\LaravelLicense\Helpers;

/**
 * Laravel Route Facade için IDE helper
 */
class RouteHelper
{
    /**
     * GET rotası tanımla
     *
     * @param string $uri
     * @param mixed $action
     * @return object
     */
    public static function get(string $uri, $action): object
    {
        return new class {};
    }
    
    /**
     * POST rotası tanımla
     *
     * @param string $uri
     * @param mixed $action
     * @return object
     */
    public static function post(string $uri, $action): object
    {
        return new class {};
    }
    
    /**
     * PUT rotası tanımla
     *
     * @param string $uri
     * @param mixed $action
     * @return object
     */
    public static function put(string $uri, $action): object
    {
        return new class {};
    }
    
    /**
     * DELETE rotası tanımla
     *
     * @param string $uri
     * @param mixed $action
     * @return object
     */
    public static function delete(string $uri, $action): object
    {
        return new class {};
    }
    
    /**
     * Rota grubu tanımla
     *
     * @param array $attributes
     * @param callable $callback
     * @return void
     */
    public static function group(array $attributes, callable $callback): void
    {
        $callback();
    }
}
