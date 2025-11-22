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
        } else {
            error_log($message);
        }
    }

    public static function startProcessing($commit = false)
    {
        $logRepo = new ProcessStatusArchived();
        if ($commit) {
            if (isset(\App::$log)) {
                \App::$log->info("Executing cleanup with commit...");
            } else {
                error_log("Executing cleanup with commit...");
            }
            $result = $logRepo->deleteAllToday();
            $message = "Cleanup completed. Result: " . ($result ? "success" : "failed");
            if (isset(\App::$log)) {
                \App::$log->info($message);
            } else {
                error_log($message);
            }
        }
    }
}
