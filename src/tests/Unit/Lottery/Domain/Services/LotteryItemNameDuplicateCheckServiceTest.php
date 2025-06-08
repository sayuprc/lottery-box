<?php

declare(strict_types=1);

namespace Tests\Unit\Lottery\Domain\Services;

use Lottery\Domain\Models\LotteryItem\ItemId;
use Lottery\Domain\Models\LotteryItem\ItemName;
use Lottery\Domain\Models\LotteryItem\LotteryItem;
use Lottery\Domain\Models\LotteryItem\LotteryItemRepositoryInterface;
use Lottery\Domain\Services\LotteryItemNameDuplicateCheckService;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LotteryItemNameDuplicateCheckServiceTest extends TestCase
{
    private LotteryItemRepositoryInterface&MockInterface $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(LotteryItemRepositoryInterface::class);
    }

    #[Test]
    public function notDuplicated(): void
    {
        $this->repository->shouldReceive('findByItemName')
            ->with(Mockery::on(fn (ItemName $arg) => $arg->value === '抽選アイテム'))
            ->andReturnNull()
            ->once();

        $this->assertFalse($this->getInstance()->exists(new ItemName('抽選アイテム')));
    }

    #[Test]
    public function duplicated(): void
    {
        $this->repository->shouldReceive('findByItemName')
            ->with(Mockery::on(fn (ItemName $arg) => $arg->value === '抽選アイテム'))
            ->andReturnUsing(fn () => new LotteryItem(new ItemId(str_repeat('a', 26)), new ItemName('抽選アイテム')))
            ->once();

        $this->assertTrue($this->getInstance()->exists(new ItemName('抽選アイテム')));
    }

    private function getInstance(): LotteryItemNameDuplicateCheckService
    {
        return new LotteryItemNameDuplicateCheckService($this->repository);
    }
}
