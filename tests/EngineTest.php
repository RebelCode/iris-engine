<?php

declare(strict_types=1);

namespace RebelCode\Iris\Test\Func;

use PHPUnit\Framework\TestCase;
use RebelCode\Iris\Aggregator;
use RebelCode\Iris\Converter;
use RebelCode\Iris\Data\Feed;
use RebelCode\Iris\Data\Item;
use RebelCode\Iris\Data\Source;
use RebelCode\Iris\Engine;
use RebelCode\Iris\Exception\ConversionShortCircuit;
use RebelCode\Iris\Fetcher;
use RebelCode\Iris\FetchQuery;
use RebelCode\Iris\FetchResult;
use RebelCode\Iris\ItemProcessor;
use RebelCode\Iris\Store;
use RebelCode\Iris\StoreQuery;
use RebelCode\Iris\StoreResult;

class EngineTest extends TestCase
{
    protected function createEngine(
        ?Fetcher $fetcher = null,
        ?Converter $converter = null,
        ?Aggregator $aggregator = null,
        ?Store $store = null
    ): Engine {
        $fetcher = $fetcher ?? $this->createMock(Fetcher::class);
        $converter = $converter ?? $this->createMock(Converter::class);
        $aggregator = $aggregator ?? $this->createMock(Aggregator::class);
        $store = $store ?? $this->createMock(Store::class);

        return new Engine($fetcher, $converter, $aggregator, $store);
    }

    public function testConstructorAndGetters()
    {
        $fetcher = $this->createMock(Fetcher::class);
        $converter = $this->createMock(Converter::class);
        $aggregator = $this->createMock(Aggregator::class);
        $store = $this->createMock(Store::class);

        $engine = $this->createEngine($fetcher, $converter, $aggregator, $store);

        self::assertSame($fetcher, $engine->getFetcher());
        self::assertSame($converter, $engine->getConverter());
        self::assertSame($aggregator, $engine->getAggregator());
        self::assertSame($store, $engine->getStore());
    }

    public function testFetch()
    {
        $fetcher = $this->createMock(Fetcher::class);
        $converter = $this->createMock(Converter::class);
        $aggregator = $this->createMock(Aggregator::class);
        $store = $this->createMock(Store::class);
        $engine = $this->createEngine($fetcher, $converter, $aggregator, $store);

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

        $fetcher->expects($this->once())
                ->method('query')
                ->with($query->source, $query->cursor, $query->count)
                ->willReturn($fetchResult);

        $converter->method('beforeBatch')->willReturnArgument(0);
        $converter->method('afterBatch')->willReturnArgument(0);
        $converter->method('finalize')->willReturnArgument(0);

        $converter->expects($this->exactly(count($fetchItems)))
                  ->method('convert')
                  ->withConsecutive([$fetchItems[0]], [$fetchItems[1]], [$fetchItems[2]])
                  ->willReturn($convItems[0], $convItems[1], $convItems[2]);

        $result = $engine->fetch($query);

        self::assertSame($convItems, $result->items);
        self::assertSame($source, $result->source);
        self::assertEquals($nCursor, $result->nextCursor);
        self::assertEquals($pCursor, $result->prevCursor);
        self::assertEquals($errors, $result->errors);
    }

    public function testConvertMultiple()
    {
        $store = $this->createMock(Store::class);
        $strategy = $this->createMock(Converter::class);

        $source = $this->createMock(Source::class);
        $items = [
            new Item('1', 1, [$source]),
            new Item('2', 2, [$source]),
            new Item('3', 3, [$source]),
        ];

        $query = StoreQuery::forIds(['1', '2', '3']);
        $store->expects($this->once())->method('query')->with($query)->willReturn(new StoreResult([]));

        $strategy->expects($this->once())->method('beforeBatch')->willReturnArgument(0);
        $strategy->expects($this->exactly(3))->method('convert')->willReturnArgument(0);
        $strategy->expects($this->never())->method('reconcile');
        $strategy->expects($this->exactly(3))->method('finalize')->willReturnArgument(0);
        $strategy->expects($this->once())->method('afterBatch')->willReturnArgument(0);

        $engine = $this->createEngine(null, $strategy, null, $store);
        $actualItems = $engine->convert($items);

        self::assertEquals($items, $actualItems);
    }

