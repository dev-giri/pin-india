<?php

namespace PinIndia\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use PinIndia\Database\Seeders\JsonPostOfficeSeeder;

class InstallCommand extends Command
{
    protected $signature = 'pinindia:install';

    protected $description = 'Install PinIndia library';

    public function handle()
    {
        // Publish config file
        Artisan::call('vendor:publish', [
            '--provider' => 'PinIndia\PinIndiaServiceProvider',
            '--tag' => 'pinindia-config',
        ]);

        // // Publish migrations and seeders
        // Artisan::call('vendor:publish', [
        //     '--provider' => 'PinIndia\PinIndiaServiceProvider',
        //     '--tag' => 'pinindia-seeders',
        // ]);

        Artisan::call('migrate');

        $apiKey = config('pinindia.data_gov_in_api_key');
        if (!$apiKey) {
            $this->error('⚠️ data.gov.in API key not found!.');
            $this->error('ⓘ Please set DATA_GOV_IN_API_KEY in your .env file.');
            $this->error('🌐 Visit https://data.gov.in/ to get an API key.');
            return;
        }

        //PinIndia data seed
        $json_data_path = config('pinindia.data_path'); // 'pinindia/post_offices.json' by default
        if (!Storage::disk('local')->exists($json_data_path)) {
            try {
                $exitCode = $this->call('pinindia:download');
            } catch (\Exception $e) {
                $this->error('❌ Failed to download data: ' . $e->getMessage());
                return;
            }

            if ($exitCode !== 0) {
                $this->error('❌ Failed to download data. Please try again or download manually using "php artisan pinindia:download".');
                return $exitCode;
            }

            $this->info('✅ Data downloaded successfully.');
        }

        $this->info('⚡ Seeding PinIndia data started.');

        // Call the seeder with output streaming to the current command
        try {
            $exitCode = $this->call('db:seed', [
                '--class' => JsonPostOfficeSeeder::class,
            ]);
        } catch (\Exception $e) {
            $this->error('❌ Failed to seed data: ' . $e->getMessage());
            return;
        }

        if ($exitCode !== 0) {
            $this->error('❌ Failed to seed data. Please try again or seed manually using "php artisan db:seed --class=PinIndia\\Database\\Seeders\\JsonPostOfficeSeeder".');
            return $exitCode;
        }

        $this->info('✅ Data seeded successfully.');

        $this->info('✅ PinIndia installed successfully.');
    }
}
