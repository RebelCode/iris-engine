<?php

declare(strict_types=1);

namespace RebelCode\Iris\Catalog;

use RebelCode\Iris\Catalog;
use RebelCode\Iris\Data\Source;
use RebelCode\Iris\FetchStrategy;

/**
 * A fetch strategy that always uses the same catalog.
 *
 * @psalm-immutable
 */
class SingleCatalogStrategy implements FetchStrategy
{
    /** @var Catalog */
    protected $catalog;

    /**
     * Constructor.
     *
     * @param Catalog $catalog The catalog to use.
     */
    public function __construct(Catalog $catalog)
    {
        $this->catalog = $catalog;
    }

    /** @inheritDoc */
    public function getCatalog(Source $source): ?Catalog
    {
        return $this->catalog;
    }
}
