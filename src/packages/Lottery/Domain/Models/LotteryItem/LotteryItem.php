<?php

declare(strict_types=1);

namespace Lottery\Domain\Models\LotteryItem;

class LotteryItem
{
    public function __construct(
        public readonly ItemId $itemId,
        public readonly ItemName $itemName,
    ) {
    }
}
