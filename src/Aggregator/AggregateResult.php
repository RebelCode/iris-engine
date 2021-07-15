<?php

declare(strict_types=1);

namespace RebelCode\Iris\Aggregator;

use RebelCode\Iris\Data\Item;

class AggregateResult
{
    /** @var Item[] */
    public $items;

    /** @var int */
    public $total;

    /**
     * Constructor.
     *
     * @param Item[] $items
     * @param int $total
     */
    public function __construct(array $items, int $total)
    {
        $this->items = $items;
        $this->total = $total;
    }
}
