<?php

namespace PinIndia;

use Illuminate\Support\ServiceProvider;
use PinIndia\Services\PinIndiaService;
use PinIndia\Console\InstallCommand;
use PinIndia\Console\UninstallCommand;
use PinIndia\Console\DownloadCommand;

class PinIndiaServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Migrations
        $this->loadMigrationsFrom(__DIR__.'/Database/Migrations');

        // // Seeders
        // $this->publishes([
        //     __DIR__.'/Database/Seeders' => database_path('seeders')
        // ], 'pinindia-seeders');

        // Config
        $this->publishes([
            __DIR__.'/Config/pinindia.php' => config_path('pinindia.php')
        ], 'pinindia-config');
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/Config/pinindia.php', 'pinindia');

        $this->app->singleton('pinindia', function ($app) {
            return new PinIndiaService();
        });

        $this->commands([
            InstallCommand::class,
            UninstallCommand::class,
            DownloadCommand::class,
        ]);
    }
}
