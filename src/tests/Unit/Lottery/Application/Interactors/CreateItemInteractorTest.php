<?php

declare(strict_types=1);

namespace Tests\Unit\Lottery\Application\Interactors;

use Lottery\Application\Interactors\CreateItemInteractor;
use Lottery\Application\UseCase\CreateItem\CreateItemInput;
use Lottery\Application\UseCase\CreateItem\CreateItemOutput;
use Lottery\Domain\Models\LotteryItem\ItemId;
use Lottery\Domain\Models\LotteryItem\ItemName;
use Lottery\Domain\Models\LotteryItem\LotteryItem;
use Lottery\Domain\Models\LotteryItem\LotteryItemFactoryInterface;
use Lottery\Domain\Models\LotteryItem\LotteryItemRepositoryInterface;
use Lottery\Domain\Services\LotteryItemNameDuplicateCheckService;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreateItemInteractorTest extends TestCase
{
    private LotteryItemFactoryInterface&MockInterface $factory;

    private LotteryItemRepositoryInterface&MockInterface $repository;

    private LotteryItemNameDuplicateCheckService&MockInterface $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = Mockery::mock(LotteryItemFactoryInterface::class);
        $this->repository = Mockery::mock(LotteryItemRepositoryInterface::class);
        $this->service = Mockery::mock(LotteryItemNameDuplicateCheckService::class);
    }

    #[Test]
    public function createsItemSuccessfully(): void
    {
        $id = 'aaaaaaaaaaaaaaaaaaaaaaaaaa';
        $name = '抽選アイテム';

        $this->factory->shouldReceive('create')
            ->with($name)
            ->andReturnUsing(fn () => new LotteryItem(new ItemId($id), new ItemName($name)))
            ->once();

        $this->service->shouldReceive('exists')
            ->with(Mockery::on(fn (ItemName $arg) => $arg->value === $name))
            ->andReturnFalse()
            ->once();

        $this->repository->shouldReceive('save')
            ->with(Mockery::on(fn (LotteryItem $arg) => $arg->itemId->value === $id && $arg->itemName->value === $name))
            ->once();

        $result = $this->getInstance()->handle(new CreateItemInput($name));

        $this->assertTrue($result->isOk());
        $this->assertInstanceOf(CreateItemOutput::class, $result->unwrap());
    }

    #[Test]
    public function duplicateItemName(): void
    {
        $id = 'aaaaaaaaaaaaaaaaaaaaaaaaaa';
        $name = '抽選アイテム';

        $this->factory->shouldReceive('create')
            ->with($name)
            ->andReturnUsing(fn () => new LotteryItem(new ItemId($id), new ItemName($name)))
            ->once();

        $this->service->shouldReceive('exists')
            ->with(Mockery::on(fn (ItemName $arg) => $arg->value === $name))
            ->andReturnTrue()
            ->once();

        $result = $this->getInstance()->handle(new CreateItemInput($name));

        $this->assertTrue($result->isErr());
        $this->assertSame('すでに同名の抽選アイテムが存在します。: 抽選アイテム', $result->unwrapErr());
    }

    private function getInstance(): CreateItemInteractor
    {
        return new CreateItemInteractor(
            $this->factory,
            $this->repository,
            $this->service
        );
    }
}
