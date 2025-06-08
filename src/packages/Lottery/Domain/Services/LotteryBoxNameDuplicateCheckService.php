<?php

declare(strict_types=1);

namespace Lottery\Domain\Services;

use Lottery\Domain\Models\LotteryBox\BoxName;
use Lottery\Domain\Models\LotteryBox\LotteryBoxRepositoryInterface;

class LotteryBoxNameDuplicateCheckService
{
    public function __construct(private readonly LotteryBoxRepositoryInterface $lotteryBoxRepository)
    {
    }

    public function exists(BoxName $boxName): bool
    {
        return ! is_null($this->lotteryBoxRepository->findByBoxName($boxName));
    }
}
