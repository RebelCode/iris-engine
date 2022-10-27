<?php

declare(strict_types=1);

namespace RebelCode\Iris;

use Generator;
use RebelCode\Iris\Data\Item;

class StoreResult
{
    /** @var list<Item> */
    protected $items;

    /** @var array<string, Item>|null */
    protected $mapCache = null;

    /**
     * Constructor.
     *
     * @param Item[] $items The list of items.
     */
    public function __construct(array $items)
    {
        $this->items = array_values($items);
    }

    /**
     * Retrieves the items in the result.
     *
     * @return list<Item>
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * Retrieves the items in the result as a generator.
     *
     * @return Generator<Item>
     */
    public function getGenerator(): Generator
    {
        yield from $this->items;
    }

    /**
     * Retrieves the first item in the result.
     *
     * @return Item|null The item, or null if the result is empty.
     */
    public function getFirst(): ?Item
    {
        return $this->items[0] ?? null;
    }

    /**
     * Retrieves the items in the result as a mapping of IDs to items.
     *
     * @return array<string, Item>
     */
    public function getMap(): array
    {
        if ($this->mapCache === null) {
            $this->mapCache = [];

            foreach ($this->items as $item) {
                $this->mapCache[$item->id] = $item;
            }
        }

        return $this->mapCache;
    }

    /**
     * Retrieves the items in the result, ensuring that duplicate items are removed.
     *
     * @return list<Item>
     */
    public function getUnique(): array
    {
        return array_values($this->getMap());
    }

    /**
     * Retrieves the item with a specific ID from the result.
     *
     * @param string $id The ID of the item to retrieve.
     * @return Item|null The item with the given ID, or null if no such item exists.
     */
    public function getItem(string $id): ?Item
    {
        return $this->getMap()[$id] ?? null;
    }
}
