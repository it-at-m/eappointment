<?php
/**
 * @package Dldb
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/
namespace BO\Dldb\Entity;

/**
 * Helper for service export
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
            'locations' => new \BO\Dldb\Collection\Locations()
        );
        return new self($data);
    }

    /**
     * Check if appointments are available
     *
     * @param String $serviceCsv
     *            only check for this serviceCsv
     * @param Bool $external
     *            allow external links, default false
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
     *
     * @param String $officepath
     *            only check for this office
     * @return Authority
     *
     */
    public function getLocationListByOfficePath($officepath)
    {
        $authority = clone $this;
        if (count($authority['locations'])) {
            $locations = new \BO\Dldb\Collection\Locations($authority['locations']);
            $authority['locations'] = $locations->getLocationListByOfficePath($officepath);
        }
        return $authority;
    }

    /**
     *
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

    /**
     * Check if Authority is part of ServiceList
     *
     * @param String $serviceCsv
     *            only check for this serviceCsv
     *
     * @return clone self
     */
    public function isInServiceList($servicelist = array())
    {
        foreach ($servicelist as $service) {
            if ($service->offsetExists('authorities')) {
                foreach ($service['authorities'] as $authority) {
                    if ($authority['id'] == $this['id']) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    public function clearLocations()
    {
        $this['locations'] = new \BO\Dldb\Collection\Locations();
    }

    public function addLocation(\BO\Dldb\Entity\Location $location)
    {
        $this['locations'][$location['id']] = $location;
    }
}
