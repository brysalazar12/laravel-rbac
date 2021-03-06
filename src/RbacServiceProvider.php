<?php namespace Aliukevicius\LaravelRbac;

use Illuminate\Support\ServiceProvider;

class RbacServiceProvider extends ServiceProvider
{

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    public function boot()
    {
        $basePath = __DIR__ . '/';

        $this->mergeConfigFrom($basePath . '../config/laravel-rbac.php', 'laravel-rbac');
        $this->loadViewsFrom($basePath . 'Resources/views', 'aliukevicius/laravelRbac');

        $this->loadTranslationsFrom($basePath . 'Resources/lang', 'aliukevicius/laravelRbac');


        $this->publishes([
            $basePath . 'Resources/views' => base_path('resources/views/vendor/aliukevicius/laravelRbac'),
            $basePath . '../config/laravel-rbac.php' => config_path('laravel-rbac.php'),
        ]);

        /** @var \Illuminate\Routing\Router $router */
        $router = $this->app->make('Illuminate\Routing\Router');

        // Register global checkPermission middleware
        $router->middleware('checkPermission', $this->app['config']->get('laravel-rbac.checkPermissionMiddleware'));

        // get package routes
        require_once $basePath . 'Http/routes.php';
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->app['command.laravel-rbac.create-migrations'] = $this->app->share(
            function ($app) {
                return $app['Aliukevicius\LaravelRbac\Console\Commands\CreateMigrationsCommand'];
            }
        );

        $this->app['command.laravel-rbac.update-permission-list'] = $this->app->share(
            function ($app) {
                return $app['Aliukevicius\LaravelRbac\Console\Commands\UpdatePermissionListCommand'];
            }
        );

        $this->app->singleton('Aliukevicius\LaravelRbac\ActiveUser', function($app){

            return $app->make($this->app['config']->get('laravel-rbac.activeUserService'));
        });

        $this->app['facade.laravel-rbac.active-user'] = $this->app->share(function($app)
        {
            return $app->make('Aliukevicius\LaravelRbac\ActiveUser');
        });

        $this->commands(['command.laravel-rbac.create-migrations', 'command.laravel-rbac.update-permission-list']);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return string[]
     */
    public function provides()
    {
        return [
            'command.laravel-rbac.create-migrations',
            'command.laravel-rbac.update-permission-list',
            'Aliukevicius\LaravelRbac\ActiveUser',
        ];
    }
}
