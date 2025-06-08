<?php

declare(strict_types=1);

namespace Tests\Unit\Lottery\Domain\Services;

use Lottery\Domain\Models\LotteryBox\BoxId;
use Lottery\Domain\Models\LotteryBox\BoxName;
use Lottery\Domain\Models\LotteryBox\LotteryBox;
use Lottery\Domain\Models\LotteryBox\LotteryBoxRepositoryInterface;
use Lottery\Domain\Services\LotteryBoxNameDuplicateCheckService;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LotteryBoxNameDuplicateCheckServiceTest extends TestCase
{
    private LotteryBoxRepositoryInterface&MockInterface $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(LotteryBoxRepositoryInterface::class);
    }

    #[Test]
    public function notDuplicated(): void
    {
        $this->repository->shouldReceive('findByBoxName')
            ->with(Mockery::on(fn (BoxName $arg) => $arg->value === '抽選箱'))
            ->andReturnNull()
            ->once();

        $this->assertFalse($this->getInstance()->exists(new BoxName('抽選箱')));
    }

    #[Test]
    public function duplicated(): void
    {
        $this->repository->shouldReceive('findByBoxName')
            ->with(Mockery::on(fn (BoxName $arg) => $arg->value === '抽選箱'))
            ->andReturnUsing(fn () => new LotteryBox(new BoxId(str_repeat('a', 26)), new BoxName('抽選箱'), []))
            ->once();

        $this->assertTrue($this->getInstance()->exists(new BoxName('抽選箱')));
    }

    private function getInstance(): LotteryBoxNameDuplicateCheckService
    {
        return new LotteryBoxNameDuplicateCheckService($this->repository);
    }
}
