<?php

namespace BO\Zmsdb\Helper;

/**
 * @codeCoverageIgnore
 */
class CalculateSlots
{
    protected $verbose = false;

    protected $startTime;

    public function __construct($verbose = false)
    {
        $this->verbose = $verbose;
        $this->startTime = microtime(true);
    }

    public function log($message)
    {
        if ($this->verbose) {
            $time = $this->getSpendTime();
            $memory = memory_get_usage()/(1024*1024);
            error_log(sprintf("[CalculateSlots %07.3fs %07.1fmb] %s", "$time", $memory, $message));
        }
        return $this;
    }

    public function getSpendTime()
    {
        $time = round(microtime(true) - $this->startTime, 3);
        return $time;
    }

    public function writeCalculations(\DateTimeInterface $now, $breakTime = 10)
    {
        \BO\Zmsdb\Connection\Select::setTransaction();
        $this->log("Fetch Slot list with time ". $now->format('c'));
        $scopeList = (new \BO\Zmsdb\Scope())->readList(1);
        $scopeLength = count($scopeList) - 1;
        foreach ($scopeList as $key => $scope) {
            if ($this->writeCalculatedScope($scope, $now)) {
                $this->log("Calculated slots $key/$scopeLength for $scope");
            }
            if ($this->getSpendTime() > $breakTime) {
                $this->log("Break update, wait for next iteration");
                break;
            }
        }
        $this->log("Update Slot-Process-Mapping");
        $slotQuery = new \BO\Zmsdb\Slot();
        $slotQuery->updateSlotProcessMapping();
        $this->log("Commit changes (may take a while)");
        \BO\Zmsdb\Connection\Select::writeCommit();
        $this->log("Slot calculation finished");
    }

    protected function writeCalculatedScope(\BO\Zmsentities\Scope $scope, \DateTimeInterface $now)
    {
        $slotQuery = new \BO\Zmsdb\Slot();
        $updatedList = $slotQuery->writeByScope($scope, $now);
        foreach ($updatedList as $availability) {
            $this->log("Updated $availability");
        }
        if (count($updatedList)) {
            \BO\Zmsdb\Connection\Select::writeCommit();
            return true;
        }
        return false;
    }

    public function writeCanceledSlots(\DateTimeInterface $now)
    {
        $slotQuery = new \BO\Zmsdb\Slot();
        if ($slotQuery->writeCanceledByTime($now->modify('+5 minutes'))) {
            \BO\Zmsdb\Connection\Select::writeCommit();
            $this->log("Cancelled old slots");
            return true;
        }
        return false;
    }

    /**
     * @SuppressWarnings(Unused)
     */
    public function deleteOldSlots(\DateTimeInterface $now)
    {
        $slotQuery = new \BO\Zmsdb\Slot();
        if ($slotQuery->deleteSlotsOlderThan($now)) {
            \BO\Zmsdb\Connection\Select::writeCommit();
            $this->log("Deleted slots older than ". $now->format('Y-m-d'));
        }
    }
}
