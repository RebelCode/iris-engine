<?php

declare(strict_types=1);

namespace RebelCode\Iris\StoreQuery;

use RebelCode\Iris\Data\Item;

/**
 * Represent a field in an expression.
 *
 * @psalm-immutable
 */
class Field
{
    public const ID = 'id';
    public const LOCAL_ID = 'localId';
    public const SOURCE = 'source';
    public const TYPE_PROP = 'prop';
    public const TYPE_DATA = 'data';

    /** @var string */
    public $type;

    /** @var string */
    public $name;

    /**
     * Constructor.
     *
     * @param string $type The type of the field. See the `TYPE_*` class constants in {@link Field}.
     * @param string $name The name of the field.
     */
    public function __construct(string $type, string $name)
    {
        $this->type = $type;
        $this->name = $name;
    }

    /**
     * Creates a field that represent a property of the {@link Item} class, with the exception of {@link Item::$data}.
     *
     * @param string $name The name of the property. Refer to the class constants in {@link Field}.
     * @return Field The created field.
     */
    public static function prop(string $name): self
    {
        return new self(self::TYPE_PROP, $name);
    }

    /**
     * Creates a field that represents an entry in the {@link Item::$data} array of an {@link Item}.
     *
     * @param string $key The key of the data entry.
     * @return Field The created field.
     */
    public static function data(string $key): self
    {
        return new self(self::TYPE_DATA, $key);
    }

    /**
     * Returns a field instance that represents the ID prop of an item.
     *
     * @return self The field instance.
     */
    public static function id(): self
    {
        /** @var Field|null $cache */
        static $cache = null;
        return $cache ?? $cache = self::prop(self::ID);
    }

    /**
     * Returns a field instance that represents the local ID prop of an item.
     *
     * @return self The field instance.
     */
    public static function localId(): self
    {
        /** @var Field|null $cache */
        static $cache = null;
        return $cache ?? $cache = self::prop(self::LOCAL_ID);
    }

    /**
     * Returns a field instance that represents the source prop of an item.
     *
     * @return self The field instance.
     */
    public static function source(): self
    {
        /** @var Field|null $cache */
        static $cache = null;
        return $cache ?? $cache = self::prop(self::SOURCE);
    }
}
