<?php

declare(strict_types=1);

namespace Tests\Integration\Lottery\DebugInfrastructures;

use DateTimeImmutable;
use Lottery\DebugInfrastructures\FileDrawnItemRepository;
use Lottery\Domain\Models\DrawnItem\DrawnAt;
use Lottery\Domain\Models\DrawnItem\DrawnItem;
use Lottery\Domain\Models\LotteryBox\BoxId;
use Lottery\Domain\Models\LotteryBox\ResetAt;
use Lottery\Domain\Models\LotteryItem\ItemId;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\FileRepositoryTransaction;
use Tests\TestCase;

class FileDrawnItemRepositoryTest extends TestCase
{
    use FileRepositoryTransaction;

    #[Test]
    public function getByBoxId(): void
    {
        $boxId = new BoxId(str_repeat('a', 26));
        $drawnItem = new DrawnItem(
            $boxId,
            new ItemId(str_repeat('b', 26)),
            new DrawnAt(new DateTimeImmutable('2025-06-14 14:26:30')),
        );

        $this->factory(
            FileDrawnItemRepository::class,
            $drawnItem->boxId->value . '-' . $drawnItem->itemId->value . '-' . $drawnItem->drawnAt->value->format('YmdHis'),
            $drawnItem
        );

        $result = $this->getInstance()->getByBoxId($boxId);

        $this->assertCount(1, $result);
        $this->assertSame($boxId->value, $result[0]->boxId->value);
        $this->assertSame(str_repeat('b', 26), $result[0]->itemId->value);
        $this->assertSame('2025-06-14 14:26:30', $result[0]->drawnAt->value->format('Y-m-d H:i:s'));
    }

    #[Test]
    public function getByBoxIdWithResetAt(): void
    {
        $boxId = new BoxId(str_repeat('a', 26));
        $drawnItem1 = new DrawnItem(
            $boxId,
            new ItemId(str_repeat('b', 26)),
            new DrawnAt(new DateTimeImmutable('2025-06-14 14:26:30')),
        );
        $drawnItem2 = new DrawnItem(
            $boxId,
            new ItemId(str_repeat('c', 26)),
            new DrawnAt(new DateTimeImmutable('2025-06-15 14:26:30')),
        );

        $this->factory(
            FileDrawnItemRepository::class,
            $drawnItem1->boxId->value . '-' . $drawnItem1->itemId->value . '-' . $drawnItem1->drawnAt->value->format('YmdHis'),
            $drawnItem1
        );
        $this->factory(
            FileDrawnItemRepository::class,
            $drawnItem2->boxId->value . '-' . $drawnItem2->itemId->value . '-' . $drawnItem2->drawnAt->value->format('YmdHis'),
            $drawnItem2
        );

        $result = $this->getInstance()->getByBoxId($boxId, new ResetAt(new DateTimeImmutable('2025-06-15 10:00:00')));

        $this->assertCount(1, $result);
        $this->assertSame($boxId->value, $result[0]->boxId->value);
        $this->assertSame(str_repeat('c', 26), $result[0]->itemId->value);
        $this->assertSame('2025-06-15 14:26:30', $result[0]->drawnAt->value->format('Y-m-d H:i:s'));
    }

    #[Test]
    public function getByBoxIdIsEmpty(): void
    {
        $boxId = new BoxId(str_repeat('a', 26));

        $result = $this->getInstance()->getByBoxId($boxId);

        $this->assertCount(0, $result);
    }

    #[Test]
    public function saved(): void
    {
        $drawnItem = new DrawnItem(
            new BoxId(str_repeat('a', 26)),
            new ItemId(str_repeat('b', 26)),
            new DrawnAt(new DateTimeImmutable('2025-06-14 14:26:30')),
        );

        $this->getInstance()->save($drawnItem);

        /** @var array<string, DrawnItem> $drawnItems */
        $drawnItems = $this->getAll(FileDrawnItemRepository::class);

        $key = $drawnItem->boxId->value . '-' . $drawnItem->itemId->value . '-' . $drawnItem->drawnAt->value->format('YmdHis');

        $this->assertCount(1, $drawnItems);
        $this->assertArrayHasKey($key, $drawnItems);
        $this->assertSame($drawnItem->boxId->value, $drawnItems[$key]->boxId->value);
        $this->assertSame($drawnItem->itemId->value, $drawnItems[$key]->itemId->value);
        $this->assertSame($drawnItem->drawnAt->value->format('Y-m-d H:i:s'), $drawnItems[$key]->drawnAt->value->format('Y-m-d H:i:s'));
    }

    private function getInstance(): FileDrawnItemRepository
    {
        return $this->app->make(FileDrawnItemRepository::class);
    }
}
