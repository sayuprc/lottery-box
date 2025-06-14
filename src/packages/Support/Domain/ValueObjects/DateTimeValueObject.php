<?php

declare(strict_types=1);

namespace Support\Domain\ValueObjects;

use DateTimeImmutable;

abstract class DateTimeValueObject
{
    public function __construct(public readonly DateTimeImmutable $value)
    {
    }
}
