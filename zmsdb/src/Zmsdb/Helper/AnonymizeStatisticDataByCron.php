<?php

namespace BO\Zmsdb\Helper;

use BO\Zmsdb\ProcessStatusArchived;
use BO\Zmsdb\Config as ConfigRepository;

class AnonymizeStatisticDataByCron
{
    protected $verbose = false;
    protected $timespan; // Timespan will be set based on the retention setting

    public function __construct($verbose = false)
    {
        $this->verbose = $verbose;
        
        // Fetching the configuration setting
        $config = (new ConfigRepository())->readEntity();
        
        // Extracting the retention setting and converting it to an integer
        $envValue = getenv('ZMS_ENV');
        $retentionSetting = explode(',', $config->getPreference('buergerarchiv', 'setRetentionPeriodDays'));
        if (isset($retentionSetting) && is_numeric($retentionSetting)) {
            // Ensure it's a positive integer and assign it to timespan
            echo "Using retention period set in config {$retentionSetting}.";
            $this->timespan = abs(intval($retentionSetting));
        } else {
            // Default to 2 days if the setting is not set or not numeric
            echo "Using default retention period 2.";
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
