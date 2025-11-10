<?php

/**
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

declare(strict_types=1);

namespace BO\Zmsdb\Helper;

use BO\Zmsdb\EventLog as EventLogRepository;

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
        if (!$this->verbose) {
            return;
        }
        $level = 'info';
        if (strpos($message, 'WARN:') === 0) {
            $level = 'warning';
        } elseif (strpos($message, 'ERROR:') === 0) {
            $level = 'error';
        }
        $message = preg_replace('/^(INFO|WARN|ERROR):\s*/', '', (string) $message);
        if (isset(\App::$log)) {
            \App::$log->{$level}($message);
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
