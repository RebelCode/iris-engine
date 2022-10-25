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

    public function testCreateForProp()
    {
        $expr = Expression::forProp('id', Expression::EQUAL_TO, 'foo');

        self::assertEquals(Field::prop('id'), $expr->field);
        self::assertEquals(Expression::EQUAL_TO, $expr->operator);
        self::assertEquals('foo', $expr->value);
    }

    public function testCreateForData()
    {
        $expr = Expression::forData('foo', Expression::EQUAL_TO, 'bar');

        self::assertEquals(Field::data('foo'), $expr->field);
        self::assertEquals(Expression::EQUAL_TO, $expr->operator);
        self::assertEquals('bar', $expr->value);
    }
}
