<?php

declare(strict_types=1);

namespace BO\Zmsbackend\Helper;

use BO\Zmsbackend\Process\Service\ProcessStatusArchived;
use DateTimeInterface;

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

    protected function log(string $message): void
    {
        if ($this->verbose) {
            \App::$log->info($message);
        }
    }

    public function startProcessing(bool $commit, DateTimeInterface $now): void
    {
        $logRepo = new ProcessStatusArchived();
        if ($commit) {
            \App::$log->info('Executing archived process cleanup with commit', [
                'beforeDate' => $now->format('Y-m-d'),
            ]);
            $result = $logRepo->deleteBeforeDate($now);
            \App::$log->info('Archived process cleanup completed', ['success' => $result]);
        }
    }
}