    public function testConvertMultipleBeforeBatch()
    {
        $store = $this->createMock(Store::class);
        $strategy = $this->createMock(Converter::class);

        $source = $this->createMock(Source::class);
        $items = [
            new Item('1', 1, [$source]),
            new Item('2', 2, [$source]),
            new Item('3', 3, [$source]),
        ];
        $changed = [
            $items[0],
            new Item('4', 4, [$source]),
            $items[2],
        ];

        $query = StoreQuery::forIds(['1', '2', '3']);
        $store->expects($this->once())->method('query')->with($query)->willReturn(new StoreResult([]));

        $strategy->expects($this->once())->method('beforeBatch')->with($items)->willReturn($changed);
        $strategy->expects($this->exactly(3))->method('convert')->willReturnArgument(0);
        $strategy->expects($this->never())->method('reconcile');
        $strategy->expects($this->exactly(3))->method('finalize')->willReturnArgument(0);
        $strategy->expects($this->once())->method('afterBatch')->willReturnArgument(0);

        $engine = $this->createEngine(null, $strategy, null, $store);
        $actualItems = $engine->convert($items);

        self::assertEquals($changed, $actualItems);
    }

    public function testConvertMultipleAfterBatch()
    {
        $store = $this->createMock(Store::class);
        $strategy = $this->createMock(Converter::class);

        $source = $this->createMock(Source::class);
        $items = [
            new Item('1', 1, [$source]),
            new Item('2', 2, [$source]),
            new Item('3', 3, [$source]),
        ];
        $changed = [
            $items[0],
            new Item('4', 4, [$source]),
            $items[2],
        ];

        $query = StoreQuery::forIds(['1', '2', '3']);
        $store->expects($this->once())->method('query')->with($query)->willReturn(new StoreResult([]));

        $strategy->expects($this->once())->method('beforeBatch')->willReturnArgument(0);
        $strategy->expects($this->exactly(3))->method('convert')->willReturnArgument(0);
        $strategy->expects($this->never())->method('reconcile');
        $strategy->expects($this->exactly(3))->method('finalize')->willReturnArgument(0);
        $strategy->expects($this->once())->method('afterBatch')->with($items)->willReturn($changed);

        $engine = $this->createEngine(null, $strategy, null, $store);
        $actualItems = $engine->convert($items);

        self::assertEquals($changed, $actualItems);
    }

    public function testConvertMultipleFilteredItems()
    {
        $store = $this->createMock(Store::class);
        $strategy = $this->createMock(Converter::class);

        $source = $this->createMock(Source::class);
        $items = [
            new Item('1', 1, [$source]),
            new Item('2', 2, [$source]),
            new Item('3', 3, [$source]),
        ];
        $expected = [
            $items[0],
            $items[2],
        ];

        $query = StoreQuery::forIds(['1', '2', '3']);
        $store->expects($this->once())->method('query')->with($query)->willReturn(new StoreResult([]));

        $strategy->expects($this->once())->method('beforeBatch')->willReturnArgument(0);
        $strategy->expects($this->exactly(3))->method('convert')->willReturnOnConsecutiveCalls(
            $items[0],
            null,
            $items[2]
        );
        $strategy->expects($this->never())->method('reconcile');
        $strategy->expects($this->exactly(2))->method('finalize')->willReturnArgument(0);
        $strategy->expects($this->once())->method('afterBatch')->willReturnArgument(0);

        $engine = $this->createEngine(null, $strategy, null, $store);
        $actualItems = $engine->convert($items);

        self::assertEquals($expected, $actualItems);
    }

    public function testConvertMultipleWithReconciliation()
    {
        $store = $this->createMock(Store::class);
        $strategy = $this->createMock(Converter::class);

        $source = $this->createMock(Source::class);

        $item1 = new Item('1', 1, [$source]);
        $item2 = new Item('2', 2, [$source]);
        $item3 = new Item('3', 3, [$source]);

        $existing1 = new Item('1', 1, [$source]);
        $existing3 = new Item('3', 3, [$source]);

        $reconciled1 = new Item('1', 1, [$source]);
        $reconciled3 = new Item('3', 3, [$source]);

        $expected = [
            $reconciled1,
            $item2,
            $reconciled3,
        ];

        $query = StoreQuery::forIds(['1', '2', '3']);
        $store->expects($this->once())
              ->method('query')
              ->with($query)
              ->willReturn(new StoreResult([$existing1, $existing3]));

        $strategy->expects($this->once())->method('beforeBatch')->willReturnArgument(0);
        $strategy->expects($this->once())->method('afterBatch')->willReturnArgument(0);

        $strategy->expects($this->exactly(3))
                 ->method('convert')
                 ->withConsecutive([$item1], [$item2], [$item3])
                 ->willReturnArgument(0);
        $strategy->expects($this->exactly(2))
                 ->method('reconcile')
                 ->withConsecutive(
                     [$item1, $existing1],
                     [$item3, $existing3]
                 )
                 ->willReturnOnConsecutiveCalls(
                     $reconciled1,
                     $reconciled3
                 );

        $strategy->expects($this->exactly(3))
                 ->method('finalize')
                 ->withConsecutive(
                     [$reconciled1],
                     [$item2],
                     [$reconciled3]
                 )
                 ->willReturnArgument(0);

        $engine = $this->createEngine(null, $strategy, null, $store);
        $actualItems = $engine->convert([$item1, $item2, $item3]);

        self::assertEquals($expected, $actualItems);
    }

