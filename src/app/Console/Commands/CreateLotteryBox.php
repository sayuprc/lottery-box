<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Lottery\Application\UseCase\CreateBox\CreateBoxInput;
use Lottery\Application\UseCase\CreateBox\CreateBoxInputPort;

class CreateLotteryBox extends Command
{
    protected $signature = 'create:lottery-box {boxName}';

    protected $description = '抽選箱を作成する';

    public function handle(CreateBoxInputPort $handler): int
    {
        $boxName = $this->argument('boxName');

        if (! is_string($boxName) || mb_trim($boxName) === '') {
            $this->error('抽選箱名を入力してください。');

            return Command::FAILURE;
        }

        $result = $handler->handle(new CreateBoxInput($boxName));

        if ($result->isErr()) {
            $this->error($result->unwrapErr());

            return Command::FAILURE;
        }

        $this->info("抽選箱「{$boxName}」を作成しました。");

        return Command::SUCCESS;
    }
}
