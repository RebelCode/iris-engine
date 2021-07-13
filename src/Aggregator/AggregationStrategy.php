<?php

declare(strict_types=1);

namespace RebelCode\IrisEngine\Aggregator;

use RebelCode\IrisEngine\Data\Feed;
use RebelCode\IrisEngine\Store\Query;

interface AggregationStrategy
{
    public function getFeedQuery(Feed $feed, ?int $count = null, int $offset = 0): ?Query;

    /**
     * Retrieves the pre-processors to use for a given feed.
     *
     * Pre-processors will make modifications to the list of items before it is returned in the aggregation result.
     * These modifications will affect the final result's {@link AggregateResult::$total} count.
     *
     * It is recommended to use pre-processors to perform manipulations that rely on the feed. That is, given the same
     * feed, the same pre-processing is applied to the items regardless of the query.
     *
     * @return ItemProcessor[]
     */
    public function getPreProcessors(Feed $feed, Query $query): array;

    /**
     * Retrieves the post-processors to use for a given feed.
     *
     * Post-processors will make modifications to the list of items before it is returned in the aggregation result,
     * but after the pre-processors have made their modifications. Post-processors do not affect the result's
     * {@link AggregateResult::$total} count.
     *
     * It is recommended to use post-processors to perform manipulations that rely on the query, rather than the feed.
     * That is, the same post-processing is applied to the items regardless of the feed.
     *
     * @return ItemProcessor[]
     */
    public function getPostProcessors(Feed $feed, Query $query): array;
}
