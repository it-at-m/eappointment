<?php

declare(strict_types=1);

namespace BO\Zmsbackend\Helper;

use BO\Zmsbackend\Process\Service\ProcessStatusArchived;

class CleanProcessArchivedToday
{
    protected $verbose = false;

    public function __construct($verbose = false)
    {
        if ($verbose) {
            $this->verbose = true;
            $this->log("INFO: CleanProcessArchivedToday");
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
        $logRepo = new \BO\Zmsbackend\Process\Service\ProcessStatusArchived();
        if ($commit) {
            \App::$log->info('Executing archived process cleanup with commit');
            $result = $logRepo->deleteAllToday();
            \App::$log->info('Archived process cleanup completed', ['success' => (bool) $result]);
        }
    }
}
