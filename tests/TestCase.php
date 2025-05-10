<?php

namespace Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use PinIndia\PinIndiaServiceProvider;

abstract class TestCase extends BaseTestCase
{
    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Ensure we're using MySQL
        $this->app['config']->set('database.default', 'mysql');

        // Set the table prefix for PinIndia
        $this->app['config']->set('pinindia.table_prefix', 'pinindia');

        // Run the migrations
        $this->loadMigrationsFrom(__DIR__ . '/../src/Database/Migrations');

        // Refresh the database for each test
        $this->artisan('migrate:fresh');
    }

    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            PinIndiaServiceProvider::class,
        ];
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use MySQL
        $app['config']->set('database.default', 'mysql');
        $app['config']->set('database.connections.mysql', [
            'driver'   => 'mysql',
            'host'     => env('DB_HOST', 'localhost'),
            'port'     => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'pinindia_test'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'charset'  => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix'   => '',
            'strict'   => false, // Set to false to allow MySQL functions like acos
            'engine'   => null,
            'modes' => [
                // Disable strict mode to allow MySQL functions
                //'ONLY_FULL_GROUP_BY',
                //'STRICT_TRANS_TABLES',
                //'NO_ZERO_IN_DATE',
                //'NO_ZERO_DATE',
                //'ERROR_FOR_DIVISION_BY_ZERO',
                //'NO_ENGINE_SUBSTITUTION',
            ],
        ]);

        // Set the API key for testing
        $app['config']->set('pinindia.data_gov_in_api_key', 'test-api-key');
    }
}
