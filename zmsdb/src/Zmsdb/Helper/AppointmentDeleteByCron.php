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
        "blocked",
        "reserved",
        "deleted",
        "confirmed",
        "preconfirmed",
        "queued",
        "called",
        "missed",
        "processing",
    ];

    protected $archivelist = [
        "confirmed",
        "queued",
        "called",
        "missed",
        "processing",
        "pending"
    ];

    protected $count = [];

    public function __construct($timeIntervalDays, \DateTimeInterface $now, $verbose = false)
    {
        $deleteInSeconds = (24 * 60 * 60) * $timeIntervalDays;
        $time = new \DateTimeImmutable();
        $this->time = $time->setTimestamp($now->getTimestamp() - $deleteInSeconds);
        if ($verbose) {
            $this->log("INFO: Deleting appointments older than " . $this->time->format('c'));
            $this->verbose = true;
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

    public function startProcessing($commit, $pending = false)
    {
        if ($pending) {
            $this->statuslist[] = "pending";
        }
        $this->count = array_fill_keys($this->statuslist, 0);
        $this->deleteBlockedProcesses($commit);
        $this->deleteExpiredProcesses($commit);
        $this->log("\nSUMMARY: Deleted processes: ".var_export($this->count, true));
    }

    protected function deleteExpiredProcesses($commit)
    {
        foreach ($this->statuslist as $status) {
            $this->log("\nDelete expired processes with status $status:");
            $count = $this->deleteByCallback($commit, function ($limit, $offset) use ($status) {
                $query = new \BO\Zmsdb\Process();
                $processList = $query->readExpiredProcessListByStatus($this->time, $status, $limit, $offset);
                return $processList;
            });
            $this->count[$status] += $count;
        }
    }

    protected function deleteBlockedProcesses($commit)
    {
        $this->log("\nDelete blocked processes in the future:");
        $count = $this->deleteByCallback($commit, function ($limit, $offset) {
            $query = new \BO\Zmsdb\Process();
            $processList = $query->readProcessListByScopeAndStatus(0, 'blocked', 0, $limit, $offset);
            return $processList;
        });
        $this->count["blocked"] += $count;
    }

    protected function deleteByCallback($commit, \Closure $callback)
    {
        $processCount = 0;
        $startposition = 0;
        while ($processCount < $this->limit) {
            $processList = $callback($this->loopCount, $startposition);
            if (0 == $processList->count()) {
                break;
            }
            foreach ($processList as $process) {
                if (!$this->removeProcess($process, $commit, $processCount)) {
                    $startposition++;
                }
                $processCount++;
            }
        }
        return $processCount;
    }

    protected function removeProcess(\BO\Zmsentities\Process $process, $commit, $processCount)
    {
        $verbose = $this->verbose;
        if (in_array($process->status, $this->statuslist)) {
            if (in_array($process->status, $this->archivelist)) {
                $this->log("INFO: $processCount. Archive $process");
                $process = $this->updateProcessStatus($process);
                if ($commit) {
                    $this->archiveProcess($process);
                }
            }
            $this->log("INFO: $processCount. Delete $process");
            if ($commit) {
                $this->deleteProcess($process);
                return 1;
            }
        } elseif ($verbose) {
            $this->log("INFO: Keep process $process");
        }
        return 0;
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
            $this->log("INFO: Archived with Status=$process->status and Id=" . $archived->archiveId);
        }
    }

    protected function deleteProcess(\BO\Zmsentities\Process $process)
    {
        $verbose = $this->verbose;
        $query = new \BO\Zmsdb\Process();
        if ($query->writeDeletedEntity($process->id)) {
            if ($verbose) {
                $this->log("INFO: Process $process->id successfully removed");
            }
        } else {
            if ($verbose) {
                $this->log("WARN: Could not remove process '$process->id'!");
            }
        }
    }
}
