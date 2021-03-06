<?php

namespace JDT\Pow;

use Illuminate\Events\Dispatcher;
use Illuminate\Session\SessionManager;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

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
        $this->registerRoutes();
        $this->registerAssets();

        \View::composer('pow::wallet.widget', 'JDT\Pow\Composers\WalletComposer');
    }

    /**
     * Register view paths.
     */
    public function registerViews()
    {
        $this->loadViewsFrom(__DIR__ . '/views', 'pow');

        $this->publishes([
            __DIR__ . '/views' => resource_path('views/vendor/pow'),
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
     * Register routes
     */
    public function registerRoutes()
    {
        $route = Route::prefix(\Config::get('pow.route_prefix'));
        $routesDomain = \Config::get('pow.route_domain');
        $middlewares = \Config::get('pow.route_middleware');

        if($routesDomain) {
            $route = $route->domain($routesDomain);
        }

        if($middlewares) {
            $route->middleware($middlewares);
        }

        $route->group(__DIR__.'/routes.php');
    }

    /**
     *
     */
    public function registerAssets()
    {
        $this->publishes([
            __DIR__ . '/../assets' => public_path('vendor/pow'),
        ], 'public');
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
            return new Pow($app['session'], $app['events']);
        });
    }
}