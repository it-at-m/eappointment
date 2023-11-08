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

    public function startProcessing(\DateTimeImmutable $datetime, $commit = false)
    {
        $availabilityList = $this->query->readAvailabilityListBefore($datetime);
        if ($this->verbose) {
            error_log("INFO: Reading availability list");
        }
        foreach ($availabilityList as $availability) {
            if ($commit) {
                $this->deleteAvailability($availability->getId());
            } elseif ($this->verbose) {
                error_log("INFO: Would remove $availability");
            }
        }
    }

    protected function deleteAvailability(string $availabilityId)
    {
        if ($this->query->deleteEntity($availabilityId)) {
            if ($this->verbose) {
                error_log("INFO: Availability $availabilityId successfully removed");
            }
        } else {
            error_log("WARN: Could not remove availability {$availabilityId}!");
        }
    }
}