    public function testConvertMultipleShortCircuitNoYield()
    {
        $store = $this->createMock(Store::class);
        $strategy = $this->createMock(Converter::class);

        $source = $this->createMock(Source::class);
        $items = [
            new Item('1', 1, [$source]),
            new Item('2', 2, [$source]),
            new Item('3', 3, [$source]),
            new Item('4', 4, [$source]),
        ];
        $expected = [
            $items[0],
        ];

        $query = StoreQuery::forIds(['1', '2', '3', '4']);
        $store->expects($this->once())->method('query')->with($query)->willReturn(new StoreResult([]));

        $strategy->expects($this->once())->method('beforeBatch')->willReturnArgument(0);
        $strategy->expects($this->once())->method('afterBatch')->willReturnArgument(0);

        // Convert short-circuits after first 2 items
        $count = 0;
        $strategy->expects($this->exactly(2))->method('convert')->willReturnCallback(function ($item) use (&$count) {
            $count++;
            if ($count >= 2) {
                throw new ConversionShortCircuit();
            }
            return $item;
        });

        $strategy->expects($this->never())->method('reconcile');
        $strategy->expects($this->exactly(1))
                 ->method('finalize')
                 ->withConsecutive([$items[0]])
                 ->willReturnArgument(0);

        $engine = $this->createEngine(null, $strategy, null, $store);
        $actualItems = $engine->convert($items);

        self::assertEquals($expected, $actualItems);
    }

    public function testConvertMultipleShortCircuitYield()
    {
        $store = $this->createMock(Store::class);
        $strategy = $this->createMock(Converter::class);

        $source = $this->createMock(Source::class);
        $items = [
            new Item('1', 1, [$source]),
            new Item('2', 2, [$source]),
            new Item('3', 3, [$source]),
            new Item('4', 4, [$source]),
        ];
        $expected = [
            $items[0],
            $items[1],
        ];

        $query = StoreQuery::forIds(['1', '2', '3', '4']);
        $store->expects($this->once())->method('query')->with($query)->willReturn(new StoreResult([]));

        $strategy->expects($this->once())->method('beforeBatch')->willReturnArgument(0);
        $strategy->expects($this->once())->method('afterBatch')->willReturnArgument(0);

        // Convert short-circuits after first 2 items
        $count = 0;
        $strategy->expects($this->exactly(2))->method('convert')->willReturnCallback(function ($item) use (&$count) {
            $count++;
            if ($count >= 2) {
                throw new ConversionShortCircuit($item);
            }
            return $item;
        });

        $strategy->expects($this->never())->method('reconcile');
        $strategy->expects($this->exactly(1))
                 ->method('finalize')
                 ->withConsecutive([$items[0]])
                 ->willReturnArgument(0);

        $engine = $this->createEngine(null, $strategy, null, $store);
        $actualItems = $engine->convert($items);

        self::assertEquals($expected, $actualItems);
    }

    public function testImport()
    {
        $fetcher = $this->createMock(Fetcher::class);
        $converter = $this->createMock(Converter::class);
        $aggregator = $this->createMock(Aggregator::class);
        $store = $this->createMock(Store::class);
        $engine = $this->createEngine($fetcher, $converter, $aggregator, $store);

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

        $fetcher->expects($this->once())
                ->method('query')
                ->with($query->source, $query->cursor, $query->count)
                ->willReturn($result);

        $converter->method('beforeBatch')->willReturnArgument(0);
        $converter->method('afterBatch')->willReturnArgument(0);
        $converter->method('finalize')->willReturnArgument(0);
        $converter->method('convert')->willReturnArgument(0);

        $store->expects($this->once())->method('insert')->with($items)->willReturn(new StoreResult($storedItems));

        $result = $engine->import($query);

        self::assertSame($storedItems, $result->items);
        self::assertSame($source, $result->source);
        self::assertEquals($nCursor, $result->nextCursor);
        self::assertEquals($pCursor, $result->prevCursor);
        self::assertEquals($errors, $result->errors);
    }

