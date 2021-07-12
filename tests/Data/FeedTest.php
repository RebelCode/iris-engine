<?php

declare(strict_types=1);

namespace RebelCode\IrisEngine\Test\Func\Data;

use PHPUnit\Framework\TestCase;
use RebelCode\IrisEngine\Data\Feed;
use RebelCode\IrisEngine\Data\ImmutableDataObject;
use RebelCode\IrisEngine\Data\Source;

class FeedTest extends TestCase
{
    public function testIsImmutableDataObject()
    {
        $feed = new Feed(0, [], []);

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

        $item = new Feed($id, $sources, $data);

        self::assertEquals($id, $item->id);
        self::assertSame($sources, $item->sources);
        self::assertEquals($data, $item->data);
    }
}
