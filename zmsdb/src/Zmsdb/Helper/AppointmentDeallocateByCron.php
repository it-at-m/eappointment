<?php

namespace BO\Zmsdb\Helper;

/**
 * @codeCoverageIgnore
 */
class AppointmentDeallocateByCron
{
    use VerboseCronLogTrait;

    protected bool $verbose = false;

    protected $limit = 10000;

    protected $loopCount = 500;

    protected \DateTimeInterface $time;

    /**
     * @var string[]
     *
     * @psalm-var list{'deleted'}
     */
    protected array $statuslist = [
        "deleted"
    ];

    protected $count = [];

    public function __construct(\DateTimeInterface $dateTime, $verbose = false)
    {
        $this->time = $dateTime;
        if ($verbose) {
            $this->log("INFO: Deallocate cancelled appointments by scopes deallocate preference");
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

    public function startProcessing($commit): void
    {
        $this->count['deallocated'] = 0;
        $this->deallocateProcessList($commit);
        $this->log("\nSUMMARY: Deallocated processes: " . var_export($this->count, true));
    }

    protected function deallocateProcessList($commit): void
    {
        $this->log("\nDeallocate cancelled processes");
        $count = $this->deleteByCallback($commit, function ($limit, $offset) {
            $query = new \BO\Zmsdb\Process();
            $processList = $query->readDeallocateProcessList($this->time, $limit, $offset);
            return $processList;
        });
        $this->count["deallocated"] += $count;
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
                if (!$this->handleProcess($process, $commit, $processCount)) {
                    $startposition++;
                }
                $processCount++;
            }
        }
        return $processCount;
    }

    protected function handleProcess(\BO\Zmsentities\Process $process, $commit, int $processCount): int
    {
        $verbose = $this->verbose;
        if (in_array($process->status, $this->statuslist)) {
            $this->log("INFO: $processCount. Deallocate $process");
            if ($commit) {
                $this->writeDeallocatedProcess($process);
                return 1;
            }
        } elseif ($verbose) {
            $this->log("INFO: Keep process $process->id");
        }
        return 0;
    }

    /**
     * It is important to know that the slots in writeBlockedEntity are unblocked again,
     * but the ID remains blocked until the next day
     */
    protected function writeDeallocatedProcess(\BO\Zmsentities\Process $process): void
    {
        $verbose = $this->verbose;
        $query = new \BO\Zmsdb\Process();
        if ($query->writeBlockedEntity($process, true)) {
            if ($verbose) {
                $this->log("INFO: Process $process->id successfully deallocated");
            }
        } else {
            if ($verbose) {
                $this->log("WARN: Could not deallocate process '$process->id'!");
            }
        }
    }
}
