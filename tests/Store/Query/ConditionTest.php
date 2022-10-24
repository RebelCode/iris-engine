<?php

declare(strict_types=1);

namespace RebelCode\Iris\Test\Func\Store\Query;

use PHPUnit\Framework\TestCase;
use RebelCode\Iris\Store\Query\Condition;
use RebelCode\Iris\Store\Query\Criterion;

class ConditionTest extends TestCase
{
    public function testConstructor()
    {
        $relation = Condition:: AND;
        $criteria = [
            $this->createMock(Criterion::class),
            $this->createMock(Criterion::class),
            $this->createMock(Criterion::class),
        ];

        $condition = new Condition($relation, $criteria);

        self::assertEquals($relation, $condition->relation);
        self::assertSame($criteria, $condition->operands);
    }
}
