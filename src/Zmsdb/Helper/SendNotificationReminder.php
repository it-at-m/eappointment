<?php

namespace BO\Zmsdb\Helper;

use BO\Zmsdb\Log;

class SendNotificationReminder
{
    protected $datetime;

    protected $reminderInSeconds;

    protected $verbose = false;

    protected $limit = 10000;

    protected $loopCount = 500;

    protected $count = 0;

    public function __construct(\DateTimeInterface $now, $verbose = false)
    {
        $this->dateTime = $now;
        if ($verbose) {
            $this->verbose = true;
            $this->log("\nINFO: Send notification reminder dependent on lead time");
        }
    }

    protected function log($message)
    {
        if ($this->verbose) {
            error_log($message);
        }
    }

    public function getCount()
    {
        return $this->count;
    }

    public function setLimit($limit)
    {
        $this->limit = $limit;
    }

    public function setLoopCount($loopCount)
    {
        $this->loopCount = $loopCount;
    }

    public function startProcessing($commit)
    {
        $this->writeNotificationReminderList($commit);
        $this->log("\nINFO: Last run ". $this->dateTime->format('Y-m-d H:i:s'));
        $this->log("\nSUMMARY: Sent notification reminder: ".$this->count);
    }

    protected function writeNotificationReminderList($commit)
    {
        $count = $this->writeByCallback($commit, function ($limit, $offset) {
            $processList = (new \BO\Zmsdb\Process)->readNotificationReminderProcessList(
                $this->dateTime,
                $limit,
                $offset,
                2
            );
            return $processList;
        });
        $this->count += $count;
    }

    protected function writeByCallback($commit, \Closure $callback)
    {
        $processCount = 0;
        $startposition = 0;
        while ($processCount < $this->limit) {
            $processList = $callback($this->loopCount, $startposition);
            if (0 == $processList->count()) {
                break;
            }
            foreach ($processList as $process) {
                if (!$this->writeReminder($process, $commit, $processCount)) {
                    $startposition++;
                }
                $processCount++;
            }
        }
        return $processCount;
    }

    protected function writeReminder(\BO\Zmsentities\Process $process, $commit, $processCount)
    {
        $notification = null;
        $department = (new \BO\Zmsdb\Department)->readByScopeId($process->getScopeId(), 2);
        if ($process->getFirstClient()->hasTelephone() &&
            $department && $department->hasNotificationReminderEnabled()
        ) {
            $config = (new \BO\Zmsdb\Config)->readEntity();
            $entity = (new \BO\Zmsentities\Notification)->toResolvedEntity($process, $config, $department, 'reminder');
            if ($commit) {
                $notification = (new \BO\Zmsdb\Notification)->writeInQueue($entity, $this->dateTime);
                Log::writeLogEntry(
                    "Write Reminder (Notification::writeInQueue) $entity ", 
                    $process->getId(), 
                    "notificationqueue"
                );
                $this->log(
                    "\nINFO: $processCount. Write reminder notification in queue with ID ". $notification->getId() . " 
                    for process id ". $process->getId()
                );
            }
            $this->deleteReminderTimestamp($process, $notification, $processCount, $commit);
        }
        return $notification;
    }

    protected function deleteReminderTimestamp(
        \BO\Zmsentities\Process $process,
        $notification,
        $processCount,
        $commit
    ) {
        if ($notification) {
            $process->reminderTimestamp = 0;
            if ($commit) {
                $process = (new \BO\Zmsdb\Process)->updateEntity($process, $this->dateTime);
            }
            $this->log("INFO: $processCount. Updated $process->id - reminder timestamp removed");
        } else {
            $this->log("\nWARNING: Notification for $process->id not possible - no telephone or not enabled");
        }
    }
}
