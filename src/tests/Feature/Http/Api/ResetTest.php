<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Api;

use Lottery\DebugInfrastructures\FileLotteryBoxRepository;
use Lottery\Domain\Models\LotteryBox\BoxId;
use Lottery\Domain\Models\LotteryBox\BoxName;
use Lottery\Domain\Models\LotteryBox\LotteryBox;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\FileRepositoryTransaction;
use Tests\TestCase;

class ResetTest extends TestCase
{
    use FileRepositoryTransaction;

    #[Test]
    public function reset(): void
    {
        $boxName = '抽選箱';

        $lotteryBox = new LotteryBox(new BoxId(str_repeat('a', 26)), new BoxName($boxName));

        $this->factory(FileLotteryBoxRepository::class, $lotteryBox->boxId->value, $lotteryBox);

        $this->post('/api/reset', ['boxName' => $boxName])
            ->assertStatus(200)
            ->assertJson([
                'reset' => 'success',
            ]);
    }
}
