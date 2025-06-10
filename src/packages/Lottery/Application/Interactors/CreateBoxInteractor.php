<?php

declare(strict_types=1);

namespace Lottery\Application\Interactors;

use Lottery\Application\UseCase\CreateBox\CreateBoxInput;
use Lottery\Application\UseCase\CreateBox\CreateBoxInputPort;
use Lottery\Application\UseCase\CreateBox\CreateBoxOutput;
use Lottery\Domain\Models\LotteryBox\LotteryBoxFactoryInterface;
use Lottery\Domain\Models\LotteryBox\LotteryBoxRepositoryInterface;
use Lottery\Domain\Services\LotteryBoxNameDuplicateCheckService;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Support\Contracts\TransactionInterface;

class CreateBoxInteractor implements CreateBoxInputPort
{
    public function __construct(
        private readonly LotteryBoxFactoryInterface $factory,
        private readonly LotteryBoxRepositoryInterface $repository,
        private readonly LotteryBoxNameDuplicateCheckService $service,
        private readonly TransactionInterface $transaction,
    ) {
    }

    public function handle(CreateBoxInput $input): Result
    {
        return $this->transaction->scope(function () use ($input) {
            $lotteryBox = $this->factory->create($input->boxName);

            if ($this->service->exists($lotteryBox->boxName)) {
                return new Err("すでに同名の抽選箱が存在します。: {$lotteryBox->boxName->value}");
            }

            $this->repository->save($lotteryBox);

            return new Ok(new CreateBoxOutput());
        });
    }
}
