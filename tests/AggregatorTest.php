<?php

declare(strict_types=1);

namespace RebelCode\IrisEngine\Test\Func;

use PHPUnit\Framework\TestCase;
use RebelCode\IrisEngine\Aggregator;
use RebelCode\IrisEngine\Aggregator\AggregationStrategy;
use RebelCode\IrisEngine\Aggregator\ItemProcessor;
use RebelCode\IrisEngine\Data\Feed;
use RebelCode\IrisEngine\Data\Item;
use RebelCode\IrisEngine\Data\Source;
use RebelCode\IrisEngine\Store;
use RebelCode\IrisEngine\Store\Query;

class AggregatorTest extends TestCase
{
    public function testAggregate()
    {
        $source1 = $this->createMock(Source::class);
        $source2 = $this->createMock(Source::class);
        $query = new Query([$source1, $source2]);
        $store = $this->createMock(Store::class);
        $feed = $this->createMock(Feed::class);
        $count = 9;
        $offset = 3;

        $preProcessors = [
            $this->createMock(ItemProcessor::class),
            $this->createMock(ItemProcessor::class),
        ];
        $postProcessors = [
            $this->createMock(ItemProcessor::class),
            $this->createMock(ItemProcessor::class),
        ];

        $strategy = $this->createMock(AggregationStrategy::class);
        $strategy->expects($this->once())->method('getFeedQuery')->with($feed, $count, $offset)->willReturn($query);
        $strategy->expects($this->once())
                 ->method('getPreProcessors')
                 ->with($feed, $query)
                 ->willReturn($preProcessors);
        $strategy->expects($this->once())
                 ->method('getPostProcessors')
                 ->with($feed, $query)
                 ->willReturn($postProcessors);

        $items = [
            new Item('1', 1, [$source1]),
            new Item('2', 2, [$source2]),
            new Item('3', 3, [$source2]),
        ];

        $store->expects($this->once())->method('query')->with($query)->willReturn($items);

        foreach ($preProcessors as $processor) {
            $processor->expects($this->once())->method('process')->with($items, $feed, $query);
        }

        foreach ($postProcessors as $processor) {
            $processor->expects($this->once())->method('process')->with($items, $feed, $query);
        }

        $aggregator = new Aggregator($store, $strategy);
        $result = $aggregator->aggregate($feed, $count, $offset);

        self::assertSame($items, $result->items);
        self::assertEquals(count($items), $result->total);
    }

    public function testAggregateNoQuery()
    {
        $feed = $this->createMock(Feed::class);
        $store = $this->createMock(Store::class);
        $count = 9;
        $offset = 3;

        $preProcessors = [
            $this->createMock(ItemProcessor::class),
            $this->createMock(ItemProcessor::class),
        ];
        $postProcessors = [
            $this->createMock(ItemProcessor::class),
            $this->createMock(ItemProcessor::class),
        ];

        $strategy = $this->createConfiguredMock(AggregationStrategy::class, [
            'getFeedQuery' => null,
            'getPreProcessors' => $preProcessors,
            'getPostProcessors' => $postProcessors,
        ]);

        $store->expects($this->never())->method('query');

        foreach ($preProcessors as $processor) {
            $processor->expects($this->never())->method('process');
        }

        foreach ($postProcessors as $processor) {
            $processor->expects($this->never())->method('process');
        }

        $aggregator = new Aggregator($store, $strategy);
        $result = $aggregator->aggregate($feed, $count, $offset);

        self::assertEmpty($result->items);
        self::assertEquals(0, $result->total);
    }

