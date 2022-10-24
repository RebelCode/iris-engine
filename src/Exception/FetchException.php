<?php

declare(strict_types=1);

namespace RebelCode\Iris\Exception;

use RebelCode\Iris\Data\Source;
use Throwable;

/** @psalm-immutable */
class FetchException extends IrisException
{
    /** @var Source|null */
    public $source;

    /** @var ?string */
    public $cursor;

    /**
     * @inheritDoc
     *
     * @param Source|null $source
     * @param string|null $cursor
     */
    public function __construct(
        string $message = "",
        ?Source $source = null,
        ?string $cursor = null,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $previous);
        $this->source = $source;
        $this->cursor = $cursor;
    }
}
