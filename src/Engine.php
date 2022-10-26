<?php

declare(strict_types=1);

namespace RebelCode\Iris;

use RebelCode\Iris\Data\Feed;
use RebelCode\Iris\Data\Item;
use RebelCode\Iris\Exception\ConversionException;
use RebelCode\Iris\Exception\ConversionShortCircuit;
use RebelCode\Iris\Exception\FetchException;
use RebelCode\Iris\Exception\InvalidSourceException;
use RebelCode\Iris\Exception\StoreException;
use RebelCode\Iris\ItemProcessor\NoopItemProcessor;

class Engine
{
    /** @var FetchStrategy */
    protected $fetchStrategy;

    /** @var ConversionStrategy */
    protected $convStrategy;

    /** @var AggregationStrategy */
    protected $aggStrategy;

    /** @var Store */
    protected $store;

    /**
     * Constructor.
     *
     * @param FetchStrategy $fetchStrategy The strategy to use for fetching, mainly responsible for providing a catalog.
     * @param ConversionStrategy $conversionStrategy The strategy to use for converting fetched items into local items.
     * @param AggregationStrategy $aggregationStrategy The strategy to use for aggregating items into feeds.
     * @param Store $store The store, used to save fetched items to persistent storage.
     */
    public function __construct(
        FetchStrategy $fetchStrategy,
        ConversionStrategy $conversionStrategy,
        AggregationStrategy $aggregationStrategy,
        Store $store
    ) {
        $this->fetchStrategy = $fetchStrategy;
        $this->convStrategy = $conversionStrategy;
        $this->aggStrategy = $aggregationStrategy;
        $this->store = $store;
    }

    public function getStore(): Store
    {
        return $this->store;
    }

    /**
     * Fetches items and converts them, without importing them into the store.
     *
     * @param FetchQuery $query The fetch query.
     * @return FetchResult The result of the fetch.
     *
     * @throws InvalidSourceException If the catalog rejected the source in the query.
     * @throws FetchException If an error occurred while fetching the items.
     * @throws ConversionException If an error occurred while converting the items.
     * @throws StoreException If an error occurred while reading existing items from the store.
     */
    public function fetch(FetchQuery $query): FetchResult
    {
        $catalog = $this->fetchStrategy->getCatalog($query->source);

        if ($catalog === null) {
            throw new InvalidSourceException("No catalog found for source \"{$query->source}\"", $query->source);
        }

        $result = $catalog->query($query->source, $query->cursor, $query->count);
        $convItems = $this->convert($result->items);

        return new FetchResult(
            $convItems,
            $result->source,
            $result->catalogSize,
            $result->nextCursor,
            $result->prevCursor,
            $result->errors
        );
    }

    /**
     * Converts a list of items.
     *
     * @param list<Item> $items The items to convert.
     * @return list<Item> The converted items.
     * @throws StoreException If an error occurs while retrieving existing items from the store.
     * @throws ConversionException If an error occurs while converting the items.
     */
    public function convert(array $items): array
    {
        $itemIds = array_map(function (Item $item) {
            return $item->id;
        }, $items);

        $existingMap = $this->store->query(StoreQuery::forIds($itemIds))->getMap();

        $items = $this->convStrategy->beforeBatch($items, $existingMap);

        $convertedItems = [];
        foreach ($items as $item) {
            $existing = $existingMap[$item->id] ?? null;

            try {
                $item = $this->convStrategy->convert($item);

                if ($item !== null && $existing !== null) {
                    $item = $this->convStrategy->reconcile($item, $existing);
                }

                if ($item !== null) {
                    $item = $this->convStrategy->finalize($item);
                }
            } catch (ConversionShortCircuit $e) {
                $item = $e->getItem();
                break;
            } finally {
                if ($item !== null) {
                    $convertedItems[] = $item;
                }
            }
        }

        return $this->convStrategy->afterBatch($convertedItems);
    }

    /**
     * Fetches items, converts them, and imports them into the store.
     *
     * @throws InvalidSourceException If the catalog rejected the source in the query.
     * @throws FetchException If an error occurred while fetching the items.
     * @throws ConversionException If an error occurred while converting the items.
     * @throws StoreException If an error occurred while importing the items into the store.
     */
    public function import(FetchQuery $query): FetchResult
    {
        $result = $this->fetch($query);
        $items = $this->store->insert($result->items)->getItems();

        return new FetchResult(
            $items,
            $result->source,
            $result->catalogSize,
            $result->nextCursor,
            $result->prevCursor,
            $result->errors
        );
    }

    /**
     * Aggregates items from the store for a feed.
     *
     * @param Feed $feed The feed to aggregate items for.
     * @param int|null $count The maximum number of items to aggregate, or null to get all the items.
     * @param int $offset The offset of the first item to aggregate.
     * @return AggregateResult The aggregation result.
     * @throws StoreException If an error occurred while reading items from the store.
     */
    public function aggregate(Feed $feed, ?int $count = null, int $offset = 0): AggregateResult
    {
        $query = $this->aggStrategy->getFeedQuery($feed, $count, $offset);

        if ($query === null) {
            return new AggregateResult([], 0, 0, 0);
        }

        if ($this->aggStrategy->doManualPagination($feed, $query)) {
            $storeQuery = $query->withoutPagination();
            $resultOffset = $query->offset;
        } else {
            $storeQuery = $query;
            $resultOffset = 0;
        }

        $items = $this->store->query($storeQuery)->getUnique();

        $preProcessor = $this->aggStrategy->getPreProcessor($feed, $query) ?? new NoopItemProcessor();
        $postProcessor = $this->aggStrategy->getPostProcessor($feed, $query) ?? new NoopItemProcessor();

        $preItems = $preProcessor->process($items, $feed, $query);
        $postItems = $postProcessor->process($preItems, $feed, $query);

        $finalItems = array_slice($postItems, $resultOffset, $query->count);

        return new AggregateResult($finalItems, count($items), count($preItems), count($postItems));
    }
}
