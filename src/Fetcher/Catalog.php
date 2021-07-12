<?php

declare(strict_types=1);

namespace RebelCode\IrisEngine\Fetcher;

use RebelCode\IrisEngine\Data\Source;
use RebelCode\IrisEngine\Exception\FetchException;
use RebelCode\IrisEngine\Exception\InvalidSourceException;

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
