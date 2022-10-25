<?php

namespace RebelCode\Iris\Test\Func\Store\Query;

use RebelCode\Iris\Store\Query\Field;
use PHPUnit\Framework\TestCase;

class FieldTest extends TestCase
{
    public function testConstructor()
    {
        $type = 'foo';
        $name = 'bar';
        $field = new Field($type, $name);

        self::assertEquals($type, $field->type);
        self::assertEquals($name, $field->name);
    }

    public function testCreateProp()
    {
        $name = 'foo';
        $field = Field::prop($name);

        self::assertEquals(Field::TYPE_PROP, $field->type);
        self::assertEquals($name, $field->name);
    }

    public function testCreateData()
    {
        $name = 'foo';
        $field = Field::data($name);

        self::assertEquals(Field::TYPE_DATA, $field->type);
        self::assertEquals($name, $field->name);
    }

    public function testCreateId()
    {
        $field = Field::id();

        self::assertEquals(Field::TYPE_PROP, $field->type);
        self::assertEquals(Field::ID, $field->name);
    }

    public function testCreateLocalId()
    {
        $field = Field::localId();

        self::assertEquals(Field::TYPE_PROP, $field->type);
        self::assertEquals(Field::LOCAL_ID, $field->name);
    }

    public function testCreateSource()
    {
        $field = Field::source();

        self::assertEquals(Field::TYPE_PROP, $field->type);
        self::assertEquals(Field::SOURCE, $field->name);
    }
}