    public function testAggregate()
    {
        $source1 = $this->createMock(Source::class);
        $source2 = $this->createMock(Source::class);
        $query = StoreQuery::forSources([$source1, $source2]);
        $store = $this->createMock(Store::class);
        $feed = $this->createMock(Feed::class);
        $count = 9;
        $offset = 3;

        $preProcessor = $this->createMock(ItemProcessor::class);
        $postProcessor = $this->createMock(ItemProcessor::class);

        $strategy = $this->createMock(Aggregator::class);
        $strategy->expects($this->once())->method('getFeedQuery')->with($feed, $count, $offset)->willReturn($query);
        $strategy->expects($this->once())->method('getPreProcessor')->with($feed, $query)->willReturn($preProcessor);
        $strategy->expects($this->once())->method('getPostProcessor')->with($feed, $query)->willReturn($postProcessor);

        $items = [
            new Item('1', 1, [$source1]),
            new Item('2', 2, [$source2]),
            new Item('3', 3, [$source2]),
        ];

        $store->expects($this->once())->method('query')->with($query)->willReturn(new StoreResult($items));

        $preProcessor->expects($this->once())->method('process')->with($items, $feed, $query)->willReturn($items);
        $postProcessor->expects($this->once())->method('process')->with($items, $feed, $query)->willReturn($items);

        $engine = $this->createEngine(null, null, $strategy, $store);
        $result = $engine->aggregate($feed, $count, $offset);

        self::assertSame($items, $result->items);
        self::assertEquals(count($items), $result->storeTotal);
        self::assertEquals(count($items), $result->preTotal);
        self::assertEquals(count($items), $result->postTotal);
    }

    public function testAggregateNoQuery()
    {
        $feed = $this->createMock(Feed::class);
        $store = $this->createMock(Store::class);
        $count = 9;
        $offset = 3;

        $preProcessor = $this->createMock(ItemProcessor::class);
        $postProcessor = $this->createMock(ItemProcessor::class);

        $strategy = $this->createConfiguredMock(Aggregator::class, [
            'getFeedQuery' => null,
            'getPreProcessor' => $preProcessor,
            'getPostProcessor' => $postProcessor,
        ]);

        $store->expects($this->never())->method('query');

        $preProcessor->expects($this->never())->method('process');
        $postProcessor->expects($this->never())->method('process');

        $engine = $this->createEngine(null, null, $strategy, $store);
        $result = $engine->aggregate($feed, $count, $offset);

        self::assertEmpty($result->items);
        self::assertEquals(0, $result->storeTotal);
        self::assertEquals(0, $result->preTotal);
        self::assertEquals(0, $result->postTotal);
    }

    public function testAggregateDuplicates()
    {
        $source1 = $this->createMock(Source::class);
        $source2 = $this->createMock(Source::class);
        $query = StoreQuery::forSources([$source1, $source2]);
        $store = $this->createMock(Store::class);
        $feed = $this->createMock(Feed::class);
        $count = 9;
        $offset = 3;

        $preProcessor = $this->createMock(ItemProcessor::class);
        $postProcessor = $this->createMock(ItemProcessor::class);

        $strategy = $this->createConfiguredMock(Aggregator::class, [
            'getFeedQuery' => $query,
            'getPreProcessor' => $preProcessor,
            'getPostProcessor' => $postProcessor,
        ]);

        $itemsWithDupes = [
            new Item('1', 1, [$source1]),
            new Item('1', 2, [$source2]),
            new Item('3', 3, [$source2]),
        ];
        $itemsNoDupes = [
            $itemsWithDupes[1],
            $itemsWithDupes[2],
        ];

        $store->expects($this->once())->method('query')->with($query)->willReturn(new StoreResult($itemsWithDupes));

        $preProcessor->expects($this->once())
                     ->method('process')
                     ->with($itemsNoDupes, $feed, $query)
                     ->willReturn($itemsNoDupes);
        $postProcessor->expects($this->once())
                      ->method('process')
                      ->with($itemsNoDupes, $feed, $query)
                      ->willReturn($itemsNoDupes);

        $engine = $this->createEngine(null, null, $strategy, $store);
        $result = $engine->aggregate($feed, $count, $offset);

        self::assertSame($itemsNoDupes, $result->items);
        self::assertEquals(count($itemsNoDupes), $result->storeTotal);
        self::assertEquals(count($itemsNoDupes), $result->preTotal);
        self::assertEquals(count($itemsNoDupes), $result->postTotal);
    }

