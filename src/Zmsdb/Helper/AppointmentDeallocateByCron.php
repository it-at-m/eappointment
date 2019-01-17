<?php

namespace BO\Zmsdb\Helper;

/**
 * @codeCoverageIgnore
 */
class AppointmentDeallocateByCron
{
    protected $verbose = false;
    
    protected $limit = 10000;
    
    protected $loopCount = 500;

    protected $time;
    
    protected $statuslist = [
        "deleted"
    ];

    public function __construct($verbose = false, $dateTime = null)
    {
        $this->time = ($dateTime) ? $dateTime : new \DateTimeImmutable();
        if ($verbose) {
            error_log(
                "INFO: Deallocate canceled appointments by scopes deallocate preference"
            );
            $this->verbose = true;
        }
    }

    public function startProcessing($commit)
    {
        $query = new \BO\Zmsdb\Process();
        $processCount = $this->loopCount;
        while ($processCount < $this->limit) {
            $processList = $query->readDeallocateProcessList($this->time, $this->loopCount);
            if (0 == $processList->count()) {
                if ($this->verbose) {
                    error_log("INFO: No process found");
                }
                break;
            }
            if ($this->limit > $processList->count()) {
                $this->limit = $processList->count();
            }
            foreach ($processList as $process) {
                if ($this->verbose) {
                    error_log("INFO: Processing $process");
                }
                if ($commit) {
                    $this->writeDeallocatedProcess($process);
                }
                $processCount++;
            }
        }
    }

    protected function writeDeallocatedProcess(\BO\Zmsentities\Process $process)
    {
        $query = new \BO\Zmsdb\Process();
        $verbose = $this->verbose;
        if (in_array($process->status, $this->statuslist)) {
            if ($query->writeBlockedEntity($process) && $verbose) {
                error_log("INFO: Process $process->id successfully deallocated");
            } elseif ($verbose) {
                error_log("WARN: Could not deallocate process '$process->id'!");
            }
        } elseif ($verbose) {
            error_log("INFO: Keep process $process->id");
        }
    }
}
