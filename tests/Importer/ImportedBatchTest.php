<?php

namespace RebelCode\Iris\Test\Func\Importer;

use PHPUnit\Framework\TestCase;
use RebelCode\Iris\Data\Item;
use RebelCode\Iris\Importer\ImportedBatch;

class ImportedBatchTest extends TestCase
{
    public function testConstructorItems()
    {
        $items = [
            $this->createMock(Item::class),
            $this->createMock(Item::class),
            $this->createMock(Item::class),
        ];

        $batch = new ImportedBatch($items, [], false);

        self::assertEquals($items, $batch->items);
    }

    public function testConstructorErrors()
    {
        $errors = [
            "foo bar",
            "Qui-Gon Jinn",
        ];

        $batch = new ImportedBatch([], $errors, false);

        self::assertEquals($errors, $batch->errors);
    }

    public function testConstructorHasNextTrue()
    {
        $batch = new ImportedBatch([], [], true);

        self::assertTrue($batch->hasNext);
    }

    public function testConstructorHasNextFalse()
    {
        $batch = new ImportedBatch([], [], false);

        self::assertFalse($batch->hasNext);
    }

    public function testMergeItems()
    {
        $items1 = [
            $this->createMock(Item::class),
            $this->createMock(Item::class),
        ];
        $items2 = [
            $this->createMock(Item::class),
            $this->createMock(Item::class),
        ];

        $batch1 = new ImportedBatch($items1, [], false);
        $batch2 = new ImportedBatch($items2, [], false);

        $result = $batch1->mergeWith($batch2);

        self::assertSame(array_merge($items1, $items2), $result->items);
    }

    public function testMergeErrors()
    {
        $errors1 = [
            "foo",
            "bar",
        ];
        $errors2 = [
            "John",
            "Wick",
        ];

        $batch1 = new ImportedBatch([], $errors1, false);
        $batch2 = new ImportedBatch([], $errors2, false);

        $result = $batch1->mergeWith($batch2);

        self::assertSame(array_merge($errors1, $errors2), $result->errors);
    }

    public function testMergeHasNextBothFalse()
    {
        $batch1 = new ImportedBatch([], [], false);
        $batch2 = new ImportedBatch([], [], false);

        $result = $batch1->mergeWith($batch2);

        self::assertFalse($result->hasNext);
    }

    public function testMergeHasNextBothTrue()
    {
        $batch1 = new ImportedBatch([], [], true);
        $batch2 = new ImportedBatch([], [], true);

        $result = $batch1->mergeWith($batch2);

        self::assertTrue($result->hasNext);
    }

    public function testMergeHasNextFirstTrue()
    {
        $batch1 = new ImportedBatch([], [], true);
        $batch2 = new ImportedBatch([], [], false);

        $result = $batch1->mergeWith($batch2);

        self::assertTrue($result->hasNext);
    }

    public function testMergeHasNextSecondTrue()
    {
        $batch1 = new ImportedBatch([], [], false);
        $batch2 = new ImportedBatch([], [], true);

        $result = $batch1->mergeWith($batch2);

        self::assertTrue($result->hasNext);
    }
}
