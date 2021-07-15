<?php

declare(strict_types=1);

namespace RebelCode\Iris\Converter;

use RebelCode\Iris\Data\Item;
use RebelCode\Iris\Exception\ConversionException;

interface ConversionStrategy
{
    /**
     * Converts an item.
     *
     * @param Item $item The item to convert.
     *
     * @return Item|null The converted item, or null if the item should be rejected.
     *
     * @throws ConversionException If an error occurred.
     */
    public function convert(Item $item): ?Item;

    /**
     * Reconciles a converted item with a corresponding existing item.
     *
     * @param Item $incoming The item to be converted.
     * @param Item $existing The corresponding existing item.
     *
     * @return Item|null The reconciled item, or null if the item should be rejected.
     *
     * @throws ConversionException If an error occurred.
     */
    public function reconcile(Item $incoming, Item $existing): ?Item;

    /**
     * Finalizes an item after conversion and possible reconciliation.
     *
     * @param Item $item The item to finalize.
     *
     * @return Item|null The finalized item, or null if the item should be rejected.
     *
     * @throws ConversionException If an error occurred.
     */
    public function finalize(Item $item): ?Item;
}
