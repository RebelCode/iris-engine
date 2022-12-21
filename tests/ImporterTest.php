<?php

namespace RebelCode\Iris\Test\Func;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RebelCode\Iris\Data\Item;
use RebelCode\Iris\Data\Source;
use RebelCode\Iris\Engine;
use RebelCode\Iris\FetchQuery;
use RebelCode\Iris\FetchResult;
use RebelCode\Iris\Importer;
use RebelCode\Iris\Store;
use RebelCode\Iris\StoreResult;
use RebelCode\Iris\Utils\Marker;
use RebelCode\Iris\Utils\NullMarker;

class ImporterTest extends TestCase
{
    protected function expect(bool $expect, MockObject $object, string $method, array $args = [], $return = null)
    {
        $mock = $object->expects($expect ? $this->once() : $this->never())
                    ->method($method)
                    ->with(...$args);

        if ($return !== null) {
            $mock->willReturn($return);
        }
    }

    public function importBatchProvider()
    {
        return [
            'No next batch + no lock + no interrupt' => [false, false, false],
            'No next batch + no lock + interrupted' => [false, false, true],
            'No next batch + locked + no interrupt' => [false, true, false],
            'No next batch + locked + interrupted' => [false, true, true],
            'Next batch + no lock + no interrupt' => [true, false, false],
            'Next batch + no lock + interrupted' => [true, false, true],
            'Next batch + locked + no interrupt' => [true, true, false],
            'Next batch + locked + interrupted' => [true, true, true],
        ];
    }

    /**
     * @dataProvider importBatchProvider
     */
    public function testImportBatch(bool $hasNext, bool $isLocked, bool $didInterrupt)
    {
        $willCreateNext = !$isLocked && !$didInterrupt;
        $willScheduleNext = $willCreateNext && $hasNext;

        // Lock mutex mock
        $lock = $this->createMock(Marker::class);
        $this->expect(true, $lock, 'isSet', [], $isLocked);
        $this->expect(!$isLocked, $lock, 'create');
        $this->expect(!$isLocked, $lock, 'delete');

        // Interrupt marker
        $interrupt = $this->createMock(Marker::class);
        $this->expect(!$isLocked, $interrupt, 'isSet', [], $didInterrupt);
        $this->expect(false, $interrupt, 'create');
        $this->expect(!$isLocked, $interrupt, 'delete');

        $source = $this->createConfiguredMock(Source::class, ['getId' => 'test']);
        $query = new FetchQuery($source);
        $nextQuery = $hasNext ? new FetchQuery($source) : null;

        $fetchItems = [$this->createMock(Item::class), $this->createMock(Item::class), $this->createMock(Item::class)];
        $fetchErrors = ['You don\'t know the power of the dark side.'];
        $result = new FetchResult($fetchItems, $source, null, null, null, $fetchErrors);

        $insertItems = [$this->createMock(Item::class), $this->createMock(Item::class)];

        // Store mock
        $store = $this->createMock(Store::class);
        $this->expect(!$isLocked, $store, 'insert', [$fetchItems], new StoreResult($insertItems));

        // Engine mock
        $engine = $this->createMock(Engine::class);
        $this->expect(!$isLocked, $engine, 'getStore', [], $store);
        $this->expect(!$isLocked, $engine, 'fetch', [$query], $result);

        // Strategy mock
        $strategy = $this->createMock(Importer\ImportStrategy::class);
        $this->expect(false, $strategy, 'createFirstBatch');
        $this->expect($willCreateNext, $strategy, 'createNextBatch', [$query], $nextQuery);

        // Scheduler mock
        $scheduler = $this->createMock(Importer\ImportScheduler::class);
        $this->expect(!$isLocked, $scheduler, 'getMaxRunTime', [$query], 30);
        $this->expect($willScheduleNext, $scheduler, 'scheduleBatch', [$query, $this->isType('callable')], true);

        $importer = new Importer($engine, $strategy, $scheduler, $lock, $interrupt);
        $batch = $importer->importBatch($query);

        if ($isLocked) {
            self::assertEmpty($batch->items);
            self::assertEmpty($batch->errors);
            self::assertFalse($batch->hasNext);
        } else {
            self::assertEquals($insertItems, $batch->items);
            self::assertEquals($fetchErrors, $batch->errors);
            self::assertEquals($willScheduleNext, $batch->hasNext);
        }
    }

    public function importForSourcesProvider()
    {
        return [
            [false],
            [true],
        ];
    }

    /**
     * @dataProvider importForSourcesProvider
     */
    public function testImportForSource(bool $hasNext)
    {
        $sources = [
            $source1 = $this->createConfiguredMock(Source::class, ['getId' => 'test1']),
            $source2 = $this->createConfiguredMock(Source::class, ['getId' => 'test2']),
            $source3 = $this->createConfiguredMock(Source::class, ['getId' => 'test3']),
        ];

        $queries = [
            new FetchQuery($sources[0]),
            new FetchQuery($sources[1]),
            new FetchQuery($sources[2]),
        ];

        $items = [
            $item1 = $this->createMock(Item::class),
            $item2 = $this->createMock(Item::class),
            $item3 = $this->createMock(Item::class),
            $item4 = $this->createMock(Item::class),
            $item5 = $this->createMock(Item::class),
        ];

        $errors = [
            $error1 = "foo",
            $error2 = "bar",
        ];

        $fetchResults = [
            new FetchResult([$item1, $item2], $source1, null, null, null, []),
            new FetchResult([], $source2, null, null, null, [$error1]),
            new FetchResult([$item3, $item4, $item5], $source3, null, null, null, [$error2]),
        ];

        $store = $this->createMock(Store::class);
        $store->method('insert')->willReturnCallback(function(array $items) {
            return new StoreResult($items);
        });

        $engine = $this->createConfiguredMock(Engine::class, ['getStore' => $store]);
        $engine->method('fetch')->willReturn(...$fetchResults);

        $strategy = $this->createMock(Importer\ImportStrategy::class);
        $strategy->method('createFirstBatch')->willReturn(...$queries);

        $strategy->method('createNextBatch')->willReturn($hasNext ? $this->createMock(FetchQuery::class) : null);

        $scheduler = $this->createMock(Importer\ImportScheduler::class);
        $scheduler->method('getMaxRunTime')->willReturn(30);
        $scheduler->method('scheduleBatch')->willReturn($hasNext);

        $lock = new NullMarker();
        $interrupt = new NullMarker();

        $importer = new Importer($engine, $strategy, $scheduler, $lock, $interrupt);
        $result = $importer->importForSources($sources);

        self::assertEquals($items, $result->items);
        self::assertEquals($errors, $result->errors);
        self::assertEquals($hasNext, $result->hasNext);
    }
}
