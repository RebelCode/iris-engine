<?php

declare(strict_types=1);

namespace RebelCode\IrisEngine\Aggregator;

use RebelCode\IrisEngine\Data\Feed;
use RebelCode\IrisEngine\Data\Item;

interface ItemProcessor
{
    /**
     * @param Item[] $items
     */
    public function process(array &$items, Feed $feed): void;
}
