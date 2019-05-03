<?php

namespace BO\Zmsdb\Helper;

/**
 * @codeCoverageIgnore
 */
class AppointmentDeleteByCron
{
    protected $verbose = false;

    protected $limit = 10000;

    protected $loopCount = 500;

    protected $time;

    protected $statuslist = [
        "reserved",
        "deleted",
        "blocked",
        "confirmed",
        "queued",
        "called",
        "missed",
        "processing",
        "free",
    ];

    protected $archivelist = [
        "confirmed",
        "queued",
        "called",
        "missed",
        "processing",
        "pending"
    ];

    public function __construct($timeIntervalDays, \DateTimeInterface $now, $verbose = false)
    {
        $deleteInSeconds = (24 * 60 * 60) * $timeIntervalDays;
        $time = new \DateTimeImmutable();
        $this->time = $time->setTimestamp($now->getTimestamp() - $deleteInSeconds);
        if ($verbose) {
            error_log(
                "INFO: Deleting appointments older than $timeIntervalDays days of date " . $this->time->format('c')
            );
            $this->verbose = true;
        }
    }

    protected function log($message)
    {
        if ($this->verbose) {
            error_log($message);
        }
    }

    public function startProcessing($commit, $pending = false)
    {
        if ($pending) {
            $this->statuslist[] = "pending";
        }
        $this->deleteExpiredProcesses($commit);
        $this->deleteBlockedProcesses($commit);
    }

    protected function deleteExpiredProcesses($commit)
    {
        $query = new \BO\Zmsdb\Process();
        $processCount = 0;
        while ($processCount < $this->limit) {
            $processList = $query->readExpiredProcessList($this->time, $this->loopCount);
            if (0 == $processList->count()) {
                break;
            }
            foreach ($processList as $process) {
                $this->removeProcess($process, $commit);
                $processCount++;
            }
            if ($this->verbose && !$commit) {
                echo "\nAttention, without commit, only the first 500 deletable can be displayed\n";
                break;
            }
        }
    }

    protected function deleteBlockedProcesses($commit)
    {
        $query = new \BO\Zmsdb\Process();
        $this->log("\nDelete blocked processes in the future:");
        $processCount = 0;
        while ($processCount < $this->limit) {
            $processList = $query->readProcessListByScopeAndStatus(0, 'blocked');
            if (0 == $processList->count()) {
                break;
            }
            foreach ($processList as $process) {
                $this->removeProcess($process, $commit);
                $processCount++;
            }
            if ($this->verbose && !$commit) {
                echo "\nAttention, without commit, only the first 1000 deletable can be displayed\n";
                break;
            }
        }
    }

    protected function removeProcess(\BO\Zmsentities\Process $process, $commit)
    {
        $verbose = $this->verbose;
        if (in_array($process->status, $this->statuslist)) {
            if (in_array($process->status, $this->archivelist)) {
                $this->log("INFO: Archive $process");
                $process = $this->updateProcessStatus($process);
                if ($commit) {
                    $this->archiveProcess($process);
                }
            }
            $this->log("INFO: Delete $process");
            if ($commit) {
                $this->deleteProcess($process);
            }
        } elseif ($verbose) {
            error_log("INFO: Keep process $process");
        }
    }

    protected function updateProcessStatus(\BO\Zmsentities\Process $process)
    {
        if (in_array($process->status, ["confirmed", "queued", "called"])) {
            $process->status = 'missed';
        }
        return $process;
    }

    protected function archiveProcess(\BO\Zmsentities\Process $process)
    {
        $verbose = $this->verbose;
        $now = new \DateTimeImmutable();
        $archiver = new \BO\Zmsdb\ProcessStatusArchived();
        $archived = null;
        $archived = $archiver->writeEntityFinished($process, $now);
        if ($archived && $verbose) {
            error_log("INFO: Archived with Status=$process->status and Id=" . $archived->archiveId);
        }
    }

    protected function deleteProcess(\BO\Zmsentities\Process $process)
    {
        $verbose = $this->verbose;
        $query = new \BO\Zmsdb\Process();
        if ($query->writeDeletedEntity($process->id)) {
            if ($verbose) {
                error_log("INFO: Process $process->id successfully removed");
            }
        } else {
            if ($verbose) {
                error_log("WARN: Could not remove process '$process->id'!");
            }
        }
    }
}
