<?php

declare(strict_types=1);

namespace Lottery\Infrastructures;

use Lottery\Domain\Models\LotteryItem\ItemId;
use Lottery\Domain\Models\LotteryItem\ItemName;
use Lottery\Domain\Models\LotteryItem\LotteryItem;
use Lottery\Domain\Models\LotteryItem\LotteryItemFactoryInterface;
use Support\Contracts\UlidGeneratorInterface;

class LotteryItemFactory implements LotteryItemFactoryInterface
{
    public function __construct(private readonly UlidGeneratorInterface $generator)
    {
    }

    public function create(string $itemName): LotteryItem
    {
        return new LotteryItem(
            new ItemId($this->generator->generate()),
            new ItemName($itemName)
        );
    }
}
