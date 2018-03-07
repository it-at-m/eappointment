<?php

namespace BO\Zmsdb\Helper;

/**
 * @codeCoverageIgnore
 */
class CalculateSlots
{
    protected $verbose = false;

    public function __construct($verbose = false)
    {
        $this->verbose = $verbose;
    }

    public function log($message)
    {
        if ($this->verbose) {
            error_log("[CalculateSlots] $message");
        }
        return $this;
    }

    public function writeCalculations(\DateTimeInterface $now)
    {
        \BO\Zmsdb\Connection\Select::setTransaction();
        $scopeList = (new \BO\Zmsdb\Scope())->readList(1);
        $slotQuery = new \BO\Zmsdb\Slot();
        foreach ($scopeList as $scope) {
            $this->log("$scope");
            $availabilityList = (new \BO\Zmsdb\Availability)
                ->readList($scope->id)
                ->withType('appointment')
                ;
            foreach ($availabilityList as $availability) {
                $availability->scope = $scope; //dayoff is required
                $this->log("$availability");
                $slotQuery->writeByAvailability($availability, $now);
            }
        }
        \BO\Zmsdb\Connection\Select::writeCommit();
    }
}
