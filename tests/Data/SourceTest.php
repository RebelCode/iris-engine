<?php

declare(strict_types=1);

namespace RebelCode\Iris\Test\Func\Data;

use PHPUnit\Framework\TestCase;
use RebelCode\Iris\Data\ImmutableDataObject;
use RebelCode\Iris\Data\Source;

class SourceTest extends TestCase
{
    public function testIsImmutableDataObject()
    {
        $source = new Source('test', 'test');

        self::assertInstanceOf(ImmutableDataObject::class, $source);
    }

    public function testConstructor()
    {
        $id = 'test';
        $type = 'foobar';
        $data = [
            'baz' => 'qux',
            'quuz' => 'quuz',
        ];

        $source = new Source($id, $type, $data);

        self::assertEquals($id, $source->id);
        self::assertEquals($type, $source->type);
        self::assertEquals($data, $source->data);
    }

    public function testToString()
    {
        $id = 'test';
        $type = 'foobar';
        $data = [
            'baz' => 'qux',
            'quuz' => 'quuz',
        ];

        $source = new Source($id, $type, $data);

        self::assertEquals($id . '||' . $type, (string) $source);
    }

    public function testFromString()
    {
        $id = 'test';
        $type = 'foobar';
        $string = $id . '||' . $type;

        $actual = Source::fromString($string);

        self::assertEquals($id, $actual->id);
        self::assertEquals($type, $actual->type);
        self::assertEmpty($actual->data);
    }
}
