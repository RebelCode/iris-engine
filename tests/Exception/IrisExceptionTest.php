<?php

declare(strict_types=1);

namespace RebelCode\IrisEngine\Test\Func\Exception;

use Exception;
use PHPUnit\Framework\TestCase;
use RebelCode\IrisEngine\Exception\IrisException;
use RuntimeException;
use Throwable;

class IrisExceptionTest extends TestCase
{
    public function testIsException()
    {
        $exception = new IrisException();

        self::assertInstanceOf(Throwable::class, $exception);
        self::assertInstanceOf(RuntimeException::class, $exception);
    }

    public function testConstructor()
    {
        $exception = new IrisException(
            $message = 'Whoops! You mucked up',
            $previous = $this->createMock(Exception::class)
        );

        self::assertEquals($message, $exception->getMessage());
        self::assertSame($previous, $exception->getPrevious());
    }
}
