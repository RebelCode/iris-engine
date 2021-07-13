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

    public function testWithLocalId()
    {
        $id = '123';
        $oldLocalId = 'abc';
        $newLocalId = 'def';
        $sources = [
            $this->createMock(Source::class),
            $this->createMock(Source::class),
        ];
        $data = [
            'foo' => 'bar',
            'baz' => 'qux',
        ];

        $item = new Item($id, $oldLocalId, $sources, $data);
        $newItem = $item->withLocalId($newLocalId);

        // Assert original item is unmodified
        self::assertEquals($id, $item->id);
        self::assertEquals($oldLocalId, $item->localId);
        self::assertSame($sources, $item->sources);
        self::assertEquals($data, $item->data);

        // Assert new item is a copy with only localId being different
        self::assertEquals($id, $newItem->id);
        self::assertEquals($newLocalId, $newItem->localId);
        self::assertSame($sources, $newItem->sources);
        self::assertEquals($data, $newItem->data);
    }

    public function testWithSources()
    {
        $id = '123';
        $localId = 'abc';
        $oldSources = [
            $this->createMock(Source::class),
            $this->createMock(Source::class),
        ];
        $newSources = [
            $this->createMock(Source::class),
            $this->createMock(Source::class),
        ];
        $data = [
            'foo' => 'bar',
            'baz' => 'qux',
        ];

        $item = new Item($id, $localId, $oldSources, $data);
        $newItem = $item->withSources($newSources);

        // Assert original item is unmodified
        self::assertEquals($id, $item->id);
        self::assertEquals($localId, $item->localId);
        self::assertSame($oldSources, $item->sources);
        self::assertEquals($data, $item->data);

        // Assert new item is a copy with only sources being different
        self::assertEquals($id, $newItem->id);
        self::assertEquals($localId, $newItem->localId);
        self::assertSame($newSources, $newItem->sources);
        self::assertEquals($data, $newItem->data);
    }

    public function testWithAddedSources()
    {
        $id = '123';
        $localId = 'abc';
        $oldSources = [
            $s1 = $this->createConfiguredMock(Source::class, ['__toString' => 's1']),
            $s2 = $this->createConfiguredMock(Source::class, ['__toString' => 's2']),
        ];
        $addedSources = [
            $s3 = $this->createConfiguredMock(Source::class, ['__toString' => 's3']),
            $s1,
        ];
        $expectedSources = [$s1, $s2, $s3];
        $data = [
            'foo' => 'bar',
            'baz' => 'qux',
        ];

        $item = new Item($id, $localId, $oldSources, $data);
        $newItem = $item->withAddedSources($addedSources);

        // Assert original item is unmodified
        self::assertEquals($id, $item->id);
        self::assertEquals($localId, $item->localId);
        self::assertSame($oldSources, $item->sources);
        self::assertEquals($data, $item->data);

        // Assert new item is a copy with only sources being different
        self::assertEquals($id, $newItem->id);
        self::assertEquals($localId, $newItem->localId);
        self::assertSame($expectedSources, $newItem->sources);
        self::assertEquals($data, $newItem->data);
    }
}
