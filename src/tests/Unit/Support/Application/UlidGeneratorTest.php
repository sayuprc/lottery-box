<?php

declare(strict_types=1);

namespace Tests\Unit\Support\Application;

use PHPUnit\Framework\Attributes\Test;
use Support\Application\UlidGenerator;
use Tests\TestCase;

class UlidGeneratorTest extends TestCase
{
    #[Test]
    public function generateSuccessfully(): void
    {
        $this->assertTrue((bool)preg_match('/\A[0-9a-hjkmnp-zA-HJKMNP-Z]{26}\z/', new UlidGenerator()->generate()));
    }
}
