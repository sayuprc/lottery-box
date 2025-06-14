<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Lottery\Application\Interactors\AddItemToBoxInteractor;
use Lottery\Application\Interactors\CreateBoxInteractor;
use Lottery\Application\Interactors\CreateItemInteractor;
use Lottery\Application\Interactors\LotteryInteractor;
use Lottery\Application\UseCase\AddItemToBox\AddItemToBoxInputPort;
use Lottery\Application\UseCase\CreateBox\CreateBoxInputPort;
use Lottery\Application\UseCase\CreateItem\CreateItemInputPort;
use Lottery\Application\UseCase\Lottery\LotteryInputPort;
use Lottery\DebugInfrastructures\FileBoxItemRepository;
use Lottery\DebugInfrastructures\FileDrawnItemRepository;
use Lottery\DebugInfrastructures\FileLotteryBoxRepository;
use Lottery\DebugInfrastructures\FileLotteryItemRepository;
use Lottery\Domain\Models\BoxItem\BoxItemRepositoryInterface;
use Lottery\Domain\Models\DrawnItem\DrawnItemRepositoryInterface;
use Lottery\Domain\Models\LotteryBox\LotteryBoxFactoryInterface;
use Lottery\Domain\Models\LotteryBox\LotteryBoxRepositoryInterface;
use Lottery\Domain\Models\LotteryItem\LotteryItemFactoryInterface;
use Lottery\Domain\Models\LotteryItem\LotteryItemRepositoryInterface;
use Lottery\Infrastructures\LotteryBoxFactory;
use Lottery\Infrastructures\LotteryItemFactory;
use Support\Application\Config;
use Support\Application\Mapper;
use Support\Application\Transaction\NopTransaction;
use Support\Application\UlidGenerator;
use Support\Contracts\ConfigInterface;
use Support\Contracts\MapperInterface;
use Support\Contracts\TransactionInterface;
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
        $this->app->bind(MapperInterface::class, Mapper::class);
        $this->app->bind(TransactionInterface::class, NopTransaction::class);

        $this->app->bind(LotteryBoxFactoryInterface::class, LotteryBoxFactory::class);
        $this->app->bind(LotteryBoxRepositoryInterface::class, FileLotteryBoxRepository::class);

        $this->app->bind(CreateBoxInputPort::class, CreateBoxInteractor::class);

        $this->app->bind(LotteryItemFactoryInterface::class, LotteryItemFactory::class);
        $this->app->bind(LotteryItemRepositoryInterface::class, FileLotteryItemRepository::class);

        $this->app->bind(CreateItemInputPort::class, CreateItemInteractor::class);

        $this->app->bind(BoxItemRepositoryInterface::class, FileBoxItemRepository::class);

        $this->app->bind(AddItemToBoxInputPort::class, AddItemToBoxInteractor::class);

        $this->app->bind(LotteryInputPort::class, LotteryInteractor::class);

        $this->app->bind(DrawnItemRepositoryInterface::class, FileDrawnItemRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
    }
}
