<?php

declare(strict_types=1);

namespace RebelCode\Iris\Test\Func;

use PHPUnit\Framework\TestCase;
use RebelCode\Iris\Aggregator;
use RebelCode\Iris\Catalog;
use RebelCode\Iris\Converter;
use RebelCode\Iris\Data\Item;
use RebelCode\Iris\Data\Source;
use RebelCode\Iris\Engine;
use RebelCode\Iris\Fetcher\FetchQuery;
use RebelCode\Iris\Fetcher\FetchResult;
use RebelCode\Iris\FetchStrategy;
use RebelCode\Iris\Store;

class EngineTest extends TestCase
{
    public function testConstructorAndGetters()
    {
        $fetchStrategy = $this->createMock(FetchStrategy::class);
        $converter = $this->createMock(Converter::class);
        $aggregator = $this->createMock(Aggregator::class);
        $store = $this->createMock(Store::class);

        $engine = new Engine($fetchStrategy, $converter, $aggregator, $store);

        self::assertSame($converter, $engine->getConverter());
        self::assertSame($aggregator, $engine->getAggregator());
        self::assertSame($store, $engine->getStore());
    }

    public function testFetch()
    {
        $fetchStrategy = $this->createMock(FetchStrategy::class);
        $catalog = $this->createMock(Catalog::class);
        $converter = $this->createMock(Converter::class);
        $aggregator = $this->createMock(Aggregator::class);
        $store = $this->createMock(Store::class);
        $engine = new Engine($fetchStrategy, $converter, $aggregator, $store);

        $source = $this->createMock(Source::class);
        $cursor = 'ABC123';
        $count = 100;
        $query = new FetchQuery($source, $cursor, $count);

        $fetchItems = [
            $this->createMock(Item::class),
            $this->createMock(Item::class),
            $this->createMock(Item::class),
        ];

        $convItems = [
            $this->createMock(Item::class),
            $this->createMock(Item::class),
            $this->createMock(Item::class),
        ];

        $cSize = 200;
        $nCursor = 'next';
        $pCursor = 'prev';
        $errors = ['Error!'];
        $fetchResult = new FetchResult($fetchItems, $source, $cSize, $nCursor, $pCursor, $errors);

        $catalog->expects($this->once())
                ->method('query')
                ->with($query->source, $query->cursor, $query->count)
                ->willReturn($fetchResult);
        $fetchStrategy->expects($this->once())->method('getCatalog')->with($query->source)->willReturn($catalog);
        $converter->expects($this->once())->method('convertMultiple')->with($fetchItems)->willReturn($convItems);

        $result = $engine->fetch($query);

        self::assertSame($convItems, $result->items);
        self::assertSame($source, $result->source);
        self::assertEquals($nCursor, $result->nextCursor);
        self::assertEquals($pCursor, $result->prevCursor);
        self::assertEquals($errors, $result->errors);
    }

    public function testImport()
    {
        $fetchStrategy = $this->createMock(FetchStrategy::class);
        $catalog = $this->createMock(Catalog::class);
        $converter = $this->createMock(Converter::class);
        $aggregator = $this->createMock(Aggregator::class);
        $store = $this->createMock(Store::class);
        $engine = new Engine($fetchStrategy, $converter, $aggregator, $store);

        $source = $this->createMock(Source::class);
        $cursor = 'ABC123';
        $count = 100;
        $query = new FetchQuery($source, $cursor, $count);

        $items = [
            $this->createMock(Item::class),
            $this->createMock(Item::class),
            $this->createMock(Item::class),
        ];

        $storedItems = [
            $this->createMock(Item::class),
            $this->createMock(Item::class),
            $this->createMock(Item::class),
        ];

        $cSize = 200;
        $nCursor = 'next';
        $pCursor = 'prev';
        $errors = ['Error!'];
        $result = new FetchResult($items, $source, $cSize, $nCursor, $pCursor, $errors);

        $catalog->expects($this->once())
                ->method('query')
                ->with($query->source, $query->cursor, $query->count)
                ->willReturn($result);
        $fetchStrategy->expects($this->once())->method('getCatalog')->with($query->source)->willReturn($catalog);
        $converter->expects($this->once())->method('convertMultiple')->with($items)->willReturn($items);
        $store->expects($this->once())->method('insertMultiple')->with($items)->willReturn($storedItems);

        $result = $engine->import($query);

        self::assertSame($storedItems, $result->items);
        self::assertSame($source, $result->source);
        self::assertEquals($nCursor, $result->nextCursor);
        self::assertEquals($pCursor, $result->prevCursor);
        self::assertEquals($errors, $result->errors);
    }
}
