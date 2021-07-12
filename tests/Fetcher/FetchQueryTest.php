<?php

declare(strict_types=1);

namespace RebelCode\IrisEngine\Test\Func\Fetcher;

use PHPUnit\Framework\TestCase;
use RebelCode\IrisEngine\Data\Source;
use RebelCode\IrisEngine\Fetcher\FetchQuery;
use RebelCode\IrisEngine\Fetcher\FetchResult;

class FetchQueryTest extends TestCase
{
    public function testConstructor()
    {
        $source = $this->createMock(Source::class);
        $cursor = 'ABC123';
        $count = 10;

        $query = new FetchQuery($source, $cursor, $count);

        self::assertSame($source, $query->source);
        self::assertEquals($cursor, $query->cursor);
        self::assertEquals($count, $query->count);
    }

    public function testForNextBatch()
    {
        $source = $this->createMock(Source::class);
        $cursor = 'ABC123';
        $nextCursor = 'DEF456';
        $count = 10;

        $query = new FetchQuery($source, $cursor, $count);
        $result = new FetchResult([], $source, 100, $nextCursor);

        $actual = $query->forNextBatch($result);

        self::assertSame($source, $actual->source);
        self::assertEquals($nextCursor, $actual->cursor);
        self::assertEquals($count, $actual->count);
    }
}
