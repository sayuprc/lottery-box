<?php

declare(strict_types=1);

namespace Tests\Unit\Lottery\Infrastructures;

use Lottery\Infrastructures\LotteryBoxFactory;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Support\Contracts\UlidGeneratorInterface;
use Tests\TestCase;

class LotteryBoxFactoryTest extends TestCase
{
    private MockInterface&UlidGeneratorInterface $generator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->generator = Mockery::mock(UlidGeneratorInterface::class);
    }

    #[Test]
    public function createSuccessfully(): void
    {
        $this->generator->shouldReceive('generate')
            ->andReturn('aaaaaaaaaabbbbbbbbbb123456')
            ->once();

        $lotteryBox = $this->getInstance()->create('抽選箱');

        $this->assertSame('aaaaaaaaaabbbbbbbbbb123456', $lotteryBox->boxId->value);
        $this->assertSame('抽選箱', $lotteryBox->boxName->value);
    }

    private function getInstance(): LotteryBoxFactory
    {
        return new LotteryBoxFactory($this->generator);
    }
}
