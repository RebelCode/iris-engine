<?php

declare(strict_types=1);

namespace RebelCode\Iris\StoreQuery;

/** @psalm-immutable */
class Order
{
    public const ASC = 'ASC';
    public const DESC = 'DESC';

    /**
     * @var string
     * @psalm-var Order::ASC|Order::DESC
     */
    public $type;

    /** @var Field */
    public $field;

    /**
     * Constructor.
     *
     * @param string $type Either {@link Order::ASC} for ascending, or {@link Order::DESC} for descending.
     * @param Field $field The field to order by.
     *
     * @psalm-param Order::ASC|Order::DESC $type
     */
    public function __construct(string $type, Field $field)
    {
        $this->type = $type;
        $this->field = $field;
    }

    /**
     * Creates an ascending order.
     *
     * @param Field $field The field to order by.
     * @return Order The order instance.
     */
    public static function asc(Field $field): Order
    {
        return new Order(self::ASC, $field);
    }

    /**
     * Creates a descending order.
     *
     * @param Field $field The field to order by.
     * @return Order The order instance.
     */
    public static function desc(Field $field): Order
    {
        return new Order(self::DESC, $field);
    }
}
