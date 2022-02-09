<?php

declare(strict_types=1);

namespace RebelCode\Iris\Test\Func;

use PHPUnit\Framework\TestCase;
use RebelCode\Iris\Converter;
use RebelCode\Iris\Data\Item;
use RebelCode\Iris\Data\Source;
use RebelCode\Iris\Store;

class ConverterTest extends TestCase
{
    public function testConvert()
    {
        $store = $this->createMock(Store::class);
        $strategy = $this->createMock(Converter\ConversionStrategy::class);

        $source = $this->createMock(Source::class);
        $inputItem = new Item('1', 1, [$source]);
        $convertedItem = new Item('c1', 1, [$source]);
        $finalizedItem = new Item('f1', 1, [$source]);

        $store->expects($this->once())->method('get')->with($inputItem->id)->willReturn(null);
        $strategy->expects($this->once())->method('convert')->with($inputItem)->willReturn($convertedItem);
        $strategy->expects($this->never())->method('reconcile');
        $strategy->expects($this->once())->method('finalize')->with($convertedItem)->willReturn($finalizedItem);

        $converter = new Converter($store, $strategy);
        $actualItem = $converter->convert($inputItem);

        self::assertSame($finalizedItem, $actualItem);
    }

    public function testConvertReturnsNull()
    {
        $store = $this->createMock(Store::class);
        $strategy = $this->createMock(Converter\ConversionStrategy::class);

        $source = $this->createMock(Source::class);
        $inputItem = new Item('1', 1, [$source]);

        $store->expects($this->once())->method('get')->with($inputItem->id)->willReturn(null);
        $strategy->expects($this->once())->method('convert')->with($inputItem)->willReturn(null);
        $strategy->expects($this->never())->method('reconcile');
        $strategy->expects($this->never())->method('finalize');

        $converter = new Converter($store, $strategy);
        $actualItem = $converter->convert($inputItem);

        self::assertNull($actualItem);
    }

    public function testFinalizeReturnsNull()
    {
        $store = $this->createMock(Store::class);
        $strategy = $this->createMock(Converter\ConversionStrategy::class);

        $source = $this->createMock(Source::class);
        $inputItem = new Item('1', 1, [$source]);
        $convertedItem = new Item('c1', 1, [$source]);

        $store->expects($this->once())->method('get')->with($inputItem->id)->willReturn(null);
        $strategy->expects($this->once())->method('convert')->with($inputItem)->willReturn($convertedItem);
        $strategy->expects($this->never())->method('reconcile');
        $strategy->expects($this->once())->method('finalize')->with($convertedItem)->willReturn(null);

        $converter = new Converter($store, $strategy);
        $actualItem = $converter->convert($inputItem);

        self::assertNull($actualItem);
    }

    public function testReconcile()
    {
        $store = $this->createMock(Store::class);
        $strategy = $this->createMock(Converter\ConversionStrategy::class);

        $source = $this->createMock(Source::class);
        $inputItem = new Item('1', 1, [$source]);
        $existingItem = new Item('e1', 1, [$source]);
        $convertedItem = new Item('c1', 1, [$source]);
        $reconciledItem = new Item('r1', 1, [$source]);
        $finalizedItem = new Item('f1', 1, [$source]);

        $store->expects($this->once())->method('get')->with($inputItem->id)->willReturn($existingItem);
        $strategy->expects($this->once())->method('convert')->with($inputItem)->willReturn($convertedItem);
        $strategy->expects($this->once())
                 ->method('reconcile')
                 ->with($convertedItem, $existingItem)
                 ->willReturn($reconciledItem);
        $strategy->expects($this->once())->method('finalize')->with($reconciledItem)->willReturn($finalizedItem);

        $converter = new Converter($store, $strategy);
        $actualItem = $converter->convert($inputItem);

        self::assertSame($finalizedItem, $actualItem);
    }

    public function testReconcileReturnsNull()
    {
        $store = $this->createMock(Store::class);
        $strategy = $this->createMock(Converter\ConversionStrategy::class);

        $source = $this->createMock(Source::class);
        $inputItem = new Item('1', 1, [$source]);
        $existingItem = new Item('e1', 1, [$source]);
        $convertedItem = new Item('c1', 1, [$source]);

        $store->expects($this->once())->method('get')->with($inputItem->id)->willReturn($existingItem);
        $strategy->expects($this->once())->method('convert')->with($inputItem)->willReturn($convertedItem);
        $strategy->expects($this->once())
                 ->method('reconcile')
                 ->with($convertedItem, $existingItem)
                 ->willReturn(null);
        $strategy->expects($this->never())->method('finalize');

        $converter = new Converter($store, $strategy);
        $actualItem = $converter->convert($inputItem);

        self::assertNull($actualItem);
    }

