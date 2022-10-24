<?php

declare(strict_types=1);

namespace RebelCode\Iris;

use RebelCode\Iris\Data\Source;
use RebelCode\Iris\Exception\FetchException;
use RebelCode\Iris\Exception\InvalidSourceException;
use RebelCode\Iris\Fetcher\FetchResult;

interface Catalog
{
    /**
     * @psalm-mutation-free
     *
     * @throws FetchException
     * @throws InvalidSourceException
     */
    public function query(Source $source, ?string $cursor = null, ?int $count = null): FetchResult;
}
