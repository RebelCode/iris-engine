<?php

namespace RebelCode\Iris\Test\Func\Aggregator;

use PHPUnit\Framework\TestCase;
use RebelCode\Iris\Aggregator\CompositeItemProcessor;
use RebelCode\Iris\Data\Feed;
use RebelCode\Iris\Data\Item;
use RebelCode\Iris\ItemProcessor;
use RebelCode\Iris\StoreQuery;

class CompositeItemProcessorTest extends TestCase
{
    public function testImplementsItemProcessorInterface()
    {
        $this->assertInstanceOf(ItemProcessor::class, new CompositeItemProcessor([]));
    }

    public function testProcess()
    {
        $processor = new CompositeItemProcessor([
            $p1 = $this->createMock(ItemProcessor::class),
            $p2 = $this->createMock(ItemProcessor::class),
            $p3 = $this->createMock(ItemProcessor::class),
        ]);

        $feed = $this->createMock(Feed::class);
        $query = $this->createMock(StoreQuery::class);

        $items = [
            new Item('1', 1, []),
            new Item('2', 2, []),
            new Item('3', 3, []),
            new Item('4', 4, []),
        ];

        $itemsAfterP1 = [
            new Item('1', 1, []),
            new Item('2', 2, []),
            new Item('3', 3, []),
        ];

        $itemsAfterP2 = [
            new Item('1', 1, []),
            new Item('3', 3, []),
        ];

        $itemsAfterP3 = [
            new Item('1', 1, []),
            new Item('3', 3, []),
            new Item('9', 9, []),
        ];

        $p1->expects($this->once())->method('process')->with($items, $feed, $query)->willReturn($itemsAfterP1);
        $p2->expects($this->once())->method('process')->with($itemsAfterP1, $feed, $query)->willReturn($itemsAfterP2);
        $p3->expects($this->once())->method('process')->with($itemsAfterP2, $feed, $query)->willReturn($itemsAfterP3);

        $result = $processor->process($items, $feed, $query);

        $this->assertEquals($itemsAfterP3, $result);
    }
}
