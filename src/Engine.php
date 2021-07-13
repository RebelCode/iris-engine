<?php

declare(strict_types=1);

namespace RebelCode\IrisEngine;

use RebelCode\IrisEngine\Exception\ConversionException;
use RebelCode\IrisEngine\Exception\FetchException;
use RebelCode\IrisEngine\Exception\InvalidSourceException;
use RebelCode\IrisEngine\Exception\StoreException;
use RebelCode\IrisEngine\Fetcher\FetchQuery;
use RebelCode\IrisEngine\Fetcher\FetchResult;

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
     */
    public function __construct(Fetcher $fetcher, Converter $converter, Aggregator $aggregator, Store $store)
    {
        $this->fetcher = $fetcher;
        $this->converter = $converter;
        $this->aggregator = $aggregator;
        $this->store = $store;
    }

    public function getFetcher(): Fetcher
    {
        return $this->fetcher;
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
     * @throws InvalidSourceException
     * @throws FetchException
     * @throws ConversionException
     * @throws StoreException
     */
    public function fetch(FetchQuery $query): FetchResult
    {
        $result = $this->fetcher->fetch($query->source, $query->cursor, $query->count);
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
     * @throws ConversionException
     * @throws FetchException
     * @throws InvalidSourceException
     * @throws StoreException
     */
    public function import(FetchQuery $query): FetchResult
    {
        $result = $this->fetch($query);

        $this->store->insertMultiple($result->items);

        return $result;
    }
}
