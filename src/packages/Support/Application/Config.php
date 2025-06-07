<?php

declare(strict_types=1);

namespace Support\Application;

use RuntimeException;
use Support\Contracts\ConfigInterface;

class Config implements ConfigInterface
{
    public function getString(string $key): string
    {
        $value = $this->get($key);

        if (! is_string($value)) {
            throw new RuntimeException("{$key} は設定されていないか、値が string ではありません。");
        }

        return $value;
    }

    private function get(string $key): mixed
    {
        return config()->get($key);
    }
}