    public function testConvertMultiple()
    {
        $store = $this->createMock(Store::class);
        $strategy = $this->createMock(Converter\ConversionStrategy::class);

        $source = $this->createMock(Source::class);
        $items = [
            new Item('1', 1, [$source]),
            new Item('2', 2, [$source]),
            new Item('3', 3, [$source]),
        ];

        $store->expects($this->once())->method('getMultiple')->with(['1', '2', '3'])->willReturn([]);

        $strategy->expects($this->once())->method('beforeBatch')->willReturnArgument(0);
        $strategy->expects($this->exactly(3))->method('convert')->willReturnArgument(0);
        $strategy->expects($this->never())->method('reconcile');
        $strategy->expects($this->exactly(3))->method('finalize')->willReturnArgument(0);
        $strategy->expects($this->once())->method('afterBatch')->willReturnArgument(0);

        $converter = new Converter($store, $strategy);
        $actualItems = $converter->convertMultiple($items);

        self::assertEquals($items, $actualItems);
    }

    public function testConvertMultipleBeforeBatch()
    {
        $store = $this->createMock(Store::class);
        $strategy = $this->createMock(Converter\ConversionStrategy::class);

        $source = $this->createMock(Source::class);
        $items = [
            new Item('1', 1, [$source]),
            new Item('2', 2, [$source]),
            new Item('3', 3, [$source]),
        ];
        $changed = [
            $items[0],
            new Item('4', 4, [$source]),
            $items[2],
        ];

        $store->expects($this->once())->method('getMultiple')->with(['1', '2', '3'])->willReturn([]);

        $strategy->expects($this->once())->method('beforeBatch')->with($items)->willReturn($changed);
        $strategy->expects($this->exactly(3))->method('convert')->willReturnArgument(0);
        $strategy->expects($this->never())->method('reconcile');
        $strategy->expects($this->exactly(3))->method('finalize')->willReturnArgument(0);
        $strategy->expects($this->once())->method('afterBatch')->willReturnArgument(0);

        $converter = new Converter($store, $strategy);
        $actualItems = $converter->convertMultiple($items);

        self::assertEquals($changed, $actualItems);
    }

    public function testConvertMultipleAfterBatch()
    {
        $store = $this->createMock(Store::class);
        $strategy = $this->createMock(Converter\ConversionStrategy::class);

        $source = $this->createMock(Source::class);
        $items = [
            new Item('1', 1, [$source]),
            new Item('2', 2, [$source]),
            new Item('3', 3, [$source]),
        ];
        $changed = [
            $items[0],
            new Item('4', 4, [$source]),
            $items[2],
        ];

        $store->expects($this->once())->method('getMultiple')->with(['1', '2', '3'])->willReturn([]);

        $strategy->expects($this->once())->method('beforeBatch')->willReturnArgument(0);
        $strategy->expects($this->exactly(3))->method('convert')->willReturnArgument(0);
        $strategy->expects($this->never())->method('reconcile');
        $strategy->expects($this->exactly(3))->method('finalize')->willReturnArgument(0);
        $strategy->expects($this->once())->method('afterBatch')->with($items)->willReturn($changed);

        $converter = new Converter($store, $strategy);
        $actualItems = $converter->convertMultiple($items);

        self::assertEquals($changed, $actualItems);
    }

    public function testConvertMultipleFilteredItems()
    {
        $store = $this->createMock(Store::class);
        $strategy = $this->createMock(Converter\ConversionStrategy::class);

        $source = $this->createMock(Source::class);
        $items = [
            new Item('1', 1, [$source]),
            new Item('2', 2, [$source]),
            new Item('3', 3, [$source]),
        ];
        $expected = [
            $items[0],
            $items[2],
        ];

        $store->expects($this->once())->method('getMultiple')->with(['1', '2', '3'])->willReturn([]);

        $strategy->expects($this->once())->method('beforeBatch')->willReturnArgument(0);
        $strategy->expects($this->exactly(3))->method('convert')->willReturnOnConsecutiveCalls(
            $items[0], null, $items[2]
        );
        $strategy->expects($this->never())->method('reconcile');
        $strategy->expects($this->exactly(2))->method('finalize')->willReturnArgument(0);
        $strategy->expects($this->once())->method('afterBatch')->willReturnArgument(0);

        $converter = new Converter($store, $strategy);
        $actualItems = $converter->convertMultiple($items);

        self::assertEquals($expected, $actualItems);
    }

