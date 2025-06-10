<?php

declare(strict_types=1);

namespace Tests\Unit\Lottery\Application\Interactors;

use Closure;
use Lottery\Application\Interactors\AddItemToBoxInteractor;
use Lottery\Application\UseCase\AddItemToBox\AddItemToBoxInput;
use Lottery\Application\UseCase\AddItemToBox\AddItemToBoxOutput;
use Lottery\Domain\Models\BoxItem\BoxItem;
use Lottery\Domain\Models\BoxItem\BoxItemRepositoryInterface;
use Lottery\Domain\Models\LotteryBox\BoxId;
use Lottery\Domain\Models\LotteryBox\BoxName;
use Lottery\Domain\Models\LotteryBox\LotteryBox;
use Lottery\Domain\Models\LotteryBox\LotteryBoxFactoryInterface;
use Lottery\Domain\Models\LotteryBox\LotteryBoxRepositoryInterface;
use Lottery\Domain\Models\LotteryItem\ItemId;
use Lottery\Domain\Models\LotteryItem\ItemName;
use Lottery\Domain\Models\LotteryItem\LotteryItem;
use Lottery\Domain\Models\LotteryItem\LotteryItemFactoryInterface;
use Lottery\Domain\Models\LotteryItem\LotteryItemRepositoryInterface;
use Lottery\Domain\Services\LotteryBoxNameDuplicateCheckService;
use Lottery\Domain\Services\LotteryItemNameDuplicateCheckService;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Support\Contracts\TransactionInterface;
use Tests\TestCase;

class AddItemToBoxInteractorTest extends TestCase
{
    private LotteryBoxFactoryInterface&MockInterface $lotteryBoxFactory;

    private LotteryBoxNameDuplicateCheckService&MockInterface $lotteryBoxNameDuplicateCheckService;

    private LotteryBoxRepositoryInterface&MockInterface $lotteryBoxRepository;

    private LotteryItemFactoryInterface&MockInterface $lotteryItemFactory;

    private LotteryItemNameDuplicateCheckService&MockInterface $lotteryItemNameDuplicateCheckService;

    private LotteryItemRepositoryInterface&MockInterface $lotteryItemRepository;

    private BoxItemRepositoryInterface&MockInterface $boxItemRepository;

    private MockInterface&TransactionInterface $transaction;

    protected function setUp(): void
    {
        parent::setUp();

        $this->lotteryBoxFactory = Mockery::mock(LotteryBoxFactoryInterface::class);
        $this->lotteryBoxNameDuplicateCheckService = Mockery::mock(LotteryBoxNameDuplicateCheckService::class);
        $this->lotteryBoxRepository = Mockery::mock(LotteryBoxRepositoryInterface::class);
        $this->lotteryItemFactory = Mockery::mock(LotteryItemFactoryInterface::class);
        $this->lotteryItemNameDuplicateCheckService = Mockery::mock(LotteryItemNameDuplicateCheckService::class);
        $this->lotteryItemRepository = Mockery::mock(LotteryItemRepositoryInterface::class);
        $this->boxItemRepository = Mockery::mock(BoxItemRepositoryInterface::class);
        $this->transaction = Mockery::mock(TransactionInterface::class);
    }

    #[Test]
    public function addItemSuccessfully(): void
    {
        $boxId = str_repeat('a', 26);
        $boxName = '抽選箱';

        $this->lotteryBoxFactory->shouldReceive('create')
            ->with($boxName)
            ->andReturnUsing(fn () => new LotteryBox(new BoxId($boxId), new BoxName($boxName)))
            ->once();

        $this->lotteryBoxNameDuplicateCheckService->shouldReceive('exists')
            ->with(Mockery::on(fn (BoxName $arg) => $arg->value === $boxName))
            ->andReturnFalse()
            ->once();

        $this->lotteryBoxRepository->shouldReceive('save')
            ->with(Mockery::on(fn (LotteryBox $arg) => $arg->boxId->value === $boxId && $arg->boxName->value === $boxName))
            ->once();

        $itemId = str_repeat('b', 26);
        $itemName = '抽選アイテム';

        $this->lotteryItemFactory->shouldReceive('create')
            ->with($itemName)
            ->andReturnUsing(fn () => new LotteryItem(new ItemId($itemId), new ItemName($itemName)))
            ->once();

        $this->lotteryItemNameDuplicateCheckService->shouldReceive('exists')
            ->with(Mockery::on(fn (ItemName $arg) => $arg->value === $itemName))
            ->andReturnFalse()
            ->once();

        $this->lotteryItemRepository->shouldReceive('save')
            ->with(Mockery::on(fn (LotteryItem $arg) => $arg->itemId->value === $itemId && $arg->itemName->value === $itemName))
            ->once();

        $this->boxItemRepository->shouldReceive('save')
            ->with(Mockery::on(fn (BoxItem $arg) => $arg->boxId->value === $boxId && $arg->itemId->value === $itemId))
            ->once();

        $this->transaction->shouldReceive('scope')
            ->with(Mockery::on(fn (Closure $arg) => true))
            ->andReturnUsing(fn (Closure $arg) => $arg())
            ->once();

        $result = $this->getInstance()->handle(new AddItemToBoxInput($boxName, [$itemName]));

        $this->assertTrue($result->isOk());
        $this->assertInstanceOf(AddItemToBoxOutput::class, $result->unwrap());
    }

