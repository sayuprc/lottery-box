<?php

declare(strict_types=1);

namespace Lottery\Application\UseCase\CreateItem;

use ResultType\Result;

interface CreateItemInputPort
{
    /**
     * @return Result<CreateItemOutput, string>
     */
    public function handle(CreateItemInput $input): Result;
}
