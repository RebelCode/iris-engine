<?php

declare(strict_types=1);

namespace RebelCode\IrisEngine;

use RebelCode\IrisEngine\Converter\ConversionStrategy;
use RebelCode\IrisEngine\Data\Item;
use RebelCode\IrisEngine\Exception\ConversionException;
use RebelCode\IrisEngine\Exception\StoreException;

class Converter
{
    /** @var Store */
    protected $store;

    /** @var ConversionStrategy */
    protected $strategy;

    /**
     * Constructor.
     */
    public function __construct(Store $store, ConversionStrategy $strategy)
    {
        $this->store = $store;
        $this->strategy = $strategy;
    }

    /**
     * @throws StoreException
     * @throws ConversionException
     */
    public function convert(Item $item): ?Item
    {
        return $this->doConversion($item, $this->store->get($item->id));
    }

    /**
     * @param Item[] $items
     *
     * @return Item[]
     *
     * @throws StoreException
     * @throws ConversionException
     */
    public function convertMultiple(array $items): array
    {
        $existingItems = $this->store->getMultiple(
            array_map(
                function (Item $item) {
                    return $item->id;
                },
                $items
            )
        );

        $convertedItems = [];
        foreach ($items as $item) {
            $item = $this->doConversion($item, $existingItems[$item->id] ?? null);

            if ($item !== null) {
                $convertedItems[] = $item;
            }
        }

        return $convertedItems;
    }

    /**
     * @throws Exception\ConversionException
     */
    protected function doConversion(Item $item, ?Item $existing): ?Item
    {
        $item = $this->strategy->convert($item);

        if ($item !== null && $existing !== null) {
            $item = $this->strategy->reconcile($item, $existing);
        }

        if ($item !== null) {
            $item = $this->strategy->finalize($item);
        }

        return $item;
    }
}
