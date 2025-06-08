<?php

declare(strict_types=1);

namespace Tests\Integration\Lottery\DebugInfrastructures;

use Lottery\DebugInfrastructures\FileLotteryBoxRepository;
use Lottery\Domain\Models\LotteryBox\BoxId;
use Lottery\Domain\Models\LotteryBox\BoxName;
use Lottery\Domain\Models\LotteryBox\LotteryBox;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\FileRepositoryTransaction;
use Tests\TestCase;

class FileLotteryBoxRepositoryTest extends TestCase
{
    use FileRepositoryTransaction;

    #[Test]
    public function foundByBoxName(): void
    {
        $lotteryBox = new LotteryBox(new BoxId(str_repeat('a', 26)), new BoxName('抽選箱'), []);

        $this->factory(FileLotteryBoxRepository::class, $lotteryBox->boxId->value, $lotteryBox);

        $this->assertInstanceOf(LotteryBox::class, $this->getInstance()->findByBoxName(new BoxName('抽選箱')));
    }

    #[Test]
    public function notFoundByBoxName(): void
    {
        $this->assertNull($this->getInstance()->findByBoxName(new BoxName('抽選箱')));
    }

    #[Test]
    public function saved(): void
    {
        $lotteryBox = new LotteryBox(new BoxId(str_repeat('a', 26)), new BoxName('抽選箱2'), []);

        $this->getInstance()->save($lotteryBox);

        /** @var array<string, LotteryBox> $boxes */
        $boxes = $this->getAll(FileLotteryBoxRepository::class);

        $this->assertCount(1, $boxes);
        $this->assertArrayHasKey($lotteryBox->boxId->value, $boxes);
        $this->assertSame($lotteryBox->boxId->value, $boxes[$lotteryBox->boxId->value]->boxId->value);
    }

    private function getInstance(): FileLotteryBoxRepository
    {
        return $this->app->make(FileLotteryBoxRepository::class);
    }
}
