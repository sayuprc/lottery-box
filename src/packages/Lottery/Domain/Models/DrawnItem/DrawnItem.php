<?php

declare(strict_types=1);

namespace Lottery\Domain\Models\DrawnItem;

use Lottery\Domain\Models\LotteryBox\BoxId;
use Lottery\Domain\Models\LotteryItem\ItemId;

class DrawnItem
{
    public function __construct(
        public readonly BoxId $boxId,
        public readonly ItemId $itemId,
        public readonly DrawnAt $drawnAt,
    ) {
    }
}
