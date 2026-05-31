<?php

/**
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

declare(strict_types=1);

namespace BO\Zmsdb\Helper;

use BO\Zmsdb\EventLog as EventLogRepository;

class EventLogCleanUpByCron
{
    protected bool $verbose = false;

    public function __construct($verbose = false)
    {
        if ($verbose) {
            $this->verbose = true;
            $this->log("INFO: Delete old eventlog entries");
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
        $eventLogRepo  = new EventLogRepository();
        if ($commit) {
            $eventLogRepo->deleteOutdated();
        }
    }
}
