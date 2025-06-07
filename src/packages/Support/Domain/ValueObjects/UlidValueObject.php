<?php

declare(strict_types=1);

namespace Support\Domain\ValueObjects;

use Support\Domain\Exceptions\InvalidFormatException;

abstract class UlidValueObject extends StringValueObject
{
    public function __construct(string $value)
    {
        if (! preg_match('/\A[0-9a-hjkmnp-zA-HJKMNP-Z]{26}\z/', $value)) {
            throw new InvalidFormatException("ULID のフォーマットが不正です。: {$value}");
        }

        parent::__construct($value);
    }
}
