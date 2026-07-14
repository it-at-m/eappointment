<?php

/**
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

declare(strict_types=1);

namespace BO\Zmsbackend\Helper;

use BO\Zmsbackend\EventLog\Service\EventLog as EventLogRepository;

class EventLogCleanUpByCron
{
    protected $verbose = false;

    public function __construct($verbose = false)
    {
        if ($verbose) {
            $this->verbose = true;
            $this->log("INFO: Delete old eventlog entries");
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
        $eventLogRepo  = new EventLogRepository();
        if ($commit) {
            $eventLogRepo->deleteOutdated();
        }
    }
}
