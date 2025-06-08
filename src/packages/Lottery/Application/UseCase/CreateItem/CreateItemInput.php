<?php

declare(strict_types=1);

namespace Lottery\Application\UseCase\CreateItem;

class CreateItemInput
{
    public function __construct(public readonly string $itemName)
    {
    }
}
