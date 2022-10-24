<?php

declare(strict_types=1);

namespace RebelCode\Iris;

use RebelCode\Iris\Exception\ConversionException;
use RebelCode\Iris\Exception\FetchException;
use RebelCode\Iris\Exception\InvalidSourceException;
use RebelCode\Iris\Exception\StoreException;

class Engine
{
    /** @var FetchStrategy */
    protected $fetchStrategy;

    /** @var Converter */
    protected $converter;

    /** @var Aggregator */
    protected $aggregator;

    /** @var Store */
    protected $store;

    /**
     * Constructor.
     *
     * @param FetchStrategy $fetchStrategy The strategy to use for fetching, mainly responsible for providing a catalog.
     * @param Converter $converter The converter, used to convert fetched items into the desired data format and layout.
     * @param Aggregator $aggregator The aggregator, used to query and aggregate imported items.
     * @param Store $store The store, used to save fetched items to persistent storage.
     */
    public function __construct(
        FetchStrategy $fetchStrategy,
        Converter $converter,
        Aggregator $aggregator,
        Store $store
    ) {
        $this->fetchStrategy = $fetchStrategy;
        $this->converter = $converter;
        $this->aggregator = $aggregator;
        $this->store = $store;
    }

    public function getConverter(): Converter
    {
        return $this->converter;
    }

    public function getAggregator(): Aggregator
    {
        return $this->aggregator;
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
        $convItems = $this->converter->convertMultiple($result->items);

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
        $items = $this->store->insert($result->items);

        return new FetchResult(
            $items,
            $result->source,
            $result->catalogSize,
            $result->nextCursor,
            $result->prevCursor,
            $result->errors
        );
    }
}
