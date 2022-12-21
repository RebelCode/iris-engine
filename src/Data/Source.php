<?php

declare(strict_types=1);

namespace RebelCode\Iris\Data;

/**
 * Represents a source of data, to which fetched items can be attributed to. It is also used as a token to select the
 * appropriate item source, such as database, API, file, etc.
 */
interface Source
{
    /**
     * Retrieves the ID of the source, which uniquely identifies it from other sources of the same type.
     *
     * @psalm-mutation-free
     * @return string The source ID.
     */
    public function getId(): string;

    /**
     * Retrieves the type of the source, which is an indicator of how the source should be used.
     *
     * @psalm-mutation-free
     * @return string The source type.
     */
    public function getType(): string;
}
