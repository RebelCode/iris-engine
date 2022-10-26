<?php

declare(strict_types=1);

namespace RebelCode\Iris\Test\Func;

use PHPUnit\Framework\TestCase;
use RebelCode\Iris\AggregateResult;
use RebelCode\Iris\Data\Item;

class AggregateResultTest extends TestCase
{
    public function testConstructor()
    {
        $items = [
            $this->createMock(Item::class),
            $this->createMock(Item::class),
            $this->createMock(Item::class),
        ];

        $rawTotal = 10;
        $preTotal = 9;
        $postTotal = 7;

        $result = new AggregateResult($items, $rawTotal, $preTotal, $postTotal);

        self::assertSame($items, $result->items);
        self::assertEquals($rawTotal, $result->storeTotal);
        self::assertEquals($preTotal, $result->preTotal);
        self::assertEquals($postTotal, $result->postTotal);
    }
}
