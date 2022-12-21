<?php

declare(strict_types=1);

namespace RebelCode\Iris\Aggregator;

use RebelCode\Iris\Aggregator;
use RebelCode\Iris\Data\Feed;
use RebelCode\Iris\ItemProcessor;
use RebelCode\Iris\StoreQuery;

class SimpleAggregator implements Aggregator
{
    /** @var bool */
    protected $doManualPagination;

    /**
     * Constructor.
     *
     * @param bool $doManualPagination Whether pagination should be done manually by the engine. Use false if the store
     *                                 supports pagination natively.
     */
    public function __construct(bool $doManualPagination = false)
    {
        $this->doManualPagination = $doManualPagination;
    }

    /** @inheritDoc */
    public function getFeedQuery(Feed $feed, ?int $count = null, int $offset = 0): ?StoreQuery
    {
        $query = StoreQuery::forSources($feed->getSources());

        if ($this->doManualPagination) {
            return $query;
        } else {
            return $query->withCount($count)->withOffset($offset);
        }
    }

    /** @inheritDoc */
    public function getPreProcessor(Feed $feed, StoreQuery $query): ?ItemProcessor
    {
        return null;
    }

    /** @inheritDoc */
    public function getPostProcessor(Feed $feed, StoreQuery $query): ?ItemProcessor
    {
        return null;
    }

    /** @inheritDoc */
    public function doManualPagination(Feed $feed, StoreQuery $query): bool
    {
        return $this->doManualPagination;
    }
}
