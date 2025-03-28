<?php

namespace Imtaxu\LaravelLicense\Commands;

use Illuminate\Console\Command;
use Imtaxu\LaravelLicense\Facades\License;

class LicenseVerifyCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'license:verify';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verify the license key with the license server';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Verifying license...');

        if (License::verify()) {
            $licenseData = License::getLicenseData();

            $this->info('License verification successful!');
            $this->info('License Key: ' . License::getLicenseKey());

            if (isset($licenseData['expires_at'])) {
                $this->info('Expires At: ' . $licenseData['expires_at']);
            }

            if (isset($licenseData['plan'])) {
                $this->info('License Plan: ' . $licenseData['plan']);
            }

            return Command::SUCCESS;
        }

        $this->error('License verification failed!');
        $this->info('Please make sure you have a valid license key set in your configuration.');

        return Command::FAILURE;
    }
}
