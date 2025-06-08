<?php

declare(strict_types=1);

namespace Tests\Integration\Lottery\Domain\Services;

use Lottery\DebugInfrastructures\FileLotteryItemRepository;
use Lottery\Domain\Models\LotteryItem\ItemId;
use Lottery\Domain\Models\LotteryItem\ItemName;
use Lottery\Domain\Models\LotteryItem\LotteryItem;
use Lottery\Domain\Services\LotteryItemNameDuplicateCheckService;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\FileRepositoryTransaction;
use Tests\TestCase;

class LotteryItemNameDuplicateCheckServiceTest extends TestCase
{
    use FileRepositoryTransaction;

    #[Test]
    public function notDuplicated(): void
    {
        $this->assertFalse($this->getInstance()->exists(new ItemName('抽選アイテム')));
    }

    #[Test]
    public function duplicated(): void
    {
        $lotteryItem = new LotteryItem(new ItemId(str_repeat('a', 26)), new ItemName('抽選アイテム'));

        $this->factory(FileLotteryItemRepository::class, $lotteryItem->itemId->value, $lotteryItem);

        $this->assertTrue($this->getInstance()->exists(new ItemName('抽選アイテム')));
    }

    private function getInstance(): LotteryItemNameDuplicateCheckService
    {
        return $this->app->make(LotteryItemNameDuplicateCheckService::class);
    }
}
