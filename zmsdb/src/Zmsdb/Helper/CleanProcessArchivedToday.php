<?php

declare(strict_types=1);

namespace BO\Zmsdb\Helper;

use BO\Zmsdb\ProcessStatusArchived;

class CleanProcessArchivedToday
{
    protected bool $verbose = false;

    public function __construct($verbose = false)
    {
        if ($verbose) {
            $this->verbose = true;
            $this->log("INFO: CleanProcessArchivedToday");
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
        $logRepo = new ProcessStatusArchived();
        if ($commit) {
            \App::$log->info('Executing archived process cleanup with commit');
            $result = $logRepo->deleteAllToday();
            \App::$log->info('Archived process cleanup completed', ['success' => (bool) $result]);
        }
    }
}
