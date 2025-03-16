<?php

// IDE helpers for Laravel Route class
if (!class_exists('ImTaxu\LaravelLicense\Helpers\RouteHelper')) {
    class RouteHelper {
        /**
         * Route GET metodu
         * @param string $uri
         * @param mixed $action
         * @return RouteHelper
         */
        public static function get($uri, $action = null) {
            return new self();
        }
        
        public static function post($uri, $action = null) {
            return new self();
        }
        
        public static function put($uri, $action = null) {
            return new self();
        }
        
        public static function patch($uri, $action = null) {
            return new self();
        }
        
        public static function delete($uri, $action = null) {
            return new self();
        }
        

        
        public static function options($uri, $action = null) {
            return new self();
        }
        
        public static function any($uri, $action = null) {
            return new self();
        }
        
        public static function match($methods, $uri, $action = null) {
            return new self();
        }
        

        
        public static function prefix($prefix) {
            return new self();
        }
        
        public static function domain($domain) {
            return new self();
        }
        
        // Statik name metodu
        public static function name($name) {
            return new self();
        }
        
        public static function namespace($namespace) {
            return new self();
        }
        
        public static function fallback($action) {
            return new self();
        }
        
        public static function redirect($uri, $destination, $status = 302) {
            return new self();
        }
        
        public static function view($uri, $view, $data = []) {
            return new self();
        }
        
        public static function resource($name, $controller, array $options = []) {
            return new self();
        }
        
        public static function apiResource($name, $controller, array $options = []) {
            return new self();
        }
        
        // Sadece statik group metodu
        public static function group($attributes, $callback) {
            if (is_callable($callback)) {
                $callback();
            }
            return new self();
        }
        
        // Statik middleware metodu
        public static function middleware($middleware) {
            return new self();
        }
        
        // Instance name metodu - kaldırıldı çünkü statik metot olarak zaten tanımlandı
        
        public function where($name, $expression = null) {
            return $this;
        }
    }
}

// Illuminate\Support\Facades\Route sınıfı için IDE yardımcısı
if (!class_exists('Illuminate\Support\Facades\Route')) {
    class_alias('ImTaxu\LaravelLicense\Helpers\RouteHelper', 'Illuminate\Support\Facades\Route');
}

// Route sınıfı için alias zaten tanımlandı

use Illuminate\Support\Facades\Route;
use ImTaxu\LaravelLicense\Http\Controllers\LicenseController;

// Lisans doğrulama rotaları
Route::get('/license-error', [LicenseController::class, 'showError'])
    ->name('license.error');

Route::middleware(['web'])->group(function () {
    Route::get('/license/verify', [LicenseController::class, 'verify'])->name('license.verify');
    Route::post('/license/activate', [LicenseController::class, 'activate'])->name('license.activate');
    Route::post('/license/deactivate', [LicenseController::class, 'deactivate'])->name('license.deactivate');
});
