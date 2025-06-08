<?php

declare(strict_types=1);

namespace Tests\Integration\Lottery\Application\Interactors;

use Lottery\Application\Interactors\LotteryInteractor;
use Lottery\Application\UseCase\Lottery\LotteryInput;
use Lottery\Application\UseCase\Lottery\LotteryOutput;
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
