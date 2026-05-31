<?php

namespace BO\Zmsdb\Helper;

/**
 * @codeCoverageIgnore
 */
class RestoreDeletedDataByCron
{
    protected $scopeList;

    protected bool $verbose = false;

    public function __construct($verbose = false)
    {
        if ($verbose) {
            \App::$log->info('Restoring deleted appointments based on delay time');
            $this->verbose = true;
        }
        $this->scopeList = (new \BO\Zmsdb\Scope())->readList();
    }

    public function startProcessing($commit): void
    {
        foreach ($this->scopeList as $scope) {
            $time = new \DateTimeImmutable();
            $reservationDuration = $scope->toProperty()->preferences->appointment->reservationDuration->get();
            $time = $time->setTimestamp($time->getTimestamp() - ($reservationDuration * 60));
            $processList = (new \BO\Zmsdb\Process())->readExpiredReservationsList($time, $scope->id, 10000);

            foreach ($processList as $process) {
                if ($this->verbose) {
                    \App::$log->info('Processing process', ['process' => (string) $process]);
                }
                if ($commit) {
                    $this->removeProcess($process);
                }
            }
        }
    }

    protected function removeProcess(\BO\Zmsentities\Process $process): void
    {
        if ('reserved' == $process->status) {
            $this->deleteProcess($process);
        } elseif ($this->verbose) {
            \App::$log->info('Keep process', ['processId' => $process->id]);
        }
    }

    protected function deleteProcess(\BO\Zmsentities\Process $process): void
    {
        $query = new \BO\Zmsdb\Process();
        if ($query->writeDeletedEntity($process->id) && $this->verbose) {
            \App::$log->info('Process successfully removed', ['processId' => $process->id]);
        } elseif ($this->verbose) {
            \App::$log->warning('Could not remove process', ['processId' => $process->id]);
        }
    }
}
