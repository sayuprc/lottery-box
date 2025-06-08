<?php

declare(strict_types=1);

namespace Tests\Feature\Console\Commands;

use Lottery\DebugInfrastructures\FileLotteryItemRepository;
use Lottery\Domain\Models\LotteryItem\ItemId;
use Lottery\Domain\Models\LotteryItem\ItemName;
use Lottery\Domain\Models\LotteryItem\LotteryItem;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\FileRepositoryTransaction;
use Tests\TestCase;

class CreateLotteryItemTest extends TestCase
{
    use FileRepositoryTransaction;

    #[Test]
    public function createLotteryItem(): void
    {
        $console = $this->artisan('create:lottery-item a');

        $console->expectsOutput('抽選アイテム「a」を作成しました。')
            ->assertSuccessful();
    }

    #[Test]
    public function failedCreateLotteryItem(): void
    {
        $lotteryItem = new LotteryItem(new ItemId(str_repeat('a', 26)), new ItemName('a'));

        $this->factory(FileLotteryItemRepository::class, $lotteryItem->itemId->value, $lotteryItem);

        $console = $this->artisan('create:lottery-item a');

        $console->expectsOutput('すでに同名の抽選アイテムが存在します。: a')
            ->assertFailed();
    }
}
