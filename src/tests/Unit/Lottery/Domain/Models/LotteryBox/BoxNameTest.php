<?php

declare(strict_types=1);

namespace Tests\Unit\Lottery\Domain\Models\LotteryBox;

use Lottery\Domain\Models\LotteryBox\BoxName;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Support\Domain\Exceptions\InvalidValueException;
use Tests\TestCase;

class BoxNameTest extends TestCase
{
    #[Test]
    #[DataProvider('validNameProvider')]
    public function instantiatesWithValidName(string $name): void
    {
        $instance = new BoxName($name);

        $this->assertSame(mb_trim($name), $instance->value);
    }

    public static function validNameProvider(): array
    {
        return [
            ['抽選箱'],
            ['  抽選箱   '],
            ['  抽 選    箱   '],
        ];
    }

    #[Test]
    public function throwsExceptionWhenNameIsEmpty(): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('無効な抽選箱名です。');

        new BoxName('');
    }
}
