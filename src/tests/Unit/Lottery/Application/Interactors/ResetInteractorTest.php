<?php

declare(strict_types=1);

namespace Tests\Unit\Lottery\Application\Interactors;

use Closure;
use Lottery\Application\Interactors\ResetInteractor;
use Lottery\Application\UseCase\Reset\ResetInput;
use Lottery\Application\UseCase\Reset\ResetOutput;
use Lottery\Domain\Models\LotteryBox\BoxId;
use Lottery\Domain\Models\LotteryBox\BoxName;
use Lottery\Domain\Models\LotteryBox\LotteryBox;
use Lottery\Domain\Models\LotteryBox\LotteryBoxRepositoryInterface;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Support\Contracts\TransactionInterface;
use Tests\TestCase;

class ResetInteractorTest extends TestCase
{
    private MockInterface&TransactionInterface $transaction;

    private LotteryBoxRepositoryInterface&MockInterface $lotteryBoxRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->transaction = Mockery::mock(TransactionInterface::class);
        $this->lotteryBoxRepository = Mockery::mock(LotteryBoxRepositoryInterface::class);
    }

    #[Test]
    public function resetSuccessfully(): void
    {
        $boxId = str_repeat('a', 26);
        $boxName = '抽選箱';

        $this->lotteryBoxRepository->shouldReceive('findByBoxName')
            ->with(Mockery::on(fn (BoxName $arg) => $arg->value === $boxName))
            ->andReturnUsing(fn () => new LotteryBox(new BoxId($boxId), new BoxName($boxName)))
            ->once();

        $this->transaction->shouldReceive('scope')
            ->with(Mockery::on(fn (Closure $_) => true))
            ->andReturnUsing(fn (Closure $arg) => $arg())
            ->once();

        $this->lotteryBoxRepository->shouldReceive('save')
            ->with(
                Mockery::on(
                    fn (LotteryBox $arg) => $arg->boxId->value === $boxId
                        && $arg->boxName->value === $boxName
                        && ! is_null($arg->resetAt)
                )
            )
            ->once();

        $result = $this->getInstance()->handle(new ResetInput($boxName));

        $this->assertTrue($result->isOk());
        $this->assertInstanceOf(ResetOutput::class, $result->unwrap());
    }

    #[Test]
    public function boxNotFound(): void
    {
        $boxName = '抽選箱';

        $this->lotteryBoxRepository->shouldReceive('findByBoxName')
            ->with(Mockery::on(fn (BoxName $arg) => $arg->value === $boxName))
            ->andReturnNull()
            ->once();

        $result = $this->getInstance()->handle(new ResetInput($boxName));

        $this->assertTrue($result->isErr());
        $this->assertSame('抽選箱「抽選箱」は存在しません。', $result->unwrapErr());
    }

    private function getInstance(): ResetInteractor
    {
        return new ResetInteractor(
            $this->transaction,
            $this->lotteryBoxRepository,
        );
    }
}
