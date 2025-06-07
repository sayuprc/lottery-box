<?php

declare(strict_types=1);

namespace Lottery\Domain\Models\LotteryBox;

use Support\Domain\Exceptions\InvalidValueException;
use Support\Domain\ValueObjects\StringValueObject;

class BoxName extends StringValueObject
{
    public function __construct(string $value)
    {
        $value = mb_trim($value);

        if ($value === '') {
            throw new InvalidValueException('無効な抽選箱名です。');
        }

        parent::__construct($value);
    }
}
