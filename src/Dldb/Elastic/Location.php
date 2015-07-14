<?php
/**
 * @package ClientDldb
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Dldb\Elastic;

use \BO\Dldb\Entity\Location as Entity;
use \BO\Dldb\Collection\Locations as Collection;
use \BO\Dldb\File\Location as Base;

/**
  *
  */
class Location extends Base
{


    /**
     * @return Entity
     */
    public function fetchId($location_id)
    {
        if ($location_id) {
            $filter = new \Elastica\Filter\Ids();
            $filter->setIds($location_id);
            $query = \Elastica\Query::create($filter);
            $result = $this->access()->getIndex()->getType('location')->search($query);
            if ($result->count() == 1) {
                $locationList = $result->getResults();
                return new Entity($locationList[0]->getData());
            }
        }
        return false;
    }

    /**
     * @return Collection
     */
    public function fetchList($service_csv = '')
    {
        $filter = null;
        if ($service_csv) {
            $filter = new \Elastica\Filter\Terms('services.service', explode(',', $service_csv));
            $filter->setExecution('and');
        }
        $query = \Elastica\Query::create($filter);
        $resultList = $this->access()->getIndex()->getType('location')->search($query, 10000);
        $locationList = new Collection();
        foreach ($resultList as $result) {
            $location = new Entity($result->getData());
            $locationList[$location['id']] = $location;
        }
        return $locationList;
    }

    /**
     * @return Collection
     */
    public function fetchFromCsv($location_csv)
    {
        $filter = new \Elastica\Filter\Ids();
        $filter->setIds(explode(',', $location_csv));
        $query = \Elastica\Query::create($filter);
        $resultList = $this->access()->getIndex()->getType('location')->search($query, 10000);
        $locationList = new Collection();
        foreach ($resultList as $result) {
            $location = new Entity($result->getData());
            $locationList[$location['id']] = $location;
        }
        return $locationList;
    }

    /**
     * @return \BO\ClientDldb\Collection\Authorities
     */
    public function searchAll($querystring, $service_csv = '')
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
        $resultList = $this->access()->getIndex()->getType('location')->search($query, $limit);
        return $this->access()->fromAuthority()->fromLocationResults($resultList, $sort);
    }
}
