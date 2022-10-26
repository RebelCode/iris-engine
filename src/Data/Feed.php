<?php

declare(strict_types=1);

namespace RebelCode\Iris\Data;

/** @psalm-immutable */
class Feed extends ImmutableDataObject
{
    /** @var int|string */
    public $id;

    /** @var Source[] */
    public $sources;

    /**
     * Constructor.
     *
     * @param int|string $id The unique ID of the feed.
     * @param Source[] $sources The sources whose items are shown in the feed.
     * @param array<string, mixed> $data The data map.
     */
    public function __construct($id, array $sources, array $data = [])
    {
        parent::__construct($data);
        $this->id = $id;
        $this->sources = $sources;
    }
}
