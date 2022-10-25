<?php

declare(strict_types=1);

namespace RebelCode\Iris;

use RebelCode\Iris\Data\Item;
use RebelCode\Iris\Exception\StoreException;

interface Store
{
    public const THROW_ON_FAIL = 0;
    public const IGNORE_FAIL = 1;

    /**
     * Inserts items into the store.
     *
     * @param Item[] $items The items to insert.
     * @param int $mode The mode. See {@link Store::THROW_ON_FAIL} and {@link Store::IGNORE_FAIL}.
     *
     * @return Item[] The new items with updated {@link Item::$localId} fields.
     *
     * @throws StoreException If an error occurred.
     */
    public function insert(array $items, int $mode = self::THROW_ON_FAIL): array;

    /**
     * Retrieves items based on a given query.
     *
     * @param StoreQuery $query The query.
     *
     * @return Item[] A list of items that satisfy and conform to the given query.
     *
     * @throws StoreException If an error occurred.
     */
    public function query(StoreQuery $query): array;

    /**
     * Deletes items that match a given query.
     *
     * @param StoreQuery $query The query.
     * @retrun int The number of items that were deleted.
     */
    public function delete(StoreQuery $query): int;
}
