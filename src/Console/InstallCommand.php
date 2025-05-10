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
        
        //PinIndia data seed
        $json_data_path = config('pinindia.data_path'); // 'pinindia/post_offices.json' by default
        if (!Storage::disk('local')->exists($json_data_path)) {
            $this->info('⏳ Downloading data from API.DATA.GOV.IN');
            Artisan::call('pinindia:download');
            $this->info('Data downloaded successfully.');
        }

        $this->info('⚡Seeding PinIndia data started');
        Artisan::call('db:seed', [
            '--class' => JsonPostOfficeSeeder::class,
        ]);

        $this->info(Artisan::output());

        $this->info('✅ PinIndia installed successfully.');
    }
}
