<?php

namespace BO\Zmsdb\Helper;

/**
 * @codeCoverageIgnore
 */
class AvailabilityDeleteByCron
{
    protected $verbose = false;
    protected $query;

    public function __construct($verbose = false)
    {
        $this->query = new \BO\Zmsdb\Availability();
        if ($verbose) {
            $this->verbose = true;
        }
    }

    public function startProcessing($commit = false)
    {
        $availabilityList = $this->query->readOldAvailabilityList();
        if ($this->verbose) {
            error_log("INFO: Reading availability list");
        }
        if ($commit) {
            foreach ($availabilityList as $availability) {
                $this->deleteAvailability($availability->getId());
            }
        }
    }

    protected function deleteAvailability(string $availabilityId)
    {
        if ($this->query->deleteEntity($availabilityId) && $this->verbose) {
            error_log("INFO: Availability $availabilityId successfully removed");
        } else {
            error_log("WARN: Could not remove availability {$availabilityId}!");
        }
    }
}
