<?php

declare(strict_types=1);

namespace RebelCode\IrisEngine\Test\Func\Aggregator;

use PHPUnit\Framework\TestCase;
use RebelCode\IrisEngine\Aggregator\AggregateResult;
use RebelCode\IrisEngine\Data\Item;

class AggregateResultTest extends TestCase
{
    public function testConstructor()
    {
        $items = [
            $this->createMock(Item::class),
            $this->createMock(Item::class),
            $this->createMock(Item::class),
        ];

        $total = 10;

        $result = new AggregateResult($items, $total);

        self::assertSame($items, $result->items);
        self::assertEquals($total, $result->total);
    }
}
