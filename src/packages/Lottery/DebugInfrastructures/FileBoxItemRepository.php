<?php

declare(strict_types=1);

namespace Lottery\DebugInfrastructures;

use Lottery\Domain\Models\BoxItem\BoxItem;
use Lottery\Domain\Models\BoxItem\BoxItemRepositoryInterface;
use Lottery\Domain\Models\LotteryBox\BoxId;
use Support\Contracts\ConfigInterface;
use Support\Debug\Repository\FileStore;

class FileBoxItemRepository implements BoxItemRepositoryInterface
{
    private const string FILE_NAME = 'box-item.dat';

    private readonly string $filePath;

    /**
     * @param FileStore<BoxItem> $store
     */
    public function __construct(
        private readonly FileStore $store,
        private readonly ConfigInterface $config,
    ) {
        $this->filePath = $this->config->getString('debug.file.path') . '/' . self::FILE_NAME;
    }

    public function save(BoxItem $boxItem): void
    {
        $this->store->put($this->filePath, $boxItem->boxId->value . '-' . $boxItem->itemId->value, $boxItem);
    }

    public function getByBoxId(BoxId $boxId): array
    {
        $founds = [];
        foreach ($this->store->getAll($this->filePath) as $key => $item) {
            if (str_starts_with($key, $boxId->value)) {
                $founds[] = $item;
            }
        }

        return $founds;
    }
}
