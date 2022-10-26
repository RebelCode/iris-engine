<?php

namespace RebelCode\Iris\Aggregator;

use RebelCode\Iris\Data\Feed;
use RebelCode\Iris\StoreQuery;

/** An item processor implementation that composes multiple processors. */
class CompositeItemProcessor implements ItemProcessor
{
    /** @var list<ItemProcessor> */
    protected $processors;

    /**
     * Constructor.
     *
     * @param list<ItemProcessor> $processors The list of processors.
     */
    public function __construct(array $processors)
    {
        $this->processors = $processors;
    }

    /** @inheritDoc */
    public function process(array &$items, Feed $feed, StoreQuery $query): void
    {
        foreach ($this->processors as $processor) {
            $processor->process($items, $feed, $query);
        }
    }
}
