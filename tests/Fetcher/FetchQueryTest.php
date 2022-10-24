<?php

declare(strict_types=1);

namespace RebelCode\Iris\Test\Func\Fetcher;

use PHPUnit\Framework\TestCase;
use RebelCode\Iris\Data\Item;
use RebelCode\Iris\Data\Source;
use RebelCode\Iris\FetchQuery;
use RebelCode\Iris\FetchResult;

class FetchQueryTest extends TestCase
{
    public function testConstructor()
    {
        $source = $this->createMock(Source::class);
        $cursor = 'ABC123';
        $count = 10;
        $accrual = 30;

        $query = new FetchQuery($source, $cursor, $count, $accrual);

        self::assertSame($source, $query->source);
        self::assertEquals($cursor, $query->cursor);
        self::assertEquals($count, $query->count);
        self::assertEquals($accrual, $query->accrual);
    }

    public function testForNextBatch()
    {
        $source = $this->createMock(Source::class);
        $cursor = 'ABC123';
        $nextCursor = 'DEF456';
        $count = 10;
        $accrual = 20;

        $numItems = 100;
        $item = $this->createMock(Item::class);
        $items = array_fill(0, $numItems, $item);

        $query = new FetchQuery($source, $cursor, $count, $accrual);
        $result = new FetchResult($items, $source, 100, $nextCursor);

        $actual = $query->forNextBatch($result);

        self::assertSame($source, $actual->source);
        self::assertEquals($nextCursor, $actual->cursor);
        self::assertEquals($count, $actual->count);
        self::assertEquals($accrual + $numItems, $actual->accrual);
    }
}
