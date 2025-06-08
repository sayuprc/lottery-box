<?php

declare(strict_types=1);

namespace Lottery\Domain\Models\BoxItem;

interface BoxItemRepositoryInterface
{
    public function save(BoxItem $boxItem): void;
}
