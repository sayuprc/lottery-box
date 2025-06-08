<?php

declare(strict_types=1);

namespace Tests\Feature\Console\Commands;

use Lottery\DebugInfrastructures\FileBoxItemRepository;
use Lottery\DebugInfrastructures\FileLotteryBoxRepository;
use Lottery\DebugInfrastructures\FileLotteryItemRepository;
use Lottery\Domain\Models\BoxItem\BoxItem;
use Lottery\Domain\Models\LotteryBox\BoxId;
use Lottery\Domain\Models\LotteryBox\BoxName;
use Lottery\Domain\Models\LotteryBox\LotteryBox;
use Lottery\Domain\Models\LotteryItem\ItemId;
use Lottery\Domain\Models\LotteryItem\ItemName;
use Lottery\Domain\Models\LotteryItem\LotteryItem;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\FileRepositoryTransaction;
use Tests\TestCase;

class LotteryTest extends TestCase
{
    use FileRepositoryTransaction;

    #[Test]
    public function lottery(): void
    {
        $boxName = '抽選箱';

        $lotteryBox = new LotteryBox(new BoxId(str_repeat('a', 26)), new BoxName($boxName));
        $lotteryItem = new LotteryItem(new ItemId(str_repeat('b', 26)), new ItemName('抽選アイテム'));
        $boxItem = new BoxItem($lotteryBox->boxId, $lotteryItem->itemId);

        $this->factory(FileLotteryBoxRepository::class, $lotteryBox->boxId->value, $lotteryBox);
        $this->factory(FileLotteryItemRepository::class, $lotteryItem->itemId->value, $lotteryItem);
        $this->factory(FileBoxItemRepository::class, $boxItem->boxId->value . '-' . $boxItem->itemId->value, $boxItem);

        $console = $this->artisan("lottery {$boxName}");

        $console->expectsOutput('抽選アイテム')
            ->assertSuccessful();
    }
}
