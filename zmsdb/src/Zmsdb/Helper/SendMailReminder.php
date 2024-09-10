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

    protected $defaultReminderInMinutes;

    protected $verbose = false;

    protected $limit = 5000;

    protected $loopCount = 500;

    protected $count = 0;

    public function __construct(\DateTimeInterface $now, $sendReminderBeforeMinutes, $verbose = false)
    {
        $config = (new ConfigRepository())->readEntity();
        $configLimit = $config->getPreference('mailings', 'sqlMaxLimit');
        $configBatchSize = $config->getPreference('mailings', 'sqlBatchSize');
        $this->limit = ($configLimit) ? $configLimit : $this->limit;
        $this->loopCount  = ($configBatchSize) ? $configBatchSize : $this->loopCount;
        $this->dateTime = $now;
        $this->defaultReminderInMinutes = $sendReminderBeforeMinutes;
        $this->lastRun = (new MailRepository)->readReminderLastRun($now);
        if ($verbose) {
            $this->verbose = true;
            $this->log(
                "\nINFO: Send email reminder (Limits: ".
                $configLimit ."|". $configBatchSize .") dependent on last run: ".
                $this->lastRun->format('Y-m-d H:i:s')
            );
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
        if ($commit) {
            (new MailRepository)->writeReminderLastRun($this->dateTime);
        }
        $this->writeMailReminderList($commit);
        $this->log("\nINFO: Last run ". $this->dateTime->format('Y-m-d H:i:s'));
        $this->log("\nSUMMARY: Sent mail reminder: ".$this->count);
    }

    protected function writeMailReminderList($commit)
    {
        // The offset parameter was removed here, because with each loop the processes are searched, which have not
        // been processed yet. An offset leads to the fact that with the renewed search the first results are skipped.
        $count = $this->writeByCallback($commit, function ($limit) {
            $processList = (new ProcessRepository)->readEmailReminderProcessListByInterval(
                $this->dateTime,
                $this->lastRun,
                $this->defaultReminderInMinutes,
                $limit,
                null,
                2
            );
            return $processList;
        });
        $this->count += $count;
    }

    protected function writeByCallback($commit, \Closure $callback)
    {
        $processCount = 0;
        while ($processCount < $this->limit) {
            $this->log("***Stack count***: ".$processCount);
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

    protected function writeReminder(Process $process, $commit, $processCount)
    {
        $department = (new DepartmentRepository())->readByScopeId($process->getScopeId(), 0);
        if ($process->getFirstClient()->hasEmail() && $department->hasMail()) {
            $config = (new ConfigRepository)->readEntity();
            $collection = $this->getProcessListOverview($process, $config);
            
            $entity = (new Mail())
            ->setTemplateProvider(new \BO\Zmsdb\Helper\MailTemplateProvider($process))
            ->toResolvedEntity($collection, $config, 'reminder');
            
            $this->log(
                "INFO: $processCount. Create mail with process ". $process->getId() .
                " - ". $entity->subject ." for ". $process->getFirstAppointment()
            );
            if ($commit) {
                $entity = (new MailRepository)->writeInQueue($entity, $this->dateTime);
                Log::writeLogEntry("Write Reminder (Mail::writeInQueue) $entity ",
                    $process->getId(),
                    Log::PROCESS,
                    $process->getScopeId()
                );
                $this->log("INFO: Mail has been written in queue successfully with ID ". $entity->getId());
            }
        }
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
