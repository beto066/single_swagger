<?php

namespace SingleSoftware\SinglesSwagger\Providers;

use Illuminate\Support\ServiceProvider;
use SingleSoftware\SinglesSwagger\Console\Commands\GenerateSwaggerJson;
use SingleSoftware\SinglesSwagger\Services\GenerateSwaggerService;
use SingleSoftware\SinglesSwagger\Services\GenerateSwaggerServiceInterface;

class AppServiceProvider extends ServiceProvider
{
    private const ROOT_PATH = __DIR__ .  '/../../';

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->loadRoutesFrom(self::ROOT_PATH.'routes/web.php');

        $this->loadRoutesFrom(self::ROOT_PATH.'routes/api.php');

        $this->loadViewsFrom(self::ROOT_PATH . 'resources/views', 'swagger');

        $this->mergeConfigFrom(self::ROOT_PATH . 'config/single-swagger.php', 'single-swagger');

        $this->app->bind(GenerateSwaggerServiceInterface::class, GenerateSwaggerService::class);

        $this->app->singleton(GenerateSwaggerServiceInterface::class, GenerateSwaggerService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(self::ROOT_PATH . 'database/migrations');

        $this->publishes([
            self::ROOT_PATH . 'config/single-swagger.php' => config_path('single-swagger.php')
        ]);

        $this->commands([
            GenerateSwaggerJson::class
        ]);
    }
}
