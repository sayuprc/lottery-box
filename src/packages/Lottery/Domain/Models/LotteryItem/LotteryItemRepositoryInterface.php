<?php

declare(strict_types=1);

namespace Lottery\Domain\Models\LotteryItem;

interface LotteryItemRepositoryInterface
{
    public function find(ItemId $itemId): ?LotteryItem;

    public function findByItemName(ItemName $itemName): ?LotteryItem;

    public function save(LotteryItem $lotteryItem): void;
}
