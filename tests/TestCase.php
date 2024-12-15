<?php

namespace AMohamed\OfflineCashier\Tests;

use AMohamed\OfflineCashier\OfflineCashierServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TestCase extends Orchestra
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom([
            '--database' => 'testing',
            '--path' => [
                __DIR__ . '/Database/Migrations',
                __DIR__ . '/../database/migrations',
            ],
        ]);
    }

    protected function getPackageProviders($app): array
    {
        return [
            OfflineCashierServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);

        $app['config']->set('offline-cashier.models.user', User::class);
    }

    protected function defineDatabaseMigrations()
    {
        $this->loadMigrationsFrom(__DIR__ . '/Database/Migrations');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }
}
