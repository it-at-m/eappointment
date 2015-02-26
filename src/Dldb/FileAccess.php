<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Dldb;

/**
 *
 */
class FileAccess
{

    /**
      * Services
      *
      * @var Array $services
      */
    protected $services = array();

    /**
      * Locations
      *
      * @var Array $locations
      */
    protected $locations = array();

    /**
     * @return self
     */
    public function __construct($locationJson, $serviceJson)
    {
        if (!is_readable($locationJson)) {
            throw new Exception("Cannot read $locationJson");
        }
        $locationlist = json_decode(file_get_contents($locationJson), true);
        if (!$locationlist) {
            throw new Exception("Could not load locations");
        }
        if (!is_readable($serviceJson)) {
            throw new Exception("Cannot read $serviceJson");
        }
        $servicelist = json_decode(file_get_contents($serviceJson), true);
        if (!$servicelist) {
            throw new Exception("Could not load services");
        }
        foreach ($locationlist['data'] as $location) {
            $this->locations[$location['id']] = $location;
        }
        foreach ($servicelist['data'] as $service) {
            $this->services[$service['id']] = $service;
        }
    }

    /**
     * @return Array
     */
    public function fetchLocationList($service_csv = false)
    {
        $locationlist = $this->locations;
        if ($service_csv) {
            $locationlist = array_filter(
                $locationlist,
                function ($item) use ($service_csv) {
                    $location = new \BO\Dldb\Entity\Location($item);
                    return $location->containsService($service_csv);
                }
            );
        }
        return $locationlist;
    }

    /**
     * @return Array
     */
    public function fetchLocation($location_id)
    {
        $locationlist = $this->fetchLocationList();
        if (array_key_exists($location_id, $locationlist)) {
            return $locationlist[$location_id];
        }
        return false;
    }

    /**
     * @return Array
     */
    public function fetchServiceList($location_csv = false)
    {
        $servicelist = $this->services;
        if ($location_csv) {
            $servicelist = array_filter(
                $servicelist,
                function ($item) use ($location_csv) {
                    $service = new \BO\Dldb\Entity\Service($item);
                    return $service->containsLocation($location_csv);
                }
            );
        }
        return $servicelist;
    }

    /**
     * @return Array
     */
    public function fetchService($service_id)
    {
        $servicelist = $this->fetchServiceList();
        if (array_key_exists($service_id, $servicelist)) {
            return $servicelist[$service_id];
        }
        return false;
    }

    /**
     * fetch locations for a list of service and group by authority
     * @return Array
     */
    public function fetchAuthorityList(Array $servicelist)
    {
        $authoritylist = array();
        foreach ($servicelist as $service_id) {
            $service = $this->fetchService($service_id);
            if ($service) {
                foreach ($service['locations'] as $locationinfo) {
                    $location = $this->fetchLocation($locationinfo['location']);
                    if ($location && array_key_exists('authority', $location)) {
                        if (!array_key_exists($location['authority']['id'], $authoritylist)) {
                            $authoritylist[$location['authority']['id']] = array(
                                "name"          => $location['authority']['name'],
                                "locations"     => array()
                            );
                        }
                        $authoritylist[$location['authority']['id']]['locations'][] = $location;
                    }
                }
            }
        }
        ksort($authoritylist);
        return $authoritylist;
    }
}
