<?php

declare(strict_types=1);

namespace Lottery\DebugInfrastructures;

use Lottery\Domain\Models\LotteryItem\ItemId;
use Lottery\Domain\Models\LotteryItem\ItemName;
use Lottery\Domain\Models\LotteryItem\LotteryItem;
use Lottery\Domain\Models\LotteryItem\LotteryItemRepositoryInterface;
use Support\Contracts\ConfigInterface;
use Support\Debug\Repository\FileStore;

class FileLotteryItemRepository implements LotteryItemRepositoryInterface
{
    private const string FILE_NAME = 'lottery-item.dat';

    private readonly string $filePath;

    /**
     * @param FileStore<LotteryItem> $store
     */
    public function __construct(
        private readonly FileStore $store,
        private readonly ConfigInterface $config,
    ) {
        $this->filePath = $this->config->getString('debug.file.path') . '/' . self::FILE_NAME;
    }

    public function find(ItemId $itemId): ?LotteryItem
    {
        return $this->store->get($this->filePath, $itemId->value);
    }

    public function findByItemName(ItemName $itemName): ?LotteryItem
    {
        foreach ($this->store->getAll($this->filePath) as $lotteryItem) {
            if ($lotteryItem->itemName->value === $itemName->value) {
                return $lotteryItem;
            }
        }

        return null;
    }

    public function save(LotteryItem $lotteryItem): void
    {
        $this->store->put($this->filePath, $lotteryItem->itemId->value, $lotteryItem);
    }
}
