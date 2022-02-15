<?php

namespace RebelCode\Iris\Test\Func\Utils;

use RebelCode\Iris\Utils\Marker;
use RebelCode\Iris\Utils\NullMarker;
use PHPUnit\Framework\TestCase;

class NullMarkerTest extends TestCase
{
    public function testImplementsMarker()
    {
        self::assertInstanceOf(Marker::class, new NullMarker());
    }

    public function testConstructorNoArgIsFalse()
    {
        $marker = new NullMarker();

        self::assertFalse($marker->isSet());
    }

    public function testNullMarkerIssetTrue()
    {
        $marker = new NullMarker(true);

        self::assertTrue($marker->isSet(), "Initial value should be true");

        $marker->create();
        self::assertTrue($marker->isSet(), "Value should remain true after calling create()");

        $marker->delete();
        self::assertTrue($marker->isSet(), "Value should remain true after calling delete()");
    }

    public function testNullMarkerIssetFalse()
    {
        $marker = new NullMarker(false);

        self::assertFalse($marker->isSet(), "Initial value should be false");

        $marker->create();
        self::assertFalse($marker->isSet(), "Value should remain false after calling create()");

        $marker->delete();
        self::assertFalse($marker->isSet(), "Value should remain false after calling delete()");
    }
}
