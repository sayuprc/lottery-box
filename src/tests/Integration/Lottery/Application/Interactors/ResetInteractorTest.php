<?php

declare(strict_types=1);

namespace Tests\Integration\Lottery\Application\Interactors;

use Lottery\Application\Interactors\ResetInteractor;
use Lottery\Application\UseCase\Reset\ResetInput;
use Lottery\Application\UseCase\Reset\ResetOutput;
use Lottery\DebugInfrastructures\FileLotteryBoxRepository;
use Lottery\Domain\Models\LotteryBox\BoxId;
use Lottery\Domain\Models\LotteryBox\BoxName;
use Lottery\Domain\Models\LotteryBox\LotteryBox;
use Lottery\Domain\Models\LotteryBox\ResetAt;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\FileRepositoryTransaction;
use Tests\TestCase;

class ResetInteractorTest extends TestCase
{
    use FileRepositoryTransaction;

    #[Test]
    public function resetSuccessfully(): void
    {
        $boxId = str_repeat('a', 26);
        $boxName = '抽選箱';

        $lotteryBox = new LotteryBox(new BoxId(str_repeat('a', 26)), new BoxName($boxName));
        $this->factory(FileLotteryBoxRepository::class, $lotteryBox->boxId->value, $lotteryBox);

        $result = $this->getInstance()->handle(new ResetInput($boxName));

        $this->assertTrue($result->isOk());
        $this->assertInstanceOf(ResetOutput::class, $result->unwrap());

        /** @var array<LotteryBox> $boxes */
        $boxes = $this->getAll(FileLotteryBoxRepository::class);
        $this->assertCount(1, $boxes);
        $key = array_key_first($boxes);
        $this->assertSame($boxId, $boxes[$key]->boxId->value);
        $this->assertSame($boxName, $boxes[$key]->boxName->value);
        $this->assertInstanceOf(ResetAt::class, $boxes[$key]->resetAt);
    }

    #[Test]
    public function boxNotFound(): void
    {
        $boxName = '抽選箱';

        $result = $this->getInstance()->handle(new ResetInput($boxName));

        $this->assertTrue($result->isErr());
        $this->assertSame('抽選箱「抽選箱」は存在しません。', $result->unwrapErr());
    }

    private function getInstance(): ResetInteractor
    {
        return $this->app->make(ResetInteractor::class);
    }
}
