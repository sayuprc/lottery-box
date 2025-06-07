<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Lottery\Domain\Models\LotteryBox\LotteryBoxFactoryInterface;
use Lottery\Infrastructures\LotteryBoxFactory;
use Support\Application\Config;
use Support\Application\UlidGenerator;
use Support\Contracts\ConfigInterface;
use Support\Contracts\UlidGeneratorInterface;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ConfigInterface::class, Config::class);
        $this->app->bind(UlidGeneratorInterface::class, UlidGenerator::class);

        $this->app->bind(LotteryBoxFactoryInterface::class, LotteryBoxFactory::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
    }
}
