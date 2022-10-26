<?php

namespace RebelCode\Iris\Test\Func\Exception;

use PHPUnit\Framework\TestCase;
use RebelCode\Iris\Exception\ConversionShortCircuit;
use Throwable;

class ConversionShortCircuitTest extends TestCase
{
    public function testIsThrowable()
    {
        self::assertInstanceOf(Throwable::class, new ConversionShortCircuit());
    }
}
