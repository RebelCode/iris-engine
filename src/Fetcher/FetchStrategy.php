<?php

declare(strict_types=1);

namespace RebelCode\IrisEngine\Fetcher;

use RebelCode\IrisEngine\Data\Source;

interface FetchStrategy
{
    /**
     * Retrieves the resource from which items for a specific source should be fetched from.
     *
     * @psalm-mutation-free
     */
    public function getCatalog(Source $source): ?Catalog;
}
