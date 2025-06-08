<?php

declare(strict_types=1);

namespace Lottery\Application\UseCase\Lottery;

use ResultType\Result;

interface LotteryInputPort
{
    /**
     * @return Result<LotteryOutput, string>
     */
    public function handle(LotteryInput $input): Result;
}
