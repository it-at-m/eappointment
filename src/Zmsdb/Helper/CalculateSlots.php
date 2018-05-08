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
            $time = round(microtime(true) - $this->startTime, 3);
            $memory = memory_get_usage()/(1024*1024);
            error_log(sprintf("[CalculateSlots %07.3fs %07.1fmb] %s", "$time", $memory, $message));
        }
        return $this;
    }

    public function writeCalculations(\DateTimeInterface $now)
    {
        \BO\Zmsdb\Connection\Select::setTransaction();
        $this->log("Fetch Slot list");
        $scopeList = (new \BO\Zmsdb\Scope())->readList(1);
        foreach ($scopeList as $scope) {
            $this->writeCalculatedScope($scope, $now);
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
        $this->log("Calculate slots for $scope");
        $availabilityList = (new \BO\Zmsdb\Availability)
            ->readList($scope->id)
            ->withType('appointment')
            ;
        foreach ($availabilityList as $availability) {
            $availability->scope = clone $scope; //dayoff is required
            //$this->log("$availability");
            if ($slotQuery->writeByAvailability($availability, $now)) {
                $this->log("Updated $availability");
                \BO\Zmsdb\Connection\Select::writeCommit();
            }
        }
    }
}