    public function testConvertMultipleWithReconciliation()
    {
        $store = $this->createMock(Store::class);
        $strategy = $this->createMock(Converter\ConversionStrategy::class);

        $source = $this->createMock(Source::class);
        $items = [
            new Item('1', 1, [$source]),
            new Item('2', 2, [$source]),
            new Item('3', 3, [$source]),
        ];
        $existing = [
            '1' => new Item('e1', 1, [$source]),
            '3' => new Item('e3', 3, [$source]),
        ];
        $reconciled = [
            new Item('r1', 1, [$source]),
            null,
            new Item('r3', 3, [$source]),
        ];

        $expected = [
            $reconciled[0],
            $items[1],
            $reconciled[2],
        ];

        $store->expects($this->once())->method('getMultiple')->with(['1', '2', '3'])->willReturn($existing);

        $strategy->expects($this->once())->method('beforeBatch')->willReturnArgument(0);
        $strategy->expects($this->once())->method('afterBatch')->willReturnArgument(0);

        $strategy->expects($this->exactly(3))
                 ->method('convert')
                 ->withConsecutive([$items[0]], [$items[1]], [$items[2]])
                 ->willReturnArgument(0);
        $strategy->expects($this->exactly(2))
                 ->method('reconcile')
                 ->withConsecutive(
                     [$items[0], $existing['1']],
                     [$items[2], $existing[3]]
                 )
                 ->willReturnOnConsecutiveCalls(
                     $reconciled[0],
                     $reconciled[2]
                 );

        $strategy->expects($this->exactly(3))
                 ->method('finalize')
                 ->withConsecutive(
                     [$reconciled[0]],
                     [$items[1]],
                     [$reconciled[2]]
                 )
                 ->willReturnArgument(0);

        $converter = new Converter($store, $strategy);
        $actualItems = $converter->convertMultiple($items);

        self::assertEquals($expected, $actualItems);
    }

    public function testConvertMultipleShortCircuitNoYield()
    {
        $store = $this->createMock(Store::class);
        $strategy = $this->createMock(Converter\ConversionStrategy::class);

        $source = $this->createMock(Source::class);
        $items = [
            new Item('1', 1, [$source]),
            new Item('2', 2, [$source]),
            new Item('3', 3, [$source]),
            new Item('4', 4, [$source]),
        ];
        $expected = [
            $items[0],
        ];

        $store->expects($this->once())->method('getMultiple')->with(['1', '2', '3', '4'])->willReturn([]);

        $strategy->expects($this->once())->method('beforeBatch')->willReturnArgument(0);
        $strategy->expects($this->once())->method('afterBatch')->willReturnArgument(0);

        // Convert short-circuits after first 2 items
        $count = 0;
        $strategy->expects($this->exactly(2))->method('convert')->willReturnCallback(function ($item) use (&$count) {
            $count++;
            if ($count >= 2) {
                throw new Converter\ConversionShortCircuit();
            }
            return $item;
        });

        $strategy->expects($this->never())->method('reconcile');
        $strategy->expects($this->exactly(1))
                 ->method('finalize')
                 ->withConsecutive([$items[0]])
                 ->willReturnArgument(0);

        $converter = new Converter($store, $strategy);
        $actualItems = $converter->convertMultiple($items);

        self::assertEquals($expected, $actualItems);
    }

    public function testConvertMultipleShortCircuitYield()
    {
        $store = $this->createMock(Store::class);
        $strategy = $this->createMock(Converter\ConversionStrategy::class);

        $source = $this->createMock(Source::class);
        $items = [
            new Item('1', 1, [$source]),
            new Item('2', 2, [$source]),
            new Item('3', 3, [$source]),
            new Item('4', 4, [$source]),
        ];
        $expected = [
            $items[0],
            $items[1],
        ];

        $store->expects($this->once())->method('getMultiple')->with(['1', '2', '3', '4'])->willReturn([]);

        $strategy->expects($this->once())->method('beforeBatch')->willReturnArgument(0);
        $strategy->expects($this->once())->method('afterBatch')->willReturnArgument(0);

        // Convert short-circuits after first 2 items
        $count = 0;
        $strategy->expects($this->exactly(2))->method('convert')->willReturnCallback(function ($item) use (&$count) {
            $count++;
            if ($count >= 2) {
                throw new Converter\ConversionShortCircuit($item);
            }
            return $item;
        });

        $strategy->expects($this->never())->method('reconcile');
        $strategy->expects($this->exactly(1))
                 ->method('finalize')
                 ->withConsecutive([$items[0]])
                 ->willReturnArgument(0);

        $converter = new Converter($store, $strategy);
        $actualItems = $converter->convertMultiple($items);

        self::assertEquals($expected, $actualItems);
    }
}
