<?php

declare(strict_types=1);

namespace Tests\Unit\Lottery\Application\Interactors;

use Lottery\Application\Interactors\LotteryInteractor;
use Lottery\Application\UseCase\Lottery\LotteryInput;
use Lottery\Application\UseCase\Lottery\LotteryOutput;
use Lottery\Domain\Models\BoxItem\BoxItem;
use Lottery\Domain\Models\BoxItem\BoxItemRepositoryInterface;
use Lottery\Domain\Models\LotteryBox\BoxId;
use Lottery\Domain\Models\LotteryBox\BoxName;
use Lottery\Domain\Models\LotteryBox\LotteryBox;
use Lottery\Domain\Models\LotteryBox\LotteryBoxRepositoryInterface;
use Lottery\Domain\Models\LotteryItem\ItemId;
use Lottery\Domain\Models\LotteryItem\ItemName;
use Lottery\Domain\Models\LotteryItem\LotteryItem;
use Lottery\Domain\Models\LotteryItem\LotteryItemRepositoryInterface;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LotteryInteractorTest extends TestCase
{
    private LotteryBoxRepositoryInterface&MockInterface $lotteryBoxRepository;

    private LotteryItemRepositoryInterface&MockInterface $lotteryItemRepository;

    private BoxItemRepositoryInterface&MockInterface $boxItemRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->lotteryBoxRepository = Mockery::mock(LotteryBoxRepositoryInterface::class);
        $this->lotteryItemRepository = Mockery::mock(LotteryItemRepositoryInterface::class);
        $this->boxItemRepository = Mockery::mock(BoxItemRepositoryInterface::class);
    }

    #[Test]
    public function lotterySuccessfully(): void
    {
        $boxName = '抽選箱';

        $this->lotteryBoxRepository->shouldReceive('findByBoxName')
            ->with(Mockery::on(fn (BoxName $arg) => $arg->value === $boxName))
            ->andReturnUsing(fn () => new LotteryBox(new BoxId(str_repeat('a', 26)), new BoxName($boxName)))
            ->once();

        $this->boxItemRepository->shouldReceive('getByBoxId')
            ->with(Mockery::on(fn (BoxId $arg) => $arg->value === str_repeat('a', 26)))
            ->andReturnUsing(fn () => [
                new BoxItem(new BoxId(str_repeat('a', 26)), new ItemId(str_repeat('b', 26))),
            ])
            ->once();

        $this->lotteryItemRepository->shouldReceive('find')
            ->with(Mockery::on(fn (ItemId $arg) => $arg->value === str_repeat('b', 26)))
            ->andReturnUsing(fn () => new LotteryItem(new ItemId(str_repeat('b', 26)), new ItemName('抽選アイテム')))
            ->once();

        $result = $this->getInstance()->handle(new LotteryInput($boxName));

        $this->assertTrue($result->isOk());
        $this->assertInstanceOf(LotteryOutput::class, $result->unwrap());

        $this->assertSame('抽選アイテム', $result->unwrap()->itemName->value);
    }

    #[Test]
    public function lotteryBoxIsNotFound(): void
    {
        $boxName = '抽選箱';

        $this->lotteryBoxRepository->shouldReceive('findByBoxName')
            ->with(Mockery::on(fn (BoxName $arg) => $arg->value === $boxName))
            ->andReturnNull()
            ->once();

        $result = $this->getInstance()->handle(new LotteryInput($boxName));

        $this->assertTrue($result->isErr());
        $this->assertSame('抽選箱「抽選箱」は存在しません。', $result->unwrapErr());
    }

    #[Test]
    public function boxIsEmpty(): void
    {
        $boxName = '抽選箱';

        $this->lotteryBoxRepository->shouldReceive('findByBoxName')
            ->with(Mockery::on(fn (BoxName $arg) => $arg->value === $boxName))
            ->andReturnUsing(fn () => new LotteryBox(new BoxId(str_repeat('a', 26)), new BoxName($boxName)))
            ->once();

        $this->boxItemRepository->shouldReceive('getByBoxId')
            ->with(Mockery::on(fn (BoxId $arg) => $arg->value === str_repeat('a', 26)))
            ->andReturn([])
            ->once();

        $result = $this->getInstance()->handle(new LotteryInput($boxName));

        $this->assertTrue($result->isErr());
        $this->assertSame('抽選箱「抽選箱」にアイテムがありません。', $result->unwrapErr());
    }

    #[Test]
    public function lotteryItemNotFound(): void
    {
        $boxName = '抽選箱';

        $this->lotteryBoxRepository->shouldReceive('findByBoxName')
            ->with(Mockery::on(fn (BoxName $arg) => $arg->value === $boxName))
            ->andReturnUsing(fn () => new LotteryBox(new BoxId(str_repeat('a', 26)), new BoxName($boxName)))
            ->once();

        $this->boxItemRepository->shouldReceive('getByBoxId')
            ->with(Mockery::on(fn (BoxId $arg) => $arg->value === str_repeat('a', 26)))
            ->andReturnUsing(fn () => [
                new BoxItem(new BoxId(str_repeat('a', 26)), new ItemId(str_repeat('b', 26))),
            ])
            ->once();

        $this->lotteryItemRepository->shouldReceive('find')
            ->with(Mockery::on(fn (ItemId $arg) => $arg->value === str_repeat('b', 26)))
            ->andReturnNull()
            ->once();

        $result = $this->getInstance()->handle(new LotteryInput($boxName));

        $this->assertTrue($result->isErr());
        $this->assertSame('抽選アイテムが存在しません。', $result->unwrapErr());
    }

    private function getInstance(): LotteryInteractor
    {
        return new LotteryInteractor(
            $this->lotteryBoxRepository,
            $this->lotteryItemRepository,
            $this->boxItemRepository,
        );
    }
}
