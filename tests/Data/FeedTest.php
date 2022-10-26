<?php

declare(strict_types=1);

namespace RebelCode\Iris\Test\Func\Data;

use PHPUnit\Framework\TestCase;
use RebelCode\Iris\Data\Feed;
use RebelCode\Iris\Data\ImmutableDataObject;
use RebelCode\Iris\Data\Source;

class FeedTest extends TestCase
{
    public function testIsImmutableDataObject()
    {
        $feed = new Feed(0, []);

        self::assertInstanceOf(ImmutableDataObject::class, $feed);
    }

    public function testConstructor()
    {
        $id = '123';
        $sources = [
            $this->createMock(Source::class),
            $this->createMock(Source::class),
        ];
        $data = [
            'foo' => 'bar',
            'baz' => 'qux',
        ];

        $feed = new Feed($id, $sources, $data);

        self::assertEquals($id, $feed->id);
        self::assertSame($sources, $feed->sources);
        self::assertEquals($data, $feed->data);
    }

    public function testConstructorDefaults()
    {
        $id = '123';
        $sources = [];

        $feed = new Feed($id, $sources);

        self::assertEquals($id, $feed->id);
        self::assertSame($sources, $feed->sources);
        self::assertEquals([], $feed->data);
    }
}
