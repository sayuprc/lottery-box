<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Lottery\Application\UseCase\AddItemToBox\AddItemToBoxInput;
use Lottery\Application\UseCase\AddItemToBox\AddItemToBoxInputPort;
use Support\Contracts\MapperInterface;

class AddItemToBox extends Command
{
    protected $signature = 'add:item-to-box {file}';

    protected $description = '抽選箱とアイテムを関連関連付ける';

    public function handle(AddItemToBoxInputPort $handler, MapperInterface $mapper): int
    {
        $filePath = $this->argument('file');

        if (! is_string($filePath) || ! file_exists($filePath)) {
            $this->error('読み取り可能なファイルを選択してください。');

            return Command::FAILURE;
        }

        $handler->handle($mapper->map(AddItemToBoxInput::class, file_get_contents($filePath)));

        $this->info('抽選箱とアイテムを紐づけました。');

        return Command::SUCCESS;
    }
}
