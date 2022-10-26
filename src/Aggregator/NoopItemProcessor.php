<?php

namespace RebelCode\Iris\Aggregator;

use RebelCode\Iris\Data\Feed;
use RebelCode\Iris\ItemProcessor;
use RebelCode\Iris\StoreQuery;

/** An item processor that simply returns the list of items it was given, without making any changes. */
class NoopItemProcessor implements ItemProcessor
{
    /** @inheritDoc */
    public function process(array $items, Feed $feed, StoreQuery $query): array
    {
        return $items;
    }
}
