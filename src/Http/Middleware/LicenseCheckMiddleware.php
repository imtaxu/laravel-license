<?php

namespace ImTaxu\LaravelLicense\Http\Middleware;

// Bu fonksiyonlar Laravel tarafından sağlanır, IDE için tanımlıyoruz
if (!function_exists('response')) {
    function response($content = '', $status = 200, array $headers = []) {
        return new \Illuminate\Http\Response($content, $status, $headers);
    }
}

if (!function_exists('redirect')) {
    function redirect($to = null, $status = 302, $headers = [], $secure = null) {
        return new \Illuminate\Http\RedirectResponse($to, $status, $headers);
    }
}

if (!function_exists('config')) {
    function config($key = null, $default = null) {
        return $default;
    }
}

use Closure;
use Illuminate\Http\Request;
use ImTaxu\LaravelLicense\LicenseChecker;

class LicenseCheckMiddleware
{
    /**
     * Lisans kontrolcüsü
     *
     * @var LicenseChecker
     */
    protected $licenseChecker;

    /**
     * LicenseCheckMiddleware constructor.
     *
     * @param LicenseChecker $licenseChecker
     */
    public function __construct(LicenseChecker $licenseChecker)
    {
        $this->licenseChecker = $licenseChecker;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Geçerli rota adını al
        $routeName = $request->route() ? $request->route()->getName() : '';
        
        // Geçerli IP adresini al
        $ip = $request->ip();

        // Eğer rota veya IP muaf ise kontrolü atla
        if ($routeName && $this->licenseChecker->isExcludedRoute($routeName)) {
            return $next($request);
        }

        if ($ip && $this->licenseChecker->isExcludedIp($ip)) {
            return $next($request);
        }

        // Lisans kontrolü yap
        if (!$this->licenseChecker->check()) {
            // API isteği ise JSON yanıtı döndür
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Lisans geçersiz veya süresi dolmuş.',
                    'code' => 'LICENSE_INVALID'
                ], 403);
            }
            
            // Normal istek ise lisans hata sayfasına yönlendir
            return redirect()->route(config('license.error_route'));
        }

        return $next($request);
    }
}
