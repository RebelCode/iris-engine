<?php

namespace RebelCode\Iris\Store\Query;

/** @psalm-immutable */
abstract class BaseCriterion implements Criterion
{
    /**
     * Creates an AND condition between this condition and another criterion.
     *
     * @param Criterion[] $criteria The criteria to AND with this expression.
     * @return Condition The AND condition.
     */
    public function and(Criterion ...$criteria): Condition
    {
        // If we are AND-ing to an AND condition, we can just add the criteria to the existing condition, since an
        // AND operation is associative. This can help save on recursion when traversing the condition tree.
        if ($this instanceof Condition && $this->relation === Condition::AND) {
            return new Condition(Condition::AND, array_merge($this->operands, $criteria));
        } else {
            return new Condition(Condition::AND, array_merge([$this], $criteria));
        }
    }

    /**
     * Creates an OR condition between this condition and another criterion.
     *
     * @param Criterion[] $criteria The criteria to OR with this expression.
     * @return Condition The OR condition.
     */
    public function or(Criterion ...$criteria): Condition
    {
        // If we are OR-ing to an OR condition, we can just add the criteria to the existing condition, since an
        // OR operation is associative. This can help save on recursion when traversing the condition tree.
        if ($this instanceof Condition && $this->relation === Condition::OR) {
            return new Condition(Condition::OR, array_merge($this->operands, $criteria));
        } else {
            return new Condition(Condition::OR, array_merge([$this], $criteria));
        }
    }
}
