<?php

declare(strict_types=1);

namespace Support\Application;

use Illuminate\Support\Str;
use Support\Contracts\UlidGeneratorInterface;

class UlidGenerator implements UlidGeneratorInterface
{
    public function generate(): string
    {
        return Str::ulid()->toString();
    }
}
