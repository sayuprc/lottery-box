<?php

declare(strict_types=1);

namespace Lottery\Application\Interactors;

use DateTimeImmutable;
use Lottery\Application\UseCase\Lottery\LotteryInput;
use Lottery\Application\UseCase\Lottery\LotteryInputPort;
use Lottery\Application\UseCase\Lottery\LotteryOutput;
use Lottery\Domain\Models\BoxItem\BoxItem;
use Lottery\Domain\Models\BoxItem\BoxItemRepositoryInterface;
use Lottery\Domain\Models\DrawnItem\DrawnAt;
use Lottery\Domain\Models\DrawnItem\DrawnItem;
use Lottery\Domain\Models\DrawnItem\DrawnItemRepositoryInterface;
use Lottery\Domain\Models\LotteryBox\BoxName;
use Lottery\Domain\Models\LotteryBox\LotteryBoxRepositoryInterface;
use Lottery\Domain\Models\LotteryItem\LotteryItemRepositoryInterface;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Support\Contracts\TransactionInterface;

class LotteryInteractor implements LotteryInputPort
{
    public function __construct(
        private readonly TransactionInterface $transaction,
        private readonly LotteryBoxRepositoryInterface $lotteryBoxRepository,
        private readonly LotteryItemRepositoryInterface $lotteryItemRepository,
        private readonly BoxItemRepositoryInterface $boxItemRepository,
        private readonly DrawnItemRepositoryInterface $drawnItemRepository,
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

        if (($boxItemCount = count($boxItems)) === 0) {
            return new Err("抽選箱「{$input->boxName}」にアイテムがありません。");
        }

        if (! $input->isUnique) {
            $lotteryItem = $this->lotteryItemRepository->find($boxItems[array_rand($boxItems)]->itemId);
        } else {
            $drawnItems = $this->drawnItemRepository->getByBoxId($lotteryBox->boxId, $lotteryBox->resetAt);

            if ($boxItemCount === count($drawnItems)) {
                return new Err("抽選箱「{$input->boxName}」からすべてのアイテムを抽選しました。リセットしてください。");
            }

            $undrawnItems = array_udiff(
                $boxItems,
                $drawnItems,
                function (BoxItem|DrawnItem $a, BoxItem|DrawnItem $b) {
                    $aValue = $a instanceof BoxItem ? $a->itemId->value : $a->itemId->value;
                    $bValue = $b instanceof BoxItem ? $b->itemId->value : $b->itemId->value;

                    return $aValue <=> $bValue;
                },
            );

            if (count($undrawnItems) === 0) {
                return new Err("抽選箱「{$input->boxName}」からすべてのアイテムを抽選しました。リセットしてください。");
            }

            $lotteryItem = $this->lotteryItemRepository->find($undrawnItems[array_rand($undrawnItems)]->itemId);
        }

        if (is_null($lotteryItem)) {
            return new Err('抽選アイテムが存在しません。');
        }

        if ($input->isUnique) {
            $this->transaction->scope(function () use ($lotteryBox, $lotteryItem) {
                $drawnItem = new DrawnItem(
                    $lotteryBox->boxId,
                    $lotteryItem->itemId,
                    new DrawnAt(new DateTimeImmutable())
                );
                $this->drawnItemRepository->save($drawnItem);
            });
        }

        return new Ok(new LotteryOutput($lotteryItem->itemName));
    }
}
