<?php

namespace BO\Zmsdb\Helper;

use BO\Zmsdb\Log;
use BO\Zmsdb\Config as ConfigRepository;

class SendNotificationReminder
{
    protected $datetime;

    protected $reminderInSeconds;

    protected $verbose = false;

    protected $limit = 5000;

    protected $loopCount = 500;

    protected $count = 0;

    public function __construct(\DateTimeInterface $now, $verbose = false)
    {
        $config = (new ConfigRepository())->readEntity();
        $configLimit = $config->getPreference('notifications', 'sqlMaxLimit');
        $configBatchSize = $config->getPreference('notifications', 'sqlBatchSize');
        $this->limit = ($configLimit) ? $configLimit : $this->limit;
        $this->loopCount  = ($configBatchSize) ? $configBatchSize : $this->loopCount;
        $this->dateTime = $now;
        if ($verbose) {
            $this->verbose = true;
            $this->log(
                "\nINFO: Send notification reminder (Limits: " .
                $configLimit . "|" . $configBatchSize . ") dependent on lead time"
            );
        }
    }

    protected function log($message)
    {
        if ($this->verbose) {
            error_log(trim($message));
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
        $this->log("\nINFO: Last run " . $this->dateTime->format('Y-m-d H:i:s'));
        $this->log("SUMMARY: Sent notification reminder: " . $this->count);
    }

    protected function writeNotificationReminderList($commit)
    {
        // The offset parameter was removed here, because with each loop the processes are searched, which have not
        // been processed yet. An offset leads to the fact that with the renewed search the first results are skipped.
        $count = $this->writeByCallback($commit, function ($limit) {
            $processList = (new \BO\Zmsdb\Process())->readNotificationReminderProcessList(
                $this->dateTime,
                $limit,
                null,
                1
            );
            return $processList;
        });
        $this->count += $count;
    }

    protected function writeByCallback($commit, \Closure $callback)
    {
        $processCount = 0;
        while ($processCount < $this->limit) {
            $this->log("***Stack count***: " . $processCount);
            $processList = $callback($this->loopCount);
            if (0 == $processList->count()) {
                break;
            }
            foreach ($processList as $process) {
                $this->writeReminder($process, $commit, $processCount);
                $processCount++;
            }
        }
        return $processCount;
    }

    protected function writeReminder(\BO\Zmsentities\Process $process, $commit, $processCount)
    {
        $config = (new \BO\Zmsdb\Config())->readEntity();
        $department = (new \BO\Zmsdb\Department())->readByScopeId($process->getScopeId(), 2);
        $entity = (new \BO\Zmsentities\Notification())->toResolvedEntity($process, $config, $department, 'reminder');

        $this->log("INFO: $processCount Create notification: $entity->message");
        if ($commit) {
            $notification = (new \BO\Zmsdb\Notification())->writeInQueue($entity, $this->dateTime);
            Log::writeProcessLog(
                "Write Reminder (Notification::writeInQueue) $entity ",
                Log::ACTION_SEND_REMINDER,
                $process
            );
            $this->log(
                "INFO: $processCount Notification has been written in queue successfully with ID " .
                $notification->getId()
            );
            $this->deleteReminderTimestamp($process, $notification, $processCount, $commit);
        }
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
                $process = (new \BO\Zmsdb\Process())->updateEntity($process, $this->dateTime);
            }
            $this->log("INFO: $processCount Updated $process->id - reminder timestamp removed");
        } else {
            $this->log(
                "WARNING: $processCount Notification for $process->id not possible - no telephone or not enabled"
            );
        }
    }
}
