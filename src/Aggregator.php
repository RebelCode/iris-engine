<?php

declare(strict_types=1);

namespace RebelCode\Iris;

use RebelCode\Iris\Aggregator\AggregateResult;
use RebelCode\Iris\Aggregator\AggregationStrategy;
use RebelCode\Iris\Aggregator\NoopItemProcessor;
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

        if ($this->strategy->doManualPagination($feed, $query)) {
            $storeQuery = $query->withoutPagination();
            $resultOffset = $query->offset;
        } else {
            $storeQuery = $query;
            $resultOffset = 0;
        }

        $items = $this->store->query($storeQuery)->getUnique();

        $preProcessor = $this->strategy->getPreProcessor($feed, $query) ?? new NoopItemProcessor();
        $preItems = $preProcessor->process($items, $feed, $query);

        $postProcessor = $this->strategy->getPostProcessor($feed, $query) ?? new NoopItemProcessor();
        $postItems = $postProcessor->process($preItems, $feed, $query);

        $finalItems = array_slice($postItems, $resultOffset, $query->count);

        return new AggregateResult($finalItems, count($items), count($preItems), count($postItems));
    }
}
