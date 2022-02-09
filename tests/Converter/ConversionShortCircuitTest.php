<?php

namespace RebelCode\Iris\Test\Func\Converter;

use PHPUnit\Framework\TestCase;
use RebelCode\Iris\Converter\ConversionShortCircuit;
use Throwable;

class ConversionShortCircuitTest extends TestCase
{
    public function testIsThrowable()
    {
        self::assertInstanceOf(Throwable::class, new ConversionShortCircuit());
    }
}
