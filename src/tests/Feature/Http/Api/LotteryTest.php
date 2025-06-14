<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Api;

use DateTimeImmutable;
use Lottery\DebugInfrastructures\FileBoxItemRepository;
use Lottery\DebugInfrastructures\FileDrawnItemRepository;
use Lottery\DebugInfrastructures\FileLotteryBoxRepository;
use Lottery\DebugInfrastructures\FileLotteryItemRepository;
use Lottery\Domain\Models\BoxItem\BoxItem;
use Lottery\Domain\Models\DrawnItem\DrawnAt;
use Lottery\Domain\Models\DrawnItem\DrawnItem;
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

        $this->post('/api/lottery', ['boxName' => $boxName])
            ->assertStatus(200)
            ->assertJson([
                'winning' => '抽選アイテム',
            ]);
    }

    #[Test]
    public function uniqueLottery(): void
    {
        $boxName = '抽選箱';

        $lotteryBox = new LotteryBox(new BoxId(str_repeat('a', 26)), new BoxName($boxName));

        $lotteryItem1 = new LotteryItem(new ItemId(str_repeat('b', 26)), new ItemName('抽選アイテム1'));
        $lotteryItem2 = new LotteryItem(new ItemId(str_repeat('c', 26)), new ItemName('抽選アイテム2'));

        $boxItem1 = new BoxItem($lotteryBox->boxId, $lotteryItem1->itemId);
        $boxItem2 = new BoxItem($lotteryBox->boxId, $lotteryItem2->itemId);

        $drawnItem = new DrawnItem($lotteryBox->boxId, $lotteryItem1->itemId, new DrawnAt(new DateTimeImmutable()));

        $this->factory(FileLotteryBoxRepository::class, $lotteryBox->boxId->value, $lotteryBox);
        $this->factory(FileLotteryItemRepository::class, $lotteryItem1->itemId->value, $lotteryItem1);
        $this->factory(FileLotteryItemRepository::class, $lotteryItem2->itemId->value, $lotteryItem2);
        $this->factory(FileBoxItemRepository::class, $boxItem1->boxId->value . '-' . $boxItem1->itemId->value, $boxItem1);
        $this->factory(FileBoxItemRepository::class, $boxItem2->boxId->value . '-' . $boxItem2->itemId->value, $boxItem2);
        $this->factory(FileDrawnItemRepository::class, $drawnItem->boxId->value . '-' . $drawnItem->itemId->value . '-' . $drawnItem->drawnAt->value->format('YmdHis'), $drawnItem);

        $this->post('/api/lottery', ['boxName' => $boxName, 'isUnique' => true])
            ->assertStatus(200)
            ->assertJson([
                'winning' => '抽選アイテム2',
            ]);
    }
}
