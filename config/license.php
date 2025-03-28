<?php

return [
    /*
    |--------------------------------------------------------------------------
    | License Key
    |--------------------------------------------------------------------------
    |
    | This is the license key for your application. You can obtain this key
    | from your license provider. An empty value indicates that the license
    | has not been verified.
    |
    */
    'key' => env('APP_LICENSE', ''),

    /*
    |--------------------------------------------------------------------------
    | Application Version
    |--------------------------------------------------------------------------
    |
    | The current version of your application. This will be sent to the
    | license server for verification.
    |
    */
    'version' => env('APP_VERSION', '1.0.0'),

    /*
    |--------------------------------------------------------------------------
    | License Server URL
    |--------------------------------------------------------------------------
    |
    | The base URL of your license server. This value should NOT be modified
    | to prevent unauthorized license verification bypass.
    |
    */
    'server_url' => 'https://your-domain.com/license-admin',
    'api_url' => 'https://your-domain.com/license-admin/api.php',
    'activation_url' => '',
    'deactivation_url' => '',
    'error_url' => '',
    'support_email' => '',

    /*
    |--------------------------------------------------------------------------
    | License Integrity Check Settings
    |--------------------------------------------------------------------------
    |
    | Configure how the license package should handle integrity violations.
    | These values are hardcoded for security reasons and should not be
    | configurable via .env to prevent tampering.
    |
    */
    'integrity_check' => true, // Hardcoded to always be enabled
    'integrity_action' => 'redirect', // 'redirect', 'log', or 'exception'
    'critical_files' => [
        'config' => config_path('license.php'),
        'routes' => base_path('routes/web.php'),
        // Add any other files you want to monitor
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuration for obfuscation settings
    |--------------------------------------------------------------------------
    |
    | - 'variable_prefix' : Prefix used when scrambling variable names
    | - 'enable_string_encoding' : Enable string literal encoding
    | - 'enable_compression' : Use zlib compression
    | - 'protected_variables' : Variable names to protect
    |
    */
    'obfuscation' => [
        'variable_prefix' => '_imtaxu',
        'enable_string_encoding' => true,
        'enable_compression' => true,
        'protected_variables' => ['this', 'app', 'request']
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Duration
    |--------------------------------------------------------------------------
    |
    | Number of days to cache license verification data.
    |
    */
    'cache_duration' => 1, // day

    /*
    |--------------------------------------------------------------------------
    | License Check Interval
    |--------------------------------------------------------------------------
    |
    | The interval (in minutes) at which license verification will be performed.
    |
    */
    'check_interval' => 1440, // 24 hours

    /*
    |--------------------------------------------------------------------------
    | License Routes
    |--------------------------------------------------------------------------
    |
    | Enable or disable the license management routes.
    |
    */
    'routes_enabled' => true,

    /*
    |--------------------------------------------------------------------------
    | License Routes Prefix
    |--------------------------------------------------------------------------
    |
    | The prefix for the license routes.
    |
    */
    'routes_prefix' => 'license',

    /*
    |--------------------------------------------------------------------------
    | License Routes Middleware
    |--------------------------------------------------------------------------
    |
    | The middleware to be applied to the license routes. Admin middleware
    | will be automatically added to ensure only admins can access license routes.
    |
    */
    'routes_middleware' => ['web'],

    /*
    |--------------------------------------------------------------------------
    | Admin Permission Check
    |--------------------------------------------------------------------------
    |
    | Define how the admin permission should be checked. Options:
    | - 'role' : Check if user has 'admin' role using ->hasRole('admin')
    | - 'permission' : Check if user has 'manage_licenses' permission using ->hasPermission('manage_licenses')
    | - 'is_admin' : Check if user is admin using ->isAdmin() method
    | - 'custom' : Provide your own check by extending the AdminLicenseMiddleware
    |
    */
    'admin_check' => 'role',

    /*
    |--------------------------------------------------------------------------
    | Custom Admin Role/Permission
    |--------------------------------------------------------------------------
    |
    | If you use a custom role/permission name other than 'admin' or 'manage_licenses',
    | you can specify it here.
    |
    */
    'admin_role' => 'admin',
    'admin_permission' => 'manage_licenses',

    /*
    |--------------------------------------------------------------------------
    | License Error View
    |--------------------------------------------------------------------------
    |
    | The view to be displayed when there is a license error.
    |
    */
    'error_view' => 'license-manager::error',

    /*
    |--------------------------------------------------------------------------
    | License Files Integrity Check
    |--------------------------------------------------------------------------
    |
    | Enable or disable checking for file integrity of the license package.
    | When enabled, the package will detect any unauthorized changes.
    |
    */
    'integrity_check' => true,

    /*
    |--------------------------------------------------------------------------
    | License Files Redirect On Error
    |--------------------------------------------------------------------------
    |
    | Enable or disable redirecting to error page when license files are modified.
    |
    */
    'redirect_on_error' => true,
];
