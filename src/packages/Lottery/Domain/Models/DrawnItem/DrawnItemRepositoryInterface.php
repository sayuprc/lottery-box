<?php

declare(strict_types=1);

namespace Lottery\Domain\Models\DrawnItem;

use Lottery\Domain\Models\LotteryBox\BoxId;

interface DrawnItemRepositoryInterface
{
    /**
     * @return array<DrawnItem>
     */
    public function getByBoxId(BoxId $boxId): array;

    public function save(DrawnItem $drawnItem): void;
}
