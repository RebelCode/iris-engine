<?php

namespace RebelCode\Iris\Test\Func\ItemProcessor;

use PHPUnit\Framework\TestCase;
use RebelCode\Iris\ItemProcessor\NoopItemProcessor;
use RebelCode\Iris\Data\Feed;
use RebelCode\Iris\Data\Item;
use RebelCode\Iris\ItemProcessor;
use RebelCode\Iris\StoreQuery;

class NoopItemProcessorTest extends TestCase
{
    public function testImplementsItemProcessorInterface()
    {
        $this->assertInstanceOf(ItemProcessor::class, new NoopItemProcessor());
    }

    public function testProcess()
    {
        $items = [
            $this->createMock(Item::class),
            $this->createMock(Item::class),
            $this->createMock(Item::class),
        ];

        $feed = $this->createMock(Feed::class);
        $query = $this->createMock(StoreQuery::class);

        $processor = new NoopItemProcessor();

        $this->assertSame($items, $processor->process($items, $feed, $query));
    }
}
