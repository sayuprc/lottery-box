<?php

declare(strict_types=1);

namespace Lottery\Domain\Models\LotteryBox;

interface LotteryBoxFactoryInterface
{
    public function create(string $boxName): LotteryBox;
}
