<?php

declare(strict_types=1);

namespace Lottery\Application\UseCase\AddItemToBox;

class AddItemToBoxInput
{
    /**
     * @param array<string> $itemNames
     */
    public function __construct(
        public readonly string $boxName,
        public readonly array $itemNames,
    ) {
    }
}
