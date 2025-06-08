<?php

declare(strict_types=1);

namespace Tests\Feature\Console\Commands;

use Lottery\DebugInfrastructures\FileLotteryBoxRepository;
use Lottery\Domain\Models\LotteryBox\BoxId;
use Lottery\Domain\Models\LotteryBox\BoxName;
use Lottery\Domain\Models\LotteryBox\LotteryBox;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\FileRepositoryTransaction;
use Tests\TestCase;

class CreateLotteryBoxTest extends TestCase
{
    use FileRepositoryTransaction;

    #[Test]
    public function createLotteryBox(): void
    {
        $console = $this->artisan('create:lottery-box a');

        $console->expectsOutput('抽選箱「a」を作成しました。')
            ->assertSuccessful();
    }

    #[Test]
    public function failedCreateLotteryBox(): void
    {
        $lotteryBox = new LotteryBox(new BoxId(str_repeat('a', 26)), new BoxName('a'), []);

        $this->factory(FileLotteryBoxRepository::class, $lotteryBox->boxId->value, $lotteryBox);

        $console = $this->artisan('create:lottery-box a');

        $console->expectsOutput('すでに同名の抽選箱が存在します。: a')
            ->assertFailed();
    }
}
