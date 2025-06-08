<?php

declare(strict_types=1);

namespace Tests\Unit\Lottery\DebugInfrastructures;

use Lottery\DebugInfrastructures\FileLotteryItemRepository;
use Lottery\Domain\Models\LotteryItem\ItemId;
use Lottery\Domain\Models\LotteryItem\ItemName;
use Lottery\Domain\Models\LotteryItem\LotteryItem;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Support\Contracts\ConfigInterface;
use Support\Debug\Repository\FileStore;
use Tests\TestCase;

class FileLotteryItemRepositoryTest extends TestCase
{
    private FileStore&MockInterface $store;

    private ConfigInterface&MockInterface $config;

    protected function setUp(): void
    {
        parent::setUp();

        $this->store = Mockery::mock(FileStore::class);
        $this->config = Mockery::mock(ConfigInterface::class);
    }

    #[Test]
    public function foundByItemName(): void
    {
        $this->store->shouldReceive('getAll')
            ->with('/lottery-item.dat')
            ->andReturn([
                new LotteryItem(new ItemId(str_repeat('a', 26)), new ItemName('抽選アイテム')),
                new LotteryItem(new ItemId(str_repeat('b', 26)), new ItemName('抽選アイテム2')),
            ])
            ->once();

        $this->assertInstanceOf(LotteryItem::class, $this->getInstance()->findByItemName(new ItemName('抽選アイテム')));
    }

    #[Test]
    public function notFoundByItemName(): void
    {
        $this->store->shouldReceive('getAll')
            ->with('/lottery-item.dat')
            ->andReturn([])
            ->once();

        $this->assertNull($this->getInstance()->findByItemName(new ItemName('抽選アイテム')));
    }

    #[Test]
    public function saved(): void
    {
        $lotteryItem = new LotteryItem(new ItemId(str_repeat('a', 26)), new ItemName('抽選アイテム2'));

        $this->store->shouldReceive('put')
            ->with(
                '/lottery-item.dat',
                $lotteryItem->itemId->value,
                Mockery::on(
                    fn (LotteryItem $arg) => $arg->itemId->value === $lotteryItem->itemId->value
                        && $arg->itemName->value === $lotteryItem->itemName->value
                )
            )
            ->once();

        $this->getInstance()->save($lotteryItem);
    }

    private function getInstance(): FileLotteryItemRepository
    {
        $this->config->shouldReceive('getString')
            ->with('debug.file.path')
            ->andReturn('')
            ->once();

        return new FileLotteryItemRepository($this->store, $this->config);
    }
}
