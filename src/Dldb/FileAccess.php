<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Dldb;

/**
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
      * Locations
      *
      * @var Array $locations
      */
    protected $topics = array();

    /**
     * @return self
     */
    public function __construct($locationJson = null, $serviceJson = null, $topicsJson = null)
    {
        $this->services = new Collection\Services();
        $this->locations = new Collection\Locations();
        $this->topics = new Collection\Topics();
        if (null !== $locationJson) {
            $this->loadLocations($locationJson);
        }
        if (null !== $serviceJson) {
            $this->loadServices($serviceJson);
        }
        if (null !== $topicsJson) {
            $this->loadTopics($topicsJson);
        }
    }

    /**
     * @return self
     */
    public function loadLocations($locationJson)
    {
        if (!is_readable($locationJson)) {
            throw new Exception("Cannot read $locationJson");
        }
        $locationlist = json_decode(file_get_contents($locationJson), true);
        if (!$locationlist) {
            throw new Exception("Could not load locations");
        }
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
        if (!is_readable($serviceJson)) {
            throw new Exception("Cannot read $serviceJson");
        }
        $servicelist = json_decode(file_get_contents($serviceJson), true);
        if (!$servicelist) {
            throw new Exception("Could not load services");
        }
        foreach ($servicelist['data'] as $service) {
            $this->services[$service['id']] = new Entity\Service($service);
        }
    }

    /**
     * @return self
     */
    public function loadTopics($topicJson)
    {
        if (!is_readable($topicJson)) {
            throw new Exception("Cannot read $topicJson");
        }
        $topiclist = json_decode(file_get_contents($topicJson), true);
        if (!$topiclist) {
            throw new Exception("Could not load services");
        }
        foreach ($topiclist['data'] as $topic) {
            $this->topics[$topic['id']] = new Entity\Topic($topic);
        }
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
    	if(count($serviceIds)){
    		$servicelistCSV = implode(',', $serviceIds);
    		$servicelist = $this->fetchServiceFromCsv($servicelistCSV);
    		return $servicelist;
    	}
    	return false;
    }

    /**
     * @return Collection\Topics
     */
    public function fetchTopicList()
    {
        return $this->topics;
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
     * @return Collection\Location\Category
     */
    public function fetchCategoryPath($category_path)
    {
    	$categorylist = $this->fetchCategoryList();
    	foreach ($categorylist as $category) {
    		if ($category['path'] == $category_path) {
    			return $category;
    		}
    	}
    	return false;
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
