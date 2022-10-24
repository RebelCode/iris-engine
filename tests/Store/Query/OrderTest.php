<?php

declare(strict_types=1);

namespace RebelCode\Iris\Test\Func\Store\Query;

use PHPUnit\Framework\TestCase;
use RebelCode\Iris\Store\Query\Order;

class OrderTest extends TestCase
{
    public function testConstructor()
    {
        $type = Order::ASC;
        $field = 'foo';

        $order = new Order($type, $field);

        self::assertEquals($type, $order->type);
        self::assertEquals($field, $order->field);
    }

    public function testStaticAscConstructor()
    {
        $field = 'foo';
        $order = Order::asc($field);

        self::assertEquals(Order::ASC, $order->type);
        self::assertEquals($field, $order->field);
    }

    public function testStaticDescConstructor()
    {
        $field = 'foo';
        $order = Order::desc($field);

        self::assertEquals(Order::DESC, $order->type);
        self::assertEquals($field, $order->field);
    }
}
