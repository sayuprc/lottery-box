<?php

declare(strict_types=1);

namespace Tests\Integration\Lottery\Infrastructures;

use Lottery\Infrastructures\LotteryBoxFactory;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LotteryBoxFactoryTest extends TestCase
{
    #[Test]
    public function createSuccessfully(): void
    {
        $lotteryBox = $this->getInstance()->create('抽選箱');

        $this->assertSame('抽選箱', $lotteryBox->boxName->value);
        $this->assertCount(0, $lotteryBox->lotteryItemIds);
    }

    private function getInstance(): LotteryBoxFactory
    {
        return $this->app->make(LotteryBoxFactory::class);
    }
}
