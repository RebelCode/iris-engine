<?php

declare(strict_types=1);

namespace RebelCode\Iris\Data;

/**
 * Represents a collection of items that are aggregated from a list of sources.
 */
interface Feed
{
    /**
     * Retrieves the sources that this feed shows items from.
     *
     * @psalm-mutation-free
     * @return Source[] A numerically-indexed list of sources.
     */
    public function getSources(): array;
}
