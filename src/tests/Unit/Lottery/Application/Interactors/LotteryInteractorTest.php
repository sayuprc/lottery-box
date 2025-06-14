<?php

declare(strict_types=1);

namespace Tests\Unit\Lottery\Application\Interactors;

use Closure;
use DateTimeImmutable;
use Lottery\Application\Interactors\LotteryInteractor;
use Lottery\Application\UseCase\Lottery\LotteryInput;
use Lottery\Application\UseCase\Lottery\LotteryOutput;
use Lottery\Domain\Models\BoxItem\BoxItem;
use Lottery\Domain\Models\BoxItem\BoxItemRepositoryInterface;
use Lottery\Domain\Models\DrawnItem\DrawnAt;
use Lottery\Domain\Models\DrawnItem\DrawnItem;
use Lottery\Domain\Models\DrawnItem\DrawnItemRepositoryInterface;
use Lottery\Domain\Models\LotteryBox\BoxId;
use Lottery\Domain\Models\LotteryBox\BoxName;
use Lottery\Domain\Models\LotteryBox\LotteryBox;
use Lottery\Domain\Models\LotteryBox\LotteryBoxRepositoryInterface;
use Lottery\Domain\Models\LotteryBox\ResetAt;
use Lottery\Domain\Models\LotteryItem\ItemId;
use Lottery\Domain\Models\LotteryItem\ItemName;
use Lottery\Domain\Models\LotteryItem\LotteryItem;
use Lottery\Domain\Models\LotteryItem\LotteryItemRepositoryInterface;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Support\Contracts\TransactionInterface;
use Tests\TestCase;

class LotteryInteractorTest extends TestCase
{
    private MockInterface&TransactionInterface $transaction;

    private LotteryBoxRepositoryInterface&MockInterface $lotteryBoxRepository;

    private LotteryItemRepositoryInterface&MockInterface $lotteryItemRepository;

    private BoxItemRepositoryInterface&MockInterface $boxItemRepository;

    private DrawnItemRepositoryInterface&MockInterface $drawnItemRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->transaction = Mockery::mock(TransactionInterface::class);
        $this->lotteryBoxRepository = Mockery::mock(LotteryBoxRepositoryInterface::class);
        $this->lotteryItemRepository = Mockery::mock(LotteryItemRepositoryInterface::class);
        $this->boxItemRepository = Mockery::mock(BoxItemRepositoryInterface::class);
        $this->drawnItemRepository = Mockery::mock(DrawnItemRepositoryInterface::class);
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
    public function uniqueLotterySuccessfully(): void
    {
        $boxId = new BoxId(str_repeat('a', 26));
        $boxName = '抽選箱';

        $this->lotteryBoxRepository->shouldReceive('findByBoxName')
            ->with(Mockery::on(fn (BoxName $arg) => $arg->value === $boxName))
            ->andReturnUsing(fn () => new LotteryBox($boxId, new BoxName($boxName)))
            ->once();

        $this->boxItemRepository->shouldReceive('getByBoxId')
            ->with(Mockery::on(fn (BoxId $arg) => $arg->value === $boxId->value))
            ->andReturnUsing(fn () => [
                new BoxItem($boxId, new ItemId(str_repeat('b', 26))),
                new BoxItem($boxId, new ItemId(str_repeat('c', 26))),
            ])
            ->once();

        $this->drawnItemRepository->shouldReceive('getByBoxId')
            ->with(Mockery::on(fn (BoxId $arg) => $arg->value === $boxId->value), null)
            ->andReturnUsing(fn () => [
                new DrawnItem($boxId, new ItemId(str_repeat('b', 26)), new DrawnAt(new DateTimeImmutable())),
            ])
            ->once();

        $this->lotteryItemRepository->shouldReceive('find')
            ->with(Mockery::on(fn (ItemId $arg) => $arg->value === str_repeat('c', 26)))
            ->andReturnUsing(fn () => new LotteryItem(new ItemId(str_repeat('c', 26)), new ItemName('抽選アイテム')))
            ->once();

        $this->transaction->shouldReceive('scope')
            ->with(Mockery::on(fn (Closure $_) => true))
            ->andReturnUsing(fn (Closure $arg) => $arg())
            ->once();

        $this->drawnItemRepository->shouldReceive('save')
            ->with(
                Mockery::on(
                    fn (DrawnItem $arg) => $arg->boxId->value === $boxId->value
                        && $arg->itemId->value === str_repeat('c', 26)
                )
            )
            ->once();

        $result = $this->getInstance()->handle(new LotteryInput($boxName, true));

        $this->assertTrue($result->isOk());
        $this->assertInstanceOf(LotteryOutput::class, $result->unwrap());

        $this->assertSame('抽選アイテム', $result->unwrap()->itemName->value);
    }

