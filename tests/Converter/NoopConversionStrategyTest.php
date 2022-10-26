<?php

namespace RebelCode\Iris\Test\Func\Converter;

use PHPUnit\Framework\TestCase;
use RebelCode\Iris\Converter;
use RebelCode\Iris\Converter\NoopConverter;
use RebelCode\Iris\Data\Item;

class NoopConversionStrategyTest extends TestCase
{
    public function testImplementsConversionStrategy()
    {
        $this->assertInstanceOf(Converter::class, new NoopConverter());
    }

    public function testBeforeBatch()
    {
        $strategy = new NoopConverter();

        $incoming = [
            $this->createMock(Item::class),
            $this->createMock(Item::class),
            $this->createMock(Item::class),
        ];

        $existing = [
            $this->createMock(Item::class),
            $this->createMock(Item::class),
            $this->createMock(Item::class),
        ];

        $result = $strategy->beforeBatch($incoming, $existing);

        $this->assertSame($incoming, $result);
    }

    public function testAfterBatch()
    {
        $strategy = new NoopConverter();

        $incoming = [
            $this->createMock(Item::class),
            $this->createMock(Item::class),
            $this->createMock(Item::class),
        ];

        $result = $strategy->afterBatch($incoming);

        $this->assertSame($incoming, $result);
    }

    public function testConvert()
    {
        $strategy = new NoopConverter();

        $item = $this->createMock(Item::class);
        $result = $strategy->convert($item);

        $this->assertSame($item, $result);
    }

    public function finalize()
    {
        $strategy = new NoopConverter();

        $item = $this->createMock(Item::class);
        $result = $strategy->finalize($item);

        $this->assertSame($item, $result);
    }

    public function provideDataForReconcileTest(): array
    {
        $incoming = $this->createMock(Item::class);
        $existing = $this->createMock(Item::class);

        return [
            'retain items' => [$incoming, $existing, false, $existing],
            'update items' => [$incoming, $existing, true, $incoming],
        ];
    }

    /** @dataProvider provideDataForReconcileTest */
    public function testReconcile(Item $incoming, Item $existing, bool $updateItems, Item $expected)
    {
        $strategy = new NoopConverter($updateItems);
        $result = $strategy->reconcile($incoming, $existing);

        $this->assertSame($expected, $result);
    }
}
