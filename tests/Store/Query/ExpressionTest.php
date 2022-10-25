<?php

declare(strict_types=1);

namespace RebelCode\Iris\Test\Func\Store\Query;

use PHPUnit\Framework\TestCase;
use RebelCode\Iris\Store\Query\BaseCriterion;
use RebelCode\Iris\Store\Query\Expression;
use RebelCode\Iris\Store\Query\Field;

class ExpressionTest extends TestCase
{
    public function testExtendsBaseCriterion()
    {
        self::assertInstanceOf(BaseCriterion::class, new Expression(Field::data('foo'), Expression::EQUAL_TO, 'bar'));
    }

    public function testConstructor()
    {
        $field = Field::data('foo');
        $value = 'bar';
        $operator = Expression::EQUAL_TO;

        $expression = new Expression($field, $operator, $value);

        self::assertEquals($field, $expression->field);
        self::assertEquals($operator, $expression->operator);
        self::assertEquals($value, $expression->value);
    }
}
