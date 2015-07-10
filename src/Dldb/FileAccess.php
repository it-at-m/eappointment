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
      * Authorities
      *
      * @var Array $authorities
      */
    protected $authorities = array();

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
        $this->authorities = new Collection\Authorities();
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
        $this->accessInstance['Location'] = new File\Location($locationJson);
        $this->accessInstance['Location']->setAccessInstance($this);
        return $this;
    }

    /**
     * @return self
     */
    public function loadServices($serviceJson)
    {
        $this->accessInstance['Service'] = new File\Service($serviceJson);
        $this->accessInstance['Service']->setAccessInstance($this);
        return $this;
    }

    /**
     * @return self
     */
    public function loadTopics($topicJson)
    {
        $this->accessInstance['Topic'] = new File\Topic($topicJson);
        $this->accessInstance['Topic']->setAccessInstance($this);
        return $this;
    }

    /**
     * @return self
     */
    public function loadSettings($settingsJson)
    {
        $this->accessInstance['Setting'] = new File\Setting($settingsJson);
        $this->accessInstance['Setting']->setAccessInstance($this);
        $this->accessInstance['Office'] = new File\Office($settingsJson);
        $this->accessInstance['Office']->setAccessInstance($this);
        $this->accessInstance['Borough'] = new File\Borough($settingsJson);
        $this->accessInstance['Borough']->setAccessInstance($this);
        return $this;
    }

    /**
     * @return self
     */
    public function loadAuthorities($authorityJson)
    {
        $this->accessInstance['Authority'] = new File\Authority($authorityJson);
        $this->accessInstance['Authority']->setAccessInstance($this);
        return $this;
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
     * @return Array
     */
    public function fetchServiceCombinations($service_csv)
    {
        return $this->fetchServiceList($this->fetchServiceLocationCsv($service_csv));
    }

    /**
     * @return String
     */
    protected function fetchServiceLocationCsv($service_csv)
    {
        $locationlist = $this->fetchLocationList($service_csv);
        $locationIdList = array();
        foreach ($locationlist as $location) {
            $locationIdList[] = $location['id'];
        }
        return implode(',', $locationIdList);
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
