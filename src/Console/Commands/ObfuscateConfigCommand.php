<?php

namespace ImTaxu\LaravelLicense\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

// IDE helper for Illuminate\Console\Command
if (!class_exists('ImTaxu\LaravelLicense\Helpers\CommandHelper')) {
    class CommandHelper {
        // Instance metotları
        public function error($message) { echo "ERROR: $message\n"; }
        public function info($message) { echo "INFO: $message\n"; }
        public function line($message) { echo "$message\n"; }
        public function warn($message) { echo "WARNING: $message\n"; }
        public function question($message) { echo "QUESTION: $message\n"; }
        public function comment($message) { echo "COMMENT: $message\n"; }
        public function success($message) { echo "SUCCESS: $message\n"; }
        public function handle() { return 0; }
        
        // Statik yardımcı metotlar
        public static function staticError($message) { echo "ERROR: $message\n"; }
        public static function staticInfo($message) { echo "INFO: $message\n"; }
        public static function staticLine($message) { echo "$message\n"; }
        public static function staticWarn($message) { echo "WARNING: $message\n"; }
    }
}



// IDE helper for Illuminate\Support\Str
if (!class_exists('ImTaxu\LaravelLicense\Helpers\StrHelper')) {
    class StrHelper {
        public static function random($length = 16) { return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)))), 1, $length); }
        public static function uuid() { return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000, mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)); }
        public static function slug($title, $separator = '-') { return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', $separator, $title))); }
        public static function contains($haystack, $needles) { return strpos($haystack, $needles) !== false; }
        public static function startsWith($haystack, $needles) { return strpos($haystack, $needles) === 0; }
        public static function endsWith($haystack, $needles) { return substr($haystack, -strlen($needles)) === $needles; }
    }
}

if (!class_exists('Illuminate\Support\Str')) {
    class_alias('ImTaxu\LaravelLicense\Helpers\StrHelper', 'Illuminate\Support\Str');
}

// IDE helpers for functions and classes
if (!class_exists('ImTaxu\LaravelLicense\Helpers\FileHelper')) {
    class FileHelper {
        public static function exists($path) { return file_exists($path); }
        public static function get($path) { return file_get_contents($path); }
        public static function put($path, $contents) { return file_put_contents($path, $contents); }
    }
}

if (!class_exists('ImTaxu\LaravelLicense\Helpers\StorageHelper')) {
    class StorageHelper {
        public static function disk($name) { return new self(); }
        public function put($path, $contents) { return file_put_contents(storage_path($path), $contents); }
    }
}

if (!class_exists('ImTaxu\LaravelLicense\Helpers\StrHelper')) {
    class StrHelper {
        public static function random($length = 16) { return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)))), 1, $length); }
        public static function slug($title, $separator = '-') { return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', $separator, $title))); }
        public static function limit($value, $limit = 100, $end = '...') { return substr($value, 0, $limit) . $end; }
        public static function contains($haystack, $needles) { return str_contains($haystack, $needles); }
        public static function startsWith($haystack, $needles) { return str_starts_with($haystack, $needles); }
        public static function endsWith($haystack, $needles) { return str_ends_with($haystack, $needles); }
        public static function camel($value) { return lcfirst(static::studly($value)); }
        public static function studly($value) { return str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $value))); }
        public static function snake($value, $delimiter = '_') { return strtolower(preg_replace('/[A-Z]/', $delimiter . '$0', lcfirst($value))); }
    }
}

if (!class_exists('ImTaxu\LaravelLicense\Helpers\CommandHelper')) {
    class CommandHelper {
        protected $signature;
        protected $description;
        protected $hidden = false;
        
        public function error($message) { echo "ERROR: {$message}\n"; }
        public function info($message) { echo "INFO: {$message}\n"; }
        public function line($message) { echo "{$message}\n"; }
        public function comment($message) { echo "COMMENT: {$message}\n"; }
        public function question($message) { echo "QUESTION: {$message}\n"; }
        public function warn($message) { echo "WARNING: {$message}\n"; }
        public function alert($message) { echo "ALERT: {$message}\n"; }
        public function table(array $headers, array $rows) { /* Table output */ }
        public function progressBar($count) { return new class { public function advance() {} public function finish() {} }; }
        public function confirm($question, $default = false) { return true; }
        public function choice($question, array $choices, $default = null) { return $default; }
        public function handle() { return 0; }
        public function argument($key = null) { return null; }
        public function option($key = null) { return null; }
        public function call($command, array $arguments = []) { return 0; }
        public function callSilent($command, array $arguments = []) { return 0; }
    }
}

// IDE helpers for functions provided by Laravel
if (!function_exists('app')) {
    function app() {
        return new class {
            public function basePath($path = '') { return __DIR__ . '/../../../../' . $path; }
            public function environment(...$args) { return in_array('local', $args); }
        };
    }
}

if (!function_exists('config_path')) {
    function config_path($path = '') {
        return app()->basePath('config') . ($path ? '/' . $path : '');
    }
}

