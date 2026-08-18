<?php

/**
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

declare(strict_types=1);

namespace BO\Zmsbackend\Helper;

use BO\Zmsbackend\Availability\Service\AvailabilityHistory as AvailabilityHistoryService;
use BO\Zmsbackend\Config\Service\Config as ConfigRepository;

class AvailabilityHistoryCleanUpByCron
{
    protected bool $verbose = false;

    public function __construct(bool $verbose = false)
    {
        if ($verbose) {
            $this->verbose = true;
            $this->log('INFO: Delete old availability_history entries');
        }
    }

    protected function log(string $message): void
    {
        if ($this->verbose) {
            \App::$log->info($message);
        }
    }

    public function startProcessing(bool $commit = false): int
    {
        $config = (new ConfigRepository())->readEntity();
        $olderThan = (int) ($config->getPreference('availability_history', 'deleteOlderThanDays')
            ?? AvailabilityHistoryService::DEFAULT_RETENTION_DAYS);
        if ($olderThan < 1) {
            $olderThan = AvailabilityHistoryService::DEFAULT_RETENTION_DAYS;
        }

        $now = \DateTimeImmutable::createFromInterface(\App::$now ?? new \DateTimeImmutable('now'));
        $cutoff = $now->modify('-' . $olderThan . ' days');

        \App::$log->info('Starting availability_history cleanup', [
            'olderThanDays' => $olderThan,
            'cutoff' => $cutoff->format('Y-m-d H:i:s'),
            'commit' => (bool) $commit,
        ]);

        if (!$commit) {
            return 0;
        }

        $deleted = (new AvailabilityHistoryService())->deleteOlderThanDays($olderThan);
        \App::$log->info('availability_history cleanup completed', ['deleted' => $deleted]);

        return $deleted;
    }
}
