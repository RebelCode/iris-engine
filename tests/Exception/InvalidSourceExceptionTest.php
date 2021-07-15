<?php

declare(strict_types=1);

namespace RebelCode\Iris\Test\Func\Exception;

use Exception;
use PHPUnit\Framework\TestCase;
use RebelCode\Iris\Data\Source;
use RebelCode\Iris\Exception\InvalidSourceException;
use RebelCode\Iris\Exception\IrisException;
use Throwable;

class InvalidSourceExceptionTest extends TestCase
{
    public function testIsException()
    {
        $source = $this->createMock(Source::class);
        $exception = new InvalidSourceException('', $source);

        self::assertInstanceOf(Throwable::class, $exception);
    }

    public function testExtendsIrisException()
    {
        $source = $this->createMock(Source::class);
        $exception = new InvalidSourceException('', $source);

        self::assertInstanceOf(IrisException::class, $exception);
    }

    public function testConstructor()
    {
        $exception = new InvalidSourceException(
            $message = 'Whoops! You mucked up',
            $source = $this->createMock(Source::class),
            $previous = $this->createMock(Exception::class)
        );

        self::assertEquals($message, $exception->getMessage());
        self::assertSame($source, $exception->source);
        self::assertSame($previous, $exception->getPrevious());
    }
}
