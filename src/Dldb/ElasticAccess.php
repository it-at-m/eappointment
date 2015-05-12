<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Dldb;

/**
 * @SuppressWarnings(TooManyMethods)
 *
 * Using elastica query classes increases object dependencies dramatically
 * @SuppressWarnings(CouplingBetweenObjects)
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
     * @return Collection\Locations
     */
    public function fetchLocationList($service_csv = '')
    {
        $filter = null;
        if ($service_csv) {
            $filter = new \Elastica\Filter\Terms('services.service', explode(',', $service_csv));
            $filter->setExecution('and');
        }
        $query = \Elastica\Query::create($filter);
        $resultList = $this->getIndex()->getType('location')->search($query, 10000);
        $locationList = new Collection\Locations();
        foreach ($resultList as $result) {
            $location = new Entity\Location($result->getData());
            $locationList[$location['id']] = $location;
        }
        return $locationList;
    }

    /**
     * @return Entity\Location
     */
    public function fetchLocation($location_id)
    {
        if ($location_id) {
            $filter = new \Elastica\Filter\Ids();
            $filter->setIds($location_id);
            $query = \Elastica\Query::create($filter);
            $result = $this->getIndex()->getType('location')->search($query);
            if ($result->count() == 1) {
                $locationList = $result->getResults();
                return new Entity\Location($locationList[0]->getData());
            }
        }
        return false;
    }

    /**
     * @return Collection\Locations
     */
    public function fetchLocationFromCsv($location_csv)
    {
        $filter = new \Elastica\Filter\Ids();
        $filter->setIds(explode(',', $location_csv));
        $query = \Elastica\Query::create($filter);
        $resultList = $this->getIndex()->getType('location')->search($query, 10000);
        $locationList = new Collection\Locations();
        foreach ($resultList as $result) {
            $location = new Entity\Location($result->getData());
            $locationList[$location['id']] = $location;
        }
        return $locationList;
    }

    /**
     * @return Collection\Services
     */
    public function fetchServiceList($location_csv = false)
    {
        $filter = null;
        if ($location_csv) {
            $filter = new \Elastica\Filter\Terms('locations.location', explode(',', $location_csv));
            $filter->setExecution('and');
        }
        $query = \Elastica\Query::create($filter);
        $resultList = $this->getIndex()->getType('service')->search($query, 10000);
        $serviceList = new Collection\Services();
        foreach ($resultList as $result) {
            $service = new Entity\Service($result->getData());
            $serviceList[$service['id']] = $service;
        }
        return $serviceList;
    }

    /**
     * @return Entity\Service
     */
    public function fetchService($service_id)
    {
        if ($service_id) {
            $filter = new \Elastica\Filter\Ids();
            $filter->setIds($service_id);
            $query = \Elastica\Query::create($filter);
            $result = $this->getIndex()->getType('service')->search($query);
            if ($result->count() == 1) {
                $locationList = $result->getResults();
                return new Entity\Service($locationList[0]->getData());
            }
        }
        return false;
    }

    /**
     * @return Collection\Services
     */
    public function fetchServiceFromCsv($service_csv)
    {
        $filter = new \Elastica\Filter\Ids();
        $filter->setIds(explode(',', $service_csv));
        $query = \Elastica\Query::create($filter);
        $resultList = $this->getIndex()->getType('service')->search($query, 10000);
        $serviceList = new Collection\Services();
        foreach ($resultList as $result) {
            $service = new Entity\Service($result->getData());
            $serviceList[$service['id']] = $service;
        }
        return $serviceList;
    }

    /**
     * fetch locations for a list of service and group by authority
     * @return Collection\Authorities
     */
    public function fetchAuthorityList(Array $servicelist)
    {
        $filter = null;
        if (count($servicelist)) {
            $filter = new \Elastica\Filter\Terms('services.service', $servicelist);
        }
        $query = \Elastica\Query::create($filter);
        $resultList = $this->getIndex()->getType('location')->search($query, 10000);
        return $this->authorityListFromLocationResults($resultList);
    }

    /**
     * Take an elasticsearch result and return a authority list
     *
     * @return Collection\Authorities
     */
    protected function authorityListFromLocationResults($resultList, $sort = true)
    {
        $authoritylist = new Collection\Authorities();
        foreach ($resultList as $result) {
            $location = new Entity\Location($result->getData());
            $authoritylist->addLocation($location);
        }
        if ($sort) {
            $authoritylist->sortByName();
        }
        return $authoritylist;
    }

    /**
     * @return Array
     */
    public function searchLocation($querystring, $service_csv = '')
    {
        $query = new \Elastica\Query();
        $limit = 1000;
        $sort = true;
        $boolquery = new \Elastica\Query\Bool();
        $searchquery = new \Elastica\Query\QueryString();
        if ($querystring > 10000 && $querystring < 15000) {
            // if it is a postal code, sort by distance and limit results
            $coordinates = \BO\Dldb\Plz\Coordinates::zip2LatLon($querystring);
            if (false !== $coordinates) {
                $searchquery->setQuery('*');
                $query->addSort([
                    "_geo_distance" => [
                        "geo" => [
                            "lat" => $coordinates['lat'],
                            "lon" => $coordinates['lon']
                        ],
                        "order" => "asc",
                        "unit" => "km"
                    ]
                ]);
                $limit = 5;
                $sort = false;
            }
        } elseif ('' === trim($querystring)) {
            // if empty, find all and trust in the filter
            $searchquery->setQuery('*');
        } else {
            $searchquery->setQuery($querystring);
        }
        $searchquery->setFields(['name^9','authority.name^5', 'address.street', 'address.postal_code^9']);
        $searchquery->setLowercaseExpandedTerms(false);
        $boolquery->addShould($searchquery);
        $filter = null;
        if ($service_csv) {
            $filter = new \Elastica\Filter\Terms('services.service', explode(',', $service_csv));
            $filter->setExecution('and');
        }
        $filteredQuery = new \Elastica\Query\Filtered($boolquery, $filter);
        $query->setQuery($filteredQuery);
        $resultList = $this->getIndex()->getType('location')->search($query, $limit);
        return $this->authorityListFromLocationResults($resultList, $sort);
    }

    /**
     * @return Collection\Services
     */
    public function searchService($query, $service_csv = '', $location_csv = '')
    {
        if (!$location_csv) {
            $location_csv = $this->fetchServiceLocationCsv($service_csv);
        }
        $boolquery = new \Elastica\Query\Bool();
        $searchquery = new \Elastica\Query\QueryString();
        if ('' === trim($query)) {
            $searchquery->setQuery('*');
        } else {
            $searchquery->setQuery($query);
        }
        $searchquery->setFields(['name^9','keywords^5']);
        $searchquery->setLowercaseExpandedTerms(false);
        $boolquery->addShould($searchquery);
        //$prefixquery = new \Elastica\Query\Prefix();
        //$prefixquery->setPrefix('az', preg_replace('#~\d$#', '', $query), 10);
        //$boolquery->addShould($prefixquery);
        $filter = null;
        if ($location_csv) {
            $filter = new \Elastica\Filter\Terms('locations.location', explode(',', $location_csv));
        }
        $query = new \Elastica\Query\Filtered($boolquery, $filter);
        $resultList = $this->getIndex()->getType('service')->search($query, 1000);
        $serviceList = new Collection\Services();
        foreach ($resultList as $result) {
            $service = new Entity\Service($result->getData());
            $serviceList[$service['id']] = $service;
        }
        return $serviceList;
    }
}
