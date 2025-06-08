<?php

declare(strict_types=1);

namespace Lottery\Domain\Services;

use Lottery\Domain\Models\LotteryItem\ItemName;
use Lottery\Domain\Models\LotteryItem\LotteryItemRepositoryInterface;

class LotteryItemNameDuplicateCheckService
{
    public function __construct(private readonly LotteryItemRepositoryInterface $repository)
    {
    }

    public function exists(ItemName $itemName): bool
    {
        return ! is_null($this->repository->findByItemName($itemName));
    }
}
