<?php

declare(strict_types=1);

namespace Tests\Integration\Lottery\Application\Interactors;

use Lottery\Application\Interactors\AddItemToBoxInteractor;
use Lottery\Application\UseCase\AddItemToBox\AddItemToBoxInput;
use Lottery\Application\UseCase\AddItemToBox\AddItemToBoxOutput;
use Lottery\DebugInfrastructures\FileBoxItemRepository;
use Lottery\DebugInfrastructures\FileLotteryBoxRepository;
use Lottery\DebugInfrastructures\FileLotteryItemRepository;
use Lottery\Domain\Models\LotteryBox\BoxId;
use Lottery\Domain\Models\LotteryBox\BoxName;
use Lottery\Domain\Models\LotteryBox\LotteryBox;
use Lottery\Domain\Models\LotteryItem\ItemId;
use Lottery\Domain\Models\LotteryItem\ItemName;
use Lottery\Domain\Models\LotteryItem\LotteryItem;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\FileRepositoryTransaction;
use Tests\TestCase;

class AddItemToBoxInteractorTest extends TestCase
{
    use FileRepositoryTransaction;

    #[Test]
    public function addItemSuccessfully(): void
    {
        $boxName = '抽選箱';

        $itemName = '抽選アイテム';

        $result = $this->getInstance()->handle(new AddItemToBoxInput($boxName, [$itemName]));

        $this->assertTrue($result->isOk());
        $this->assertInstanceOf(AddItemToBoxOutput::class, $result->unwrap());

        /** @var array<LotteryBox> $boxes */
        $boxes = $this->getAll(FileLotteryBoxRepository::class);
        $this->assertCount(1, $boxes);
        $key = array_key_first($boxes);
        $this->assertSame($boxName, $boxes[$key]->boxName->value);

        /** @var array<LotteryItem> $items */
        $items = $this->getAll(FileLotteryItemRepository::class);
        $this->assertCount(1, $items);
        $key = array_key_first($items);
        $this->assertSame($itemName, $items[$key]->itemName->value);

        /** @var array<BoxItem> $boxItems */
        $boxItems = $this->getAll(FileBoxItemRepository::class);
        $this->assertCount(1, $boxItems);
    }

    #[Test]
    public function addDuplicatedItemName(): void
    {
        $boxName = '抽選箱';

        $itemName = '抽選アイテム';

        $lotteryBox = new LotteryBox(new BoxId(str_repeat('a', 26)), new BoxName($boxName));
        $this->factory(FileLotteryBoxRepository::class, $lotteryBox->boxId->value, $lotteryBox);
        $lotteryItem = new LotteryItem(new ItemId(str_repeat('b', 26)), new ItemName($itemName));
        $this->factory(FileLotteryItemRepository::class, $lotteryItem->itemId->value, $lotteryItem);

        $result = $this->getInstance()->handle(new AddItemToBoxInput($boxName, [$itemName]));

        $this->assertTrue($result->isOk());
        $this->assertInstanceOf(AddItemToBoxOutput::class, $result->unwrap());

        /** @var array<LotteryBox> $boxes */
        $boxes = $this->getAll(FileLotteryBoxRepository::class);
        $this->assertCount(1, $boxes);
        $key = array_key_first($boxes);
        $this->assertSame($boxName, $boxes[$key]->boxName->value);

        /** @var array<LotteryItem> $items */
        $items = $this->getAll(FileLotteryItemRepository::class);
        $this->assertCount(1, $items);
        $key = array_key_first($items);
        $this->assertSame($itemName, $items[$key]->itemName->value);

        /** @var array<BoxItem> $boxItems */
        $boxItems = $this->getAll(FileBoxItemRepository::class);
        $this->assertCount(1, $boxItems);
    }

    private function getInstance(): AddItemToBoxInteractor
    {
        return $this->app->make(AddItemToBoxInteractor::class);
    }
}
