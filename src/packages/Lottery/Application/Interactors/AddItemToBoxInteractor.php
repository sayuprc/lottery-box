<?php

declare(strict_types=1);

namespace Lottery\Application\Interactors;

use Lottery\Application\UseCase\AddItemToBox\AddItemToBoxInput;
use Lottery\Application\UseCase\AddItemToBox\AddItemToBoxInputPort;
use Lottery\Application\UseCase\AddItemToBox\AddItemToBoxOutput;
use Lottery\Domain\Models\BoxItem\BoxItem;
use Lottery\Domain\Models\BoxItem\BoxItemRepositoryInterface;
use Lottery\Domain\Models\LotteryBox\LotteryBox;
use Lottery\Domain\Models\LotteryBox\LotteryBoxFactoryInterface;
use Lottery\Domain\Models\LotteryBox\LotteryBoxRepositoryInterface;
use Lottery\Domain\Models\LotteryItem\LotteryItem;
use Lottery\Domain\Models\LotteryItem\LotteryItemFactoryInterface;
use Lottery\Domain\Models\LotteryItem\LotteryItemRepositoryInterface;
use Lottery\Domain\Services\LotteryBoxNameDuplicateCheckService;
use Lottery\Domain\Services\LotteryItemNameDuplicateCheckService;
use ResultType\Ok;
use ResultType\Result;
use Support\Contracts\TransactionInterface;

class AddItemToBoxInteractor implements AddItemToBoxInputPort
{
    public function __construct(
        private readonly LotteryBoxFactoryInterface $lotteryBoxFactory,
        private readonly LotteryBoxNameDuplicateCheckService $lotteryBoxNameDuplicateCheckService,
        private readonly LotteryBoxRepositoryInterface $lotteryBoxRepository,
        private readonly LotteryItemFactoryInterface $lotteryItemFactory,
        private readonly LotteryItemNameDuplicateCheckService $lotteryItemNameDuplicateCheckService,
        private readonly LotteryItemRepositoryInterface $lotteryItemRepository,
        private readonly BoxItemRepositoryInterface $boxItemRepository,
        private readonly TransactionInterface $transaction,
    ) {
    }

    public function handle(AddItemToBoxInput $input): Result
    {
        return $this->transaction->scope(function () use ($input) {
            $lotteryBox = $this->lotteryBoxFactory->create($input->boxName);

            if ($this->lotteryBoxNameDuplicateCheckService->exists($lotteryBox->boxName)) {
                $lotteryBox = $this->lotteryBoxRepository->findByBoxName($lotteryBox->boxName);

                // サービスで存在チェックをしているので null の可能性はない
                assert($lotteryBox instanceof LotteryBox);
            }

            $this->lotteryBoxRepository->save($lotteryBox);

            $boxItems = [];

            foreach ($input->itemNames as $itemName) {
                $lotteryItem = $this->lotteryItemFactory->create($itemName);

                if ($this->lotteryItemNameDuplicateCheckService->exists($lotteryItem->itemName)) {
                    $lotteryItem = $this->lotteryItemRepository->findByItemName($lotteryItem->itemName);

                    // サービスで存在チェックをしているので null の可能性はない
                    assert($lotteryItem instanceof LotteryItem);
                }

                $this->lotteryItemRepository->save($lotteryItem);

                $boxItems[] = new BoxItem($lotteryBox->boxId, $lotteryItem->itemId);
            }

            foreach ($boxItems as $boxItem) {
                $this->boxItemRepository->save($boxItem);
            }

            return new Ok(new AddItemToBoxOutput());
        });
    }
}
