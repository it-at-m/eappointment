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
     * @return self
     */
    public function loadFromPath($path)
    {
        if (!is_dir($path)) {
            throw new Exception("Could not read directory $path");
        }
        $this->loadAuthorities($path . DIRECTORY_SEPARATOR . 'authority_de.json', 'de');
        $this->loadAuthorities($path . DIRECTORY_SEPARATOR . 'authority_de.json', 'en');
        $this->loadLocations($path . DIRECTORY_SEPARATOR . 'locations_de.json', 'de');
        $this->loadLocations($path . DIRECTORY_SEPARATOR . 'locations_en.json', 'en');
        $this->loadServices($path . DIRECTORY_SEPARATOR . 'services_de.json', 'de');
        $this->loadServices($path . DIRECTORY_SEPARATOR . 'services_en.json', 'en');
        $this->loadSettings($path . DIRECTORY_SEPARATOR . 'settings.json');
        $this->loadTopics($path . DIRECTORY_SEPARATOR . 'topic_de.json', 'de');
        $this->loadTopics($path . DIRECTORY_SEPARATOR . 'topic_de.json', 'en');
        return $this;
    }

    /**
     *
     * @return self
     */
    public function loadLocations($locationJson, $locale = 'de')
    {
        $this->accessInstance[$locale]['Location'] = new File\Location($locationJson, $locale);
        $this->accessInstance[$locale]['Location']->setAccessInstance($this);
        return $this;
    }

    /**
     *
     * @return self
     */
    public function loadServices($serviceJson, $locale = 'de')
    {
        $this->accessInstance[$locale]['Service'] = new File\Service($serviceJson, $locale);
        $this->accessInstance[$locale]['Service']->setAccessInstance($this);
        return $this;
    }

    /**
     *
     * @return self
     */
    public function loadTopics($topicJson, $locale = 'de')
    {
        $this->accessInstance[$locale]['Topic'] = new File\Topic($topicJson, $locale);
        $this->accessInstance[$locale]['Topic']->setAccessInstance($this);
        $this->accessInstance[$locale]['Link'] = new File\Link($topicJson, $locale);
        $this->accessInstance[$locale]['Link']->setAccessInstance($this);
        return $this;
    }

    /**
     *
     * @return self
     */
    public function loadSettings($settingsJson)
    {
        $this->accessInstance['de']['Setting'] = new File\Setting($settingsJson);
        $this->accessInstance['de']['Setting']->setAccessInstance($this);
        $this->accessInstance['de']['Office'] = new File\Office($settingsJson);
        $this->accessInstance['de']['Office']->setAccessInstance($this);
        $this->accessInstance['de']['Borough'] = new File\Borough($settingsJson);
        $this->accessInstance['de']['Borough']->setAccessInstance($this);
        return $this;
    }

    /**
     *
     * @return self
     */
    public function loadAuthorities($authorityJson, $locale = 'de')
    {
        $this->accessInstance[$locale]['Authority'] = new File\Authority($authorityJson, $locale);
        $this->accessInstance[$locale]['Authority']->setAccessInstance($this);
        return $this;
    }

    /**
     *
     * @todo refactor: returns services, not topics.
     */
    public function fetchTopicServicesList($topic_path)
    {
        trigger_error("Deprecated function fetchTopicServicesList, use fromService()->fetchTopic()");
        return $this->fromService()->fetchTopicPath($topic_path);
    }

    /**
     *
     * @todo will not work in every edge case, cause authority export does not contain officeinformations
     * @todo returns Collection\Authorities and not locations
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
        $locationlist;
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
        $servicelist;
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
        $authoritylist;
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
