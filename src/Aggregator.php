<?php

declare(strict_types=1);

namespace RebelCode\IrisEngine;

use RebelCode\IrisEngine\Aggregator\AggregateResult;
use RebelCode\IrisEngine\Aggregator\AggregationStrategy;
use RebelCode\IrisEngine\Data\Feed;
use RebelCode\IrisEngine\Data\Item;
use RebelCode\IrisEngine\Exception\StoreException;

class Aggregator
{
    /** @var Store */
    protected $store;

    /** @var AggregationStrategy */
    protected $strategy;

    /**
     * Constructor.
     */
    public function __construct(Store $store, AggregationStrategy $strategy)
    {
        $this->store = $store;
        $this->strategy = $strategy;
    }

    /**
     * @throws StoreException
     */
    public function aggregate(Feed $feed): AggregateResult
    {
        $query = $this->strategy->getFeedQuery($feed);

        if ($query === null) {
            return new AggregateResult([], 0);
        }

        $items = $this->store->query($query);
        $this->removeDuplicates($items);

        $preProcessors = $this->strategy->getPreProcessors($feed, $query);
        foreach ($preProcessors as $processor) {
            $processor->process($items);
        }

        $total = count($items);

        $postProcessors = $this->strategy->getPostProcessors($feed, $query);
        foreach ($postProcessors as $processor) {
            $processor->process($items);
        }

        // Make sure that the list of items is not greater than the query's count after post-processing
        $count = max(0, $query->count ?? 0);
        if ($count > 0) {
            $items = array_slice($items, 0, $count);
        }

        return new AggregateResult($items, $total);
    }

    /**
     * Removes duplicate items that share the same ID.
     *
     * Note: this method takes the item list by reference for performance reasons.
     *
     * @param Item[] $items The list of items.
     */
    protected function removeDuplicates(array &$items): void
    {
        $unique = [];
        foreach ($items as $item) {
            $unique[$item->id] = $item;
        }
        $items = array_values($unique);
    }
}
