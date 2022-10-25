<?php

declare(strict_types=1);

namespace RebelCode\Iris\Test\Func;

use PHPUnit\Framework\TestCase;
use RebelCode\Iris\Data\Item;
use RebelCode\Iris\Data\Source;
use RebelCode\Iris\FetchResult;

class FetchResultTest extends TestCase
{
    public function testConstructor()
    {
        $items = [
            $this->createMock(Item::class),
            $this->createMock(Item::class),
            $this->createMock(Item::class),
        ];

        $source = $this->createMock(Source::class);
        $catalogSize = 100;
        $nextCursor = 'DEF456';
        $prevCursor = 'ABC123';
        $errors = [
            'Whoops!',
            'Something is not quite right',
        ];

        $result = new FetchResult($items, $source, $catalogSize, $nextCursor, $prevCursor, $errors);

        self::assertSame($items, $result->items);
        self::assertSame($source, $result->source);
        self::assertEquals($catalogSize, $result->catalogSize);
        self::assertEquals($nextCursor, $result->nextCursor);
        self::assertEquals($prevCursor, $result->prevCursor);
        self::assertEquals($errors, $result->errors);
    }
}
