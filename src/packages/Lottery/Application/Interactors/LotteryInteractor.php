<?php

declare(strict_types=1);

namespace Lottery\Application\Interactors;

use Lottery\Application\UseCase\Lottery\LotteryInput;
use Lottery\Application\UseCase\Lottery\LotteryInputPort;
use Lottery\Application\UseCase\Lottery\LotteryOutput;
use Lottery\Domain\Models\BoxItem\BoxItemRepositoryInterface;
use Lottery\Domain\Models\LotteryBox\BoxName;
use Lottery\Domain\Models\LotteryBox\LotteryBoxRepositoryInterface;
use Lottery\Domain\Models\LotteryItem\LotteryItemRepositoryInterface;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;

class LotteryInteractor implements LotteryInputPort
{
    public function __construct(
        private readonly LotteryBoxRepositoryInterface $lotteryBoxRepository,
        private readonly LotteryItemRepositoryInterface $lotteryItemRepository,
        private readonly BoxItemRepositoryInterface $boxItemRepository,
    ) {
    }

    public function handle(LotteryInput $input): Result
    {
        $boxName = new BoxName($input->boxName);

        $lotteryBox = $this->lotteryBoxRepository->findByBoxName($boxName);

        if (is_null($lotteryBox)) {
            return new Err("抽選箱「{$input->boxName}」は存在しません。");
        }

        $boxItems = $this->boxItemRepository->getByBoxId($lotteryBox->boxId);

        if (count($boxItems) === 0) {
            return new Err("抽選箱「{$input->boxName}」にアイテムがありません。");
        }

        $lotteryItem = $this->lotteryItemRepository->find($boxItems[array_rand($boxItems)]->itemId);

        if (is_null($lotteryItem)) {
            return new Err('抽選アイテムが存在しません。');
        }

        return new Ok(new LotteryOutput($lotteryItem->itemName));
    }
}
