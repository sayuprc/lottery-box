<?php

declare(strict_types=1);

namespace Lottery\Domain\Models\LotteryItem;

interface LotteryItemRepositoryInterface
{
    public function findByItemName(ItemName $itemName): ?LotteryItem;
}
