<?php

namespace BO\Zmsdb\Helper;

use BO\Zmsdb\Log;

class SendMailReminder
{
    protected $datetime;

    protected $lastRun;

    protected $reminderInSeconds;

    protected $verbose = false;

    protected $limit = 10000;

    protected $loopCount = 500;

    protected $count = 0;

    public function __construct(\DateTimeInterface $now, $hours = 2, $verbose = false)
    {
        $this->dateTime = $now;
        $this->reminderInSeconds = (60 * 60) * $hours;
        $this->lastRun = (new \BO\Zmsdb\Mail)->readReminderLastRun($now);
        if ($verbose) {
            $this->verbose = true;
            $this->log("\nINFO: Send email reminder dependent on last run: ". $this->lastRun->format('Y-m-d H:i:s'));
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
        $this->writeMailReminderList($commit);
        $this->log("\nINFO: Last run ". $this->dateTime->format('Y-m-d H:i:s'));
        if ($commit) {
            (new \BO\Zmsdb\Mail)->writeReminderLastRun($this->dateTime);
        }
        $this->log("\nSUMMARY: Sent mail reminder: ".$this->count);
    }

    protected function writeMailReminderList($commit)
    {
        $count = $this->writeByCallback($commit, function ($limit, $offset) {
            $processList = (new \BO\Zmsdb\Process)->readEmailReminderProcessListByInterval(
                $this->dateTime,
                $this->lastRun,
                $this->reminderInSeconds,
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
        $entity = null;
        $department = (new \BO\Zmsdb\Department())->readByScopeId($process->getScopeId(), 0);
        if ($process->getFirstClient()->hasEmail() && $department->hasMail()) {
            $config = (new \BO\Zmsdb\Config)->readEntity();
            $entity = (new \BO\Zmsentities\Mail)->toResolvedEntity($process, $config, 'reminder');
            if ($commit) {
                $entity = (new \BO\Zmsdb\Mail)->writeInQueue($entity, $this->dateTime);
                Log::writeLogEntry("Write Reminder (Mail::writeInQueue) $entity ", $process->getId(), "mailqueue");
                $this->log(
                    "INFO: $processCount. Write mail in queue with ID ". $entity->getId() ." - ". $entity->subject
                );
            }
        }
        return $entity;
    }
}
