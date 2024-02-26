<?php

namespace BO\Zmsdb\Helper;

use BO\Zmsdb\ProcessStatusArchived;

class AnonymizeStatisticDataByCron
{
    protected $verbose = false;
    protected $timespan = '-2 days';

    public function __construct($verbose = false)
    {
        $this->verbose = $verbose;
    }

    public function startAnonymizing(\DateTimeImmutable $currentDate, $commit = false)
    {
        $targetDate = $currentDate->modify($this->timespan);
        $this->logMessage("INFO: Beginning anonymization for entries older than {$targetDate->format('Y-m-d')}.");

        if ($commit) {
            $processStatusArchived = new ProcessStatusArchived();
            $success = $processStatusArchived->anonymizeNames($targetDate);
            if ($success) {
                $this->logMessage("INFO: Anonymization process completed successfully.");
            } else {
                $this->logMessage("ERROR: An error occurred during the anonymization process.");
            }
        } else {
            $this->logMessage("INFO: Dry run mode - no changes have been made to the database.");
        }
    }

    protected function logMessage($message)
    {
        if ($this->verbose) {
            error_log($message);
        }
    }
}

