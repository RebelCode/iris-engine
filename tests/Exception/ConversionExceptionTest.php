<?php

declare(strict_types=1);

namespace RebelCode\IrisEngine\Test\Func\Exception;

use Exception;
use PHPUnit\Framework\TestCase;
use RebelCode\IrisEngine\Data\Item;
use RebelCode\IrisEngine\Exception\ConversionException;
use RebelCode\IrisEngine\Exception\IrisException;
use Throwable;

class ConversionExceptionTest extends TestCase
{
    public function testIsException()
    {
        $item = $this->createMock(Item::class);
        $exception = new ConversionException('', $item);

        self::assertInstanceOf(Throwable::class, $exception);
    }

    public function testExtendsIrisException()
    {
        $item = $this->createMock(Item::class);
        $exception = new ConversionException('', $item);

        self::assertInstanceOf(IrisException::class, $exception);
    }

    public function testConstructor()
    {
        $exception = new ConversionException(
            $message = 'Whoops! You mucked up',
            $item = $this->createMock(Item::class),
            $previous = $this->createMock(Exception::class)
        );

        self::assertEquals($message, $exception->getMessage());
        self::assertSame($item, $exception->item);
        self::assertSame($previous, $exception->getPrevious());
    }
}
