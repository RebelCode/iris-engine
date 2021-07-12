<?php

declare(strict_types=1);

namespace RebelCode\IrisEngine\Test\Func\Data;

use PHPUnit\Framework\TestCase;
use RebelCode\IrisEngine\Data\ImmutableDataObject;
use RebelCode\IrisEngine\Data\Source;

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
}
