<?php

declare(strict_types=1);

namespace Lottery\Application\Interactors;

use Lottery\Application\UseCase\Reset\ResetInput;
use Lottery\Application\UseCase\Reset\ResetInputPort;
use Lottery\Application\UseCase\Reset\ResetOutput;
use Lottery\Domain\Models\LotteryBox\BoxName;
use Lottery\Domain\Models\LotteryBox\LotteryBoxRepositoryInterface;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Support\Contracts\TransactionInterface;

class ResetInteractor implements ResetInputPort
{
    public function __construct(
        private readonly TransactionInterface $transaction,
        private readonly LotteryBoxRepositoryInterface $lotteryBoxRepository,
    ) {
    }

    public function handle(ResetInput $input): Result
    {
        $boxName = new BoxName($input->boxName);

        $lotteryBox = $this->lotteryBoxRepository->findByBoxName($boxName);

        if (is_null($lotteryBox)) {
            return new Err("抽選箱「{$input->boxName}」は存在しません。");
        }

        $this->transaction->scope(fn () => $this->lotteryBoxRepository->save($lotteryBox->reset()));

        return new Ok(new ResetOutput());
    }
}
