<?php

declare(strict_types=1);

namespace BO\Zmsdb\Helper;

use BO\Zmsdb\ProcessStatusArchived;

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
            error_log($message);
        }
    }

    public static function startProcessing($commit = false)
    {
        $logRepo = new ProcessStatusArchived();
        if ($commit) {
            error_log("Executing cleanup with commit...");
            $result = $logRepo->deleteAllToday();
            error_log("Cleanup completed. Result: " . ($result ? "success" : "failed"));
        }
    }
}
