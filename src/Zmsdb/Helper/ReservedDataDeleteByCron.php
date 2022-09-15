<?php

namespace BO\Zmsdb\Helper;

/**
 * @codeCoverageIgnore
 */
class ReservedDataDeleteByCron
{
    protected $scopeList;

    protected $verbose = false;

    protected $limit = 10000;

    protected $loopCount = 500;

    protected $count = [];

    public function __construct(\DateTimeInterface $now, $verbose = false)
    {
        if ($verbose) {
            $this->log("INFO: Deleting expired reservations older than scopes reservation duration");
            $this->verbose = true;
        }
        $this->time = $now;
        $this->scopeList = (new \BO\Zmsdb\Scope)->readList();
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
        $this->count = array_fill_keys($this->scopeList->getIds(), 0);
        $this->deleteExpiredReservations($commit);
        $this->log("\nSUMMARY: Deleted reservations: ".var_export($this->count, true));
    }

    protected function deleteExpiredReservations($commit)
    {
        foreach ($this->scopeList as $scope) {
            $this->log("\nDelete expired reservations for scope $scope->id: ");
            $count = $this->deleteByCallback($commit, function ($limit, $offset) use ($scope) {
                $time = clone $this->time;
                $reservationDuration = $scope->toProperty()->preferences->appointment->reservationDuration->get();
                $time = $time->setTimestamp($time->getTimestamp() - ($reservationDuration * 60));
                $processList = (new \BO\Zmsdb\Process)->readExpiredReservationsList($time, $scope->id, $limit, $offset);
                    return $processList;
            });
            $this->count[$scope->id] += $count;
        }
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
        if ('reserved' == $process->status) {
            $this->log("INFO: $processCount. Delete $process");
            if ($commit) {
                $this->deleteProcess($process);
                return 1;
            }
        } elseif ($verbose) {
            $this->log("INFO: Keep process $process->id");
        }
        return 0;
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
