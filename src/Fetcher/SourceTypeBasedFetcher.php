<?php

declare(strict_types=1);

namespace RebelCode\Iris\Fetcher;

use RebelCode\Iris\Data\Source;
use RebelCode\Iris\Fetcher;
use RebelCode\Iris\FetchResult;

/**
 * A fetcher implementation that delegates to other fetchers based on the source's type.
 *
 * @psalm-immutable
 */
class SourceTypeBasedFetcher implements Fetcher
{
    /** @var array<string, Fetcher> */
    protected $map;

    /** @var Fetcher|null */
    protected $default;

    /**
     * Constructor.
     *
     * @param array<string, Fetcher> $map The mapping of source types to catalogs.
     * @param Fetcher|null $default The catalog to use if no mapping is found for a source type.
     */
    public function __construct(array $map, ?Fetcher $default = null)
    {
        $this->map = $map;
        $this->default = $default;
    }

    /** @inheritDoc */
    public function query(Source $source, ?string $cursor = null, ?int $count = null): FetchResult
    {
        $type = $source->getType();
        $fetcher = $this->map[$type] ?? $this->default;

        if ($fetcher === null) {
            return new FetchResult([], $source, null, null, null, [
                'No suitable fetcher found for source type "' . $type . '"',
            ]);
        } else {
            return $fetcher->query($source, $cursor, $count);
        }
    }
}
