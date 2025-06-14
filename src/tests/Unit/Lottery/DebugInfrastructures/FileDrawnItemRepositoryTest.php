<?php

declare(strict_types=1);

namespace Tests\Unit\Lottery\DebugInfrastructures;

use DateTimeImmutable;
use Lottery\DebugInfrastructures\FileDrawnItemRepository;
use Lottery\Domain\Models\DrawnItem\DrawnAt;
use Lottery\Domain\Models\DrawnItem\DrawnItem;
use Lottery\Domain\Models\LotteryBox\BoxId;
use Lottery\Domain\Models\LotteryItem\ItemId;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Support\Contracts\ConfigInterface;
use Support\Debug\Repository\FileStore;
use Tests\TestCase;

class FileDrawnItemRepositoryTest extends TestCase
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
    public function getByBoxId(): void
    {
        $boxId = new BoxId(str_repeat('a', 26));
        $itemId = new ItemId(str_repeat('b', 26));
        $drawnAt = new DrawnAt(new DateTimeImmutable('2025-06-14 14:26:30'));

        $this->store->shouldReceive('getAll')
            ->with('/drawn-item.dat')
            ->andReturn([
                $boxId->value . '-' . $itemId->value . '-' . $drawnAt->value->format('YmdHis') => new DrawnItem($boxId, $itemId, $drawnAt),
            ])
            ->once();

        $result = $this->getInstance()->getByBoxId($boxId);

        $this->assertCount(1, $result);
        $this->assertSame($boxId->value, $result[0]->boxId->value);
        $this->assertSame($itemId->value, $result[0]->itemId->value);
        $this->assertSame('2025-06-14 14:26:30', $result[0]->drawnAt->value->format('Y-m-d H:i:s'));
    }

    #[Test]
    public function getByBoxIdIsEmpty(): void
    {
        $boxId = new BoxId(str_repeat('a', 26));

        $this->store->shouldReceive('getAll')
            ->with('/drawn-item.dat')
            ->andReturn([])
            ->once();

        $result = $this->getInstance()->getByBoxId($boxId);

        $this->assertCount(0, $result);
    }

    #[Test]
    public function saved(): void
    {
        $drawnItem = new DrawnItem(
            new BoxId(str_repeat('a', 26)),
            new ItemId(str_repeat('b', 26)),
            new DrawnAt(new DateTimeImmutable('2025-06-14 14:26:30'))
        );

        $this->store->shouldReceive('put')
            ->with(
                '/drawn-item.dat',
                $drawnItem->boxId->value . '-' . $drawnItem->itemId->value . '-' . $drawnItem->drawnAt->value->format('YmdHis'),
                Mockery::on(
                    fn (DrawnItem $arg) => $arg->boxId->value === $drawnItem->boxId->value
                        && $arg->itemId->value === $drawnItem->itemId->value
                        && $arg->drawnAt->value->format('Y-m-d H:i:s') === $drawnItem->drawnAt->value->format('Y-m-d H:i:s')
                )
            )
            ->once();

        $this->getInstance()->save($drawnItem);
    }

    private function getInstance(): FileDrawnItemRepository
    {
        $this->config->shouldReceive('getString')
            ->with('debug.file.path')
            ->andReturn('')
            ->once();

        return new FileDrawnItemRepository($this->store, $this->config);
    }
}
