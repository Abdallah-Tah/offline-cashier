<?php

namespace AMohamed\OfflineCashier;

use Illuminate\Support\ServiceProvider;

class OfflineCashierServiceProvider extends ServiceProvider
{
    protected $listen = [
        \AMohamed\OfflineCashier\Events\PaymentReceived::class => [
            \AMohamed\OfflineCashier\Listeners\SendPaymentNotification::class,
        ],
        \AMohamed\OfflineCashier\Events\SubscriptionCreated::class => [
            \AMohamed\OfflineCashier\Listeners\SendSubscriptionNotification::class,
        ],
    ];

    public function boot()
    {
        $this->registerPublishables();
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'offline-cashier');

        $this->bootEvents();
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/offline-cashier.php', 'offline-cashier'
        );

        $this->app->bind(
            \AMohamed\OfflineCashier\Contracts\SubscriptionManager::class,
            \AMohamed\OfflineCashier\Services\SubscriptionService::class
        );

        $this->app->bind(
            \AMohamed\OfflineCashier\Contracts\PaymentManager::class,
            \AMohamed\OfflineCashier\Services\PaymentService::class
        );
    }

    protected function registerPublishables(): void
    {
        $this->publishes([
            __DIR__.'/../config/offline-cashier.php' => config_path('offline-cashier.php'),
            __DIR__.'/../resources/views' => resource_path('views/vendor/offline-cashier'),
        ]);
    }

    protected function bootEvents(): void
    {
        $this->booting(function () {
            $events = $this->app['events'];

            foreach ($this->listen as $event => $listeners) {
                foreach ($listeners as $listener) {
                    $events->listen($event, $listener);
                }
            }
        });
    }
} 