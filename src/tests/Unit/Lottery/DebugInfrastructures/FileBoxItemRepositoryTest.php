<?php

declare(strict_types=1);

namespace Tests\Unit\Lottery\DebugInfrastructures;

use Lottery\DebugInfrastructures\FileBoxItemRepository;
use Lottery\Domain\Models\BoxItem\BoxItem;
use Lottery\Domain\Models\LotteryBox\BoxId;
use Lottery\Domain\Models\LotteryItem\ItemId;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Support\Contracts\ConfigInterface;
use Support\Debug\Repository\FileStore;
use Tests\TestCase;

class FileBoxItemRepositoryTest extends TestCase
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
    public function saved(): void
    {
        $boxItem = new BoxItem(new BoxId(str_repeat('a', 26)), new ItemId(str_repeat('b', 26)));

        $this->store->shouldReceive('put')
            ->with(
                '/box-item.dat',
                $boxItem->boxId->value . '-' . $boxItem->itemId->value,
                Mockery::on(
                    fn (BoxItem $arg) => $arg->boxId->value === $boxItem->boxId->value
                        && $arg->itemId->value === $boxItem->itemId->value
                )
            )
            ->once();

        $this->getInstance()->save($boxItem);
    }

    private function getInstance(): FileBoxItemRepository
    {
        $this->config->shouldReceive('getString')
            ->with('debug.file.path')
            ->andReturn('')
            ->once();

        return new FileBoxItemRepository($this->store, $this->config);
    }
}
