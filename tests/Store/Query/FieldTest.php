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
}
