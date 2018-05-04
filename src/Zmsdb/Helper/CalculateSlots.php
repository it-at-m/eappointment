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
            error_log(sprintf("[CalculateSlots %07.3f] %s", "$time", $message));
        }
        return $this;
    }

    public function writeCalculations(\DateTimeInterface $now)
    {
        \BO\Zmsdb\Connection\Select::setTransaction();
        $scopeList = (new \BO\Zmsdb\Scope())->readList(1);
        $slotQuery = new \BO\Zmsdb\Slot();
        foreach ($scopeList as $scope) {
            $this->log("Calculate slots for $scope");
            $availabilityList = (new \BO\Zmsdb\Availability)
                ->readList($scope->id)
                ->withType('appointment')
                ;
            foreach ($availabilityList as $availability) {
                $availability->scope = $scope; //dayoff is required
                //$this->log("$availability");
                if ($slotQuery->writeByAvailability($availability, $now)) {
                    $this->log("Updated $availability");
                }
            }
        }
        $this->log("Update Slot-Process-Mapping");
        $slotQuery->updateSlotProcessMapping();
        $this->log("Commit changes (may take a while)");
        \BO\Zmsdb\Connection\Select::writeCommit();
        $this->log("Slot calculation finished");
    }
}
