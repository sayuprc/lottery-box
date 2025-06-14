<?php

declare(strict_types=1);

namespace Lottery\Application\UseCase\Reset;

use ResultType\Result;

interface ResetInputPort
{
    /**
     * @return Result<ResetOutput, string>
     */
    public function handle(ResetInput $input): Result;
}
