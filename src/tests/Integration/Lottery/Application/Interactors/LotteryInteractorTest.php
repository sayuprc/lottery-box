<?php

declare(strict_types=1);

namespace Tests\Integration\Lottery\Application\Interactors;

use DateTimeImmutable;
use Lottery\Application\Interactors\LotteryInteractor;
use Lottery\Application\UseCase\Lottery\LotteryInput;
use Lottery\Application\UseCase\Lottery\LotteryOutput;
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
use Lottery\Domain\Models\LotteryBox\ResetAt;
use Lottery\Domain\Models\LotteryItem\ItemId;
use Lottery\Domain\Models\LotteryItem\ItemName;
use Lottery\Domain\Models\LotteryItem\LotteryItem;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\FileRepositoryTransaction;
use Tests\TestCase;

class LotteryInteractorTest extends TestCase
{
    use FileRepositoryTransaction;

    #[Test]
    public function lotterySuccessfully(): void
    {
        $boxName = '抽選箱';

        $lotteryBox = new LotteryBox(new BoxId(str_repeat('a', 26)), new BoxName($boxName));
        $lotteryItem = new LotteryItem(new ItemId(str_repeat('b', 26)), new ItemName('抽選アイテム'));
        $boxItem = new BoxItem($lotteryBox->boxId, $lotteryItem->itemId);

        $this->factory(FileLotteryBoxRepository::class, $lotteryBox->boxId->value, $lotteryBox);
        $this->factory(FileLotteryItemRepository::class, $lotteryItem->itemId->value, $lotteryItem);
        $this->factory(FileBoxItemRepository::class, $boxItem->boxId->value . '-' . $boxItem->itemId->value, $boxItem);

        $result = $this->getInstance()->handle(new LotteryInput($boxName));

        $this->assertTrue($result->isOk());
        $this->assertInstanceOf(LotteryOutput::class, $result->unwrap());

        $this->assertSame('抽選アイテム', $result->unwrap()->itemName->value);
    }

    #[Test]
    public function uniqueLotterySuccessfully(): void
    {
        $boxName = '抽選箱';

        $lotteryBox = new LotteryBox(new BoxId(str_repeat('a', 26)), new BoxName($boxName));

        $lotteryItem1 = new LotteryItem(new ItemId(str_repeat('b', 26)), new ItemName('抽選アイテム1'));
        $lotteryItem2 = new LotteryItem(new ItemId(str_repeat('c', 26)), new ItemName('抽選アイテム2'));

        $boxItem1 = new BoxItem($lotteryBox->boxId, $lotteryItem1->itemId);
        $boxItem2 = new BoxItem($lotteryBox->boxId, $lotteryItem2->itemId);

        $drawnItem = new DrawnItem($lotteryBox->boxId, $lotteryItem1->itemId, new DrawnAt(new DateTimeImmutable('2025-06-13 14:26:30')));

        $this->factory(FileLotteryBoxRepository::class, $lotteryBox->boxId->value, $lotteryBox);
        $this->factory(FileLotteryItemRepository::class, $lotteryItem1->itemId->value, $lotteryItem1);
        $this->factory(FileLotteryItemRepository::class, $lotteryItem2->itemId->value, $lotteryItem2);
        $this->factory(FileBoxItemRepository::class, $boxItem1->boxId->value . '-' . $boxItem1->itemId->value, $boxItem1);
        $this->factory(FileBoxItemRepository::class, $boxItem2->boxId->value . '-' . $boxItem2->itemId->value, $boxItem2);
        $this->factory(FileDrawnItemRepository::class, $drawnItem->boxId->value . '-' . $drawnItem->itemId->value . '-' . $drawnItem->drawnAt->value->format('YmdHis'), $drawnItem);

        $result = $this->getInstance()->handle(new LotteryInput($boxName, true));

        $this->assertTrue($result->isOk());
        $this->assertInstanceOf(LotteryOutput::class, $result->unwrap());

        $this->assertSame('抽選アイテム2', $result->unwrap()->itemName->value);
    }

    #[Test]
    public function resetUniqueLotterySuccessfully(): void
    {
        $boxName = '抽選箱';

        $lotteryBox = new LotteryBox(new BoxId(str_repeat('a', 26)), new BoxName($boxName), new ResetAt(new DateTimeImmutable('2025-06-14 14:26:30')));

        $lotteryItem1 = new LotteryItem(new ItemId(str_repeat('b', 26)), new ItemName('抽選アイテム1'));
        $lotteryItem2 = new LotteryItem(new ItemId(str_repeat('c', 26)), new ItemName('抽選アイテム2'));

        $boxItem1 = new BoxItem($lotteryBox->boxId, $lotteryItem1->itemId);
        $boxItem2 = new BoxItem($lotteryBox->boxId, $lotteryItem2->itemId);

        $drawnItem = new DrawnItem($lotteryBox->boxId, $lotteryItem1->itemId, new DrawnAt(new DateTimeImmutable('2025-06-15 14:26:30')));

        $this->factory(FileLotteryBoxRepository::class, $lotteryBox->boxId->value, $lotteryBox);
        $this->factory(FileLotteryItemRepository::class, $lotteryItem1->itemId->value, $lotteryItem1);
        $this->factory(FileLotteryItemRepository::class, $lotteryItem2->itemId->value, $lotteryItem2);
        $this->factory(FileBoxItemRepository::class, $boxItem1->boxId->value . '-' . $boxItem1->itemId->value, $boxItem1);
        $this->factory(FileBoxItemRepository::class, $boxItem2->boxId->value . '-' . $boxItem2->itemId->value, $boxItem2);
        $this->factory(FileDrawnItemRepository::class, $drawnItem->boxId->value . '-' . $drawnItem->itemId->value . '-' . $drawnItem->drawnAt->value->format('YmdHis'), $drawnItem);

        $result = $this->getInstance()->handle(new LotteryInput($boxName, true));

        $this->assertTrue($result->isOk());
        $this->assertInstanceOf(LotteryOutput::class, $result->unwrap());

        $this->assertSame('抽選アイテム2', $result->unwrap()->itemName->value);
    }

