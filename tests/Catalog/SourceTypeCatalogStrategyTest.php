<?php

namespace RebelCode\Iris\Test\Func\Catalog;

use PHPUnit\Framework\TestCase;
use RebelCode\Iris\Catalog;
use RebelCode\Iris\Catalog\SourceTypeCatalogStrategy;
use RebelCode\Iris\Data\Source;
use RebelCode\Iris\FetchStrategy;

class SourceTypeCatalogStrategyTest extends TestCase
{
    public function testImplementsFetchStrategy()
    {
        $this->assertInstanceOf(FetchStrategy::class, new SourceTypeCatalogStrategy([]));
    }

    public function provideDataForGetCatalogTest(): array
    {
        $foo = $this->createMock(Catalog::class);
        $bar = $this->createMock(Catalog::class);

        $map = [
            'foo' => $foo,
            'bar' => $bar,
        ];

        return [
            'get "foo" catalog' => [$map, 'foo', $foo],
            'get "bar" catalog' => [$map, 'bar', $bar],
            'get unknown catalog' => [$map, 'baz', null],
        ];
    }

    /** @dataProvider provideDataForGetCatalogTest */
    public function testGetCatalog(array $map, string $type, $expected)
    {
        $source = new Source('test_source', $type);
        $strategy = new SourceTypeCatalogStrategy($map);

        $this->assertSame($expected, $strategy->getCatalog($source));
    }

    public function testGetDefaultCatalog()
    {
        $source = new Source('test_source', 'baz');
        $default = $this->createMock(Catalog::class);
        $map = [
            'foo' => $this->createMock(Catalog::class),
            'bar' => $this->createMock(Catalog::class),
        ];

        $strategy = new SourceTypeCatalogStrategy($map, $default);

        $this->assertSame($default, $strategy->getCatalog($source));
    }
}
