<?php

declare(strict_types=1);

namespace Lottery\Domain\Models\DrawnItem;

use Lottery\Domain\Models\LotteryBox\BoxId;
use Lottery\Domain\Models\LotteryBox\ResetAt;

interface DrawnItemRepositoryInterface
{
    /**
     * @return array<DrawnItem>
     */
    public function getByBoxId(BoxId $boxId, ?ResetAt $resetAt = null): array;

    public function save(DrawnItem $drawnItem): void;
}
