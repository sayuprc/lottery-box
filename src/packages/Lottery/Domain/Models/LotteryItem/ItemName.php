<?php

declare(strict_types=1);

namespace Lottery\Domain\Models\LotteryItem;

use Support\Domain\Exceptions\InvalidValueException;
use Support\Domain\ValueObjects\StringValueObject;

class ItemName extends StringValueObject
{
    public function __construct(string $value)
    {
        $value = mb_trim($value);

        if ($value === '') {
            throw new InvalidValueException('無効な抽選アイテム名です。');
        }

        parent::__construct($value);
    }
}
