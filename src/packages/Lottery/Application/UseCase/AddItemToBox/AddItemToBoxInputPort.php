<?php

declare(strict_types=1);

namespace Lottery\Application\UseCase\AddItemToBox;

use ResultType\Result;

interface AddItemToBoxInputPort
{
    /**
     * @return Result<AddItemToBoxOutput, never>
     */
    public function handle(AddItemToBoxInput $input): Result;
}
