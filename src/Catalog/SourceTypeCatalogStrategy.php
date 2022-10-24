<?php

declare(strict_types=1);

namespace RebelCode\Iris\Catalog;

use RebelCode\Iris\Catalog;
use RebelCode\Iris\Data\Source;
use RebelCode\Iris\FetchStrategy;

/**
 * A fetch strategy implementation that selects the catalog using a mapping of source types to catalogs.
 *
 * @psalm-immutable
 */
class SourceTypeCatalogStrategy implements FetchStrategy
{
    /** @var array<string, Catalog> */
    protected $map;

    /** @var Catalog|null */
    protected $default;

    /**
     * Constructor.
     *
     * @param array<string, Catalog> $map The mapping of source types to catalogs.
     * @param Catalog|null $default The catalog to use if no mapping is found for a source type.
     */
    public function __construct(array $map, ?Catalog $default = null)
    {
        $this->map = $map;
        $this->default = $default;
    }

    /** @inheritDoc */
    public function getCatalog(Source $source): ?Catalog
    {
        return $this->map[$source->type] ?? $this->default;
    }
}
