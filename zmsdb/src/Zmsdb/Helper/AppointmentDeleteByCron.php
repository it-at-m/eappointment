<?php

namespace BO\Zmsdb\Helper;

/**
 * @codeCoverageIgnore
 */
class AppointmentDeleteByCron
{
    use VerboseCronLogTrait;

    protected bool $verbose = false;

    protected $limit = 10000;

    protected $loopCount = 500;

    /**
     * @var \DateTimeImmutable
     */
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

    /**
     * @var string[]
     *
     * @psalm-var list{'confirmed', 'queued', 'called', 'missed', 'parked', 'processing', 'pending'}
     */
    protected array $archivelist = [
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

    protected function log(string $message, string $level = 'info'): void
    {
        $this->writeVerboseCronLog($message, $level);
    }

    public function getCount()
    {
        return $this->count;
    }

    public function setLimit($limit): void
    {
        $this->limit = $limit;
    }

    public function setLoopCount($loopCount): void
    {
        $this->loopCount = $loopCount;
    }

    public function startProcessing($commit, $pending = false): void
    {
        if ($pending) {
            $this->statuslist[] = "pending";
        }
        $this->count = array_fill_keys($this->statuslist, 0);
        $this->deleteBlockedProcesses($commit);
        $this->deleteExpiredProcesses($commit);
        $this->log("\nSUMMARY: Deleted processes: " . var_export($this->count, true));
    }

    protected function deleteExpiredProcesses($commit): void
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

    protected function deleteBlockedProcesses($commit): void
    {
        $this->log("\nDelete blocked processes in the future:");
        $count = $this->deleteByCallback($commit, function ($limit, $offset) {
            $query = new \BO\Zmsdb\Process();
            $processList = $query->readProcessListByScopeAndStatus(0, 'blocked', 0, $limit, $offset);
            return $processList;
        });
        $this->count["blocked"] += $count;
    }

    /**
     * @psalm-return int<0, max>
     */
    protected function deleteByCallback($commit, \Closure $callback): int
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

    protected function removeProcess(\BO\Zmsentities\Process $process, $commit, int $processCount): int
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

    protected function updateProcessStatus(\BO\Zmsentities\Process $process): \BO\Zmsentities\Process
    {
        if (in_array($process->status, ["confirmed", "queued", "called"])) {
            $process->status = 'missed';
        }
        return $process;
    }

    protected function archiveProcess(\BO\Zmsentities\Process $process): void
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

    protected function deleteProcess(\BO\Zmsentities\Process $process): void
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
