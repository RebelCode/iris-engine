<?php

declare(strict_types=1);

namespace RebelCode\IrisEngine\Aggregator;

use RebelCode\IrisEngine\Data\Feed;
use RebelCode\IrisEngine\Store\Query;

interface AggregationStrategy
{
    /**
     * Retrieves the query that the aggregator will use to obtain the items from the store.
     */
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

    /**
     * Whether the aggregator should manually offset the items after post-processing and before potential truncation.
     *
     * This is useful if the consumer is unable to include all of their criteria in the query that is returned by
     * {@link AggregationStrategy::getFeedQuery()}. In those situations, the consumer may need to perform programmatic
     * filtering on the list of items using pre-processors or post-processors. The offset can be omitted from the
     * initial query in order to obtain all of the items from the store, and then have the aggregator apply the
     * offset manually.
     *
     * @return bool If true, the aggregator will manually apply the offset. If false, no changes will be made.
     */
    public function offsetItems(Feed $feed, Query $query): bool;

    /**
     * Whether the aggregator should truncate the items after post-processing to ensure that the number of items does
     * not exceed the query count.
     *
     * @return bool If true, the aggregator will truncate the list of items if it's too long. If false, no truncation
     *              will be performed.
     */
    public function truncateItems(Feed $feed, Query $query): bool;
}
