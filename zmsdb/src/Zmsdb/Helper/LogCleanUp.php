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
            error_log($message);
        }
    }

    public static function startProcessing($commit = false)
    {
        error_log("Starting log cleanup process...");

        $config = (new ConfigRepository())->readEntity();
        $olderThan = $config->getPreference('log', 'deleteOlderThanDays') ?? 90;
        $olderThanDate = (new \DateTime())->modify('-' . $olderThan . ' days');
        error_log("Config loaded, older than: $olderThan days (Datum: " . $olderThanDate->format('Y-m-d H:i:s') . ")");

        $logRepo = new Log();
        if ($commit) {
            error_log("Executing cleanup with commit...");
            $result = $logRepo->clearDataOlderThan((int) $olderThan);
            error_log("Cleanup completed. Result: " . ($result ? "success" : "failed"));
        }
    }
}
