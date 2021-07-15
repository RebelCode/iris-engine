<?php

declare(strict_types=1);

namespace RebelCode\Iris\Test\Func;

use PHPUnit\Framework\TestCase;
use RebelCode\Iris\Data\Source;
use RebelCode\Iris\Exception\InvalidSourceException;
use RebelCode\Iris\Fetcher;
use RebelCode\Iris\Fetcher\Catalog;
use RebelCode\Iris\Fetcher\FetchResult;
use RebelCode\Iris\Fetcher\FetchStrategy;

class FetcherTest extends TestCase
{
    public function testFetch()
    {
        $source = $this->createMock(Source::class);
        $cursor = 'abcdef';
        $count = 100;
        $catalog = $this->createMock(Catalog::class);
        $strategy = $this->createMock(FetchStrategy::class);
        $result = $this->createMock(FetchResult::class);

        $strategy->expects($this->once())->method('getCatalog')->with($source)->willReturn($catalog);
        $catalog->expects($this->once())->method('query')->with($source, $cursor, $count)->willReturn($result);

        $fetcher = new Fetcher($strategy);
        $actual = $fetcher->fetch($source, $cursor, $count);

        self::assertSame($result, $actual);
    }

    public function testFetchNoCatalog()
    {
        $this->expectException(InvalidSourceException::class);

        $source = $this->createMock(Source::class);
        $cursor = 'abcdef';
        $count = 100;
        $strategy = $this->createMock(FetchStrategy::class);

        $strategy->expects($this->once())->method('getCatalog')->with($source)->willReturn(null);

        $fetcher = new Fetcher($strategy);
        $fetcher->fetch($source, $cursor, $count);
    }
}
