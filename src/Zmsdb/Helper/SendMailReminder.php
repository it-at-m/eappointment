<?php

namespace BO\Zmsdb\Helper;

use BO\Zmsdb\Log;
use BO\Zmsdb\Process as ProcessRepository;
use BO\Zmsdb\Mail as MailRepository;
use BO\Zmsdb\Department as DepartmentRepository;
use BO\Zmsdb\Config as ConfigRepository;
use BO\Zmsentities\Collection\ProcessList as Collection;
use BO\Zmsentities\Mail;
use BO\Zmsentities\Process;

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
        $this->lastRun = (new MailRepository)->readReminderLastRun($now);
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
            (new MailRepository)->writeReminderLastRun($this->dateTime);
        }
        $this->log("\nSUMMARY: Sent mail reminder: ".$this->count);
    }

    protected function writeMailReminderList($commit)
    {
        $count = $this->writeByCallback($commit, function ($limit, $offset) {
            $processList = (new ProcessRepository)->readEmailReminderProcessListByInterval(
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

    protected function writeReminder(Process $process, $commit, $processCount)
    {
        $entity = null;
        $department = (new DepartmentRepository())->readByScopeId($process->getScopeId(), 0);
        if ($process->getFirstClient()->hasEmail() && $department->hasMail()) {
            $config = (new ConfigRepository)->readEntity();
            $collection = $this->getProcessListOverview($process, $config);
            $entity = (new Mail)->toResolvedEntity($collection, $config, 'reminder');
            if ($commit) {
                $entity = (new MailRepository)->writeInQueue($entity, $this->dateTime);
                Log::writeLogEntry("Write Reminder (Mail::writeInQueue) $entity ", $process->getId());
                $this->log(
                    "INFO: $processCount. Write mail in queue with ID ". $entity->getId() ." - ". $entity->subject
                );
            }
        }
        return $entity;
    }

    protected function getProcessListOverview($process, $config)
    {
        $collection  = (new Collection())->addEntity($process);
        if (in_array(getenv('ZMS_ENV'), explode(',', $config->getPreference('appointments', 'enableSummaryByMail')))) {
            $processList = (new ProcessRepository())->readListByMailAndStatusList(
                $process->getFirstClient()->email,
                [
                    Process::STATUS_CONFIRMED,
                    Process::STATUS_PICKUP
                ],
                2,
                50
            );
            //add list of found processes without the main process
            $collection->addList($processList->withOutProcessId($process->getId()));
        }
        return $collection;
    }
}
