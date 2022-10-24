<?php

declare(strict_types=1);

namespace RebelCode\Iris\Store\Query;

interface Criterion
{
    /**
     * Creates an AND condition using this criterion and one or more other criteria.
     *
     * @param Criterion[] $criteria The criteria to AND with this expression.
     * @return Condition The AND condition.
     */
    public function and(Criterion ...$criteria): Condition;

    /**
     * Creates an OR condition using this criterion and one or more other criteria.
     *
     * @param Criterion[] $criteria The criteria to OR with this expression.
     * @return Condition The OR condition.
     */
    public function or(Criterion ...$criteria): Condition;
}
