<?php
/**
 * @package ClientDldb
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/
namespace BO\Dldb\MySQL;

use \BO\Dldb\MySQL\Entity\Location as Entity;
use \BO\Dldb\MySQL\Collection\Locations as Collection;
use \BO\Dldb\Elastic\Location as Base;

/**
 * @SuppressWarnings(Coupling)
 */
class Location extends Base
{
    public function fetchId($location_id)
    {
        try {
            if ($location_id) {
                $sqlArgs = [$this->locale, (int)$location_id];
                $sql = 'SELECT data_json FROM location WHERE locale = ? AND id = ?';

                $stm = $this->access()->prepare($sql);
                $stm->setFetchMode(\PDO::FETCH_CLASS|\PDO::FETCH_PROPS_LATE, '\\BO\\Dldb\\MySQL\\Entity\\Location');
                $stm->execute($sqlArgs);
                if (!$stm || ($stm && $stm->rowCount() == 0)) {
                    return false;
                }
                $service = $stm->fetch();
                return $service;
            }
            return false;
        }
        catch (\Exception $e) {
            throw $e;
        }
    }

    public function fetchList($service_csv = false)
    {
        try {
            $sqlArgs = [$this->locale];
            $sql = 'SELECT data_json FROM location WHERE locale = ?';

            if (!empty($service_csv)) {
                $sqlArgs[] = $this->locale;
                $ids = explode(',', $service_csv);
                $qm = array_fill(0, count($ids), '?');

                $sql = "SELECT l.data_json
                FROM location AS l 
                LEFT JOIN location_service AS ls ON ls.service_id = l.id AND l.locale = ?
                WHERE l.locale = ? AND
                ls.location_id IN (" . implode(', ', $qm) . ")
                GROUP BY s.id";
                array_push($sqlArgs, ...$ids);
            }

            $stm = $this->access()->prepare($sql);
            $stm->setFetchMode(\PDO::FETCH_CLASS|\PDO::FETCH_PROPS_LATE, '\\BO\\Dldb\\MySQL\\Entity\\Location');
            $stm->execute($sqlArgs);
            
            $locations = $stm->fetchAll();
            $locationList = new Collection();
            foreach ($locations as $location) {
                $locationList[$location['id']] = $location;
            }
            #echo '<pre>' . print_r($serviceList,1) . '</pre>';exit;
            return $locationList;
        }
        catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     *
     * @return Collection\Services
     */
    public function fetchFromCsv($location_csv)
    {
        try {
            $sqlArgs = [$this->locale];
            $sql = 'SELECT data_json FROM location WHERE locale = ?';

            $ids = explode(',', $location_csv);
            $qm = array_fill(0, count($ids), '?');
            $sql .= ' AND id IN (' . implode(', ', $qm) . ')';
            array_push($sqlArgs, ...$ids);

            $stm = $this->access()->prepare($sql);
            $stm->setFetchMode(\PDO::FETCH_CLASS|\PDO::FETCH_PROPS_LATE, '\\BO\\Dldb\\MySQL\\Entity\\Location');
            $stm->execute($sqlArgs);
            
            $locations = $stm->fetchAll();
            $locationList = new Collection();
            foreach ($locations as $location) {
                $locationList[$location['id']] = $location;
            }
            return $locationList;
        }
        catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     *
     * @return \BO\ClientDldb\Collection\Authorities
     */
    public function searchAll($querystring, $service_csv = '')
    {
        $query = Helper::boolFilteredQuery();
        $mainquery = new \Elastica\Query();
        $limit = 1000;
        $searchquery = new \Elastica\Query\QueryString();
        if ($querystring > 10000 && $querystring < 15000) {
            // if it is a postal code, sort by distance and limit results
            $coordinates = \BO\Dldb\Plz\Coordinates::zip2LatLon($querystring);
            if (false !== $coordinates) {
                $searchquery->setQuery('*');
                $mainquery->addSort([
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
            }
        } elseif ('' === trim($querystring)) {
            // if empty, find all and trust in the filter
            $searchquery->setQuery('*');
        } else {
            $searchquery->setQuery($querystring);
        }
        $searchquery->setFields([
            'name^9',
            'authority.name^5',
            'address.street',
            'address.postal_code^9'
        ]);
        $query->getQuery()->addShould($searchquery);
        $filter = null;
        if ($service_csv) {
            foreach (explode(',', $service_csv) as $service_id) {
                $filter = new \Elastica\Filter\Term(array(
                    'services.service' => $service_id
                ));
                $query->getFilter()->addMust($filter);
            }
        }
        $mainquery->setQuery($query);
        $resultList = $this->access()
            ->getIndex()
            ->getType('location')
            ->search($mainquery, $limit);
        return $this->access()
            ->fromAuthority()
            ->fromLocationResults($resultList);
    }

    /**
     * search locations
     * this function is similar to self::searchAll() but it might get different boosts in the future
     *
     * @return Collection
     */
    public function readSearchResultList($querystring, $service_csv = '')
    {
        $query = Helper::boolFilteredQuery();
        $query->getFilter()->addMust(Helper::localeFilter($this->locale));
        $mainquery = new \Elastica\Query();
        $limit = 1000;
        $sort = true;
        $searchquery = new \Elastica\Query\QueryString();
        if ($querystring > 10000 && $querystring < 15000) {
            // if it is a postal code, sort by distance and limit results
            $coordinates = \BO\Dldb\Plz\Coordinates::zip2LatLon($querystring);
            if (false !== $coordinates) {
                $searchquery->setQuery('*');
                $mainquery->addSort([
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
        $searchquery->setFields([
            'name^9',
            'authority.name^5',
            'address.street',
            'address.postal_code^9'
        ]);
        $query->getQuery()->addShould($searchquery);
        $filter = null;
        if ($service_csv) {
            foreach (explode(',', $service_csv) as $service_id) {
                $filter = new \Elastica\Filter\Term(array(
                    'services.service' => $service_id
                ));
                $query->getFilter()->addMust($filter);
            }
        }
        $mainquery->setQuery($query);
        $resultList = $this->access()
            ->getIndex()
            ->getType('location')
            ->search($mainquery, $limit);
        return $this->access()
            ->fromAuthority()
            ->fromLocationResults($resultList);
    }

    protected function fetchGeoJsonLocations($category, $getAll)
    {
        $query = new \Elastica\Query();
        $query->setSource(['id', 'name', 'address.*', 'geo.*', 'meta.*', 'category.*']);

        $filter =  new \Elastica\Query\MatchAll();

        if (!empty($category) && false === $getAll) {
            $filter = new \Elastica\Query\BoolQuery();
            $termFilter = new \Elastica\Query\Term(['category.identifier' => $category]);
            $filter->addMust($termFilter);
        }
        $query->setQuery($filter);
        $query->addSort(['office' => ['order' => 'asc']]);
        $query->addSort(['name' => ['order' => 'asc']]);
        $resultList = $this->access()
            ->getIndex()
            ->getType('location')
            ->search($query, 1000)
        ;
        return $resultList;
    }

    /**
     * @todo Refactoring required, functions in this class should return entities, not JSON data
     */
    public function fetchGeoJson($category = null, $getAll = false)
    {
        $resultList = $this->fetchGeoJsonLocations($category, $getAll);
        $geoJson = [];
        // TODO check refactoring: the following lines were ineffective cause the line $geoJson=[] happened afterwards
        //if (!empty($category) && false === $getAll) {
        //    $geoJson['category'] = $category;
        //}
        foreach ($resultList as $result) {
            $location = new Entity($result->getData());
            if (empty($location['category']['identifier'])) {
                continue;
            }
            if (!isset($geoJson[$location['category']['identifier']])) {
                $geoJson[$location['category']['identifier']] = [
                    'name' => $location['category']['name'],
                    'type' => 'cluster',
                    'active' => (
                        !empty($category)
                        && $category == $location['category']['identifier'] ? true : (
                            !empty($category) && $category != $location['category']['identifier'] ? false : true
                        )
                    ),
                    'data' => ['type' => 'FeatureCollection', 'features' => []]
                ];
            }
            $geoJson[$location['category']['identifier']]['data']['features'][] = $location->getGeoJson();
        }

        return $geoJson;
    }

    public function fetchLocationsForCompilation($authoritys = [], $locations = [])
    {
        $limit = 1000;

        $localeFilter = new \Elastica\Query\Term(array(
            'meta.locale' => $this->locale
        ));

        $boolquery = new \Elastica\Query\BoolQuery();
        $boolquery->addMust($localeFilter);

        if (!empty($authoritys)) {
            $authorityFilter = new \Elastica\Query\Terms('authority.id', $authoritys);
            $boolquery->addMust($authorityFilter);
        }
        if (!empty($locations)) {
            $locationFilter = new \Elastica\Query\Terms('id', $locations);
            $boolquery->addMust($locationFilter);
        }

        $query = \Elastica\Query::create($boolquery);
        $query->addSort(['sort' => 'asc']);
        #print_r(json_encode($query->toArray()));exit;
        $resultList = $this
            ->access()
            ->getIndex()
            ->getType('location')
            ->search($query, $limit)
        ;

        $locationList = new Collection();
        foreach ($resultList as $result) {
            $location = new Entity($result->getData());
            $locationList[$location['id']] = $location;
        }
        return $locationList;
    }
}
