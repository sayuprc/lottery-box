<?php

declare(strict_types=1);

namespace Tests\Integration\Lottery\DebugInfrastructures;

use Lottery\DebugInfrastructures\FileBoxItemRepository;
use Lottery\Domain\Models\BoxItem\BoxItem;
use Lottery\Domain\Models\LotteryBox\BoxId;
use Lottery\Domain\Models\LotteryItem\ItemId;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\FileRepositoryTransaction;
use Tests\TestCase;

class FileBoxItemRepositoryTest extends TestCase
{
    use FileRepositoryTransaction;

    #[Test]
    public function saved(): void
    {
        $boxItem = new BoxItem(new BoxId(str_repeat('a', 26)), new ItemId(str_repeat('b', 26)));

        $this->getInstance()->save($boxItem);

        /** @var array<string, BoxItem> $boxItems */
        $boxItems = $this->getAll(FileBoxItemRepository::class);

        $key = $boxItem->boxId->value . '-' . $boxItem->itemId->value;

        $this->assertCount(1, $boxItems);
        $this->assertArrayHasKey($key, $boxItems);
        $this->assertSame($boxItem->boxId->value, $boxItems[$key]->boxId->value);
        $this->assertSame($boxItem->itemId->value, $boxItems[$key]->itemId->value);
    }

    #[Test]
    public function getByBoxId(): void
    {
        $boxId = new BoxId(str_repeat('a', 26));
        $boxItem = new BoxItem($boxId, new ItemId(str_repeat('b', 26)));

        $this->factory(FileBoxItemRepository::class, $boxItem->boxId->value . '-' . $boxItem->itemId->value, $boxItem);

        $result = $this->getInstance()->getByBoxId($boxId);

        $this->assertCount(1, $result);
        $this->assertSame($boxId->value, $result[0]->boxId->value);
        $this->assertSame(str_repeat('b', 26), $result[0]->itemId->value);
    }

    #[Test]
    public function getByBoxIdFromEmpty(): void
    {
        $boxId = new BoxId(str_repeat('a', 26));

        $result = $this->getInstance()->getByBoxId($boxId);

        $this->assertCount(0, $result);
    }

    private function getInstance(): FileBoxItemRepository
    {
        return $this->app->make(FileBoxItemRepository::class);
    }
}
