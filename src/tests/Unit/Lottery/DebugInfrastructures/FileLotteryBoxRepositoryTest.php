<?php

declare(strict_types=1);

namespace Tests\Unit\Lottery\DebugInfrastructures;

use Lottery\DebugInfrastructures\FileLotteryBoxRepository;
use Lottery\Domain\Models\LotteryBox\BoxId;
use Lottery\Domain\Models\LotteryBox\BoxName;
use Lottery\Domain\Models\LotteryBox\LotteryBox;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Support\Contracts\ConfigInterface;
use Support\Debug\Repository\FileStore;
use Tests\TestCase;

class FileLotteryBoxRepositoryTest extends TestCase
{
    private FileStore&MockInterface $store;

    private ConfigInterface&MockInterface $config;

    protected function setUp(): void
    {
        parent::setUp();

        $this->store = Mockery::mock(FileStore::class);
        $this->config = Mockery::mock(ConfigInterface::class);
    }

    #[Test]
    public function foundByBoxName(): void
    {
        $this->store->shouldReceive('getAll')
            ->with('/lottery-box.dat')
            ->andReturn([
                new LotteryBox(new BoxId(str_repeat('a', 26)), new BoxName('抽選箱'), []),
                new LotteryBox(new BoxId(str_repeat('b', 26)), new BoxName('抽選箱2'), []),
            ])
            ->once();

        $this->assertInstanceOf(LotteryBox::class, $this->getInstance()->findByBoxName(new BoxName('抽選箱')));
    }

    #[Test]
    public function notFoundByBoxName(): void
    {
        $this->store->shouldReceive('getAll')
            ->with('/lottery-box.dat')
            ->andReturn([])
            ->once();

        $this->assertNull($this->getInstance()->findByBoxName(new BoxName('抽選箱')));
    }

    #[Test]
    public function saved(): void
    {
        $lotteryBox = new LotteryBox(new BoxId(str_repeat('a', 26)), new BoxName('抽選箱2'), []);

        $this->store->shouldReceive('put')
            ->with(
                '/lottery-box.dat',
                $lotteryBox->boxId->value,
                Mockery::on(
                    fn (LotteryBox $arg) => $arg->boxId->value === $lotteryBox->boxId->value
                        && $arg->boxName->value === $lotteryBox->boxName->value
                        && $arg->lotteryItemIds === $lotteryBox->lotteryItemIds
                )
            )
            ->once();

        $this->getInstance()->save($lotteryBox);
    }

    private function getInstance(): FileLotteryBoxRepository
    {
        $this->config->shouldReceive('getString')
            ->with('debug.file.path')
            ->andReturn('')
            ->once();

        return new FileLotteryBoxRepository($this->store, $this->config);
    }
}
