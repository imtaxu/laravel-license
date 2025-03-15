<?php

use Illuminate\Support\Facades\Route;
use ImTaxu\LaravelLicense\Http\Controllers\LicenseController;

// Bu satır sadece IDE için yardımcı olması amacıyla eklenmiştir
if (!class_exists('Illuminate\\Support\\Facades\\Route')) {
    class Route {
        /**
         * Route::get metodu
         * 
         * @param string $uri
         * @param mixed $action
         * @return mixed
         */
        public static function get($uri, $action = null) {
            return new class {
                public function name($name) { return $this; }
                public function middleware($middleware) { return $this; }
            };
        }
        
        /**
         * Route::middleware metodu
         * 
         * @param array|string $middleware
         * @return mixed
         */
        public static function middleware($middleware) {
            return new class {
                public function group($attributes, $callback) { return $this; }
            };
        }
        
        /**
         * Route::post metodu
         * 
         * @param string $uri
         * @param mixed $action
         * @return mixed
         */
        public static function post($uri, $action = null) {
            return new class {
                public function name($name) { return $this; }
                public function middleware($middleware) { return $this; }
            };
        }
        
        /**
         * Route::group metodu
         * 
         * @param array $attributes
         * @param \Closure $callback
         * @return void
         */
        public static function group(array $attributes, $callback) {}
    }
    
    class_alias('Illuminate\\Support\\Facades\\Route', 'Illuminate\\Support\\Facades\\Route');
}

if (!class_exists('ImTaxu\\LaravelLicense\\Http\\Controllers\\LicenseController')) {
    class_alias('stdClass', 'ImTaxu\\LaravelLicense\\Http\\Controllers\\LicenseController');
}

Route::get('/license-error', [LicenseController::class, 'showError'])
    ->name('license.error');
