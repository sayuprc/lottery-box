<?php

declare(strict_types=1);

namespace Tests\Integration\Lottery\Domain\Services;

use Lottery\DebugInfrastructures\FileLotteryBoxRepository;
use Lottery\Domain\Models\LotteryBox\BoxId;
use Lottery\Domain\Models\LotteryBox\BoxName;
use Lottery\Domain\Models\LotteryBox\LotteryBox;
use Lottery\Domain\Services\LotteryBoxNameDuplicateCheckService;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\FileRepositoryTransaction;
use Tests\TestCase;

class LotteryBoxNameDuplicateCheckServiceTest extends TestCase
{
    use FileRepositoryTransaction;

    #[Test]
    public function notDuplicated(): void
    {
        $this->assertFalse($this->getInstance()->exists(new BoxName('抽選箱')));
    }

    #[Test]
    public function duplicated(): void
    {
        $lotteryBox = new LotteryBox(new BoxId(str_repeat('a', 26)), new BoxName('抽選箱'), []);

        $this->factory(FileLotteryBoxRepository::class, $lotteryBox->boxId->value, $lotteryBox);

        $this->assertTrue($this->getInstance()->exists(new BoxName('抽選箱')));
    }

    private function getInstance(): LotteryBoxNameDuplicateCheckService
    {
        return $this->app->make(LotteryBoxNameDuplicateCheckService::class);
    }
}
