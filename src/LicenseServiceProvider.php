<?php

namespace Imtaxu\LaravelLicense;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;
use Illuminate\Console\Scheduling\Schedule;
use Imtaxu\LaravelLicense\Commands\LicenseActivateCommand;
use Imtaxu\LaravelLicense\Commands\LicenseDeactivateCommand;
use Imtaxu\LaravelLicense\Commands\LicenseObfuscateCommand;
use Imtaxu\LaravelLicense\Commands\LicenseVerifyCommand;
use Imtaxu\LaravelLicense\Middleware\LicenseMiddleware;
use Imtaxu\LaravelLicense\Middleware\AdminLicenseMiddleware;
use Imtaxu\LaravelLicense\Services\LicenseVerificationService;

class LicenseServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        // Merge configuration file
        $this->mergeConfigFrom(
            __DIR__ . '/../config/license.php',
            'license'
        );

        // Register singleton license manager
        $this->app->singleton('license', function ($app) {
            return new LicenseManager($app);
        });

        // Register license verification service
        $this->app->singleton(LicenseVerificationService::class, function ($app) {
            return new LicenseVerificationService();
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        // Register publishable resources
        if ($this->app->runningInConsole()) {
            // Publish configuration
            $this->publishes([
                __DIR__ . '/../config/license.php' => config_path('license.php'),
            ], 'license-config');

            // Publish views
            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/license-manager'),
            ], 'license-views');

            // Publish translations
            $this->publishes([
                __DIR__ . '/../resources/lang' => lang_path('vendor/license-manager'),
            ], 'license-translations');

            // Register commands
            $this->commands([
                LicenseActivateCommand::class,
                LicenseDeactivateCommand::class,
                LicenseVerifyCommand::class,
                LicenseObfuscateCommand::class,
            ]);
        }

        // Register middlewares
        $this->registerMiddlewares();

        // Load views
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'license-manager');

        // Load translations
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'license-manager');

        // Register routes if enabled
        if (config('license.routes_enabled', true)) {
            $this->registerRoutes();
        }

        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Register integrity check hook (enhanced security)
        $this->app->booted(function () {
            if (config('license.integrity_check', true)) {
                try {
                    // Get the license verification service
                    $licenseService = $this->app->make(LicenseVerificationService::class);

                    // Store initial file hashes if they don't exist
                    if (!$licenseService->hasStoredHashes()) {
                        $licenseService->storeFileHashes();
                    }

                    // Check license and file integrity
                    if (!$licenseService->isValid()) {
                        // Create Log
                        Log::critical('License validation failed: Integrity check failed', [
                            'domain' => request()->getHost(),
                            'ip' => request()->ip(),
                            'timestamp' => now()->toIso8601String()
                        ]);

                        // If the application is not in console mode, redirect to error
                        if (!$this->app->runningInConsole() && $this->app->request->path() !== 'license/error') {
                            // First check if route exists, otherwise redirect to custom error url
                            if (Route::has('license.error')) {
                                redirect()->route('license.error')->send();
                            } else {
                                // Fallback to a direct URL if route doesn't exist
                                redirect(config('license.routes_prefix', 'license') . '/error')->send();
                            }
                            exit;
                        }
                    }
                } catch (\Exception $e) {
                    // Log integrity check failure
                    Log::error('License integrity check failed: ' . $e->getMessage());

                    // If the application is not in console mode, redirect to error
                    if (!$this->app->runningInConsole() && $this->app->request->path() !== 'license/error') {
                        // First check if route exists, otherwise redirect to custom error url
                        if (Route::has('license.error')) {
                            redirect()->route('license.error')->send();
                        } else {
                            // Fallback to a direct URL if route doesn't exist
                            redirect(config('license.routes_prefix', 'license') . '/error')->send();
                        }
                        exit;
                    }
                }
            }
        });

        // Add scheduled tasks to check license validity periodically
        $this->app->booted(function () {
            if (class_exists('Schedule')) {
                $schedule = $this->app->make(Schedule::class);
                $schedule->command('license:verify')
                    ->hourly()
                    ->withoutOverlapping();
            }
        });
    }

    /**
     * Register package routes.
     *
     * @return void
     */
    /**
     * Register the package middlewares.
     *
     * @return void
     */
    protected function registerMiddlewares(): void
    {
        // Laravel 12 uses the middleware() method instead of aliasMiddleware()
        $router = $this->app->make('router');

        // Register the middlewares
        $router->aliasMiddleware('license', LicenseMiddleware::class);
        $router->aliasMiddleware('admin.license', AdminLicenseMiddleware::class);
    }

    /**
     * Register package routes.
     *
     * @return void
     */
    protected function registerRoutes(): void
    {
        // Get route middleware configuration
        $middleware = config('license.routes_middleware', ['web']);

        // Add admin middleware to ensure only admins can access license management routes
        $adminMiddleware = array_merge($middleware, ['admin.license']);

        Route::prefix(config('license.routes_prefix', 'license'))
            ->middleware($adminMiddleware)
            ->group(function () {
                $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
            });
    }
}
