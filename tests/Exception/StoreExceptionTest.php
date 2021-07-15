<?php

declare(strict_types=1);

namespace RebelCode\Iris\Test\Func\Exception;

use Exception;
use PHPUnit\Framework\TestCase;
use RebelCode\Iris\Exception\IrisException;
use RebelCode\Iris\Exception\StoreException;
use RebelCode\Iris\Store;
use Throwable;

class StoreExceptionTest extends TestCase
{
    public function testIsException()
    {
        $store = $this->createMock(Store::class);
        $exception = new StoreException('', $store);

        self::assertInstanceOf(Throwable::class, $exception);
    }

    public function testExtendsIrisException()
    {
        $store = $this->createMock(Store::class);
        $exception = new StoreException('', $store);

        self::assertInstanceOf(IrisException::class, $exception);
    }

    public function testConstructor()
    {
        $exception = new StoreException(
            $message = 'Whoops! You mucked up',
            $store = $this->createMock(Store::class),
            $previous = $this->createMock(Exception::class)
        );

        self::assertEquals($message, $exception->getMessage());
        self::assertSame($store, $exception->store);
        self::assertSame($previous, $exception->getPrevious());
    }
}
