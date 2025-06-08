<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Lottery\Application\UseCase\CreateItem\CreateItemInput;
use Lottery\Application\UseCase\CreateItem\CreateItemInputPort;

class CreateLotteryItem extends Command
{
    protected $signature = 'create:lottery-item {itemName}';

    protected $description = '抽選アイテムを作成する';

    public function handle(CreateItemInputPort $handler): int
    {
        $itemName = $this->argument('itemName');

        if (! is_string($itemName) || mb_trim($itemName) === '') {
            $this->error('抽選アイテム名を入力してください。');

            return Command::FAILURE;
        }

        $result = $handler->handle(new CreateItemInput($itemName));

        if ($result->isErr()) {
            $this->error($result->unwrapErr());

            return Command::FAILURE;
        }

        $this->info("抽選アイテム「{$itemName}」を作成しました。");

        return Command::SUCCESS;
    }
}
