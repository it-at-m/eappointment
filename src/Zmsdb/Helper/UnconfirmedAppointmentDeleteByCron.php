<?php

namespace BO\Zmsdb\Helper;

/**
 * @codeCoverageIgnore
 */
class UnconfirmedAppointmentDeleteByCron
{
    protected $verbose = false;

    protected $limit = 1000;

    protected $loopCount = 100;

    protected $time;

    protected $now;

    protected $statusListForDeletion = ['preconfirmed'];

    protected $count = [];

    public function __construct(\DateTimeInterface $now, $verbose = false)
    {
        $this->now = $now;
        $this->verbose = $verbose;
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
        $this->deleteUnconfirmedProcesses($commit);
        $this->log("\nSUMMARY: Deleted processes: ".var_export($this->count, true));
    }

    protected function deleteUnconfirmedProcesses($commit)
    {
        foreach ($this->scopeList as $scope) {
            $count = $this->deleteByCallback($commit, function ($limit, $offset) use ($scope) {
                $query = new \BO\Zmsdb\Process();
                $activationDuration = $scope->toProperty()->preferences->appointment->activationDuration->get();
                $time = new \DateTimeImmutable();
                $deleteFromTime = $time->setTimestamp(
                    $this->now->getTimestamp() - ($activationDuration * 60)
                );

                if ($this->verbose) {
                    $this->log(
                        "INFO: Deleting appointments older than "
                        . $deleteFromTime->format('c') . 'limit: '
                        . $limit . ' offset: '
                        . $offset
                    );
                }

                $processList = $query->readUnconfirmedProcessList(
                    $deleteFromTime,
                    $scope->id,
                    $limit,
                    $offset
                );
                return $processList;
            });
            $this->count['preconfirmed'] = $count;
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

            if ($this->verbose) {
                $this->log("INFO: ProcessList count " . $processList->count());
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

    protected function removeProcess(\BO\Zmsentities\Process $process, $commit)
    {
        $verbose = $this->verbose;
        if (in_array($process->status, $this->statusListForDeletion)) {
            if ($commit) {
                $this->deleteProcess($process);
                return 1;
            }
        } elseif ($verbose) {
            $this->log("INFO: Keep process $process");
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