    public function testAggregateDuplicates()
    {
        $source1 = $this->createMock(Source::class);
        $source2 = $this->createMock(Source::class);
        $query = new Query([$source1, $source2]);
        $store = $this->createMock(Store::class);
        $feed = $this->createMock(Feed::class);
        $count = 9;
        $offset = 3;

        $preProcessors = [
            $this->createMock(ItemProcessor::class),
            $this->createMock(ItemProcessor::class),
        ];
        $postProcessors = [
            $this->createMock(ItemProcessor::class),
            $this->createMock(ItemProcessor::class),
        ];

        $strategy = $this->createConfiguredMock(AggregationStrategy::class, [
            'getFeedQuery' => $query,
            'getPreProcessors' => $preProcessors,
            'getPostProcessors' => $postProcessors,
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

        $store->expects($this->once())->method('query')->with($query)->willReturn($itemsWithDupes);

        foreach ($preProcessors as $processor) {
            $processor->expects($this->once())->method('process')->with($itemsNoDupes, $feed, $query);
        }

        foreach ($postProcessors as $processor) {
            $processor->expects($this->once())->method('process')->with($itemsNoDupes, $feed, $query);
        }

        $aggregator = new Aggregator($store, $strategy);
        $result = $aggregator->aggregate($feed, $count, $offset);

        self::assertSame($itemsNoDupes, $result->items);
        self::assertEquals(count($itemsNoDupes), $result->total);
    }

    public function testAggregatePreProcessorsRemoveItems()
    {
        $source1 = $this->createMock(Source::class);
        $source2 = $this->createMock(Source::class);
        $query = new Query([$source1, $source2]);
        $store = $this->createMock(Store::class);
        $feed = $this->createMock(Feed::class);
        $count = 9;
        $offset = 3;

        $preProcessors = [
            new class implements ItemProcessor {
                public function process(array &$items, Feed $feed, Query $query): void
                {
                    unset($items[1]);
                    $items = array_values($items);
                }
            },
            $this->createMock(ItemProcessor::class),
        ];

        $postProcessors = [
            $this->createMock(ItemProcessor::class),
            $this->createMock(ItemProcessor::class),
        ];

        $strategy = $this->createConfiguredMock(AggregationStrategy::class, [
            'getFeedQuery' => $query,
            'getPreProcessors' => $preProcessors,
            'getPostProcessors' => $postProcessors,
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

        $store->expects($this->once())->method('query')->with($query)->willReturn($storeItems);

        $preProcessors[1]->expects($this->once())->method('process')->with($processedItems, $feed, $query);
        foreach ($postProcessors as $processor) {
            $processor->expects($this->once())->method('process')->with($processedItems, $feed, $query);
        }

        $aggregator = new Aggregator($store, $strategy);
        $result = $aggregator->aggregate($feed, $count, $offset);

        self::assertSame($processedItems, $result->items);
        self::assertEquals(count($processedItems), $result->total);
    }

    public function testAggregatePostProcessorsModifyItems()
    {
        $source1 = $this->createMock(Source::class);
        $source2 = $this->createMock(Source::class);
        $query = new Query([$source1, $source2]);
        $store = $this->createMock(Store::class);
        $feed = $this->createMock(Feed::class);
        $count = 9;
        $offset = 3;

        $preProcessors = [
            $this->createMock(ItemProcessor::class),
            $this->createMock(ItemProcessor::class),
        ];

        $postProcessors = [
            new class implements ItemProcessor {
                public function process(array &$items, Feed $feed, Query $query): void
                {
                    unset($items[1]);
                    $items = array_values($items);
                }
            },
            $this->createMock(ItemProcessor::class),
        ];

        $strategy = $this->createConfiguredMock(AggregationStrategy::class, [
            'getFeedQuery' => $query,
            'getPreProcessors' => $preProcessors,
            'getPostProcessors' => $postProcessors,
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

        $store->expects($this->once())->method('query')->with($query)->willReturn($storeItems);

        foreach ($preProcessors as $processor) {
            $processor->expects($this->once())->method('process')->with($storeItems, $feed, $query);
        }
        $postProcessors[1]->expects($this->once())->method('process')->with($processedItems, $feed, $query);

        $aggregator = new Aggregator($store, $strategy);
        $result = $aggregator->aggregate($feed, $count, $offset);

        self::assertSame($processedItems, $result->items);
        self::assertEquals(count($storeItems), $result->total);
    }

    public function testAggregateBothProcessorsModifyItems()
    {
        $source1 = $this->createMock(Source::class);
        $source2 = $this->createMock(Source::class);
        $query = new Query([$source1, $source2]);
        $store = $this->createMock(Store::class);
        $feed = $this->createMock(Feed::class);
        $count = 9;
        $offset = 3;

        $removeProcessor = new class implements ItemProcessor {
            public function process(array &$items, Feed $feed, Query $query): void
            {
                unset($items[1]);
                $items = array_values($items);
            }
        };

        $preProcessors = [
            $removeProcessor,
            $this->createMock(ItemProcessor::class),
        ];

        $postProcessors = [
            $removeProcessor,
            $this->createMock(ItemProcessor::class),
        ];

        $strategy = $this->createConfiguredMock(AggregationStrategy::class, [
            'getFeedQuery' => $query,
            'getPreProcessors' => $preProcessors,
            'getPostProcessors' => $postProcessors,
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

        $store->expects($this->once())->method('query')->with($query)->willReturn($storeItems);

        $preProcessors[1]->expects($this->once())->method('process')->with($preProcessedItems, $feed, $query);
        $postProcessors[1]->expects($this->once())->method('process')->with($postProcessedItems, $feed, $query);

        $aggregator = new Aggregator($store, $strategy);
        $result = $aggregator->aggregate($feed, $count, $offset);

        self::assertSame($postProcessedItems, $result->items);
        self::assertEquals(count($preProcessedItems), $result->total);
    }

    public function testAggregateTruncateItems()
    {
        $count = 3;
        $offset = 0;

        $source1 = $this->createMock(Source::class);
        $source2 = $this->createMock(Source::class);
        $query = new Query([$source1, $source2], null, null, $count, $offset);
        $store = $this->createMock(Store::class);
        $feed = $this->createMock(Feed::class);

        $newItem = new Item('4', 4, [$source1]);

        $addProcessor = new class($newItem) implements ItemProcessor {
            protected $newItem;

            public function __construct($newItem) {
                $this->newItem = $newItem;
            }

            public function process(array &$items, Feed $feed, Query $query): void
            {
                array_splice($items, 1, 0, [$this->newItem]);
            }
        };

        $preProcessors = [
            $addProcessor,
            $this->createMock(ItemProcessor::class),
        ];
        $postProcessors = [
            $addProcessor,
            $this->createMock(ItemProcessor::class),
        ];

        $strategy = $this->createConfiguredMock(AggregationStrategy::class, [
            'getFeedQuery' => $query,
            'getPreProcessors' => $preProcessors,
            'getPostProcessors' => $postProcessors,
        ]);

        $storeItems = [
            new Item('1', 1, [$source1]),
            new Item('2', 2, [$source2]),
            new Item('3', 3, [$source2]),
        ];
        $preProcessedItems = [
            $storeItems[0],
            $newItem,
            $storeItems[1],
            $storeItems[2],
        ];
        $postProcessedItems = [
            $storeItems[0],
            $newItem,
            $newItem,
            $storeItems[1],
            $storeItems[2],
        ];
        $finalItems = [
            $postProcessedItems[0],
            $postProcessedItems[1],
            $postProcessedItems[2],
        ];

        $store->expects($this->once())->method('query')->with($query)->willReturn($storeItems);

        $preProcessors[1]->expects($this->once())->method('process')->with($preProcessedItems, $feed, $query);
        $postProcessors[1]->expects($this->once())->method('process')->with($postProcessedItems, $feed, $query);

        $aggregator = new Aggregator($store, $strategy);
        $result = $aggregator->aggregate($feed, $count, $offset);

        self::assertSame($finalItems, $result->items);
        self::assertEquals(count($preProcessedItems), $result->total);
    }
}