if (!function_exists('storage_path')) {
    function storage_path($path = '') {
        return app()->basePath('storage') . ($path ? '/' . $path : '');
    }
}

if (!function_exists('env')) {
    function env($key, $default = null) {
        return $default;
    }
}

// IDE helpers for Laravel classes

if (!class_exists('Illuminate\Console\Command')) {
    class_alias('ImTaxu\LaravelLicense\Helpers\CommandHelper', 'Illuminate\Console\Command');
}

if (!class_exists('Illuminate\Support\Facades\File')) {
    class_alias('ImTaxu\LaravelLicense\Helpers\FileHelper', 'Illuminate\Support\Facades\File');
}

if (!class_exists('Illuminate\Support\Facades\Storage')) {
    class_alias('ImTaxu\LaravelLicense\Helpers\StorageHelper', 'Illuminate\Support\Facades\Storage');
}

if (!class_exists('Illuminate\Support\Str')) {
    class_alias('ImTaxu\LaravelLicense\Helpers\StrHelper', 'Illuminate\Support\Str');
}



// Use statements moved to the top

// Command sınıfı için alias
if (!class_exists('Illuminate\Console\Command')) {
    class_alias('ImTaxu\LaravelLicense\Helpers\CommandHelper', 'Illuminate\Console\Command');
}

// Str sınıfı için alias
if (!class_exists('Illuminate\Support\Str')) {
    class_alias('ImTaxu\LaravelLicense\Helpers\StrHelper', 'Illuminate\Support\Str');
}

// File sınıfı için alias
if (!class_exists('ImTaxu\LaravelLicense\Helpers\FileHelper')) {
    class FileHelper {
        public static function exists($path) { return file_exists($path); }
        public static function get($path) { return file_get_contents($path); }
        public static function put($path, $contents) { return file_put_contents($path, $contents); }
    }
}

if (!class_exists('Illuminate\Support\Facades\File')) {
    class_alias('ImTaxu\LaravelLicense\Helpers\FileHelper', 'Illuminate\Support\Facades\File');
}

class ObfuscateConfigCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'license:obfuscate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Obfuscates the license configuration file and adds protection';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $configPath = config_path('license.php');
        
        if (!\Illuminate\Support\Facades\File::exists($configPath)) {
            $this->error('License configuration file not found. You must publish the file first:');
            $this->line('php artisan vendor:publish --tag=license-config');
            return 1;
        }

        // Read the original config content
        $originalContent = \Illuminate\Support\Facades\File::get($configPath);
        
        // Evaluate the config content and get it as an array
        $configArray = require $configPath;
        
        // Convert config array to JSON
        $configJson = json_encode($configArray);
        
        // Create a unique key (app name + domain + timestamp)
        $encryptionKey = md5(
            ($configArray['variables']['app_name'] ?? env('APP_NAME', 'Laravel')) . 
            ($configArray['variables']['domain'] ?? env('APP_URL', 'localhost')) . 
            'license-salt-' . env('APP_KEY', \Illuminate\Support\Str::random(32))
        );
        
        // Obfuscate the config JSON
        $encryptedConfig = $this->obfuscateConfig($configJson, $encryptionKey);
        
        // Create a checksum for the obfuscated config
        $checksum = md5($encryptedConfig . $encryptionKey);
        
        // Create a new PHP file containing the obfuscated config and checksum
        $obfuscatedContent = <<<PHP
<?php
// This file is automatically generated and should not be modified.
// To make changes, first edit the original config file,
// then run the 'php artisan license:obfuscate' command.

return [
    '_encrypted' => true,
    '_data' => '{$encryptedConfig}',
    '_checksum' => '{$checksum}',
    '_key' => '{$encryptionKey}',
];
PHP;

        // Backup the original config file
        $backupPath = storage_path('app/license-config-backup-' . date('Y-m-d-His') . '.php');
        \Illuminate\Support\Facades\File::put($backupPath, $originalContent);
        
        // Write the obfuscated content to the config file
        \Illuminate\Support\Facades\File::put($configPath, $obfuscatedContent);
        
        // Also save a copy of the obfuscated config to the vendor folder
        $vendorConfigPath = __DIR__ . '/../../config/license.php.obfuscated';
        \Illuminate\Support\Facades\File::put($vendorConfigPath, $obfuscatedContent);
        
        $this->info('License configuration file successfully obfuscated and protected!');
        $this->line('Backup of the original configuration file is stored at: ' . $backupPath);
        
        return 0;
    }

    /**
     * Obfuscate config content
     *
     * @param string $content
     * @param string $key
     * @return string
     */
    protected function obfuscateConfig(string $content, string $key): string
    {
        // Apply a simple XOR encryption
        $result = '';
        $keyLength = strlen($key);
        
        for ($i = 0; $i < strlen($content); $i++) {
            $result .= $content[$i] ^ $key[$i % $keyLength];
        }
        
        // Encode with Base64
        return base64_encode($result);
    }
}
