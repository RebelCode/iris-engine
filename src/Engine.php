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
    /** @var Fetcher */
    protected $fetcher;

    /** @var Converter */
    protected $converter;

    /** @var Aggregator */
    protected $aggregator;

    /** @var Store */
    protected $store;

    /**
     * Constructor.
     *
     * @param Fetcher $fetcher The fetcher, for obtaining items for a source.
     * @param Converter $converter The converter, for converting fetched items into items to store.
     * @param Aggregator $aggregator The aggregator, for fetching items from the store for a feed.
     * @param Store $store The store, used to save fetched items to persistent storage.
     */
    public function __construct(
        Fetcher $fetcher,
        Converter $converter,
        Aggregator $aggregator,
        Store $store
    ) {
        $this->fetcher = $fetcher;
        $this->converter = $converter;
        $this->aggregator = $aggregator;
        $this->store = $store;
    }

    /** Retrieves the fetches used by the engine. */
    public function getFetcher(): Fetcher
    {
        return $this->fetcher;
    }

    /** Retrieves the converter used by the engine. */
    public function getConverter(): Converter
    {
        return $this->converter;
    }

    /** Retrieves the aggregator used by the engine. */
    public function getAggregator(): Aggregator
    {
        return $this->aggregator;
    }

    /** Retrieves the store used by the engine. */
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
        $result = $this->fetcher->query($query->source, $query->cursor, $query->count);
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
            return $item->getId();
        }, $items);

        $existingMap = $this->store->query(StoreQuery::forIds($itemIds))->getMap();

        $items = $this->converter->beforeBatch($items, $existingMap);

        $convertedItems = [];
        foreach ($items as $item) {
            $existing = $existingMap[$item->getId()] ?? null;

            try {
                $item = $this->converter->convert($item);

                if ($item !== null && $existing !== null) {
                    $item = $this->converter->reconcile($item, $existing);
                }

                if ($item !== null) {
                    $item = $this->converter->finalize($item);
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

        return $this->converter->afterBatch($convertedItems);
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
        $query = $this->aggregator->getFeedQuery($feed, $count, $offset);

        if ($query === null) {
            return new AggregateResult([], 0, 0, 0);
        }

        if ($this->aggregator->doManualPagination($feed, $query)) {
            $storeQuery = $query->withoutPagination();
            $resultOffset = $query->offset;
        } else {
            $storeQuery = $query;
            $resultOffset = 0;
        }

        $items = $this->store->query($storeQuery)->getUnique();

        $preProcessor = $this->aggregator->getPreProcessor($feed, $query) ?? new NoopItemProcessor();
        $postProcessor = $this->aggregator->getPostProcessor($feed, $query) ?? new NoopItemProcessor();

        $preItems = $preProcessor->process($items, $feed, $query);
        $postItems = $postProcessor->process($preItems, $feed, $query);

        $finalItems = array_slice($postItems, $resultOffset, $query->count);

        return new AggregateResult($finalItems, count($items), count($preItems), count($postItems));
    }
}
