<?php

namespace RebelCode\Iris\Test\Func\Aggregator;

use PHPUnit\Framework\TestCase;
use RebelCode\Iris\Aggregator;
use RebelCode\Iris\Aggregator\SimpleAggregator;
use RebelCode\Iris\Data\Feed;
use RebelCode\Iris\Data\Source;
use RebelCode\Iris\StoreQuery;
use RebelCode\Iris\StoreQuery\Expression;
use RebelCode\Iris\StoreQuery\Field;

class SimpleAggregatorTest extends TestCase
{
    public function testImplementsAggregator(): void
    {
        $this->assertInstanceOf(Aggregator::class, new SimpleAggregator());
    }

    public function provideManualPagination(): array
    {
        return [
            'store pagination' => [false],
            'manual pagination' => [true],
        ];
    }

    /** @dataProvider provideManualPagination */
    public function testDoManualPagination(bool $doManualPagination): void
    {
        $aggregator = new SimpleAggregator($doManualPagination);

        $feed = $this->createMock(Feed::class);
        $query = $this->createMock(StoreQuery::class);

        $this->assertEquals($doManualPagination, $aggregator->doManualPagination($feed, $query));
    }

    public function testGetFeedQuery(): void
    {
        $feed = new Feed(1, [
            $this->createConfiguredMock(Source::class, ['getId' => 'foo']),
            $this->createConfiguredMock(Source::class, ['getId' => 'bar']),
        ]);
        $count = 10;
        $offset = 5;

        $expectedCriterion = Expression::forProp(Field::SOURCE, Expression::IN, ['foo', 'bar']);

        $aggregator = new SimpleAggregator();
        $query = $aggregator->getFeedQuery($feed, $count, $offset);

        $this->assertEquals($expectedCriterion, $query->criterion);
        $this->assertEquals($count, $query->count);
        $this->assertEquals($offset, $query->offset);
    }

    public function testGetFeedQueryManualPagination(): void
    {
        $feed = new Feed(1, [
            $this->createConfiguredMock(Source::class, ['getId' => 'foo']),
            $this->createConfiguredMock(Source::class, ['getId' => 'bar']),
        ]);
        $count = 10;
        $offset = 5;

        $expectedCriterion = Expression::forProp(Field::SOURCE, Expression::IN, ['foo', 'bar']);

        $aggregator = new SimpleAggregator(true);
        $query = $aggregator->getFeedQuery($feed, $count, $offset);

        $this->assertEquals($expectedCriterion, $query->criterion);
        $this->assertNull($query->count);
        $this->assertEquals(0, $query->offset);
    }

    /** @dataProvider provideManualPagination */
    public function testGetProcessors(bool $doManualPagination): void
    {
        $feed = $this->createMock(Feed::class);
        $query = $this->createMock(StoreQuery::class);

        $aggregator = new SimpleAggregator($doManualPagination);

        $this->assertNull($aggregator->getPreProcessor($feed, $query));
        $this->assertNull($aggregator->getPostProcessor($feed, $query));
    }
}
