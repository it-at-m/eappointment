<?php
/**
 * @package Dldb
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Dldb\Collection;

class Locations extends Base
{

    public function __clone()
    {
        foreach ($this as $key => $location) {
            $this[$key] = clone $location;
        }
    }

    /**
     * @param Int $locationId
     *
     * @return Bool
     */
    public function hasLocationId($locationId)
    {
        return array_key_exists($locationId, $this);
    }

    /**
     * Remove a location
     *
     * @param Int $locationId
     *
     * @return clone self
     */
    public function removeLocation($locationId)
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
    public function getLocationsWithAppointmentsFor($serviceCsv = null, $external = false)
    {
        $list = new self();
        foreach ($this as $location) {
            if ($location->hasAppointments($serviceCsv, $external)) {
                $list[] = $location;
            }
        }
        return $list;
    }

    public function getIds()
    {
        $idList = array();
        foreach ($this as $location) {
            $idList[] = $location['id'];
        }
        return $idList;
    }

    public function getCSV()
    {
        return implode(',', $this->getIds());
    }

    /**
     * @todo check why no clone keyword is used
     *
     */
    public function getWithOffice($officepath)
    {
        $locationList= new self();
        foreach ($this as $location_id => $location) {
            if ($location['office'] == $officepath) {
                $locationList[$location_id] = new \BO\Dldb\Entity\Location($location->getArrayCopy());
            }
        }
        return $locationList;
    }
}
