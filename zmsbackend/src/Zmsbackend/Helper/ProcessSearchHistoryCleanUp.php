<?php

namespace BO\Zmsbackend\Helper;

use BO\Zmsbackend\Config\Service\Config as ConfigService;
use BO\Zmsbackend\ProcessSearchHistory\Service\ProcessSearchHistory as HistoryService;

class ProcessSearchHistoryCleanUp
{
    private const DEFAULT_RETENTION_DAYS = 90;

    public function startProcessing(
        \DateTimeInterface $now,
        bool $commit = false
    ): void {
        $config = (new ConfigService())->readEntity();

        $retentionDays = (int) (
            $config->getPreference(
                'processSearchHistory',
                'deleteOlderThanDays'
            )
            ?? self::DEFAULT_RETENTION_DAYS
        );

        $cutoff = (new \DateTimeImmutable())
            ->setTimestamp($now->getTimestamp())
            ->modify('-' . $retentionDays . ' days');

        \App::$log->info(
            'Process search history cleanup',
            [
                'retentionDays' => $retentionDays,
                'olderThan' => $cutoff->format('Y-m-d H:i:s'),
            ]
        );

        if (!$commit) {
            return;
        }

        $historyService = new HistoryService();
        $historyService->deleteOlderThan($cutoff);
    }
}
