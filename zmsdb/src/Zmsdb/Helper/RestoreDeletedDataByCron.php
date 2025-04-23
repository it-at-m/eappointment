<?php

namespace BO\Zmsdb\Helper;

/**
 * @codeCoverageIgnore
 */
class RestoreDeletedDataByCron
{
    protected $scopeList;

    protected $verbose = false;

    public function __construct($verbose = false)
    {
        if ($verbose) {
            error_log("INFO: Restoring deleted appointments based on delay time");
            $this->verbose = true;
        }
        $this->scopeList = (new \BO\Zmsdb\Scope())->readList();
    }

    public function startProcessing($commit)
    {
        foreach ($this->scopeList as $scope) {
            $time = new \DateTimeImmutable();
            $reservationDuration = $scope->toProperty()->preferences->appointment->reservationDuration->get();
            $time = $time->setTimestamp($time->getTimestamp() - ($reservationDuration * 60));
            $processList = (new \BO\Zmsdb\Process())->readExpiredReservationsList($time, $scope->id, 10000);

            foreach ($processList as $process) {
                if ($this->verbose) {
                    error_log("INFO: Processing $process");
                }
                if ($commit) {
                    $this->removeProcess($process);
                }
            }
        }
    }

    protected function removeProcess(\BO\Zmsentities\Process $process)
    {
        if ('reserved' == $process->status) {
            $this->deleteProcess($process);
        } elseif ($this->verbose) {
            error_log("INFO: Keep process $process->id");
        }
    }

    protected function deleteProcess(\BO\Zmsentities\Process $process)
    {
        $query = new \BO\Zmsdb\Process();
        if ($query->writeDeletedEntity($process->id) && $this->verbose) {
            error_log("INFO: Process $process->id successfully removed");
        } elseif ($this->verbose) {
            error_log("WARN: Could not remove process '$process->id'!");
        }
    }
}
