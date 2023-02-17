<?php

namespace BO\Zmsdb\Helper;

/**
 * @codeCoverageIgnore
 */
use BO\Zmsentities\Collection\ProcessList as Collection;

class ReservedDataDeleteByCron
{
    protected $scopeList;

    protected $verbose = false;

    protected $limit = 500;

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

    public function startProcessing($commit)
    {
        $this->count = array_fill_keys($this->scopeList->getIds(), 0);
        $this->deleteExpiredReservations($commit);
        $this->log("\nSUMMARY: Processed reservations: ".var_export(array_filter($this->count), true));
    }

    protected function deleteExpiredReservations($commit)
    {
        foreach ($this->scopeList as $scope) {
            $count = $this->deleteByCallback($commit, function ($loopLimit) use ($scope) {
                $reservationDuration = $scope->toProperty()->preferences->appointment->reservationDuration->get();
                $expiredTime = $this->getExpiredTimeByScopePreference($reservationDuration);
                $processList = (new \BO\Zmsdb\Process)
                    ->readExpiredReservationsList($expiredTime, $scope->id, $loopLimit)
                    ->sortByCustomKey('createTimestamp');
                if ($processList->count()) {
                    $this->log(
                        "Now: ". $this->time->format('H:i:s') .
                        "\nExpiring time: ". $expiredTime->format('H:i:s') ." | scope ". $scope->id .
                        " | duration $reservationDuration minutes (". $processList->count() . " found)\n-------------------------------------------------------------------"
                    );
                }
                return $processList;
            });

            $this->count[$scope->id] += $count;
        }
    }

    protected function getExpiredTimeByScopePreference($reservationDuration)
    {
        $expiredTime = clone $this->time;
        $expiredTimestamp = ($expiredTime->getTimestamp() - ($reservationDuration * 60));
        $expiredTime = $expiredTime->setTimestamp($expiredTimestamp);
        return $expiredTime;
    }

    protected function deleteByCallback($commit, \Closure $callback): int
    {
        $processCount = 0;
        $processList = $callback($this->limit);
        foreach ($processList as $process) {
            if ('reserved' == $process->status) {
                $this->log(
                    "INFO: ($process->id) found reservation with age of ". 
                    ($this->time->getTimestamp() - $process->createTimestamp) ." seconds"
                );
                if ($commit) {
                    $this->writeDeleteProcess($process);
                }
            } elseif ($this->verbose) {
                $this->log("INFO: Keep process $process->id with status $process->status");
            }
                $processCount++;
        }
        return $processCount;
    }

    protected function writeDeleteProcess(\BO\Zmsentities\Process $process)
    {
        $verbose = $this->verbose;
        $query = new \BO\Zmsdb\Process();
        if ($query->writeDeletedEntity($process->id)) {
            if ($verbose) {
                $this->log("INFO: ($process->id) removed successfully\n");
            }
        } else {
            if ($verbose) {
                $this->log("WARN: Could not remove process '$process->id'!\n");
            }
        }
    }
}
