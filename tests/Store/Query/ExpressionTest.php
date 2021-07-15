<?php

declare(strict_types=1);

namespace RebelCode\Iris\Test\Func\Store\Query;

use PHPUnit\Framework\TestCase;
use RebelCode\Iris\Store\Query\Expression;

class ExpressionTest extends TestCase
{
    public function testConstructor()
    {
        $field = 'foo';
        $value = 'bar';
        $operator = Expression::EQUAL_TO;

        $expression = new Expression($field, $operator, $value);

        self::assertEquals($field, $expression->field);
        self::assertEquals($operator, $expression->operator);
        self::assertEquals($value, $expression->value);
    }
}
