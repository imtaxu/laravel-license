<?php

namespace Imtaxu\LaravelLicense\Middleware;

use Closure;
use Illuminate\Http\Request;
use Imtaxu\LaravelLicense\Services\LicenseVerificationService;
use Illuminate\Support\Facades\Route;

class LicenseMiddleware
{
    /**
     * License verification service
     */
    protected $licenseService;

    /**
     * Routes that should be excluded from license check
     */
    protected $excludedRoutes = [
        'license.error',
        'license.activate.form',
        'license.activate',
        'license.check',
        'license.clear-cache'
    ];

    /**
     * Constructor
     */
    public function __construct(LicenseVerificationService $licenseService)
    {
        $this->licenseService = $licenseService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        // Check if the current route is excluded
        $currentRoute = Route::currentRouteName();
        if (in_array($currentRoute, $this->excludedRoutes)) {
            return $next($request);
        }

        // For AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            if (!$this->licenseService->isValid()) {
                return response()->json([
                    'error' => 'License verification failed',
                    'code' => 'LICANSE_INVALID',
                    'message' => 'Your license is inactive or has been tampered with.'
                ], 403);
            }
        }
        // For normal requests, redirect to error page
        else {
            if (!$this->licenseService->isValid()) {
                return redirect()->route('license.error');
            }
        }

        return $next($request);
    }
}
