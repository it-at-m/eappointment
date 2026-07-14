<?php

/**
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

declare(strict_types=1);

namespace BO\Zmsbackend\Helper;

use BO\Zmsbackend\Config\Service\Config as ConfigRepository;
use BO\Zmsbackend\Log\Service\Log;

class LogCleanUp
{
    protected $verbose = false;

    public function __construct($verbose = false)
    {
        if ($verbose) {
            $this->verbose = true;
            $this->log("INFO: Delete old logs");
        }
    }

    protected function log($message)
    {
        if ($this->verbose) {
            \App::$log->info($message);
        }
    }

    public static function startProcessing($commit = false)
    {
        \App::$log->info('Starting log cleanup process');

        $config = (new ConfigRepository())->readEntity();
        $olderThan = $config->getPreference('log', 'deleteOlderThanDays') ?? 90;
        $olderThanDate = (new \DateTime())->modify('-' . $olderThan . ' days');
        \App::$log->info('Log cleanup config loaded', [
            'olderThanDays' => $olderThan,
            'olderThanDate' => $olderThanDate->format('Y-m-d H:i:s'),
        ]);

        $logRepo = new \BO\Zmsbackend\Log\Service\Log();
        if ($commit) {
            \App::$log->info('Executing log cleanup with commit');
            $result = $logRepo->clearLogsOlderThan((int) $olderThan);
            \App::$log->info('Log cleanup completed', ['success' => (bool) $result]);
        }
    }
}
