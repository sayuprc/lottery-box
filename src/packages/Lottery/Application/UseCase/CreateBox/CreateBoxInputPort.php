<?php

declare(strict_types=1);

namespace Lottery\Application\UseCase\CreateBox;

use ResultType\Result;

interface CreateBoxInputPort
{
    /**
     * @return Result<CreateBoxOutput, string>
     */
    public function handle(CreateBoxInput $input): Result;
}
