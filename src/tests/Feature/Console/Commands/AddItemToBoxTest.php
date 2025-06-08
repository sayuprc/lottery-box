<?php

declare(strict_types=1);

namespace Tests\Feature\Console\Commands;

use PHPUnit\Framework\Attributes\Test;
use Tests\Support\FileRepositoryTransaction;
use Tests\TestCase;

class AddItemToBoxTest extends TestCase
{
    use FileRepositoryTransaction;

    #[Test]
    public function addItemToBox(): void
    {
        file_put_contents(
            $filePath = __DIR__ . '/../../../../storage/app/tests/addItemToBox.json',
            json_encode([
                'boxName' => '抽選箱',
                'itemNames' => [
                    'アイテム1',
                    'アイテム2',
                    'アイテム3',
                    'アイテム4',
                ],
            ])
        );

        $console = $this->artisan("add:item-to-box {$filePath}");

        $console->expectsOutput('抽選箱とアイテムを紐づけました。')
            ->assertSuccessful();
    }
}
