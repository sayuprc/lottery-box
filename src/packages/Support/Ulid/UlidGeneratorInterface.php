<?php

declare(strict_types=1);

namespace Support\Ulid;

interface UlidGeneratorInterface
{
    public function generate(): string;
}
