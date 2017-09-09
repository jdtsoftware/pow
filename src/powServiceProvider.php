<?php

namespace JDT\Pow;

use Illuminate\Support\ServiceProvider;
use JDT\LaravelPow\Pow;

class powServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerViews();

        $this->registerMigrations();
    }

    /**
     * Register view paths.
     */
    public function registerViews()
    {
        $this->loadViewsFrom(__DIR__ . '/../views', 'pow');

        $this->publishes([
            __DIR__ . '/../views' => resource_path('views/vendor/pow'),
        ]);
    }

    /**
     * Register config paths.
     */
    public function registerConfig()
    {
        $this->publishes([
            __DIR__ . '/../config/pow.php' => config_path('pow.php'),
        ]);

        $this->mergeConfigFrom(
            __DIR__ . '/../config/pow.php',
            'pow'
        );
    }

    /**
     * Register migrations.
     */
    public function registerMigrations()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../migrations');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerConfig();
        $this->app->bind('pow', function ($app) {
            return new Pow();
        });
    }
}