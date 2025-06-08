<?php

declare(strict_types=1);

namespace Tests\Integration\Lottery\Application\Interactors;

use Lottery\Application\Interactors\CreateBoxInteractor;
use Lottery\Application\UseCase\CreateBox\CreateBoxInput;
use Lottery\Application\UseCase\CreateBox\CreateBoxOutput;
use Lottery\DebugInfrastructures\FileLotteryBoxRepository;
use Lottery\Domain\Models\LotteryBox\BoxId;
use Lottery\Domain\Models\LotteryBox\BoxName;
use Lottery\Domain\Models\LotteryBox\LotteryBox;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\FileRepositoryTransaction;
use Tests\TestCase;

class CreateBoxInteractorTest extends TestCase
{
    use FileRepositoryTransaction;

    #[Test]
    public function createsBoxSuccessfully(): void
    {
        $name = '抽選箱';

        $result = $this->getInteractor()->handle(new CreateBoxInput($name));

        $this->assertTrue($result->isOk());
        $this->assertInstanceOf(CreateBoxOutput::class, $result->unwrap());
    }

    #[Test]
    public function duplicateBoxName(): void
    {
        $name = '抽選箱';

        $lotteryBox = new LotteryBox(new BoxId(str_repeat('a', 26)), new BoxName($name), []);
        $this->factory(FileLotteryBoxRepository::class, $lotteryBox->boxId->value, $lotteryBox);

        $result = $this->getInteractor()->handle(new CreateBoxInput($name));

        $this->assertTrue($result->isErr());
        $this->assertSame('すでに同名の抽選箱が存在します。: 抽選箱', $result->unwrapErr());
    }

    private function getInteractor(): CreateBoxInteractor
    {
        return $this->app->make(CreateBoxInteractor::class);
    }
}
