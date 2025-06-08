<?php

declare(strict_types=1);

namespace Tests\Integration\Lottery\Infrastructures;

use Lottery\Infrastructures\LotteryItemFactory;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LotteryItemFactoryTest extends TestCase
{
    #[Test]
    public function createSuccessfully(): void
    {
        $lotteryItem = $this->getInstance()->create('抽選アイテム');

        $this->assertSame('抽選アイテム', $lotteryItem->itemName->value);
    }

    private function getInstance(): LotteryItemFactory
    {
        return $this->app->make(LotteryItemFactory::class);
    }
}
