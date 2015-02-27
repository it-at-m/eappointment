<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Dldb;

/**
 *
 */
class ElasticAccess extends FileAccess
{

    /**
     * The client used to talk to elastic search.
     *
     * @var \Elastica\Client
     */
    protected $connection;

    /**
      * Index from elastic search
      *
      * @var \Elastica\Index $index
      */
    protected $index;

    /**
     * @return self
     */
    public function __construct($index, $host = 'localhost', $port = '9200', $transport = 'Http')
    {
        $this->connection = new \Elastica\Client(
            array(
                'host' => $host,
                'port' => $port,
                'transport' => $transport
            )
        );
        $this->index = $this->getConnection()->getIndex($index);
    }

    /**
     * @return \Elastica\Index
     */
    protected function getIndex()
    {
        return $this->index;
    }

    /**
     * @return \Elastica\Client
     */
    protected function getConnection()
    {
        return $this->connection;
    }

    /**
     * @return Array
     */
    public function fetchLocationList($service_csv = '')
    {
        $filter = new \Elastica\Filter\Terms('services.service', explode(',', $service_csv));
        $filter->setExecution('and');
        $query = \Elastica\Query::create($filter);
        $resultList = $this->getIndex()->getType('location')->search($query, 10000);
        $locationList = array();
        foreach ($resultList as $result) {
            $location = $result->getData();
            $locationList[$location['id']] = $location;
        }
        return $locationList;
    }

    /**
     * @return Array
     */
    public function fetchLocation($location_id)
    {
        $filter = new \Elastica\Filter\Ids();
        $filter->setIds($location_id);
        $query = \Elastica\Query::create($filter);
        $result = $this->getIndex()->getType('location')->search($query);
        if ($result->count() == 1) {
            $locationList = $result->getResults();
            return $locationList[0]->getData();
        }
        return false;
    }

    /**
     * @return Array
     */
    public function fetchLocationFromCsv($location_csv)
    {
        $filter = new \Elastica\Filter\Ids();
        $filter->setIds(explode(',', $location_csv));
        $query = \Elastica\Query::create($filter);
        $resultList = $this->getIndex()->getType('location')->search($query, 10000);
        $locationList = array();
        foreach ($resultList as $result) {
            $location = $result->getData();
            $locationList[$location['id']] = $location;
        }
        return $locationList;
    }

    /**
     * @return Array
     */
    public function fetchServiceList($location_csv = false)
    {
        $filter = new \Elastica\Filter\Terms('locations.location', explode(',', $location_csv));
        $filter->setExecution('and');
        $query = \Elastica\Query::create($filter);
        $resultList = $this->getIndex()->getType('service')->search($query, 10000);
        $serviceList = array();
        foreach ($resultList as $result) {
            $service = $result->getData();
            $serviceList[$service['id']] = $service;
        }
        return $serviceList;
    }

    /**
     * @return Array
     */
    public function fetchService($service_id)
    {
        $filter = new \Elastica\Filter\Ids();
        $filter->setIds($service_id);
        $query = \Elastica\Query::create($filter);
        $result = $this->getIndex()->getType('service')->search($query);
        if ($result->count() == 1) {
            $locationList = $result->getResults();
            return $locationList[0]->getData();
        }
        return false;
    }

    /**
     * @return Array
     */
    public function fetchServiceFromCsv($service_csv)
    {
        $filter = new \Elastica\Filter\Ids();
        $filter->setIds(explode(',', $service_csv));
        $query = \Elastica\Query::create($filter);
        $resultList = $this->getIndex()->getType('service')->search($query, 10000);
        $serviceList = array();
        foreach ($resultList as $result) {
            $service = $result->getData();
            $serviceList[$service['id']] = $service;
        }
        return $serviceList;
    }

    /**
     * fetch locations for a list of service and group by authority
     * @return Array
     */
    public function fetchAuthorityList(Array $servicelist)
    {
        $authoritylist = array();
        $filter = new \Elastica\Filter\Terms('services.service', $servicelist);
        $query = \Elastica\Query::create($filter);
        $resultList = $this->getIndex()->getType('location')->search($query, 10000);
        foreach ($resultList as $result) {
            $location = $result->getData();
            if (array_key_exists('authority', $location)) {
                if (!array_key_exists($location['authority']['id'], $authoritylist)) {
                    $authoritylist[$location['authority']['id']] = array(
                        "name"          => $location['authority']['name'],
                        "locations"     => array()
                    );
                }
                $authoritylist[$location['authority']['id']]['locations'][] = $location;
            }
        }
        ksort($authoritylist);
        return $authoritylist;
    }

    /**
     * @return Array
     */
    public function searchLocation($query, $service_csv = '')
    {
        $searchquery = new \Elastica\Query\QueryString($query);
        $filter = new \Elastica\Filter\Terms('services.service', explode(',', $service_csv));
        $query = new \Elastica\Query\Filtered($searchquery, $filter);
        $resultList = $this->getIndex()->getType('location')->search($query, 100);
        $locationList = array();
        foreach ($resultList as $result) {
            $location = $result->getData();
            $locationList[$location['id']] = $location;
        }
        return $locationList;
    }

    /**
     * @return Array
     */
    public function searchService($query, $location_csv = '')
    {
        $searchquery = new \Elastica\Query\QueryString($query);
        $filter = new \Elastica\Filter\Terms('locations.location', explode(',', $location_csv));
        $query = new \Elastica\Query\Filtered($searchquery, $filter);
        $resultList = $this->getIndex()->getType('service')->search($query, 100);
        $serviceList = array();
        foreach ($resultList as $result) {
            $service = $result->getData();
            $serviceList[$service['id']] = $service;
        }
        return $serviceList;
    }
}
