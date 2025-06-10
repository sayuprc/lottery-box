<?php

declare(strict_types=1);

namespace Lottery\Application\Interactors;

use Lottery\Application\UseCase\CreateItem\CreateItemInput;
use Lottery\Application\UseCase\CreateItem\CreateItemInputPort;
use Lottery\Application\UseCase\CreateItem\CreateItemOutput;
use Lottery\Domain\Models\LotteryItem\LotteryItemFactoryInterface;
use Lottery\Domain\Models\LotteryItem\LotteryItemRepositoryInterface;
use Lottery\Domain\Services\LotteryItemNameDuplicateCheckService;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Support\Contracts\TransactionInterface;

class CreateItemInteractor implements CreateItemInputPort
{
    public function __construct(
        private readonly LotteryItemFactoryInterface $factory,
        private readonly LotteryItemRepositoryInterface $repository,
        private readonly LotteryItemNameDuplicateCheckService $service,
        private readonly TransactionInterface $transaction,
    ) {
    }

    public function handle(CreateItemInput $input): Result
    {
        return $this->transaction->scope(function () use ($input) {
            $lotteryItem = $this->factory->create($input->itemName);

            if ($this->service->exists($lotteryItem->itemName)) {
                return new Err("すでに同名の抽選アイテムが存在します。: {$lotteryItem->itemName->value}");
            }

            $this->repository->save($lotteryItem);

            return new Ok(new CreateItemOutput());
        });
    }
}
