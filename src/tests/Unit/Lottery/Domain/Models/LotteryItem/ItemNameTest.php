<?php

declare(strict_types=1);

namespace Tests\Unit\Lottery\Domain\Models\LotteryItem;

use Lottery\Domain\Models\LotteryItem\ItemName;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Support\Domain\Exceptions\InvalidValueException;
use Tests\TestCase;

class ItemNameTest extends TestCase
{
    #[Test]
    #[DataProvider('validNameProvider')]
    public function instantiatesWithValidName(string $name): void
    {
        $instance = new ItemName($name);

        $this->assertSame(mb_trim($name), $instance->value);
    }

    public static function validNameProvider(): array
    {
        return [
            ['抽選アイテム'],
            ['  抽選アイテム   '],
            ['  抽 選    アイテム   '],
        ];
    }

    #[Test]
    public function throwsExceptionWhenNameIsEmpty(): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('無効な抽選アイテム名です。');

        new ItemName('');
    }
}
