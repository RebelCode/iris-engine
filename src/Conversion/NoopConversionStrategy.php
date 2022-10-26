<?php

namespace RebelCode\Iris\Conversion;

use RebelCode\Iris\ConversionStrategy;
use RebelCode\Iris\Data\Item;

/** A conversion strategy that performs no conversion. */
class NoopConversionStrategy implements ConversionStrategy
{
    /** @var bool */
    protected $updateItems;

    /**
     * Constructor.
     *
     * @param bool $updateItems If true, incoming items will replace existing items with the same ID. If false, existing
     *                          items will be preserved.
     */
    public function __construct(bool $updateItems = false)
    {
        $this->updateItems = $updateItems;
    }

    /** @inheritDoc */
    public function beforeBatch(array $incoming, array $existing): array
    {
        return $incoming;
    }

    /** @inheritDoc */
    public function convert(Item $item): ?Item
    {
        return $item;
    }

    /** @inheritDoc */
    public function reconcile(Item $incoming, Item $existing): ?Item
    {
        return $this->updateItems ? $incoming : $existing;
    }

    /** @inheritDoc */
    public function finalize(Item $item): ?Item
    {
        return $item;
    }

    /** @inheritDoc */
    public function afterBatch(array $items): array
    {
        return $items;
    }
}
