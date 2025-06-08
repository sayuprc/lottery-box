<?php

declare(strict_types=1);

namespace Tests\Unit\Lottery\Infrastructures;

use Lottery\Infrastructures\LotteryItemFactory;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Support\Contracts\UlidGeneratorInterface;
use Tests\TestCase;

class LotteryItemFactoryTest extends TestCase
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

        $lotteryItem = $this->getInstance()->create('抽選アイテム');

        $this->assertSame('aaaaaaaaaabbbbbbbbbb123456', $lotteryItem->itemId->value);
        $this->assertSame('抽選アイテム', $lotteryItem->itemName->value);
    }

    private function getInstance(): LotteryItemFactory
    {
        return new LotteryItemFactory($this->generator);
    }
}
