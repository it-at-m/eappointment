<?php

namespace BO\Zmsdb\Helper;

use BO\Zmsdb\ProcessStatusArchived;

class AnonymizeStatisticDataByCron
{
    protected $verbose = false;
    protected $timespan; // Timespan will be set based on the retention setting

    public function __construct($verbose = false)
    {
        $this->verbose = $verbose;
        
        // Fetching the configuration setting
        $config = \App::$http->readGetResult('/config/', [], \App::CONFIG_SECURE_TOKEN)->getEntity();
        
        // Extracting the retention setting and converting it to an integer
        $retentionSetting = explode(',', $config->getPreference('buergerarchiv', 'setRetentionPeriodDays'));
        if (isset($retentionSetting[0]) && is_numeric($retentionSetting[0])) {
            // Ensure it's a positive integer and assign it to timespan
            $this->timespan = abs(intval($retentionSetting[0]));
        } else {
            // Default to 2 days if the setting is not set or not numeric
            $this->timespan = 2;
        }
    }

    public function startAnonymizing(\DateTimeImmutable $currentDate, $commit = false)
    {
        // Adjust the currentDate based on the numeric timespan
        $targetDate = $currentDate->modify("-{$this->timespan} days");
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
