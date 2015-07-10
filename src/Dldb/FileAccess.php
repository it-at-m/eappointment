<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Dldb;

/**
 * @SuppressWarnings(TooManyMethods)
 * @SuppressWarnings(CouplingBetweenObjects)
 *
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
      * Topics
      *
      * @var Array $topics
      */
    protected $topics = array();

    /**
      * Settings
      *
      * @var Array $settings
      */
    protected $settings = array();

    /**
      * Authorities
      *
      * @var Array $authorities
      */
    protected $authorities = array();

    /**
      * Authorities
      *
      * @var Array $authorities
      */
    protected $offices = array();

    /**
      * Authorities
      *
      * @var Array $authorities
      */
    protected $boroughs = array();

    /**
     * @return self
     */
    public function __construct(
        $locationJson = null,
        $serviceJson = null,
        $topicsJson = null,
        $authoritiesJson = null,
        $settingsJson = null
    ) {
        $this->services = new Collection\Services();
        $this->locations = new Collection\Locations();
        $this->topics = new Collection\Topics();
        $this->authorities = new Collection\Authorities();
        $this->offices = new Collection\Offices();
        $this->boroughs = new Collection\Boroughs();
        if (null !== $locationJson) {
            $this->loadLocations($locationJson);
        }
        if (null !== $serviceJson) {
            $this->loadServices($serviceJson);
        }
        if (null !== $topicsJson) {
            $this->loadTopics($topicsJson);
        }
        if (null !== $authoritiesJson) {
            $this->loadAuthorities($authoritiesJson);
        }
        if (null !== $settingsJson) {
            $this->loadSettings($settingsJson);
        }
    }

    /**
     * @return self
     */
    public function loadLocations($locationJson)
    {
        $locationlist = self::readJson($locationJson);
        foreach ($locationlist['data'] as $location) {
            $this->locations[$location['id']] = new Entity\Location($location);
        }
        return $this;
    }

    /**
     * @return self
     */
    public function loadServices($serviceJson)
    {
        $servicelist = self::readJson($serviceJson);
        foreach ($servicelist['data'] as $service) {
            $this->services[$service['id']] = new Entity\Service($service);
        }
    }

    /**
     * @return self
     */
    public function loadTopics($topicJson)
    {
        $this->accessInstance['Topic'] = new File\Topic($topicJson);
    }

    /**
     * @return self
     */
    public function loadSettings($settingsJson)
    {
        $settinglist = self::readJson($settingsJson);
        $this->settings = $settinglist['data']['settings'];
        foreach ($settinglist['data']['office'] as $office) {
            $this->offices[$office['path']] = new Entity\Office($office);
        }
        foreach ($settinglist['data']['boroughs'] as $borough) {
            $this->boroughs[$borough['id']] = new Entity\Borough($borough);
        }
    }

    /**
     * @return self
     */
    public function loadAuthorities($authorityJson)
    {
        $authoritylist = self::readJson($authorityJson);
        foreach ($authoritylist['data'] as $authority) {
            $this->authorities[$authority['id']] = new Entity\Authority($authority);
        }
    }

    protected static function readJson($jsonFile)
    {
        if (!is_readable($jsonFile)) {
            throw new Exception("Cannot read $jsonFile");
        }
        $list = json_decode(file_get_contents($jsonFile), true);
        if (!$list) {
            throw new Exception("Could not decide $jsonFile");
        }
        return $list;
    }

    public function fetchSettingName($name)
    {
        if (isset($this->settings[$name])) {
            return $this->settings[$name];
        }
        return null;
    }

    public function fetchBoroughId($borough_id)
    {
        if (isset($this->boroughs[$borough_id])) {
            return $this->boroughs[$borough_id];
        }
        return null;
    }

    public function fetchOfficePath($office_path)
    {
        $offices = $this->fetchOfficeList();
        if (isset($offices[$office_path])) {
            return $offices[$office_path];
        }
        return null;
    }

    public function fetchOfficeList()
    {
        return $this->offices;
    }

    /**
     * @return Entity\Topic
     */
    public function fetchTopic($topic_id)
    {
        $topiclist = $this->fetchTopicList();
        if (array_key_exists($topic_id, $topiclist)) {
            return $topiclist[$topic_id];
        }
        return false;
    }

    /**
     * @return Entity\Topic
     */
    public function fetchTopicPath($topic_path)
    {
        $topiclist = $this->fetchTopicList();
        foreach ($topiclist as $topic) {
            if ($topic['path'] == $topic_path) {
                return $topic;
            }
        }
        return false;
    }

    /**
     * @return Entity\Topic\Services
     */
    public function getTopicServicesIds($topic)
    {
        if (isset($topic['relation']['services'])) {
            return $this->services->getIds($topic['relation']['services']);
        }
        return false;
    }

    public function fetchTopicServicesList($topic_path)
    {
        $serviceIds = array();
        $topic = $this->fetchTopicPath($topic_path);
        $serviceIds = $this->getTopicServicesIds($topic);
        if (isset($topic['relation']['childs'])) {
            foreach ($topic['relation']['childs'] as $child) {
                $childtopic = $this->fetchTopicPath($child['path']);
                $serviceIds = array_merge($serviceIds, $this->getTopicServicesIds($childtopic));
            }
        }
        if (count($serviceIds)) {
            $servicelistCSV = implode(',', $serviceIds);
            $servicelist = $this->fetchServiceFromCsv($servicelistCSV);
            return $servicelist;
        }
        return false;
    }

    /**
     * @return Collection\Locations
     */
    public function fetchLocationList($service_csv = false)
    {
        $locationlist = $this->locations;
        if ($service_csv) {
            $locationlist = new Collection\Locations(array_filter(
                (array)$locationlist,
                function ($item) use ($service_csv) {
                    $location = new \BO\Dldb\Entity\Location($item);
                    return $location->containsService($service_csv);
                }
            ));
        }
        return $locationlist;
    }
    
    /**
     * @return Collection\Locations
     */
    public function fetchLocationListByOffice($officepath = false)
    {
        $authoritylist = $this->authorities;
        if ($officepath) {
            $authoritylist = new Collection\Authorities(array_filter(
                (array)$authoritylist,
                function ($item) use ($officepath) {
                    $authority = new \BO\Dldb\Entity\Authority($item);
                    return $authority->matchLocationWithOffice($officepath);
                }
            ));
        }
        return $authoritylist;
    }

    /**
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
     * @return Collection\Services
     */
    public function fetchServiceList($location_csv = false)
    {
        $servicelist = $this->services;
        if ($location_csv) {
            $servicelist = new Collection\Services(array_filter(
                (array)$servicelist,
                function ($item) use ($location_csv) {
                    $service = new \BO\Dldb\Entity\Service($item);
                    return $service->containsLocation($location_csv);
                }
            ));
        }
        return $servicelist;
    }

    /**
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
     * @return Collection\Locations
     */
    public function fetchLocationFromCsv($location_csv)
    {
        $locationlist = new Collection\Locations();
        foreach (explode(',', $location_csv) as $location_id) {
            $location = $this->fetchLocation($location_id);
            if ($location) {
                $locationlist[$location_id] = $location;
            };
        }
        $locationlist->sortByName();
        return $locationlist;
    }

    /**
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
     * @return Collection\Authorities
     */
    public function fetchAuthorityList(Array $servicelist = array())
    {
        if (count($servicelist)) {
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
        } else {
            $authoritylist = $this->authorities;
        }
        return $authoritylist;
    }

    /**
     * @return Collection\Locations
     */
    public function searchLocation($query, $service_csv = '')
    {
        $locationlist = $this->fetchLocationList($service_csv);
        $locationlist = new Collection\Locations(array_filter(
            (array)$locationlist,
            function ($item) use ($query) {
                return false !== strpos($item['name'], $query);
            }
        ));
        return $locationlist;
    }

    /**
     * @return Collection\Services
     */
    public function searchService($query, $service_csv = '')
    {
        $servicelist = $this->fetchServiceCombinations($service_csv);
        $servicelist = new Collection\Services(array_filter(
            (array)$servicelist,
            function ($item) use ($query) {
                return false !== strpos($item['name'], $query);
            }
        ));
        return $servicelist;
    }
}
