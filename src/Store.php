<?php

declare(strict_types=1);

namespace RebelCode\IrisEngine;

use RebelCode\IrisEngine\Data\Item;
use RebelCode\IrisEngine\Data\Source;
use RebelCode\IrisEngine\Exception\StoreException;

interface Store
{
    public const THROW_ON_FAIL = 0;
    public const IGNORE_FAIL = 1;

    /**
     * Inserts a single item into the store.
     *
     * @param Item $item The item to store.
     *
     * @throws StoreException If an error occurred.
     */
    public function insert(Item $item): void;

    /**
     * Inserts multiple items into the store.
     *
     * @param Item[] $items The items to insert.
     * @param int $mode The mode. See {@link Store::THROW_ON_FAIL} and {@link Store::IGNORE_FAIL}.
     *
     * @return int The number of successful insertions.
     *
     * @throws StoreException If an error occurred.
     */
    public function insertMultiple(array $items, int $mode = self::THROW_ON_FAIL): int;

    /**
     * Retrieves a single item from storage by ID.
     *
     * @param string $id The ID of the item to retrieve.
     *
     * @return Item|null The item with the given ID, or null if no such item exists.
     *
     * @throws StoreException If an error occurred.
     */
    public function get(string $id): ?Item;

    /**
     * Retrieves a list of items by their IDs.
     *
     * IDs that do not correspond to a stored item will be ignored.
     *
     * @param string[] $ids The IDs of the items to retrieve.
     *
     * @return array<string, Item> A map of items by their IDs.
     *
     * @throws StoreException If an error occurred.
     */
    public function getMultiple(array $ids): array;

    /**
     * Retrieves a list of items for a specific set of sources.
     *
     * @param Source[] $sources The sources.
     * @param int|null $count The number of items to retrieve. If null, all items will be retrieved.
     * @param int $offset The number of items to skip over.
     *
     * @return Item[] A list of items that belong to any of the given sources.
     *
     * @throws StoreException If an error occurred.
     */
    public function getForSources(array $sources, ?int $count = null, int $offset = 0): array;

    /**
     * Retrieves items based on a given query.
     *
     * @param Store\Query $query The query.
     *
     * @return Item[] A list of items that satisfy and conform to the given query.
     *
     * @throws StoreException If an error occurred.
     */
    public function query(Store\Query $query): array;

    /**
     * Deletes a single item by its ID.
     *
     * @param string $id The ID of the item to delete.
     *
     * @return bool True if the item was deleted, false if the ID did not correspond to a stored item.
     *
     * @throws StoreException If an error occurred.
     */
    public function delete(string $id): bool;

    /**
     * Deletes a list of items by their IDs.
     *
     * @param string[] $ids The list of the IDs of the items to be deleted.
     *
     * @return int The number of items that were deleted. This may be less than the number of IDs given, since IDs that
     *             do not correspond to a stored item will be ignored.
     *
     * @throws StoreException If an error occurred.
     */
    public function deleteMultiple(array $ids): int;

    /**
     * Deletes items that belong to any of the given sources.
     *
     * @param Source[] $sources The sources.
     *
     * @return int The number of deleted items.
     *
     * @throws StoreException If an error occurred.
     */
    public function deleteForSources(array $sources): int;
}
