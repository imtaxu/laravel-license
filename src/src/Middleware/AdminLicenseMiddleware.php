<?php

namespace Imtaxu\LaravelLicense\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminLicenseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthenticated.'], 401);
            }

            return redirect()->route('login');
        }

        $user = Auth::user();
        $checkType = config('license.admin_check', 'role');

        switch ($checkType) {
            case 'role':
                $role = config('license.admin_role', 'admin');
                if (method_exists($user, 'hasRole') && !$user->hasRole($role)) {
                    return $this->handleUnauthorized($request);
                }
                break;

            case 'permission':
                $permission = config('license.admin_permission', 'manage_licenses');
                if (method_exists($user, 'hasPermission') && !$user->hasPermission($permission)) {
                    return $this->handleUnauthorized($request);
                }
                break;

            case 'is_admin':
                if (method_exists($user, 'isAdmin') && !$user->isAdmin()) {
                    return $this->handleUnauthorized($request);
                }
                break;

            case 'custom':
                // Use this case to extend with your own logic
                if (!$this->customCheck($user)) {
                    return $this->handleUnauthorized($request);
                }
                break;

            default:
                // Default to basic role check
                if (method_exists($user, 'hasRole') && !$user->hasRole('admin')) {
                    return $this->handleUnauthorized($request);
                }
        }

        return $next($request);
    }

    /**
     * Handle unauthorized access attempt.
     *
     * @param  Request  $request
     * @return mixed
     */
    protected function handleUnauthorized(Request $request): mixed
    {
        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'Unauthorized. Admin permission required to access license management.'
            ], 403);
        }

        return redirect()->back()->with('error', 'You do not have permission to access license management.');
    }

    /**
     * Custom check for admin permission.
     * Override this method in a child class for custom authorization logic.
     *
     * @param  mixed  $user
     * @return bool
     */
    protected function customCheck($user): bool
    {
        // Default implementation
        // For custom logic, extend this class and override this method

        return false;
    }
}
