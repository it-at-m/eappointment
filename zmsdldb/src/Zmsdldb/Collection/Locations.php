<?php

/**
 * @package Zmsdldb
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsdldb\Collection;

class Locations extends Base
{
    public function __clone()
    {
        foreach ($this as $key => $location) {
            $this[$key] = clone $location;
        }
    }

    /**
     * @param int $locationId
     *
     * @return Bool
     */
    public function hasLocationId($locationId): bool
    {
        return $this->offsetExists($locationId);
    }

    /**
     * Remove a location
     *
     * @param Int $locationId
     *
     * @return static self
     */
    public function removeLocation($locationId): static
    {
        $locationList = clone $this;
        if ($locationList->hasLocationId($locationId)) {
            unset($locationList[$locationId]);
        }
        return $locationList;
    }

    /**
     * find locations with appointments
     *
     * @param String $serviceCsv only check for this serviceCsv
     * @param Bool $external allow external links, default false
     */
    public function getLocationsWithAppointmentsFor($serviceCsv = null, $external = false): self
    {
        $list = new self();
        foreach ($this as $location) {
            if ($location->hasAppointments($serviceCsv, $external)) {
                $list[] = $location;
            }
        }
        return $list;
    }

    /**
     * @psalm-return list{0?: mixed,...}
     */
    public function getIds(): array
    {
        $idList = array();
        foreach ($this as $location) {
            $idList[] = $location['id'];
        }
        return $idList;
    }

    public function getNames(): array
    {
        $nameList = array();
        foreach ($this as $location) {
            $nameList[$location['id']] = $location['name'];
        }
        return $nameList;
    }

    public function getCSV(): string
    {
        return implode(',', $this->getIds());
    }

    public function getLocationListByOfficePath(string $officepath): self
    {
        $locationList = new self();
        foreach ($this as $location_id => $location) {
            if ($location['office'] == $officepath) {
                $locationList[$location_id] = $location;
            }
        }
        return $locationList;
    }
}
