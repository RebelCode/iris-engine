<?php

namespace RebelCode\Iris\Test\Func\Store\Query;

use PHPUnit\Framework\TestCase;
use RebelCode\Iris\StoreQuery\Condition;
use RebelCode\Iris\StoreQuery\Criterion;
use RebelCode\Iris\StoreQuery\Expression;
use RebelCode\Iris\StoreQuery\Field;

class BaseCriterionTest extends TestCase
{
    public function testAndWithExpression()
    {
        $expr = new Expression(Field::data('foo'), Expression::EQUAL_TO, 'bar');
        $c1 = $this->createMock(Criterion::class);
        $c2 = $this->createMock(Criterion::class);
        $c3 = $this->createMock(Criterion::class);

        $result = $expr->and($c1, $c2, $c3);

        self::assertEquals(Condition::AND, $result->relation);
        self::assertSame([$expr, $c1, $c2, $c3], $result->operands);
    }

    public function testOrWithExpression()
    {
        $expr = new Expression(Field::data('foo'), Expression::EQUAL_TO, 'bar');
        $c1 = $this->createMock(Criterion::class);
        $c2 = $this->createMock(Criterion::class);
        $c3 = $this->createMock(Criterion::class);

        $result = $expr->or($c1, $c2, $c3);

        self::assertEquals(Condition::OR, $result->relation);
        self::assertSame([$expr, $c1, $c2, $c3], $result->operands);
    }

    public function testAndWithAndCondition()
    {
        $condition = new Condition(Condition::AND, [
            $c1 = $this->createMock(Criterion::class),
            $c2 = $this->createMock(Criterion::class),
        ]);

        $c3 = $this->createMock(Criterion::class);
        $c4 = $this->createMock(Criterion::class);
        $c5 = $this->createMock(Criterion::class);

        $result = $condition->and($c3, $c4, $c5);

        self::assertEquals(Condition::AND, $result->relation);
        self::assertSame([$c1, $c2, $c3, $c4, $c5], $result->operands);
    }

    public function testAndWithOrCondition()
    {
        $condition = new Condition(Condition::OR, [
            $this->createMock(Criterion::class),
            $this->createMock(Criterion::class),
        ]);

        $c1 = $this->createMock(Criterion::class);
        $c2 = $this->createMock(Criterion::class);
        $c3 = $this->createMock(Criterion::class);

        $result = $condition->and($c1, $c2, $c3);

        self::assertEquals(Condition::AND, $result->relation);
        self::assertSame([$condition, $c1, $c2, $c3], $result->operands);
    }

    public function testOrWithOrCondition()
    {
        $condition = new Condition(Condition::OR, [
            $c1 = $this->createMock(Criterion::class),
            $c2 = $this->createMock(Criterion::class),
        ]);

        $c3 = $this->createMock(Criterion::class);
        $c4 = $this->createMock(Criterion::class);
        $c5 = $this->createMock(Criterion::class);

        $result = $condition->or($c3, $c4, $c5);

        self::assertEquals(Condition::OR, $result->relation);
        self::assertSame([$c1, $c2, $c3, $c4, $c5], $result->operands);
    }

    public function testOrWithAndCondition()
    {
        $condition = new Condition(Condition::AND, [
            $this->createMock(Criterion::class),
            $this->createMock(Criterion::class),
        ]);

        $c1 = $this->createMock(Criterion::class);
        $c2 = $this->createMock(Criterion::class);
        $c3 = $this->createMock(Criterion::class);

        $result = $condition->or($c1, $c2, $c3);

        self::assertEquals(Condition::OR, $result->relation);
        self::assertSame([$condition, $c1, $c2, $c3], $result->operands);
    }
}
