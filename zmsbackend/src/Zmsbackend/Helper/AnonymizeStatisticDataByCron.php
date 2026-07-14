<?php

namespace BO\Zmsbackend\Helper;

use BO\Zmsbackend\Process\Service\ProcessStatusArchived;
use BO\Zmsbackend\Config\Service\Config as ConfigRepository;

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
            $this->timespan = (int)$retentionSetting[0];
            \App::$log->info('Using retention period from admin system config', [
                'retentionDays' => $this->timespan,
            ]);
        } else {
            $this->timespan = 90;
            \App::$log->info('Using default retention period', [
                'retentionDays' => $this->timespan,
            ]);
        }
    }

    public function startAnonymizing(\DateTimeImmutable $currentDate, $commit = false)
    {
        // Adjust the currentDate based on the numeric timespan
        $targetDate = $currentDate->modify("-{$this->timespan} days");
        \App::$log->info('Beginning anonymization', [
            'olderThan' => $targetDate->format('Y-m-d'),
            'retentionDays' => $this->timespan,
        ]);

        if ($commit) {
            $processStatusArchived = new \BO\Zmsbackend\Process\Service\ProcessStatusArchived();
            $success = $processStatusArchived->anonymizeNames($targetDate);
            if ($success) {
                \App::$log->info('Anonymization process completed successfully', [
                    'olderThan' => $targetDate->format('Y-m-d'),
                ]);
            } else {
                \App::$log->error('Anonymization process failed', [
                    'olderThan' => $targetDate->format('Y-m-d'),
                ]);
            }
        } else {
            \App::$log->notice('Anonymization dry run — no database changes', [
                'olderThan' => $targetDate->format('Y-m-d'),
            ]);
        }
    }
}
