<?php

declare(strict_types=1);

namespace RebelCode\IrisEngine\Fetcher;

use RebelCode\IrisEngine\Data\Source;

/** @psalm-immutable */
class FetchQuery
{
    /** @var Source */
    public $source;

    /** @var string|null */
    public $cursor;

    /** @var int|null */
    public $count;

    public function __construct(Source $source, ?string $cursor = null, ?int $count = null)
    {
        $this->source = $source;
        $this->cursor = $cursor;
        $this->count = $count;
    }

    public function forNextBatch(FetchResult $result): ?self
    {
        if ($result->nextCursor === null) {
            return null;
        }

        return new self($this->source, $result->nextCursor, $this->count);
    }
}