    public function testAggregatePreProcessorsRemoveItems()
    {
        $source1 = $this->createMock(Source::class);
        $source2 = $this->createMock(Source::class);
        $query = StoreQuery::forSources([$source1, $source2]);
        $store = $this->createMock(Store::class);
        $feed = $this->createMock(Feed::class);
        $count = 9;
        $offset = 3;

        $preProcessor = $this->createMock(ItemProcessor::class);
        $postProcessor = $this->createMock(ItemProcessor::class);

        $strategy = $this->createConfiguredMock(Aggregator::class, [
            'getFeedQuery' => $query,
            'getPreProcessor' => $preProcessor,
            'getPostProcessor' => $postProcessor,
        ]);

        $storeItems = [
            new Item('1', 1, [$source1]),
            new Item('2', 2, [$source2]),
            new Item('3', 3, [$source2]),
        ];
        $processedItems = [
            $storeItems[0],
            $storeItems[2],
        ];

        $store->expects($this->once())->method('query')->with($query)->willReturn(new StoreResult($storeItems));

        $preProcessor->expects($this->once())
                     ->method('process')
                     ->with($storeItems, $feed, $query)
                     ->willReturn($processedItems);
        $postProcessor->expects($this->once())
                      ->method('process')
                      ->with($processedItems, $feed, $query)
                      ->willReturn($processedItems);

        $engine = $this->createEngine(null, null, $strategy, $store);
        $result = $engine->aggregate($feed, $count, $offset);

        self::assertSame($processedItems, $result->items);
        self::assertEquals(count($storeItems), $result->storeTotal);
        self::assertEquals(count($processedItems), $result->preTotal);
        self::assertEquals(count($processedItems), $result->postTotal);
    }

    public function testAggregatePostProcessorsModifyItems()
    {
        $source1 = $this->createMock(Source::class);
        $source2 = $this->createMock(Source::class);
        $query = StoreQuery::forSources([$source1, $source2]);
        $store = $this->createMock(Store::class);
        $feed = $this->createMock(Feed::class);
        $count = 9;
        $offset = 3;

        $preProcessor = $this->createMock(ItemProcessor::class);
        $postProcessor = $this->createMock(ItemProcessor::class);

        $strategy = $this->createConfiguredMock(Aggregator::class, [
            'getFeedQuery' => $query,
            'getPreProcessor' => $preProcessor,
            'getPostProcessor' => $postProcessor,
        ]);

        $storeItems = [
            new Item('1', 1, [$source1]),
            new Item('2', 2, [$source2]),
            new Item('3', 3, [$source2]),
        ];
        $processedItems = [
            $storeItems[0],
            $storeItems[2],
        ];

        $store->expects($this->once())->method('query')->with($query)->willReturn(new StoreResult($storeItems));

        $preProcessor->expects($this->once())
                     ->method('process')
                     ->with($storeItems, $feed, $query)
                     ->willReturn($storeItems);
        $postProcessor->expects($this->once())
                      ->method('process')
                      ->with($storeItems, $feed, $query)
                      ->willReturn($processedItems);

        $engine = $this->createEngine(null, null, $strategy, $store);
        $result = $engine->aggregate($feed, $count, $offset);

        self::assertSame($processedItems, $result->items);
        self::assertEquals(count($storeItems), $result->storeTotal);
        self::assertEquals(count($storeItems), $result->preTotal);
        self::assertEquals(count($processedItems), $result->postTotal);
    }

