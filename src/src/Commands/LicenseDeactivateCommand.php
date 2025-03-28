<?php

namespace Imtaxu\LaravelLicense\Commands;

use Illuminate\Console\Command;
use Imtaxu\LaravelLicense\Facades\License;
use Illuminate\Support\Facades\File;

class LicenseDeactivateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'license:deactivate {--force : Force deactivation without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deactivate the current license key';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $licenseKey = License::getLicenseKey();

        if (empty($licenseKey)) {
            $this->error('No active license key found!');
            return Command::FAILURE;
        }

        if (!$this->option('force') && !$this->confirm('Are you sure you want to deactivate your license?')) {
            $this->info('Operation cancelled.');
            return Command::SUCCESS;
        }

        $this->info('Deactivating license key: ' . $licenseKey);

        if (License::deactivate()) {
            $this->info('License deactivation successful!');

            // Update the .env file to remove the license key
            $this->updateEnvFile();

            return Command::SUCCESS;
        }

        $this->error('License deactivation failed!');
        $this->info('Please try again or contact support.');

        return Command::FAILURE;
    }

    /**
     * Update the .env file to remove the license key.
     *
     * @return void
     */
    protected function updateEnvFile(): void
    {
        if (File::exists(base_path('.env'))) {
            $envContent = File::get(base_path('.env'));

            if (strpos($envContent, 'APP_LICENSE=') !== false) {
                $envContent = preg_replace('/APP_LICENSE=(.*)/', 'APP_LICENSE=', $envContent);
                File::put(base_path('.env'), $envContent);
                $this->info('Removed APP_LICENSE from .env file.');
            }
        } else {
            $this->warn('Could not find .env file to update.');
        }
    }
}
