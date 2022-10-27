<?php

declare(strict_types=1);

namespace RebelCode\Iris;

use RebelCode\Iris\Data\Source;
use RebelCode\Iris\StoreQuery\Criterion;
use RebelCode\Iris\StoreQuery\Expression;
use RebelCode\Iris\StoreQuery\Field;
use RebelCode\Iris\StoreQuery\Order;

class StoreQuery
{
    /** @var Criterion|null */
    public $criterion;

    /** @var Order|null */
    public $order;

    /** @var int|null */
    public $count;

    /** @var int */
    public $offset;

    /**
     * Constructor.
     *
     * @param Criterion|null $criterion Optional criterion for the query.
     * @param Order|null $order Optional ordering for the query.
     * @param int|null $count Optional count for the query. If null, all results will be returned.
     * @param int $offset Optional offset for the query. Defaults to 0.
     */
    public function __construct(?Criterion $criterion = null, ?Order $order = null, ?int $count = null, int $offset = 0)
    {
        $this->criterion = $criterion;
        $this->order = $order;
        $this->count = $count;
        $this->offset = $offset;
    }

    /**
     * Creates a copy of the query with a different criterion.
     *
     * @param Criterion $criterion The new criterion.
     * @return self The new query.
     */
    public function withCriterion(Criterion $criterion): self
    {
        return new self($criterion, $this->order, $this->count, $this->offset);
    }

    /**
     * Creates a copy of the query with a different order.
     *
     * @param Order $order The new order.
     * @return self The new query.
     */
    public function withOrder(Order $order): self
    {
        return new self($this->criterion, $order, $this->count, $this->offset);
    }

    /**
     * Creates a copy of the query with a different count.
     *
     * @param int|null $count The new count, or null for no count.
     * @return self The new query.
     */
    public function withCount(?int $count): self
    {
        return new self($this->criterion, $this->order, $count, $this->offset);
    }

    /**
     * Creates a copy of the query with a different offset.
     *
     * @param int $offset The new offset.
     * @return self The new query.
     */
    public function withOffset(int $offset): self
    {
        return new self($this->criterion, $this->order, $this->count, $offset);
    }

    /**
     * Creates a copy of the query without a count and offset.
     *
     * @return self The new query.
     */
    public function withoutPagination(): self
    {
        return $this->withCount(null)->withOffset(0);
    }

    /**
     * Static constructor that creates a query for a list of items with specific IDs.
     *
     * @param string[] $ids The IDs of the items.
     * @return self The created query.
     */
    public static function forIds(array $ids): self
    {
        return new self(new Expression(Field::id(), Expression::IN, $ids));
    }

    /**
     * Static constructor that creates a query for a list of items with specific sources.
     *
     * @param Source[] $sources The sources of the items.
     * @return self The created query.
     */
    public static function forSources(array $sources): self
    {
        $sourceIds = array_map(function (Source $source) {
            return $source->id;
        }, $sources);

        return new self(new Expression(Field::source(), Expression::IN, $sourceIds));
    }
}
