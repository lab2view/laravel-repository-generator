<?php

namespace Lab2view\RepositoryGenerator;

use Illuminate\Support\ServiceProvider;

class RepositoryGeneratorServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/repository-generator.php', 'repository-generator'
        );
        if ($this->app->runningInConsole()) {
            $this->commands([
                \Lab2view\RepositoryGenerator\Console\Commands\Generate::class,
            ]);
        }
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/repository-generator.php' => config_path('repository-generator.php'),
        ]);
    }
}