    #[Test]
    public function addDuplicatedItemName(): void
    {
        $boxId = str_repeat('a', 26);
        $boxName = '抽選箱';
        $foundBoxId = str_repeat('x', 26);

        $this->lotteryBoxFactory->shouldReceive('create')
            ->with($boxName)
            ->andReturnUsing(fn () => new LotteryBox(new BoxId($boxId), new BoxName($boxName)))
            ->once();

        $this->lotteryBoxNameDuplicateCheckService->shouldReceive('exists')
            ->with(Mockery::on(fn (BoxName $arg) => $arg->value === $boxName))
            ->andReturnTrue()
            ->once();

        $this->lotteryBoxRepository->shouldReceive('findByBoxName')
            ->with(Mockery::on(fn (BoxName $arg) => $arg->value === $boxName))
            ->andReturnUsing(fn () => new LotteryBox(new BoxId($foundBoxId), new BoxName($boxName)))
            ->once();

        $this->lotteryBoxRepository->shouldReceive('save')
            ->with(Mockery::on(fn (LotteryBox $arg) => $arg->boxId->value === $foundBoxId && $arg->boxName->value === $boxName))
            ->once();

        $itemId = str_repeat('b', 26);
        $itemName = '抽選アイテム';
        $foundItemxId = str_repeat('y', 26);

        $this->lotteryItemFactory->shouldReceive('create')
            ->with($itemName)
            ->andReturnUsing(fn () => new LotteryItem(new ItemId($itemId), new ItemName($itemName)))
            ->once();

        $this->lotteryItemNameDuplicateCheckService->shouldReceive('exists')
            ->with(Mockery::on(fn (ItemName $arg) => $arg->value === $itemName))
            ->andReturnTrue()
            ->once();

        $this->lotteryItemRepository->shouldReceive('findByItemName')
            ->with(Mockery::on(fn (ItemName $arg) => $arg->value === $itemName))
            ->andReturnUsing(fn () => new LotteryItem(new ItemId($foundItemxId), new ItemName($itemName)))
            ->once();

        $this->lotteryItemRepository->shouldReceive('save')
            ->with(Mockery::on(fn (LotteryItem $arg) => $arg->itemId->value === $foundItemxId && $arg->itemName->value === $itemName))
            ->once();

        $this->boxItemRepository->shouldReceive('save')
            ->with(Mockery::on(fn (BoxItem $arg) => $arg->boxId->value === $foundBoxId && $arg->itemId->value === $foundItemxId))
            ->once();

        $this->transaction->shouldReceive('scope')
            ->with(Mockery::on(fn (Closure $arg) => true))
            ->andReturnUsing(fn (Closure $arg) => $arg())
            ->once();

        $result = $this->getInstance()->handle(new AddItemToBoxInput($boxName, [$itemName]));

        $this->assertTrue($result->isOk());
        $this->assertInstanceOf(AddItemToBoxOutput::class, $result->unwrap());
    }

    private function getInstance(): AddItemToBoxInteractor
    {
        return new AddItemToBoxInteractor(
            $this->lotteryBoxFactory,
            $this->lotteryBoxNameDuplicateCheckService,
            $this->lotteryBoxRepository,
            $this->lotteryItemFactory,
            $this->lotteryItemNameDuplicateCheckService,
            $this->lotteryItemRepository,
            $this->boxItemRepository,
            $this->transaction,
        );
    }
}
