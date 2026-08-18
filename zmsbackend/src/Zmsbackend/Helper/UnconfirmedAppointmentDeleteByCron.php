<?php

namespace BO\Zmsbackend\Helper;

/**
 * @codeCoverageIgnore
 */
class UnconfirmedAppointmentDeleteByCron
{
    use VerboseCronLogTrait;

    protected $verbose = false;

    protected $limit = 1000;

    protected $loopCount = 100;

    protected $time;

    protected $now;

    protected $statusListForDeletion = ['preconfirmed'];

    protected $scopeList;

    protected $count = [];

    public function __construct(\DateTimeInterface $now, $verbose = false)
    {
        $this->now = $now;
        $this->verbose = $verbose;
        $this->scopeList = (new \BO\Zmsbackend\Scope\Service\Scope())->readList();
    }

    protected function log(string $message, string $level = 'info'): void
    {
        $this->writeVerboseCronLog($message, $level);
    }

    /** @psalm-api */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * @psalm-api
     */
    public function setLimit($limit): void
    {
        $this->limit = $limit;
    }

    /**
     * @psalm-api
     */
    public function setLoopCount($loopCount): void
    {
        $this->loopCount = $loopCount;
    }

    public function startProcessing($commit): void
    {
        $this->deleteUnconfirmedProcesses($commit);
        $this->log("\nSUMMARY: Deleted processes: " . var_export($this->count, true));
    }

    protected function deleteUnconfirmedProcesses($commit): void
    {
        foreach ($this->scopeList as $scope) {
            $count = $this->deleteByCallback($commit, function ($limit, $offset) use ($scope) {
                $query = new \BO\Zmsbackend\Process\Service\Process();
                $activationDuration = $scope->toProperty()->preferences->appointment->activationDuration->get();
                $time = new \DateTimeImmutable();
                $deleteFromTime = $time->setTimestamp(
                    $this->now->getTimestamp() - ($activationDuration * 60)
                );

                if ($this->verbose) {
                    $this->log(
                        "INFO: Deleting appointments older than "
                        . $deleteFromTime->format('c') . 'limit: ' . $limit
                        . ' offset: ' . $offset
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

    protected function deleteByCallback($commit, \Closure $callback): int
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

    protected function removeProcess(\BO\Zmsentities\Process $process, $commit): int
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

    protected function deleteProcess(\BO\Zmsentities\Process $process): void
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
