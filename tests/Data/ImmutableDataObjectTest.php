<?php

declare(strict_types=1);

namespace RebelCode\Iris\Test\Func\Data;

use PHPUnit\Framework\TestCase;
use RebelCode\Iris\Data\ImmutableDataObject;

class ImmutableDataObjectTest extends TestCase
{
    public function testConstructor()
    {
        $data = [
            'foo' => 'bar',
            'baz' => 'qux',
        ];

        $subject = new ImmutableDataObject($data);

        self::assertSame($data, $subject->data);
    }

    public function testGet()
    {
        $data = [
            'foo' => 'bar',
            'baz' => 'qux',
        ];

        $subject = new ImmutableDataObject($data);

        self::assertEquals($data['foo'], $subject->get('foo'));
    }

    public function testGetNotExists()
    {
        $data = [
            'foo' => 'bar',
            'baz' => 'qux',
        ];

        $subject = new ImmutableDataObject($data);

        self::assertNull($subject->get('quux'));
    }

    public function testGetNotExistsWithDefault()
    {
        $data = [
            'foo' => 'bar',
            'baz' => 'qux',
        ];

        $subject = new ImmutableDataObject($data);
        $default = 'default';

        self::assertEquals($default, $subject->get('quux', $default));
    }

    public function testWithAddData()
    {
        $data = [
            'foo' => 'bar',
            'baz' => 'qux',
        ];
        $newData = array_merge($data, [
            'quux' => 'quuz',
        ]);

        $subject = new ImmutableDataObject($data);
        $actual = $subject->with('quux', 'quuz');

        self::assertInstanceOf(ImmutableDataObject::class, $actual);
        self::assertEquals($newData, $actual->data);
    }

    public function testWithOverwriteData()
    {
        $data = [
            'foo' => 'bar',
            'baz' => 'qux',
        ];
        $newData = array_merge($data, [
            'baz' => 'QUUX',
        ]);

        $subject = new ImmutableDataObject($data);
        $actual = $subject->with('baz', 'QUUX');

        self::assertInstanceOf(ImmutableDataObject::class, $actual);
        self::assertEquals($newData, $actual->data);
    }

    public function testWithNoChange()
    {
        $subject = new ImmutableDataObject([
            'foo' => 'bar',
            'baz' => 'qux',
        ]);

        $actual = $subject->with('foo', 'bar');

        self::assertSame($subject, $actual);
    }

    public function testWithChanges()
    {
        $data = [
            'foo' => 'bar',
            'baz' => 'qux',
        ];
        $changes = [
            'baz' => 'QUUX',
            'quuz' => 'corge',
        ];
        $newData = array_merge($data, $changes);

        $subject = new ImmutableDataObject($data);
        $actual = $subject->withChanges($changes);

        self::assertInstanceOf(ImmutableDataObject::class, $actual);
        self::assertEquals($newData, $actual->data);
    }

    public function testWithChangesEmptyChanges()
    {
        $subject = new ImmutableDataObject([
            'foo' => 'bar',
            'baz' => 'qux',
        ]);

        $actual = $subject->withChanges([]);

        self::assertSame($subject, $actual);
    }

    public function testWithChangesNoChange()
    {
        $data = [
            'foo' => 'bar',
            'baz' => 'qux',
        ];
        $changes = [
            'foo' => 'bar',
        ];

        $subject = new ImmutableDataObject($data);
        $actual = $subject->withChanges($changes);

        self::assertSame($subject, $actual);
    }
}
