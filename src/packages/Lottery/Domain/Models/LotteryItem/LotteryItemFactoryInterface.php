<?php

declare(strict_types=1);

namespace Lottery\Domain\Models\LotteryItem;

interface LotteryItemFactoryInterface
{
    public function create(string $itemName): LotteryItem;
}
