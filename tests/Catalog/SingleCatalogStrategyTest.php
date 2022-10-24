<?php

namespace RebelCode\Iris\Test\Func\Catalog;

use PHPUnit\Framework\TestCase;
use RebelCode\Iris\Catalog;
use RebelCode\Iris\Catalog\SingleCatalogStrategy;
use RebelCode\Iris\Data\Source;
use RebelCode\Iris\FetchStrategy;

class SingleCatalogStrategyTest extends TestCase
{
    public function testImplementsFetchStrategy()
    {
        $catalog = $this->createMock(Catalog::class);
        $this->assertInstanceOf(FetchStrategy::class, new SingleCatalogStrategy($catalog));
    }

    public function testGetCatalog()
    {
        $catalog = $this->createMock(Catalog::class);
        $source = $this->createMock(Source::class);
        $strategy = new SingleCatalogStrategy($catalog);

        $this->assertSame($catalog, $strategy->getCatalog($source));
    }
}
