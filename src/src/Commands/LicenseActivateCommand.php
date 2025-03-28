<?php

namespace Imtaxu\LaravelLicense\Commands;

use Illuminate\Console\Command;
use Imtaxu\LaravelLicense\Facades\License;
use Illuminate\Support\Facades\File;

class LicenseActivateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'license:activate {key? : The license key to activate}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Activate a license key with the license server';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $licenseKey = $this->argument('key');

        if (empty($licenseKey)) {
            $licenseKey = $this->ask('Enter your license key');
        }

        if (empty($licenseKey)) {
            $this->error('No license key provided!');
            return Command::FAILURE;
        }

        $this->info('Activating license key: ' . $licenseKey);

        if (License::activate($licenseKey)) {
            $licenseData = License::getLicenseData();

            $this->info('License activation successful!');

            if (isset($licenseData['expires_at'])) {
                $this->info('Expires At: ' . $licenseData['expires_at']);
            }

            if (isset($licenseData['plan'])) {
                $this->info('License Plan: ' . $licenseData['plan']);
            }

            // Update the .env file with the license key
            $this->updateEnvFile($licenseKey);

            return Command::SUCCESS;
        }

        $this->error('License activation failed!');
        $this->info('Please make sure the license key is valid and not already activated on another domain.');

        return Command::FAILURE;
    }

    /**
     * Update the .env file with the license key.
     *
     * @param string $licenseKey
     * @return void
     */
    protected function updateEnvFile(string $licenseKey): void
    {
        if (File::exists(base_path('.env'))) {
            $envContent = File::get(base_path('.env'));

            if (strpos($envContent, 'APP_LICENSE=') !== false) {
                $envContent = preg_replace('/APP_LICENSE=(.*)/', 'APP_LICENSE=' . $licenseKey, $envContent);
            } else {
                $envContent .= "\nAPP_LICENSE={$licenseKey}\n";
            }

            File::put(base_path('.env'), $envContent);
            $this->info('Updated APP_LICENSE in .env file.');
        } else {
            $this->warn('Could not find .env file to update.');
        }
    }
}
