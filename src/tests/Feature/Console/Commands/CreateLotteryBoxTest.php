<?php

declare(strict_types=1);

namespace Tests\Feature\Console\Commands;

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
}
