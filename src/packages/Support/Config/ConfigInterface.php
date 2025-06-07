<?php

declare(strict_types=1);

namespace Suuport\Config;

interface ConfigInterface
{
    public function getString(string $key): string;
}
