<?php

declare(strict_types=1);

namespace RebelCode\Iris;

use RebelCode\Iris\Aggregator\AggregateResult;
use RebelCode\Iris\Aggregator\AggregationStrategy;
use RebelCode\Iris\Data\Feed;
use RebelCode\Iris\Exception\StoreException;

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
    public function aggregate(Feed $feed, ?int $count = null, int $offset = 0): AggregateResult
    {
        $query = $this->strategy->getFeedQuery($feed, $count, $offset);

        if ($query === null) {
            return new AggregateResult([], 0, 0, 0);
        }

        $manualPagination = $this->strategy->doManualPagination($feed, $query);

        $storeQuery = $manualPagination ? $query->withoutPagination() : $query;

        $items = $this->store->query($storeQuery)->getUnique();
        $storeTotal = $preTotal = $postTotal = count($items);

        $preProcessor = $this->strategy->getPreProcessor($feed, $query);
        $postProcessor = $this->strategy->getPostProcessor($feed, $query);

        if ($preProcessor !== null) {
            $items = $preProcessor->process($items, $feed, $query);
            $preTotal = $postTotal = count($items);
        }

        if ($postProcessor !== null) {
            $items = $postProcessor->process($items, $feed, $query);
            $postTotal = count($items);
        }

        if ($manualPagination) {
            /** @psalm-suppress RedundantConditionGivenDocblockType, DocblockTypeContradiction */
            $items = array_slice($items, $query->offset ?? 0, $query->count);
        } else {
            // Make sure that the list of items is not greater than the query's count after post-processing
            $count = max(0, $query->count ?? 0);
            if ($count > 0) {
                $items = array_slice($items, 0, $count);
            }
        }

        return new AggregateResult($items, $storeTotal, $preTotal, $postTotal);
    }
}
