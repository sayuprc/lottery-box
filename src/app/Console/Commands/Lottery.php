<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Lottery\Application\UseCase\Lottery\LotteryInput;
use Lottery\Application\UseCase\Lottery\LotteryInputPort;

class Lottery extends Command
{
    protected $signature = 'lottery {boxName} {--u|unique}';

    protected $description = '抽選する';

    public function handle(LotteryInputPort $handler): int
    {
        $boxName = $this->argument('boxName');
        $isUnique = (bool)$this->option('unique');

        if (! is_string($boxName) || mb_trim($boxName) === '') {
            $this->error('抽選箱名を入力してください。');

            return Command::FAILURE;
        }

        $result = $handler->handle(new LotteryInput($boxName, $isUnique));

        if ($result->isErr()) {
            $this->error($result->unwrapErr());

            return Command::FAILURE;
        }

        $this->info($result->unwrap()->itemName->value);

        return Command::SUCCESS;
    }
}
