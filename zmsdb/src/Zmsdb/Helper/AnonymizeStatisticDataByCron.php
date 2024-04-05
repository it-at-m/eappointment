<?php

namespace BO\Zmsdb\Helper;

use BO\Zmsdb\ProcessStatusArchived;
use BO\Zmsdb\Config as ConfigRepository;

class AnonymizeStatisticDataByCron
{
    protected $verbose = false;
    protected $timespan = '-90 days';

    public function __construct($verbose = false)
    {
        $this->verbose = $verbose;
        
        // Fetching the configuration setting
        $config = (new ConfigRepository())->readEntity();
        
        // Extracting the retention setting and converting it to an integer
        $envValue = getenv('ZMS_ENV');
        $retentionSetting = explode(',', $config->getPreference('buergerarchiv', 'setRetentionPeriodDays'));
        print_r($retentionSetting);
        if ($retentionSetting[0] !== "none") {
            // Ensure it's a positive integer and assign it to timespan
            print("Using retention period set in config {$retentionSetting[0]}.\n\n");
            $this->timespan = (int)$retentionSetting[0];
        } else {
            // Default to 90 days if the setting is not set or not numeric
            print("Using default retention period 90.\n\n");
            $this->timespan = 90;
        }
    }

    public function startAnonymizing(\DateTimeImmutable $currentDate, $commit = false)
    {
        // Adjust the currentDate based on the numeric timespan
        $targetDate = $currentDate->modify("-{$this->timespan} days");
        $this->print("INFO: Beginning anonymization for entries older than {$targetDate->format('Y-m-d')}.\n\n");

        if ($commit) {
            $processStatusArchived = new ProcessStatusArchived();
            $success = $processStatusArchived->anonymizeNames($targetDate);
            if ($success) {
                $this->print("INFO: Anonymization process completed successfully.\n\n");
            } else {
                $this->print("ERROR: An error occurred during the anonymization process.\n\n");
            }
        } else {
            $this->print("INFO: Dry run mode - no changes have been made to the database.\n\n");
        }
    }
}