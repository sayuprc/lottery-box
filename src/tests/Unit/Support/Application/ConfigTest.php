<?php

declare(strict_types=1);

namespace Tests\Unit\Support\Application;

use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Support\Application\Config;
use Tests\TestCase;

class ConfigTest extends TestCase
{
    #[Test]
    public function getStringValueSuccessfully(): void
    {
        config(['key' => 'value']);

        $config = $this->getInstance();

        $this->assertSame('value', $config->getString('key'));
    }

    #[Test]
    public function throwsExceptionWhenNotStringValue(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('key は設定されていないか、値が string ではありません。');

        config(['key' => null]);

        $config = $this->getInstance();

        $config->getString('key');
    }

    private function getInstance(): Config
    {
        return new Config();
    }
}
