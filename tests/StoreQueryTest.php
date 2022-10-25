<?php

declare(strict_types=1);

namespace RebelCode\Iris\Test\Func;

use PHPUnit\Framework\TestCase;
use RebelCode\Iris\Data\Source;
use RebelCode\Iris\Store\Query\Criterion;
use RebelCode\Iris\Store\Query\Expression;
use RebelCode\Iris\Store\Query\Field;
use RebelCode\Iris\Store\Query\Order;
use RebelCode\Iris\StoreQuery;

class StoreQueryTest extends TestCase
{
    public function testConstructor()
    {
        $criterion = $this->createMock(Criterion::class);
        $order = $this->createMock(Order::class);
        $count = 10;
        $offset = 5;

        $query = new StoreQuery($criterion, $order, $count, $offset);

        $this->assertSame($criterion, $query->criterion);
        $this->assertSame($order, $query->order);
        $this->assertEquals($count, $query->count);
        $this->assertEquals($offset, $query->offset);
    }

    public function testConstructorDefaults()
    {
        $query = new StoreQuery();

        $this->assertNull($query->criterion);
        $this->assertNull($query->order);
        $this->assertNull($query->count);
        $this->assertEquals(0, $query->offset);
    }

    public function testCreateForIds()
    {
        $ids = ['id1', 'id2', 'id3'];
        $query = StoreQuery::forIds($ids);

        $this->assertInstanceOf(Expression::class, $query->criterion);
        $this->assertEquals(Field::id(), $query->criterion->field);
        $this->assertEquals(Expression::IN, $query->criterion->operator);
        $this->assertEquals($ids, $query->criterion->value);
    }

    public function testCreateForSources()
    {
        $sources = [
            new Source('id1', 'type1'),
            new Source('id2', 'type2'),
            new Source('id3', 'type3'),
        ];

        $query = StoreQuery::forSources($sources);

        $this->assertInstanceOf(Expression::class, $query->criterion);
        $this->assertEquals(Field::source(), $query->criterion->field);
        $this->assertEquals(Expression::IN, $query->criterion->operator);
        $this->assertEquals(['id1', 'id2', 'id3'], $query->criterion->value);
    }

    public function testWithCriterion()
    {
        $original = new StoreQuery();
        $query = clone $original;

        $query2 = $query->withCriterion($criterion = $this->createMock(Criterion::class));

        $this->assertNotSame($query, $query2, 'Returned query should be a new instance');
        $this->assertEquals($original, $query, 'Original query should not be modified');

        $this->assertEquals($criterion, $query2->criterion);
        $this->assertNull($query2->order);
        $this->assertNull($query2->count);
        $this->assertEquals(0, $query2->offset);
    }

    public function testWithOrder()
    {
        $original = new StoreQuery();
        $query = clone $original;

        $query2 = $query->withOrder($order = $this->createMock(Order::class));

        $this->assertNotSame($query, $query2, 'Returned query should be a new instance');
        $this->assertEquals($original, $query, 'Original query should not be modified');

        $this->assertNull($query2->criterion);
        $this->assertEquals($order, $query2->order);
        $this->assertNull($query2->count);
        $this->assertEquals(0, $query2->offset);
    }

    public function testWithCount()
    {
        $original = new StoreQuery();
        $query = clone $original;

        $query2 = $query->withCount($count = 10);

        $this->assertNotSame($query, $query2, 'Returned query should be a new instance');
        $this->assertEquals($original, $query, 'Original query should not be modified');

        $this->assertNull($query2->criterion);
        $this->assertNull($query2->order);
        $this->assertEquals($count, $query2->count);
        $this->assertEquals(0, $query2->offset);
    }

    public function testWithOffset()
    {
        $original = new StoreQuery();
        $query = clone $original;

        $query2 = $query->withOffset($offset = 10);

        $this->assertNotSame($query, $query2, 'Returned query should be a new instance');
        $this->assertEquals($original, $query, 'Original query should not be modified');

        $this->assertNull($query2->criterion);
        $this->assertNull($query2->order);
        $this->assertNull($query2->count);
        $this->assertEquals($offset, $query2->offset);
    }
}
