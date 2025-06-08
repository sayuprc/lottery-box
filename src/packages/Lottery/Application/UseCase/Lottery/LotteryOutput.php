<?php

declare(strict_types=1);

namespace Lottery\Application\UseCase\Lottery;

use Lottery\Domain\Models\LotteryItem\ItemName;

class LotteryOutput
{
    public function __construct(public readonly ItemName $itemName)
    {
    }
}
