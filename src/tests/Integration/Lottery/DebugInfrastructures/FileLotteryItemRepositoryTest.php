<?php

declare(strict_types=1);

namespace Tests\Integration\Lottery\DebugInfrastructures;

use Lottery\DebugInfrastructures\FileLotteryItemRepository;
use Lottery\Domain\Models\LotteryItem\ItemId;
use Lottery\Domain\Models\LotteryItem\ItemName;
use Lottery\Domain\Models\LotteryItem\LotteryItem;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\FileRepositoryTransaction;
use Tests\TestCase;

class FileLotteryItemRepositoryTest extends TestCase
{
    use FileRepositoryTransaction;

    #[Test]
    public function foundByItemName(): void
    {
        $lotteryItem = new LotteryItem(new ItemId(str_repeat('a', 26)), new ItemName('抽選アイテム'));

        $this->factory(FileLotteryItemRepository::class, $lotteryItem->itemId->value, $lotteryItem);

        $this->assertInstanceOf(LotteryItem::class, $this->getInstance()->findByItemName(new ItemName('抽選アイテム')));
    }

    #[Test]
    public function notFoundByItemName(): void
    {
        $this->assertNull($this->getInstance()->findByItemName(new ItemName('抽選アイテム')));
    }

    private function getInstance(): FileLotteryItemRepository
    {
        return $this->app->make(FileLotteryItemRepository::class);
    }
}