    public function testAggregateBothProcessorsModifyItems()
    {
        $source1 = $this->createMock(Source::class);
        $source2 = $this->createMock(Source::class);
        $query = StoreQuery::forSources([$source1, $source2]);
        $store = $this->createMock(Store::class);
        $feed = $this->createMock(Feed::class);
        $count = 9;
        $offset = 3;

        $preProcessor = $this->createMock(ItemProcessor::class);
        $postProcessor = $this->createMock(ItemProcessor::class);

        $strategy = $this->createConfiguredMock(Aggregator::class, [
            'getFeedQuery' => $query,
            'getPreProcessor' => $preProcessor,
            'getPostProcessor' => $postProcessor,
        ]);

        $storeItems = [
            new Item('1', 1, [$source1]),
            new Item('2', 2, [$source2]),
            new Item('3', 3, [$source2]),
        ];
        $preProcessedItems = [
            $storeItems[0],
            $storeItems[2],
        ];
        $postProcessedItems = [
            $preProcessedItems[0],
        ];

        $store->expects($this->once())->method('query')->with($query)->willReturn(new StoreResult($storeItems));

        $preProcessor->expects($this->once())
                     ->method('process')
                     ->with($storeItems, $feed, $query)
                     ->willReturn($preProcessedItems);
        $postProcessor->expects($this->once())
                      ->method('process')
                      ->with($preProcessedItems, $feed, $query)
                      ->willReturn($postProcessedItems);

        $engine = $this->createEngine(null, null, $strategy, $store);
        $result = $engine->aggregate($feed, $count, $offset);

        self::assertSame($postProcessedItems, $result->items);
        self::assertEquals(count($storeItems), $result->storeTotal);
        self::assertEquals(count($preProcessedItems), $result->preTotal);
        self::assertEquals(count($postProcessedItems), $result->postTotal);
    }

    public function testAggregateTruncateItems()
    {
        $count = 3;
        $offset = 0;

        $source1 = $this->createMock(Source::class);
        $source2 = $this->createMock(Source::class);
        $query = StoreQuery::forSources([$source1, $source2])->withCount($count)->withOffset($offset);
        $store = $this->createMock(Store::class);
        $feed = $this->createMock(Feed::class);

        $preProcessor = $this->createMock(ItemProcessor::class);

        $strategy = $this->createConfiguredMock(Aggregator::class, [
            'getFeedQuery' => $query,
            'getPreProcessor' => $preProcessor,
            'getPostProcessor' => null,
            'doManualPagination' => false,
        ]);

        $newItem = new Item('4', 4, [$source1]);

        $storeItems = [
            new Item('1', 1, [$source1]),
            new Item('2', 2, [$source2]),
            new Item('3', 3, [$source2]),
        ];
        $processedItems = [
            $storeItems[0],
            $newItem,
            $storeItems[1],
            $storeItems[2],
        ];
        $finalItems = [
            $processedItems[0],
            $processedItems[1],
            $processedItems[2],
        ];

        $store->expects($this->once())->method('query')->with($query)->willReturn(new StoreResult($storeItems));

        $preProcessor->expects($this->once())
                     ->method('process')
                     ->with($storeItems, $feed, $query)
                     ->willReturn($processedItems);

        $engine = $this->createEngine(null, null, $strategy, $store);
        $result = $engine->aggregate($feed, $count, $offset);

        self::assertSame($finalItems, $result->items);
        self::assertEquals(count($storeItems), $result->storeTotal);
        self::assertEquals(count($processedItems), $result->preTotal);
        self::assertEquals(count($processedItems), $result->postTotal);
    }

    public function testAggregateOffsetItems()
    {
        $count = 2;
        $offset = 1;

        $source1 = $this->createMock(Source::class);
        $source2 = $this->createMock(Source::class);
        $query = StoreQuery::forSources([$source1, $source2])->withCount($count)->withOffset($offset);
        $storeQuery = StoreQuery::forSources([$source1, $source2]);
        $store = $this->createMock(Store::class);
        $feed = $this->createMock(Feed::class);

        $strategy = $this->createMock(Aggregator::class);
        $strategy->expects($this->once())->method('getFeedQuery')->with($feed, $count, $offset)->willReturn($query);
        $strategy->expects($this->once())->method('getPreProcessor')->with($feed, $query)->willReturn(null);
        $strategy->expects($this->once())->method('getPostProcessor')->with($feed, $query)->willReturn(null);
        $strategy->expects($this->once())->method('doManualPagination')->with($feed, $query)->willReturn(true);

        $storeItems = [
            new Item('1', 1, [$source1]),
            new Item('2', 2, [$source2]),
            new Item('3', 3, [$source2]),
            new Item('4', 4, [$source2]),
        ];
        $finalItems = [
            $storeItems[1],
            $storeItems[2],
        ];

        $store->expects($this->once())->method('query')->with($storeQuery)->willReturn(new StoreResult($storeItems));

        $engine = $this->createEngine(null, null, $strategy, $store);
        $result = $engine->aggregate($feed, $count, $offset);

        self::assertSame($finalItems, $result->items);
        self::assertEquals(count($storeItems), $result->storeTotal);
        self::assertEquals(count($storeItems), $result->preTotal);
        self::assertEquals(count($storeItems), $result->postTotal);
    }
}