    #[Test]
    public function lotteryBoxIsNotFound(): void
    {
        $boxName = '抽選箱';

        $result = $this->getInstance()->handle(new LotteryInput($boxName));

        $this->assertTrue($result->isErr());
        $this->assertSame('抽選箱「抽選箱」は存在しません。', $result->unwrapErr());
    }

    #[Test]
    public function boxIsEmpty(): void
    {
        $boxName = '抽選箱';

        $lotteryBox = new LotteryBox(new BoxId(str_repeat('a', 26)), new BoxName($boxName));

        $this->factory(FileLotteryBoxRepository::class, $lotteryBox->boxId->value, $lotteryBox);

        $result = $this->getInstance()->handle(new LotteryInput($boxName));

        $this->assertTrue($result->isErr());
        $this->assertSame('抽選箱「抽選箱」にアイテムがありません。', $result->unwrapErr());
    }

    #[Test]
    public function mustReset(): void
    {
        $boxName = '抽選箱';

        $lotteryBox = new LotteryBox(new BoxId(str_repeat('a', 26)), new BoxName($boxName));

        $lotteryItem1 = new LotteryItem(new ItemId(str_repeat('b', 26)), new ItemName('抽選アイテム1'));
        $lotteryItem2 = new LotteryItem(new ItemId(str_repeat('c', 26)), new ItemName('抽選アイテム2'));

        $boxItem1 = new BoxItem($lotteryBox->boxId, $lotteryItem1->itemId);
        $boxItem2 = new BoxItem($lotteryBox->boxId, $lotteryItem2->itemId);

        $drawnItem1 = new DrawnItem($lotteryBox->boxId, $lotteryItem1->itemId, new DrawnAt(new DateTimeImmutable('2025-06-13 14:26:30')));
        $drawnItem2 = new DrawnItem($lotteryBox->boxId, $lotteryItem2->itemId, new DrawnAt(new DateTimeImmutable('2025-06-13 14:30:30')));

        $this->factory(FileLotteryBoxRepository::class, $lotteryBox->boxId->value, $lotteryBox);
        $this->factory(FileLotteryItemRepository::class, $lotteryItem1->itemId->value, $lotteryItem1);
        $this->factory(FileLotteryItemRepository::class, $lotteryItem2->itemId->value, $lotteryItem2);
        $this->factory(FileBoxItemRepository::class, $boxItem1->boxId->value . '-' . $boxItem1->itemId->value, $boxItem1);
        $this->factory(FileBoxItemRepository::class, $boxItem2->boxId->value . '-' . $boxItem2->itemId->value, $boxItem2);
        $this->factory(FileDrawnItemRepository::class, $drawnItem1->boxId->value . '-' . $drawnItem1->itemId->value . '-' . $drawnItem1->drawnAt->value->format('YmdHis'), $drawnItem1);
        $this->factory(FileDrawnItemRepository::class, $drawnItem2->boxId->value . '-' . $drawnItem2->itemId->value . '-' . $drawnItem2->drawnAt->value->format('YmdHis'), $drawnItem2);

        $result = $this->getInstance()->handle(new LotteryInput($boxName, true));

        $this->assertTrue($result->isErr());
        $this->assertSame('抽選箱「抽選箱」からすべてのアイテムを抽選しました。リセットしてください。', $result->unwrapErr());
    }

    #[Test]
    public function lotteryItemNotFound(): void
    {
        $boxName = '抽選箱';

        $lotteryBox = new LotteryBox(new BoxId(str_repeat('a', 26)), new BoxName($boxName));
        $boxItem = new BoxItem($lotteryBox->boxId, new ItemId(str_repeat('b', 26)));

        $this->factory(FileLotteryBoxRepository::class, $lotteryBox->boxId->value, $lotteryBox);
        $this->factory(FileBoxItemRepository::class, $boxItem->boxId->value . '-' . $boxItem->itemId->value, $boxItem);

        $result = $this->getInstance()->handle(new LotteryInput($boxName));

        $this->assertTrue($result->isErr());
        $this->assertSame('抽選アイテムが存在しません。', $result->unwrapErr());
    }

    private function getInstance(): LotteryInteractor
    {
        return $this->app->make(LotteryInteractor::class);
    }
}
