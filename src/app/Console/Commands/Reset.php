<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Lottery\Application\UseCase\Reset\ResetInput;
use Lottery\Application\UseCase\Reset\ResetInputPort;

class Reset extends Command
{
    protected $signature = 'reset {boxName}';

    protected $description = '抽選の結果をリセットする';

    public function handle(ResetInputPort $handler): int
    {
        $boxName = $this->argument('boxName');

        if (! is_string($boxName) || mb_trim($boxName) === '') {
            $this->error('抽選箱名を入力してください。');

            return Command::FAILURE;
        }

        $result = $handler->handle(new ResetInput($boxName));

        if ($result->isErr()) {
            $this->error($result->unwrapErr());

            return Command::FAILURE;
        }

        $this->info("抽選箱「{$boxName}」の抽選結果をリセットしました。");

        return Command::SUCCESS;
    }
}
