<?php

declare(strict_types=1);

namespace RebelCode\IrisEngine\Test\Func\Data;

use PHPUnit\Framework\TestCase;
use RebelCode\IrisEngine\Data\ImmutableDataObject;
use RebelCode\IrisEngine\Data\Item;
use RebelCode\IrisEngine\Data\Source;

class ItemTest extends TestCase
{
    public function testIsImmutableDataObject()
    {
        $item = new Item('', 0, []);

        self::assertInstanceOf(ImmutableDataObject::class, $item);
    }

    public function testConstructor()
    {
        $id = '123';
        $localId = 'abc';
        $sources = [
            $this->createMock(Source::class),
            $this->createMock(Source::class),
        ];
        $data = [
            'foo' => 'bar',
            'baz' => 'qux',
        ];

        $item = new Item($id, $localId, $sources, $data);

        self::assertEquals($id, $item->id);
        self::assertEquals($localId, $item->localId);
        self::assertSame($sources, $item->sources);
        self::assertEquals($data, $item->data);
    }
}
