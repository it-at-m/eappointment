<?php

/**
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

declare(strict_types=1);

namespace BO\Zmsdb\Helper;

use BO\Zmsdb\Config as ConfigRepository;
use BO\Zmsdb\Log;

class LogCleanUp
{
    protected bool $verbose = false;

    public function __construct($verbose = false)
    {
        if ($verbose) {
            $this->verbose = true;
            $this->log("INFO: Delete old logs");
        }
    }

    protected function log(string $message): void
    {
        if ($this->verbose) {
            \App::$log->info($message);
        }
    }

    public static function startProcessing($commit = false): void
    {
        \App::$log->info('Starting log cleanup process');

        $config = (new ConfigRepository())->readEntity();
        $olderThan = $config->getPreference('log', 'deleteOlderThanDays') ?? 90;
        $olderThanDate = (new \DateTime())->modify('-' . $olderThan . ' days');
        \App::$log->info('Log cleanup config loaded', [
            'olderThanDays' => $olderThan,
            'olderThanDate' => $olderThanDate->format('Y-m-d H:i:s'),
        ]);

        $logRepo = new Log();
        if ($commit) {
            \App::$log->info('Executing log cleanup with commit');
            $result = $logRepo->clearLogsOlderThan((int) $olderThan);
            \App::$log->info('Log cleanup completed', ['success' => (bool) $result]);
        }
    }
}
