<?php

namespace Abedin99\Bulksms;

use Illuminate\Support\ServiceProvider;

class BulksmsServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/config/config.php', 'bulksms');
        
        // Register the service the package provides.
        $this->app->singleton('bulksms', function ($app) {
            return new Bulksms;
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Allow your user to publish the config
        $this->publishes([
            __DIR__.'/Config/config.php' => config_path('bulksms.php'),
        ], 'config');

    }
}
