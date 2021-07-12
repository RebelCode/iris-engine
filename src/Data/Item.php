<?php

declare(strict_types=1);

namespace RebelCode\IrisEngine\Data;

/** @psalm-immutable */
class Item extends ImmutableDataObject
{
    /** @var string */
    public $id;

    /** @var int|string|null */
    public $localId;

    /** @var Source[] */
    public $sources;

    /**
     * Constructor.
     *
     * @param string $id An ID that uniquely identifies the item from other items from the same source.
     * @param int|string|null $localId The ID of the item in local storage.
     * @param Source[] $sources The sources from which the item was fetched.
     * @param array<string, mixed> $data The data for this item.
     */
    public function __construct(string $id, $localId, array $sources, array $data = [])
    {
        parent::__construct($data);
        $this->id = $id;
        $this->localId = $localId;
        $this->sources = $sources;
    }
}
