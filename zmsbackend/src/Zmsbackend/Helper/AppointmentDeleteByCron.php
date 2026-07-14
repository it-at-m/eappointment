<?php

namespace BO\Zmsbackend\Helper;

/**
 * @codeCoverageIgnore
 */
class AppointmentDeleteByCron
{
    use VerboseCronLogTrait;

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
        'parked',
        "processing",
    ];

    protected $archivelist = [
        "confirmed",
        "queued",
        "called",
        "missed",
        'parked',
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

    protected function log($message, string $level = 'info')
    {
        $this->writeVerboseCronLog($message, $level);
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
        $this->log("\nSUMMARY: Deleted processes: " . var_export($this->count, true));
    }

    protected function deleteExpiredProcesses($commit)
    {
        foreach ($this->statuslist as $status) {
            $this->log("\nDelete expired processes with status $status:");
            $count = $this->deleteByCallback($commit, function ($limit, $offset) use ($status) {
                $query = new \BO\Zmsbackend\Process\Service\Process();
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
            $query = new \BO\Zmsbackend\Process\Service\Process();
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
                if ($commit && $this->shouldArchiveProcess($process)) {
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

    protected function shouldArchiveProcess(\BO\Zmsentities\Process $process): bool
    {
        if ($process->isDereferenced()) {
            $this->log("WARN: Skip archive for dereferenced process {$process->id}, delete only", 'warning');
            return false;
        }
        if (!$process->getScopeId()) {
            $this->log("WARN: Skip archive for process {$process->id} without scope, delete only", 'warning');
            return false;
        }
        return true;
    }

    protected function archiveProcess(\BO\Zmsentities\Process $process)
    {
        $verbose = $this->verbose;
        $now = new \DateTimeImmutable();
        $archiver = new \BO\Zmsbackend\Process\Service\ProcessStatusArchived();
        $archived = $archiver->writeEntityFinished($process, $now);
        if ($archived && $verbose) {
            $this->log("INFO: Archived with Status=$process->status and Id=" . $archived->archiveId);
        }
    }

    protected function deleteProcess(\BO\Zmsentities\Process $process)
    {
        $verbose = $this->verbose;
        $query = new \BO\Zmsbackend\Process\Service\Process();
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
