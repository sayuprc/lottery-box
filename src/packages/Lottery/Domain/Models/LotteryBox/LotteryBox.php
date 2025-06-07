<?php

declare(strict_types=1);

namespace Lottery\Domain\Models\LotteryBox;

use Lottery\Domain\Models\LotteryItem\ItemId;

class LotteryBox
{
    /**
     * @param array<ItemId> $lotteryItemIds
     */
    public function __construct(
        public readonly BoxId $boxId,
        public readonly BoxName $boxName,
        public readonly array $lotteryItemIds,
    ) {
    }
}
