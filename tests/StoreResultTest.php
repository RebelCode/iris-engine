<?php

namespace RebelCode\Iris\Test\Func;

use PHPUnit\Framework\TestCase;
use RebelCode\Iris\Data\Item;
use RebelCode\Iris\StoreResult;

class StoreResultTest extends TestCase
{
    public function testGetItems()
    {
        $items = [
            new Item('1', 1, []),
            new Item('2', 2, []),
            new Item('3', 3, []),
        ];

        $result = new StoreResult($items);

        $this->assertSame($items, $result->getItems());
    }

    public function testGetGenerator()
    {
        $items = [
            new Item('1', 1, []),
            new Item('2', 2, []),
            new Item('3', 3, []),
        ];

        $result = new StoreResult($items);

        $this->assertSame($items, iterator_to_array($result->getGenerator()));
    }

    public function provideDataForGetFirstTest(): array
    {
        $items = [
            new Item('1', 1, []),
            new Item('2', 2, []),
            new Item('3', 3, []),
        ];

        return [
            'empty list' => [[], null],
            'list of items' => [$items, $items[0]],
        ];
    }

    /** @dataProvider provideDataForGetFirstTest */
    public function testGetFirst(array $items, ?Item $expected)
    {
        $result = new StoreResult($items);

        $this->assertEquals($expected, $result->getFirst());
    }

    public function testGetMap()
    {
        $items = [
            new Item('1', 1, []),
            new Item('2', 2, []),
            new Item('3', 3, []),
        ];

        $expected = [
            '1' => $items[0],
            '2' => $items[1],
            '3' => $items[2],
        ];

        $result = new StoreResult($items);

        $this->assertSame($expected, $result->getMap());
    }

    public function provideDataForGetItem(): array
    {
        $items = [
            new Item('1', 1, []),
            new Item('2', 2, []),
            new Item('3', 3, []),
        ];

        return [
            'empty list' => [[], '1', null],
            'valid id' => [$items, '2', $items[1]],
            'invalid id' => [$items, '4', null,],
        ];
    }

    /** @dataProvider provideDataForGetItem */
    public function testGetItem(array $items, string $id, ?Item $expected)
    {
        $result = new StoreResult($items);

        $this->assertSame($expected, $result->getItem($id));
    }
}
