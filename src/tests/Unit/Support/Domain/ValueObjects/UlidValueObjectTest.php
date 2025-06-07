<?php

declare(strict_types=1);

namespace Tests\Unit\Support\Domain\ValueObjects;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Support\Domain\Exceptions\InvalidFormatException;
use Support\Domain\ValueObjects\UlidValueObject;
use Tests\TestCase;

class UlidValueObjectTest extends TestCase
{
    #[Test]
    #[DataProvider('validUlidProvider')]
    public function instantiatesWithValidUlid(string $ulid): void
    {
        $instance = new Ulid($ulid);

        $this->assertSame($ulid, $instance->value);
    }

    public static function validUlidProvider(): array
    {
        return [
            ['01JX5JWCNA5G5ND0VG8NDNFE3Q'],
            ['01jx5jwcna5g5nd0vg8ndnfe3q'],
        ];
    }

    #[Test]
    #[DataProvider('throwsExceptionWhenInvalidFormatProvider')]
    public function throwsExceptionWhenInvalidFormat(string $ulid): void
    {
        $this->expectException(InvalidFormatException::class);
        $this->expectExceptionMessage("ULID のフォーマットが不正です。: {$ulid}");

        new Ulid($ulid);
    }

    public static function throwsExceptionWhenInvalidFormatProvider(): array
    {
        return [
            [''],
            ['01JX5JWCNA5G5ND0VG8NDLFE3Q'],
            ['01JX5JWCNA5G5ND0VG8NDIFE3Q'],
            ['01JX5JWCNO5G5ND0VG8NDNFE3Q'],
        ];
    }
}

class Ulid extends UlidValueObject
{
}
