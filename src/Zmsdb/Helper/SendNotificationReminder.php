<?php

namespace BO\Zmsdb\Helper;

class SendNotificationReminder
{
    protected $processList;

    protected $datetime;

    protected $verbose = false;

    public function __construct($verbose = false, \DateTimeInterface $now = null)
    {
        if (!$now) {
            $now = new \DateTimeImmutable();
        }
        $this->dateTime = $now;
        if ($verbose) {
            error_log("INFO: Send notification reminder dependent on lead time");
            $this->verbose = true;
        }

        $this->processList = (new \BO\Zmsdb\Process)->readNotificationReminderProcessList($this->dateTime, 10000, 2);
    }

    public function startProcessing($commit)
    {
        foreach ($this->processList as $process) {
            if ($this->verbose) {
                error_log("INFO: Processing $process");
            }
            if ($commit) {
                $notification = $this->writeNotification($process);
                if ($notification) {
                    if ($this->updateProcessRemoveReminder($process) && $this->verbose) {
                        error_log("INFO: Updated $process->id - reminder timestamp removed");
                    }
                } else {
                    error_log("WARNING: Notification for $process->id not possible - no telephone or not enabled");
                }
            }
        }
    }

    protected function writeNotification(\BO\Zmsentities\Process $process)
    {
        $notification = null;
        $department = (new \BO\Zmsdb\Department)->readByScopeId($process->getScopeId(), 2);
        if ($process->getFirstClient()->hasTelephone() && $department->hasNotificationReminderEnabled()) {
            $config = (new \BO\Zmsdb\Config)->readEntity();
            $entity = (new \BO\Zmsentities\Notification)->toResolvedEntity($process, $config, $department);
            $notification = (new \BO\Zmsdb\Notification)->writeInQueue($entity, $this->dateTime);
        }
        return $notification;
    }

    protected function updateProcessRemoveReminder(\BO\Zmsentities\Process $process)
    {
        $process->reminderTimestamp = 0;
        $process = (new \BO\Zmsdb\Process)->updateEntity($process, $this->dateTime);
        return $process;
    }
}
