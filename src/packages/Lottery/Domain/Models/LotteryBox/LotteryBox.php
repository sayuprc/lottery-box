<?php

declare(strict_types=1);

namespace Lottery\Domain\Models\LotteryBox;

use DateTimeImmutable;

class LotteryBox
{
    public function __construct(
        public readonly BoxId $boxId,
        public readonly BoxName $boxName,
        public readonly ?ResetAt $resetAt = null,
    ) {
    }

    public function reset(): self
    {
        return new self($this->boxId, $this->boxName, new ResetAt(new DateTimeImmutable()));
    }
}
