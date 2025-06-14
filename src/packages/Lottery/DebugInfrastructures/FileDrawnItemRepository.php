<?php

declare(strict_types=1);

namespace Lottery\DebugInfrastructures;

use Lottery\Domain\Models\DrawnItem\DrawnItem;
use Lottery\Domain\Models\DrawnItem\DrawnItemRepositoryInterface;
use Lottery\Domain\Models\LotteryBox\BoxId;
use Support\Contracts\ConfigInterface;
use Support\Debug\Repository\FileStore;

class FileDrawnItemRepository implements DrawnItemRepositoryInterface
{
    private const string FILE_NAME = 'drawn-item.dat';

    private readonly string $filePath;

    /**
     * @param FileStore<DrawnItem> $store
     */
    public function __construct(
        private readonly FileStore $store,
        private readonly ConfigInterface $config,
    ) {
        $this->filePath = $this->config->getString('debug.file.path') . '/' . self::FILE_NAME;
    }

    public function getByBoxId(BoxId $boxId): array
    {
        return array_values(
            array_filter(
                $this->store->getAll($this->filePath),
                fn (string $key) => str_starts_with($key, $boxId->value),
                ARRAY_FILTER_USE_KEY
            )
        );
    }

    public function save(DrawnItem $drawnItem): void
    {
        $this->store->put(
            $this->filePath,
            $drawnItem->boxId->value . '-' . $drawnItem->itemId->value . '-' . $drawnItem->drawnAt->value->format('YmdHis'),
            $drawnItem,
        );
    }
}
