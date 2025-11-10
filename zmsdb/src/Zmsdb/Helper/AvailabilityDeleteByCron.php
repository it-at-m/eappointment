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
            \App::$log->info("Reading availability list");
        }
        foreach ($availabilityList as $availability) {
            if ($commit) {
                $this->deleteAvailability($availability->getId());
            } elseif ($this->verbose) {
                \App::$log->info("Would remove availability", ['availability' => (string) $availability]);
            }
        }
    }

    protected function deleteAvailability(string $availabilityId)
    {
        if ($this->query->deleteEntity($availabilityId)) {
            if ($this->verbose) {
                \App::$log->info("Availability successfully removed", ['availabilityId' => $availabilityId]);
            }
        } else {
            \App::$log->warning("Could not remove availability", ['availabilityId' => $availabilityId]);
        }
    }
}
