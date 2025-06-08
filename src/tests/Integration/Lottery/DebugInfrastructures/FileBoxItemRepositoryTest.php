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

    private function getInstance(): FileBoxItemRepository
    {
        return $this->app->make(FileBoxItemRepository::class);
    }
}
