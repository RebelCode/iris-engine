<?php

declare(strict_types=1);

namespace RebelCode\IrisEngine\Test\Func\Exception;

use Exception;
use PHPUnit\Framework\TestCase;
use RebelCode\IrisEngine\Data\Source;
use RebelCode\IrisEngine\Exception\InvalidSourceException;
use RebelCode\IrisEngine\Exception\IrisException;
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
