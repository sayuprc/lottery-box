<?php

declare(strict_types=1);

namespace Tests\Unit\Lottery\Domain\Models\LotteryBox;

use Lottery\Domain\Models\LotteryBox\BoxId;
use Lottery\Domain\Models\LotteryBox\BoxName;
use Lottery\Domain\Models\LotteryBox\LotteryBox;
use Lottery\Domain\Models\LotteryBox\ResetAt;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LotteryBoxTest extends TestCase
{
    #[Test]
    public function reset(): void
    {
        $lotteryBox = new LotteryBox(new BoxId(str_repeat('a', 26)), new BoxName('抽選箱'));

        $this->assertNull($lotteryBox->resetAt);

        $resetLotteryBox = $lotteryBox->reset();

        $this->assertNotSame($resetLotteryBox, $lotteryBox);
        $this->assertInstanceOf(ResetAt::class, $resetLotteryBox->resetAt);
    }
}
