<?php

namespace BO\Zmsbackend\Tests\Helper;

use \BO\Zmsbackend\Helper\AppointmentDeleteByCron;
use \BO\Zmsbackend\Process\Service\Process as Query;
use Psr\Log\LoggerInterface;
use \BO\Zmsbackend\ProcessSearchHistory\Service\ProcessSearchHistory as HistoryService;

class AppointmentDeleteByCronTest extends \BO\Zmsbackend\Tests\Service\Base
{
    private const CLEANUP_NOW = '2016-04-01 07:00:00';

    private const PROCESS_CONFIRMED = 990101;
    private const PROCESS_QUEUED = 990102;
    private const PROCESS_CALLED = 990103;
    private const PROCESS_MISSED = 990104;
    private const PROCESS_PROCESSING = 990105;
    private const PROCESS_PARKED = 990106;
    private const PROCESS_PENDING = 990107;
    private const PROCESS_RESERVED = 990108;
    private const PROCESS_DELETED = 990109;
    private const PROCESS_BLOCKED = 990110;

    public function setUp(): void
    {
        $mockLogger = $this->createMock(LoggerInterface::class);
        $mockLogger->expects($this->any())
            ->method('info');
        \App::$log = $mockLogger;

        parent::setUp();
    }

    public function testConstructor()
    {
        $now = new \DateTimeImmutable('2016-04-02 11:55');
        $helper = new AppointmentDeleteByCron(0, $now, false);
        $this->assertInstanceOf(AppointmentDeleteByCron::class, $helper);
    }

    public function testStartProcessingByCron()
    {
        $now = new \DateTimeImmutable('2016-04-02 00:10');
        $expired = new \DateTimeImmutable('2016-04-02 00:10');
        $helper = new AppointmentDeleteByCron(0, $now, false); // verbose
        $helper->setLimit(10);
        $helper->setLoopCount(5);
        $helper->startProcessing(false, false);
        $this->assertEquals(10, $helper->getCount()['preconfirmed']);
    }

    public function testStartProcessingExpiredExakt()
    {
        $now = new \DateTimeImmutable('2016-04-01 07:00');
        $expired = new \DateTimeImmutable('2016-04-01 07:00');
        $helper = new AppointmentDeleteByCron(0, $now, false); // verbose
        $helper->setLimit(10);
        $helper->setLoopCount(5);
        $helper->startProcessing(false, false);
        $this->assertEquals(8, $helper->getCount()['preconfirmed']);
        $this->assertEquals(8, count((new Query())->readExpiredProcessListByStatus($expired, 'preconfirmed')));
     
        $helper->startProcessing(true, false);
        $this->assertEquals(0, count((new Query())->readExpiredProcessListByStatus($expired, 'preconfirmed')));
    }

    public function testCleanupWritesMissedHistory(): void
    {
        $this->runCleanup();

        foreach ([
            self::PROCESS_CONFIRMED,
            self::PROCESS_QUEUED,
            self::PROCESS_CALLED,
            self::PROCESS_MISSED,
        ] as $processId) {
            $this->assertHistoryStatus(
                $processId,
                HistoryService::STATUS_MISSED
            );

            $this->assertFalse(
                $this->processExists($processId)
            );
        }
    }

    public function testCleanupWritesCompletedHistory(): void
    {
        $this->runCleanup();

        foreach ([
            self::PROCESS_PROCESSING,
            self::PROCESS_PARKED,
        ] as $processId) {
            $this->assertHistoryStatus(
                $processId,
                HistoryService::STATUS_COMPLETED
            );

            $this->assertFalse(
                $this->processExists($processId)
            );
        }
    }

    public function testPendingIsOnlyArchivedWhenEnabled(): void
    {
        $this->runCleanup(false);

        $this->assertNoHistory(
            self::PROCESS_PENDING
        );

        $this->assertTrue(
            $this->processExists(self::PROCESS_PENDING)
        );

        $this->runCleanup(true);

        $this->assertHistoryStatus(
            self::PROCESS_PENDING,
            HistoryService::STATUS_COMPLETED
        );

        $this->assertFalse(
            $this->processExists(self::PROCESS_PENDING)
        );
    }

    public function testCleanupDoesNotWriteHistoryForNonArchivedStatuses(): void
    {
        $this->runCleanup();

        foreach ([
            self::PROCESS_RESERVED,
            self::PROCESS_DELETED,
            self::PROCESS_BLOCKED,
        ] as $processId) {
            $this->assertNoHistory($processId);

            $this->assertFalse(
                $this->processExists($processId)
            );
        }
    }

    private function runCleanup(bool $pending = false): void
    {
        $helper = new AppointmentDeleteByCron(
            0,
            new \DateTimeImmutable(self::CLEANUP_NOW),
            false
        );

        $helper->setLimit(1);
        $helper->setLoopCount(1);

        $helper->startProcessing(
            true,
            $pending
        );
    }

    private function assertHistoryStatus(
        int $processId,
        string $expectedStatus
    ): void {
        $service = new HistoryService();

        $historyEntry = $service->fetchRow(
            '
                SELECT
                    `process_id`,
                    `status`,
                    `finalized_at`
                FROM `process_search_history`
                WHERE `process_id` = :processId
                ORDER BY `id` DESC
                LIMIT 1
            ',
            [
                'processId' => $processId,
            ]
        );

        $this->assertIsArray($historyEntry);

        $this->assertSame(
            $processId,
            (int) $historyEntry['process_id']
        );

        $this->assertSame(
            $expectedStatus,
            $historyEntry['status']
        );

        $this->assertSame(
            self::CLEANUP_NOW,
            $historyEntry['finalized_at']
        );
    }

    private function assertNoHistory(int $processId): void
    {
        $service = new HistoryService();

        $count = (int) $service->fetchValue(
            '
                SELECT COUNT(*)
                FROM `process_search_history`
                WHERE `process_id` = :processId
            ',
            [
                'processId' => $processId,
            ]
        );

        $this->assertSame(0, $count);
    }

    private function processExists(int $processId): bool
    {
        $service = new HistoryService();

        return (int) $service->fetchValue(
            '
                SELECT COUNT(*)
                FROM `buerger`
                WHERE `BuergerID` = :processId
            ',
            [
                'processId' => $processId,
            ]
        ) > 0;
    }
}
