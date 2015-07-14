<?php
/**
 * @package Dldb
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Dldb\Entity;

/**
  * Helper for service export
  *
  */
class Authority extends Base
{

    public function __clone()
    {
        if ($this['locations'] instanceof \BO\Dldb\Collection\Locations) {
            $this['locations'] = clone $this['locations'];
        }
    }

    public static function create($name)
    {
        $data = array(
            'name' => $name,
            'locations' => new \BO\Dldb\Collection\Locations(),
        );
        return new self($data);
    }

    /**
     * Check if appointments are available
     *
     * @param String $serviceCsv only check for this serviceCsv
     * @param Bool $external allow external links, default false
     *
     * @return Bool
     */
    public function hasAppointments($serviceCsv = null, $external = false)
    {
        foreach ($this['locations'] as $location) {
            if ($location->hasAppointments($serviceCsv, $external)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if locations are available
     *
     * @return Bool
     */
    public function hasLocations()
    {
        return count($this['locations']) > 0 ? true : false;
    }

    /**
     * Check if locations are available for defined office
     * @todo Remove this function, this is a data query and self manipulation, extreme bug probability
     *
     * @param String $officepath only check for this office
     *
     * @return Authority
     */
    public function getOffice($officepath = null)
    {
        foreach ($this['locations'] as $key => $location) {
            // better: Entity\Location::isOffice($officepath)
            if ($location['office'] != $officepath) {
                unset($this['locations'][$key]); // help :-/
            }
        }
        $data = array(
            'name' => $this['name'],
            'locations' => $this['locations']
        );
        if (count($data['locations'])) {
            return new self($data);
        }
    }

    public function getWithOffice($officepath)
    {
        $authority= new self($this->getArrayCopy());
        $authority['locations'] = $authority['locations']->getWithOffice($officepath);
        return $authority;
    }

    /**
     * @param Int $locationId
     *
     * @return Bool
     */
    public function hasLocationId($locationId)
    {
        return $this['locations']->hasLocationId($locationId);
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
        $authority = clone $this;
        $authority['locations'] = $authority['locations']->removeLocation($locationId);
        return $authority;
    }
}
