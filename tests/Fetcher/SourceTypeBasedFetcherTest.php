<?php

namespace RebelCode\Iris\Test\Func\Fetcher;

use PHPUnit\Framework\TestCase;
use RebelCode\Iris\Fetcher\SourceTypeBasedFetcher;
use RebelCode\Iris\Data\Source;
use RebelCode\Iris\Fetcher;
use RebelCode\Iris\FetchResult;

class SourceTypeBasedFetcherTest extends TestCase
{
    public function testImplementsFetcher()
    {
        $this->assertInstanceOf(Fetcher::class, new SourceTypeBasedFetcher([]));
    }

    public function provideDataForQueryTest(): array
    {
        $foo = $this->createMock(Fetcher::class);
        $bar = $this->createMock(Fetcher::class);

        $map = [
            'foo' => $foo,
            'bar' => $bar,
        ];

        return [
            'query "foo"' => [$map, 'foo', $foo],
            'query "bar"' => [$map, 'bar', $bar],
        ];
    }

    public function testQuery()
    {
        $source = new Source('test_source', 'foo');
        $cursor = 'cursor';
        $count = 10;

        $map = [
            'foo' => $this->createMock(Fetcher::class),
            'bar' => $this->createMock(Fetcher::class),
        ];

        $expected = $this->createMock(FetchResult::class);

        $map['foo']->expects($this->once())->method('query')->with($source, $cursor, $count)->willReturn($expected);
        $map['bar']->expects($this->never())->method('query');

        $fetcher = new SourceTypeBasedFetcher($map);
        $result = $fetcher->query($source, $cursor, $count);

        $this->assertSame($expected, $result);
    }

    public function testQueryUnknownFetcher()
    {
        $source = new Source('test_source', 'baz');
        $cursor = 'cursor';
        $count = 10;

        $map = [
            'foo' => $this->createMock(Fetcher::class),
            'bar' => $this->createMock(Fetcher::class),
        ];

        $fetcher = new SourceTypeBasedFetcher($map);
        $result = $fetcher->query($source, $cursor, $count);

        $this->assertEmpty($result->items);
        $this->assertSame($source, $result->source);
        $this->assertCount(1, $result->errors);
    }

    public function testQueryDefault()
    {
        $source = new Source('test_source', 'baz');
        $cursor = 'cursor';
        $count = 10;

        $default = $this->createMock(Fetcher::class);

        $map = [
            'foo' => $this->createMock(Fetcher::class),
            'bar' => $this->createMock(Fetcher::class),
        ];

        $fetcher = new SourceTypeBasedFetcher($map, $default);

        $expected = $this->createMock(FetchResult::class);
        $default->expects($this->once())->method('query')->with($source, $cursor, $count)->willReturn($expected);

        $result = $fetcher->query($source, $cursor, $count);

        $this->assertSame($expected, $result);
    }
}