    #[Test]
    public function resetUniqueLotterySuccessfully(): void
    {
        $boxId = new BoxId(str_repeat('a', 26));
        $boxName = '抽選箱';

        $this->lotteryBoxRepository->shouldReceive('findByBoxName')
            ->with(Mockery::on(fn (BoxName $arg) => $arg->value === $boxName))
            ->andReturnUsing(fn () => new LotteryBox(
                $boxId,
                new BoxName($boxName),
                new ResetAt(new DateTimeImmutable('2025-06-14 14:26:30'))
            ))
            ->once();

        $this->boxItemRepository->shouldReceive('getByBoxId')
            ->with(Mockery::on(fn (BoxId $arg) => $arg->value === $boxId->value))
            ->andReturnUsing(fn () => [
                new BoxItem($boxId, new ItemId(str_repeat('b', 26))),
                new BoxItem($boxId, new ItemId(str_repeat('c', 26))),
            ])
            ->once();

        $this->drawnItemRepository->shouldReceive('getByBoxId')
            ->with(
                Mockery::on(fn (BoxId $arg) => $arg->value === $boxId->value),
                Mockery::on(fn (ResetAt $arg) => $arg->value->format('Y-m-d H:i:s') === '2025-06-14 14:26:30')
            )
            ->andReturnUsing(fn () => [
                new DrawnItem(
                    $boxId,
                    new ItemId(str_repeat('b', 26)),
                    new DrawnAt(new DateTimeImmutable('2025-06-14 15:00:00'))
                ),
            ])
            ->once();

        $this->lotteryItemRepository->shouldReceive('find')
            ->with(Mockery::on(fn (ItemId $arg) => $arg->value === str_repeat('c', 26)))
            ->andReturnUsing(fn () => new LotteryItem(new ItemId(str_repeat('c', 26)), new ItemName('抽選アイテム')))
            ->once();

        $this->transaction->shouldReceive('scope')
            ->with(Mockery::on(fn (Closure $_) => true))
            ->andReturnUsing(fn (Closure $arg) => $arg())
            ->once();

        $this->drawnItemRepository->shouldReceive('save')
            ->with(
                Mockery::on(
                    fn (DrawnItem $arg) => $arg->boxId->value === $boxId->value
                        && $arg->itemId->value === str_repeat('c', 26)
                )
            )
            ->once();

        $result = $this->getInstance()->handle(new LotteryInput($boxName, true));

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
    public function mustReset(): void
    {
        $boxId = new BoxId(str_repeat('a', 26));
        $boxName = '抽選箱';

        $this->lotteryBoxRepository->shouldReceive('findByBoxName')
            ->with(Mockery::on(fn (BoxName $arg) => $arg->value === $boxName))
            ->andReturnUsing(fn () => new LotteryBox($boxId, new BoxName($boxName)))
            ->once();

        $this->boxItemRepository->shouldReceive('getByBoxId')
            ->with(Mockery::on(fn (BoxId $arg) => $arg->value === $boxId->value))
            ->andReturnUsing(fn () => [
                new BoxItem($boxId, new ItemId(str_repeat('b', 26))),
                new BoxItem($boxId, new ItemId(str_repeat('c', 26))),
            ])
            ->once();

        $this->drawnItemRepository->shouldReceive('getByBoxId')
            ->with(Mockery::on(fn (BoxId $arg) => $arg->value === $boxId->value), null)
            ->andReturnUsing(fn () => [
                new DrawnItem($boxId, new ItemId(str_repeat('b', 26)), new DrawnAt(new DateTimeImmutable())),
                new DrawnItem($boxId, new ItemId(str_repeat('c', 26)), new DrawnAt(new DateTimeImmutable())),
            ])
            ->once();

        $result = $this->getInstance()->handle(new LotteryInput($boxName, true));

        $this->assertTrue($result->isErr());
        $this->assertSame('抽選箱「抽選箱」からすべてのアイテムを抽選しました。リセットしてください。', $result->unwrapErr());
    }

    #[Test]
    public function mustReset2(): void
    {
        $boxId = new BoxId(str_repeat('a', 26));
        $boxName = '抽選箱';

        $this->lotteryBoxRepository->shouldReceive('findByBoxName')
            ->with(Mockery::on(fn (BoxName $arg) => $arg->value === $boxName))
            ->andReturnUsing(fn () => new LotteryBox($boxId, new BoxName($boxName)))
            ->once();

        $this->boxItemRepository->shouldReceive('getByBoxId')
            ->with(Mockery::on(fn (BoxId $arg) => $arg->value === $boxId->value))
            ->andReturnUsing(fn () => [
                new BoxItem($boxId, new ItemId(str_repeat('b', 26))),
                new BoxItem($boxId, new ItemId(str_repeat('c', 26))),
            ])
            ->once();

        $this->drawnItemRepository->shouldReceive('getByBoxId')
            ->with(Mockery::on(fn (BoxId $arg) => $arg->value === $boxId->value), null)
            ->andReturnUsing(fn () => [
                new DrawnItem($boxId, new ItemId(str_repeat('b', 26)), new DrawnAt(new DateTimeImmutable())),
                new DrawnItem($boxId, new ItemId(str_repeat('c', 26)), new DrawnAt(new DateTimeImmutable())),
                // ありえないが重複している場合
                new DrawnItem($boxId, new ItemId(str_repeat('c', 26)), new DrawnAt(new DateTimeImmutable())),
            ])
            ->once();

        $result = $this->getInstance()->handle(new LotteryInput($boxName, true));

        $this->assertTrue($result->isErr());
        $this->assertSame('抽選箱「抽選箱」からすべてのアイテムを抽選しました。リセットしてください。', $result->unwrapErr());
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
            $this->transaction,
            $this->lotteryBoxRepository,
            $this->lotteryItemRepository,
            $this->boxItemRepository,
            $this->drawnItemRepository,
        );
    }
}
