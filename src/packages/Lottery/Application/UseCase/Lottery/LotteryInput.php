<?php

declare(strict_types=1);

namespace Lottery\Application\UseCase\Lottery;

class LotteryInput
{
    public function __construct(
        public readonly string $boxName,
        public readonly bool $isUnique = false
    ) {
    }
}
