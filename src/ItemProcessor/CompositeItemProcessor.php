<?php

declare(strict_types=1);

namespace RebelCode\Iris\ItemProcessor;

use RebelCode\Iris\Data\Feed;
use RebelCode\Iris\ItemProcessor;
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
    public function process(array $items, Feed $feed, StoreQuery $query): array
    {
        foreach ($this->processors as $processor) {
            $items = $processor->process($items, $feed, $query);
        }

        return $items;
    }
}
