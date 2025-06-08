<?php

declare(strict_types=1);

namespace Lottery\Application\UseCase\CreateBox;

class CreateBoxInput
{
    public function __construct(public readonly string $boxName)
    {
    }
}
