<?php

declare(strict_types=1);

namespace Lottery\Domain\Models\BoxItem;

use Lottery\Domain\Models\LotteryBox\BoxId;
use Lottery\Domain\Models\LotteryItem\ItemId;

class BoxItem
{
    public function __construct(
        public readonly BoxId $boxId,
        public readonly ItemId $itemId,
    ) {
    }
}
