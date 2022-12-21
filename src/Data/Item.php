<?php

declare(strict_types=1);

namespace RebelCode\Iris\Data;

/**
 * Represents a fetched and/or stored item.
 */
interface Item
{
    /**
     * Retrieves the ID that uniquely identifies the item from other items from the same source.
     *
     * @psalm-mutation-free
     * @return string The item ID.
     */
    public function getId(): string;

    /**
     * Retrieves the ID of the item in persistent local storage.
     *
     * @psalm-mutation-free
     * @return int|string|null The local ID, or null if the item has not been stored yet.
     */
    public function getLocalId();

    /**
     * Retrieves the sources from which the item was fetched.
     *
     * @psalm-mutation-free
     * @return Source[] A numerically indexes list of sources.
     */
    public function getSources(): array;
}
