<?php

declare(strict_types=1);

namespace Lottery\DebugInfrastructures;

use Lottery\Domain\Models\LotteryBox\BoxName;
use Lottery\Domain\Models\LotteryBox\LotteryBox;
use Lottery\Domain\Models\LotteryBox\LotteryBoxRepositoryInterface;
use Support\Contracts\ConfigInterface;
use Support\Debug\Repository\FileStore;

class FileLotteryBoxRepository implements LotteryBoxRepositoryInterface
{
    private const string FILE_NAME = 'lottery-box.dat';

    private readonly string $filePath;

    /**
     * @param FileStore<LotteryBox> $store
     */
    public function __construct(
        private readonly FileStore $store,
        private readonly ConfigInterface $config,
    ) {
        $this->filePath = $this->config->getString('debug.file.path') . '/' . self::FILE_NAME;
    }

    public function findByBoxName(BoxName $boxName): ?LotteryBox
    {
        foreach ($this->store->getAll($this->filePath) as $lotteryBox) {
            if ($lotteryBox->boxName->value === $boxName->value) {
                return $lotteryBox;
            }
        }

        return null;
    }

    public function save(LotteryBox $lotteryBox): void
    {
        $this->store->put($this->filePath, $lotteryBox->boxId->value, $lotteryBox);
    }
}
