<?php

namespace BO\Zmsbackend\Tests\Helper;

use BO\Zmsbackend\Helper\ProcessSearchHistoryCleanUp;
use BO\Zmsbackend\ProcessSearchHistory\Service\ProcessSearchHistory as HistoryService;

class ProcessSearchHistoryCleanUpTest extends \BO\Zmsbackend\Tests\Service\Base
{
    public function testStartProcessing(): void
    {
        $historyService = new HistoryService();

        $oldHistoryKey = hash(
            'sha256',
            'retention-old'
        );

        $boundaryHistoryKey = hash(
            'sha256',
            'retention-boundary'
        );

        $newHistoryKey = hash(
            'sha256',
            'retention-new'
        );

        $now = new \DateTimeImmutable(
            '2010-07-01 12:00:00'
        );

        $cleanup = new ProcessSearchHistoryCleanUp();

        $buergerCountBefore = (int) $historyService->fetchValue(
            'SELECT COUNT(*) FROM `buerger`'
        );

        $archiveCountBefore = (int) $historyService->fetchValue(
            'SELECT COUNT(*) FROM `buergerarchiv`'
        );

        $cleanup->startProcessing(
            $now,
            false
        );

        $this->assertSame(
            1,
            $this->countHistoryEntries(
                $historyService,
                $oldHistoryKey
            )
        );

        $cleanup->startProcessing(
            $now,
            true
        );

        $this->assertSame(
            0,
            $this->countHistoryEntries(
                $historyService,
                $oldHistoryKey
            )
        );

        $this->assertSame(
            0,
            $this->countHistoryEntries(
                $historyService,
                $boundaryHistoryKey
            )
        );

        $this->assertSame(
            1,
            $this->countHistoryEntries(
                $historyService,
                $newHistoryKey
            )
        );

        $this->assertSame(
            $buergerCountBefore,
            (int) $historyService->fetchValue(
                'SELECT COUNT(*) FROM `buerger`'
            )
        );

        $this->assertSame(
            $archiveCountBefore,
            (int) $historyService->fetchValue(
                'SELECT COUNT(*) FROM `buergerarchiv`'
            )
        );
    }

    private function countHistoryEntries(
        HistoryService $service,
        string $historyKey
    ): int {
        return (int) $service->fetchValue(
            '
                SELECT COUNT(*)
                FROM `process_search_history`
                WHERE `history_key` = :historyKey
            ',
            [
                'historyKey' => $historyKey,
            ]
        );
    }
}