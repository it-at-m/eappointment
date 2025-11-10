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
        if (isset(\App::$log)) {
            \App::$log->info("Starting log cleanup process...");
        } else {
            error_log("Starting log cleanup process...");
        }

        $config = (new ConfigRepository())->readEntity();
        $olderThan = $config->getPreference('log', 'deleteOlderThanDays') ?? 90;
        $olderThanDate = (new \DateTime())->modify('-' . $olderThan . ' days');
        $configMessage = "Config loaded, older than: $olderThan days (Datum: " . $olderThanDate->format('Y-m-d H:i:s') . ")";
        if (isset(\App::$log)) {
            \App::$log->info($configMessage);
        } else {
            error_log($configMessage);
        }

        $logRepo = new Log();
        if ($commit) {
            if (isset(\App::$log)) {
                \App::$log->info("Executing cleanup with commit...");
            } else {
                error_log("Executing cleanup with commit...");
            }
            $result = $logRepo->clearLogsOlderThan((int) $olderThan);
            $message = "Cleanup completed. Result: " . ($result ? "success" : "failed");
            if (isset(\App::$log)) {
                \App::$log->info($message);
            } else {
                error_log($message);
            }
        }
    }
}
