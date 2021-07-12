<?php

declare(strict_types=1);

namespace RebelCode\IrisEngine\Test\Func\Exception;

use Exception;
use PHPUnit\Framework\TestCase;
use RebelCode\IrisEngine\Data\Source;
use RebelCode\IrisEngine\Exception\FetchException;
use RebelCode\IrisEngine\Exception\IrisException;
use RebelCode\IrisEngine\Fetcher;
use Throwable;

class FetchExceptionTest extends TestCase
{
    public function testIsException()
    {
        self::assertInstanceOf(Throwable::class, new FetchException());
    }

    public function testExtendsIrisException()
    {
        self::assertInstanceOf(IrisException::class, new FetchException());
    }

    public function testConstructor()
    {
        $exception = new FetchException(
            $message = 'Whoops! You mucked up',
            $fetcher = $this->createMock(Fetcher::class),
            $source = $this->createMock(Source::class),
            $cursor = 'ABC123',
            $previous = $this->createMock(Exception::class)
        );

        self::assertEquals($message, $exception->getMessage());
        self::assertSame($fetcher, $exception->fetcher);
        self::assertSame($source, $exception->source);
        self::assertEquals($cursor, $exception->cursor);
        self::assertSame($previous, $exception->getPrevious());
    }
}
