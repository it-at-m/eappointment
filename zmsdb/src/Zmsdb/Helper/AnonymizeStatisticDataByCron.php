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
        $retentionSetting = explode(',', $config->getPreference('buergerarchiv', 'setRetentionPeriodDays'));
        if ($retentionSetting[0] !== "none") {
            // Ensure it's a positive integer and assign it to timespan
            if (isset(\App::$log)) {
                \App::$log->info("Using retention period from admin system config", ['days' => (int)$retentionSetting[0]]);
            }
            $this->timespan = (int)$retentionSetting[0];
        } else {
            // Default to 90 days if the setting is not set or not numeric
            if (isset(\App::$log)) {
                \App::$log->info("Using default retention period", ['days' => 90]);
            }
            $this->timespan = 90;
        }
    }

    public function startAnonymizing(\DateTimeImmutable $currentDate, $commit = false)
    {
        // Adjust the currentDate based on the numeric timespan
        $targetDate = $currentDate->modify("-{$this->timespan} days");
        if (isset(\App::$log)) {
            \App::$log->info("Beginning anonymization", ['targetDate' => $targetDate->format('Y-m-d')]);
        }

        if ($commit) {
            $processStatusArchived = new ProcessStatusArchived();
            $success = $processStatusArchived->anonymizeNames($targetDate);
            if ($success) {
                if (isset(\App::$log)) {
                    \App::$log->info("Anonymization process completed successfully");
                }
            } else {
                if (isset(\App::$log)) {
                    \App::$log->error("An error occurred during the anonymization process");
                }
            }
        } else {
            if (isset(\App::$log)) {
                \App::$log->info("Dry run mode - no changes have been made to the database");
            }
        }
    }
}
