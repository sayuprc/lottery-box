<?php

declare(strict_types=1);

namespace Lottery\Domain\Models\BoxItem;

use Lottery\Domain\Models\LotteryBox\BoxId;

interface BoxItemRepositoryInterface
{
    public function save(BoxItem $boxItem): void;

    /**
     * @return array<BoxItem>
     */
    public function getByBoxId(BoxId $boxId): array;
}
