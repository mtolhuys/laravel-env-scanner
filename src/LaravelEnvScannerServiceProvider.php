<?php

namespace Mtolhuys\LaravelEnvScanner;

use Illuminate\Support\ServiceProvider;

class LaravelEnvScannerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        /*
         * Optional methods to load your package assets
         */
        if ($this->app->runningInConsole()) {
            // Registering package commands.
             $this->commands([
                 Commands\EnvCheck::class
             ]);
        }
    }
}
