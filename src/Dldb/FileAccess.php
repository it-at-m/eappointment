<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/
namespace BO\Dldb;

/**
 */
class FileAccess extends AbstractAccess
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
     *
     * @return self
     */
    public function __construct($locationJson, $serviceJson)
    {
        if (! is_readable($locationJson)) {
            throw new Exception("Cannot read $locationJson");
        }
        $locationlist = json_decode(file_get_contents($locationJson), true);
        if (! $locationlist) {
            throw new Exception("Could not load locations");
        }
        if (! is_readable($serviceJson)) {
            throw new Exception("Cannot read $serviceJson");
        }
        $servicelist = json_decode(file_get_contents($serviceJson), true);
        if (! $servicelist) {
            throw new Exception("Could not load services");
        }
        if (null !== $settingsJson) {
            $this->loadSettings($settingsJson);
        }
    }

    /**
     *
     * @return Collection\Locations
     */
    public function fetchLocationListByOffice($officepath = false)
    {
        $locationlist = $this->locations;
        if ($service_csv) {
            $locationlist = new Collection\Locations(array_filter((array) $locationlist, function ($item) use($service_csv) {
                $location = new \BO\Dldb\Entity\Location($item);
                return $location->containsService($service_csv);
            }));
        }
        return $locationlist;
    }

    /**
     *
     * @return Entity\Location
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
     *
     * @return Collection\Services
     */
    public function fetchServiceList($location_csv = false)
    {
        $servicelist = $this->services;
        if ($location_csv) {
            $servicelist = new Collection\Services(array_filter((array) $servicelist, function ($item) use($location_csv) {
                $service = new \BO\Dldb\Entity\Service($item);
                return $service->containsLocation($location_csv);
            }));
        }
        return $servicelist;
    }

    /**
     *
     * @return Entity\Service
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
     *
     * @return Collection\Locations
     */
    public function fetchLocationFromCsv($location_csv)
    {
        $locationlist = new Collection\Locations();
        foreach (explode(',', $location_csv) as $location_id) {
            $location = $this->fetchLocation($location_id);
            if ($location) {
                $locationlist[$location_id] = $location;
            }
            ;
        }
        $locationlist->sortByName();
        return $locationlist;
    }

    /**
     *
     * @return Collection\Services
     */
    public function fetchServiceFromCsv($service_csv)
    {
        $servicelist = new Collection\Services();
        foreach (explode(',', $service_csv) as $service_id) {
            $service = $this->fetchService($service_id);
            if ($service) {
                $servicelist[$service_id] = $service;
            }
        }
        $servicelist->sortByName();
        return $servicelist;
    }

    /**
     * fetch locations for a list of service and group by authority
     *
     * @return Collection\Authorities
     */
    public function fetchAuthorityList(Array $servicelist)
    {
        $authoritylist = new Collection\Authorities();
        foreach ($servicelist as $service_id) {
            $service = $this->fetchService($service_id);
            if ($service) {
                foreach ($service['locations'] as $locationinfo) {
                    $location = $this->fetchLocation($locationinfo['location']);
                    if ($location) {
                        $authoritylist->addLocation($location);
                    }
                }
            }
        }
        $authoritylist->sortByName();
        return $authoritylist;
    }

    /**
     *
     * @return Collection\Locations
     */
    public function searchLocation($query, $service_csv = '')
    {
        $locationlist = $this->fetchLocationList($service_csv);
        $locationlist = new Collection\Locations(array_filter((array) $locationlist, function ($item) use($query) {
            return false !== strpos($item['name'], $query);
        }));
        return $locationlist;
    }

    /**
     *
     * @return Collection\Services
     */
    public function searchService($query, $service_csv = '')
    {
        $servicelist = $this->fetchServiceCombinations($service_csv);
        $servicelist = new Collection\Services(array_filter((array) $servicelist, function ($item) use($query) {
            return false !== strpos($item['name'], $query);
        }));
        return $servicelist;
    }
}
