<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Support\Application\Config;
use Support\Application\UlidGenerator;
use Support\Ulid\UlidGeneratorInterface;
use Support\Config\ConfigInterface;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ConfigInterface::class, Config::class);
        $this->app->bind(UlidGeneratorInterface::class, UlidGenerator::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
    }
}
