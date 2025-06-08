<?php

declare(strict_types=1);

namespace Tests\Integration\Lottery\Application\Interactors;

use Lottery\Application\Interactors\CreateItemInteractor;
use Lottery\Application\UseCase\CreateItem\CreateItemInput;
use Lottery\Application\UseCase\CreateItem\CreateItemOutput;
use Lottery\DebugInfrastructures\FileLotteryItemRepository;
use Lottery\Domain\Models\LotteryItem\ItemId;
use Lottery\Domain\Models\LotteryItem\ItemName;
use Lottery\Domain\Models\LotteryItem\LotteryItem;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\FileRepositoryTransaction;
use Tests\TestCase;

class CreateItemInteractorTest extends TestCase
{
    use FileRepositoryTransaction;

    #[Test]
    public function createsItemSuccessfully(): void
    {
        $name = '抽選アイテム';

        $result = $this->getInstance()->handle(new CreateItemInput($name));

        $this->assertTrue($result->isOk());
        $this->assertInstanceOf(CreateItemOutput::class, $result->unwrap());
    }

    #[Test]
    public function duplicateItemName(): void
    {
        $name = '抽選アイテム';

        $lotteryItem = new LotteryItem(new ItemId(str_repeat('a', 26)), new ItemName($name));
        $this->factory(FileLotteryItemRepository::class, $lotteryItem->itemName->value, $lotteryItem);

        $result = $this->getInstance()->handle(new CreateItemInput($name));

        $this->assertTrue($result->isErr());
        $this->assertSame('すでに同名の抽選アイテムが存在します。: 抽選アイテム', $result->unwrapErr());
    }

    private function getInstance(): CreateItemInteractor
    {
        return $this->app->make(CreateItemInteractor::class);
    }
}
