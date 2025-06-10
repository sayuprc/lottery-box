<?php

declare(strict_types=1);

namespace Tests\Unit\Lottery\Application\Interactors;

use Closure;
use Lottery\Application\Interactors\CreateBoxInteractor;
use Lottery\Application\UseCase\CreateBox\CreateBoxInput;
use Lottery\Application\UseCase\CreateBox\CreateBoxOutput;
use Lottery\Domain\Models\LotteryBox\BoxId;
use Lottery\Domain\Models\LotteryBox\BoxName;
use Lottery\Domain\Models\LotteryBox\LotteryBox;
use Lottery\Domain\Models\LotteryBox\LotteryBoxFactoryInterface;
use Lottery\Domain\Models\LotteryBox\LotteryBoxRepositoryInterface;
use Lottery\Domain\Services\LotteryBoxNameDuplicateCheckService;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Support\Contracts\TransactionInterface;
use Tests\TestCase;

class CreateBoxInteractorTest extends TestCase
{
    private LotteryBoxFactoryInterface&MockInterface $factory;

    private LotteryBoxRepositoryInterface&MockInterface $repository;

    private LotteryBoxNameDuplicateCheckService&MockInterface $service;

    private MockInterface&TransactionInterface $transaction;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = Mockery::mock(LotteryBoxFactoryInterface::class);
        $this->repository = Mockery::mock(LotteryBoxRepositoryInterface::class);
        $this->service = Mockery::mock(LotteryBoxNameDuplicateCheckService::class);
        $this->transaction = Mockery::mock(TransactionInterface::class);
    }

    #[Test]
    public function createsBoxSuccessfully(): void
    {
        $id = 'aaaaaaaaaaaaaaaaaaaaaaaaaa';
        $name = '抽選箱';

        $this->factory->shouldReceive('create')
            ->with($name)
            ->andReturnUsing(fn () => new LotteryBox(
                new BoxId($id),
                new BoxName($name),
                []
            ))
            ->once();

        $this->service->shouldReceive('exists')
            ->with(Mockery::on(fn (BoxName $arg) => $arg->value === $name))
            ->andReturnFalse()
            ->once();

        $this->repository->shouldReceive('save')
            ->with(Mockery::on(fn (LotteryBox $arg) => $arg->boxId->value === $id && $arg->boxName->value === $name))
            ->once();

        $this->transaction->shouldReceive('scope')
            ->with(Mockery::on(fn (Closure $arg) => true))
            ->andReturnUsing(fn (Closure $arg) => $arg())
            ->once();

        $result = $this->getInteractor()->handle(new CreateBoxInput($name));

        $this->assertTrue($result->isOk());
        $this->assertInstanceOf(CreateBoxOutput::class, $result->unwrap());
    }

    #[Test]
    public function duplicateBoxName(): void
    {
        $id = 'aaaaaaaaaaaaaaaaaaaaaaaaaa';
        $name = '抽選箱';

        $this->factory->shouldReceive('create')
            ->with($name)
            ->andReturnUsing(fn () => new LotteryBox(
                new BoxId($id),
                new BoxName($name),
                []
            ))
            ->once();

        $this->service->shouldReceive('exists')
            ->with(Mockery::on(fn (BoxName $arg) => $arg->value === $name))
            ->andReturnTrue()
            ->once();

        $this->transaction->shouldReceive('scope')
            ->with(Mockery::on(fn (Closure $arg) => true))
            ->andReturnUsing(fn (Closure $arg) => $arg())
            ->once();

        $result = $this->getInteractor()->handle(new CreateBoxInput($name));

        $this->assertTrue($result->isErr());
        $this->assertSame('すでに同名の抽選箱が存在します。: 抽選箱', $result->unwrapErr());
    }

    private function getInteractor(): CreateBoxInteractor
    {
        return new CreateBoxInteractor(
            $this->factory,
            $this->repository,
            $this->service,
            $this->transaction,
        );
    }
}
