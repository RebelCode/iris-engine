<?php

declare(strict_types=1);

namespace RebelCode\IrisEngine\Store;

use RebelCode\IrisEngine\Data\Source;
use RebelCode\IrisEngine\Store\Query\Condition;
use RebelCode\IrisEngine\Store\Query\Order;

class Query
{
    /** @var Source[] */
    public $sources;

    /** @var Order|null */
    public $order;

    /** @var Condition|null */
    public $condition;

    /** @var int|null */
    public $count;

    /** @var int */
    public $offset;

    /**
     * Constructor.
     *
     * @param Source[] $sources
     * @param Order|null $order
     * @param Condition|null $condition
     * @param int|null $count
     * @param int $offset
     */
    public function __construct(
        array $sources,
        ?Order $order = null,
        ?Condition $condition = null,
        ?int $count = null,
        int $offset = 0
    ) {
        $this->sources = $sources;
        $this->order = $order;
        $this->condition = $condition;
        $this->count = $count;
        $this->offset = $offset;
    }
}
