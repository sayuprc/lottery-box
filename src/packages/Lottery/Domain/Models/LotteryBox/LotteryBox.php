<?php

declare(strict_types=1);

namespace Lottery\Domain\Models\LotteryBox;

class LotteryBox
{
    public function __construct(
        public readonly BoxId $boxId,
        public readonly BoxName $boxName,
    ) {
    }
}
