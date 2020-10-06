<?php

namespace BO\Zmsdb\Helper;

class SendMailReminder
{
    protected $processList;

    protected $datetime;

    protected $verbose = false;

    public function __construct($hours = 2, $verbose = false, \DateTimeInterface $now)
    {
        $this->dateTime = $now;
        $reminderInSeconds = (60 * 60) * $hours;
        $lastRun = (new \BO\Zmsdb\Mail)->readReminderLastRun($now);
        if ($verbose) {
            error_log("INFO: Send email reminder dependent on last run: ". $lastRun->format('Y-m-d H:i:s'));
            $this->verbose = true;
        }
        
        $this->processList = (new \BO\Zmsdb\Process)->readEmailReminderProcessListByInterval(
            $now,
            $lastRun,
            $reminderInSeconds,
            10000,
            2
        );
    }

    public function startProcessing($commit)
    {
        foreach ($this->processList as $process) {
            if ($this->verbose) {
                error_log("INFO: Processing $process");
            }
            if ($commit) {
                if (null == $this->writeReminder($process)) {
                    error_log("WARNING: Mail reminder for $process->id not possible - no email or not enabled");
                }
            }
        }
        if ($this->verbose) {
            error_log("INFO: Last run ". $this->dateTime->format('Y-m-d H:i:s'));
        }
        if ($commit) {
            (new \BO\Zmsdb\Mail)->writeReminderLastRun($this->dateTime);
        }
    }

    protected function writeReminder(\BO\Zmsentities\Process $process)
    {
        $entity = null;
        $department = (new \BO\Zmsdb\Department())->readByScopeId($process->getScopeId(), 0);
        if ($process->getFirstClient()->hasEmail() && $department->hasMail()) {
            $config = (new \BO\Zmsdb\Config)->readEntity();
            $entity = (new \BO\Zmsentities\Mail)->toResolvedEntity($process, $config, 'reminder');
            $entity = (new \BO\Zmsdb\Mail)->writeInQueue($entity, $this->dateTime);
        }
        return $entity;
    }
}
