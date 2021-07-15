<?php

declare(strict_types=1);

namespace RebelCode\Iris\Test\Func\Store;

use PHPUnit\Framework\TestCase;
use RebelCode\Iris\Data\Source;
use RebelCode\Iris\Store\Query;
use RebelCode\Iris\Store\Query\Condition;
use RebelCode\Iris\Store\Query\Order;

class QueryTest extends TestCase
{
    public function testConstructor()
    {
        $sources = [
            $this->createMock(Source::class),
            $this->createMock(Source::class),
            $this->createMock(Source::class),
        ];

        $order = $this->createMock(Order::class);
        $condition = $this->createMock(Condition::class);
        $count = 10;
        $offset = 5;

        $query = new Query($sources, $order, $condition, $count, $offset);

        self::assertSame($sources, $query->sources);
        self::assertSame($order, $query->order);
        self::assertSame($condition, $query->condition);
        self::assertEquals($count, $query->count);
        self::assertEquals($offset, $query->offset);
    }
}
