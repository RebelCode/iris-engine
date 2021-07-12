<?php

declare(strict_types=1);

namespace RebelCode\IrisEngine\Test\Func\Store\Query;

use PHPUnit\Framework\TestCase;
use RebelCode\IrisEngine\Store\Query\Order;

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
}
