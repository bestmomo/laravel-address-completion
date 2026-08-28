<?php

declare(strict_types=1);

namespace AddressCompletion;

use Illuminate\Support\ServiceProvider;
use AddressCompletion\Providers\BanProvider;
use AddressCompletion\Providers\GeoapifyProvider;

class AddressCompletionServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/address-completion.php', 'address-completion');

        $this->app->singleton(AddressManager::class, function ($app) {
            return new AddressManager([
                $app->make(BanProvider::class),
                $app->make(GeoapifyProvider::class),
            ]);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../config/address-completion.php' => config_path('address-completion.php'),
        ], ['address-completion', 'address-completion-config']);        
    }
}
