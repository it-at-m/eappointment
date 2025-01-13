<?php

namespace BO\Zmsdb\Helper;

/**
 * @codeCoverageIgnore
 */

use BO\Zmsdb\Process as ProcessRepository;
use BO\Zmsdb\Scope as ScopeRepository;
use BO\Zmsentities\Collection\ScopeList;
use BO\Zmsentities\Process;
use BO\Zmsentities\Scope;

class ReservedDataDeleteByCron
{
    /** @var \DateTimeInterface */
    protected $nowTime;

    /** @var bool */
    protected $verbose = false;

    /** @var int */
    protected $limit = 500;

    /** @var bool */
    protected $isDryRun = false;

    /** @var int[] */
    protected $countByScopeId = [];

    /** @var Scope */
    protected $scopeRepository;

    protected $processRepository;

    public function __construct(\DateTimeInterface $now, bool $verbose = false, bool $isDryRun = false)
    {
        $this->nowTime  = $now;
        $this->verbose  = $verbose;
        $this->isDryRun = $isDryRun;

        $this->scopeRepository = new ScopeRepository();
        $this->processRepository = new ProcessRepository();
    }

    /**
     * @return int[]
     */
    public function getCount(): array
    {
        return $this->countByScopeId;
    }

    public function setLimit(int $limit): ReservedDataDeleteByCron
    {
        $this->limit = $limit;

        return $this;
    }

    public function startProcessing(): void
    {
        $this->log("INFO: Deleting expired reservations older than scopes reservation duration");

        $scopeList = $this->scopeRepository->readList();
        $this->countByScopeId = array_fill_keys($scopeList->getIds(), 0);
        $this->deleteExpiredReservations($scopeList);
        $filteredCount = array_filter($this->getCount());

        $this->log(PHP_EOL . "SUMMARY: Processed reservations: " . var_export($filteredCount, true));
    }

    protected function log($message): bool
    {
        return $this->verbose && error_log($message);
    }

    protected function deleteExpiredReservations(ScopeList $scopeList): void
    {
        foreach ($scopeList as $scope) {
            $countedProcesses = $this->deleteProcessesByScope($scope);
            $this->countByScopeId[$scope->id] += $countedProcesses;
        }
    }

    protected function getExpirationTimeByScopePreference(int $reservationDuration): \DateTimeInterface
    {
        $expirationTime = clone $this->nowTime;
        $expiredTimestamp = ($this->nowTime->getTimestamp() - ($reservationDuration * 60));

        return $expirationTime->setTimestamp($expiredTimestamp);
    }

    protected function deleteProcessesByScope(Scope $scope): int
    {
        $processCount = 0;
        $processList  = $this->getProcessListByScope($scope);

        foreach ($processList as $process) {
            if ($process->status === 'reserved') {
                $age = ($this->nowTime->getTimestamp() - $process->createTimestamp);
                $this->log("INFO: found process($process->id) with a reservation age of $age seconds");
                $this->writeDeleteProcess($process);
            } else {
                $this->log("INFO: Keep process $process->id with status $process->status");
            }

            $processCount++;
        }

        return $processCount;
    }

    protected function getProcessListByScope(Scope $scope): iterable
    {
        $reservationDuration = $scope->toProperty()->preferences->appointment->reservationDuration->get();
        $expirationTime = $this->getExpirationTimeByScopePreference((int) $reservationDuration);
        $processList = $this->processRepository
            ->readExpiredReservationsList($expirationTime, $scope->id, $this->limit)
            ->sortByCustomKey('createTimestamp');

        if ($processList->count() === 0) {
            return [];
        }

        $this->log(
            "\nNow: " . $this->nowTime->format('H:i:s') .
            "\nTime of expiration: " . $expirationTime->format('H:i:s') . " | scope " . $scope->id .
            " | $reservationDuration minutes reservation time (" . $processList->count() . " found)" .
            "\n-------------------------------------------------------------------"
        );

        return $processList;
    }

    protected function writeDeleteProcess(Process $process)
    {
        if ($this->isDryRun) {
            return;
        }

        if ($this->processRepository->writeDeletedEntity($process->id)) {
            $this->log("INFO: ($process->id) removed successfully\n");
        } else {
            $this->log("WARN: Could not remove process '$process->id'!\n");
        }
    }
}
