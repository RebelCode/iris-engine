<?php

declare(strict_types=1);

namespace RebelCode\Iris;

use RebelCode\Iris\Data\Feed;
use RebelCode\Iris\Data\Item;

/** Modifies a list of items for aggregation. */
interface ItemProcessor
{
    /**
     * Processes a list of items to generate a new, modified list of items.
     *
     * @param list<Item> $items The items to process.
     * @param Feed $feed The feed that the items belong to.
     * @param StoreQuery $query The query that was used to fetch the items.
     * @return list<Item> The processed items.
     */
    public function process(array $items, Feed $feed, StoreQuery $query): array;
}
