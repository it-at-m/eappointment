<?php

declare(strict_types=1);

namespace BO\Slim\Tests\Helper;

use BO\Slim\Helper\ProcessPool;
use PHPUnit\Framework\TestCase;

class ProcessPoolTest extends TestCase
{
    #[\Override]
    protected function tearDown(): void
    {
        putenv('ZMS_CACHE_WARMUP_WORKERS');
        parent::tearDown();
    }

    public function testWorkerCountDefaultsToFour(): void
    {
        putenv('ZMS_CACHE_WARMUP_WORKERS');

        self::assertSame(4, ProcessPool::workerCount());
    }

    public function testWorkerCountReadsTheEnvironment(): void
    {
        putenv('ZMS_CACHE_WARMUP_WORKERS=3');

        self::assertSame(3, ProcessPool::workerCount());
    }

    public function testRunReportsTheWorkerThatFails(): void
    {
        $failed = ProcessPool::run(
            [
                [PHP_BINARY, '-r', 'exit(0);'],
                [PHP_BINARY, '-r', 'exit(2);'],
            ],
            sys_get_temp_dir()
        );

        self::assertSame([1], $failed);
    }

    public function testRunStopsAWorkerThatPassesTheDeadline(): void
    {
        $started = time();
        $failed = ProcessPool::run(
            [[PHP_BINARY, '-r', 'sleep(30);']],
            sys_get_temp_dir(),
            1
        );

        self::assertSame([0], $failed);
        self::assertLessThan(15, time() - $started);
    }
}
