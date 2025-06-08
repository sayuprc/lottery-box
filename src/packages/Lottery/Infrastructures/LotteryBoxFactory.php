<?php

declare(strict_types=1);

namespace Lottery\Infrastructures;

use Lottery\Domain\Models\LotteryBox\BoxId;
use Lottery\Domain\Models\LotteryBox\BoxName;
use Lottery\Domain\Models\LotteryBox\LotteryBox;
use Lottery\Domain\Models\LotteryBox\LotteryBoxFactoryInterface;
use Support\Contracts\UlidGeneratorInterface;

class LotteryBoxFactory implements LotteryBoxFactoryInterface
{
    public function __construct(private readonly UlidGeneratorInterface $generator)
    {
    }

    public function create(string $boxName): LotteryBox
    {
        return new LotteryBox(
            new BoxId($this->generator->generate()),
            new BoxName($boxName),
            []
        );
    }
}
