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

    public function fetchList($service_csv = false, $mixLanguages = false)
    {
        try {
            $sqlArgs = [$this->locale];
            $where = [];
            $join = [];
            $groupBy = '';
            if (false === $mixLanguages) {
                $where[] = 'l.locale = ?';
                $sql = 'SELECT data_json FROM location AS l';
            }
            else {
                $where[] = "l.locale='de'";
                $sql = "SELECT 
                IF(l2.id, l2.data_json, l.data_json) AS data_json
                FROM location AS l
                LEFT JOIN location AS l2 ON l2.id = l.id AND l2.locale = ?
                ";
            }

            if (!empty($service_csv)) {
                #$sqlArgs[] = $this->locale;
                $ids = explode(',', $service_csv);
                $qm = array_fill(0, count($ids), '?');
                $join[] = 'LEFT JOIN location_service AS ls ON ls.location_id = l.id';# AND ls.locale = ?';

                $where[] = "ls.service_id IN (" . implode(', ', $qm) . ")";
                $groupBy = 'GROUP BY l.id';
                array_push($sqlArgs, ...$ids);
            }
            $sql .= " " . implode(' ', $join);
            $sql .= " WHERE " . implode(' AND ', $where);
            $sql .= " " . $groupBy;

            #echo '<pre>' . print_r([$sql, $sqlArgs],1) . '</pre>';exit;
            $stm = $this->access()->prepare($sql);
            $stm->setFetchMode(\PDO::FETCH_CLASS|\PDO::FETCH_PROPS_LATE, '\\BO\\Dldb\\MySQL\\Entity\\Location');
            $stm->execute($sqlArgs);
            
            $locations = $stm->fetchAll();
            $locationList = new Collection();
            foreach ($locations as $location) {
                $locationList[$location['id']] = $location;
            }
            #echo '<pre>' . print_r($locationList,1) . '</pre>';exit;
            return $locationList;
        }
        catch (\Exception $e) {
            throw $e;
        }
    }

    public function fetchListByOffice($office, $mixLanguages = false)
    {
        try {
            $sqlArgs = [$this->locale];
            $where = [];
            $join = [];
            $groupBy = '';
            if (false === $mixLanguages) {
                $where[] = 'l.locale = ?';
                $sql = 'SELECT data_json FROM location AS l';
            }
            else {
                $where[] = "l.locale='de'";
                $sql = "SELECT 
                IF(l2.id, l2.data_json, l.data_json) AS data_json
                FROM location AS l
                LEFT JOIN location AS l2 ON l2.id = l.id AND l2.locale = ?
                ";
            }
            $where[] = "l.category_identifier = ?";
            $sqlArgs[] = $office;
                
            $sql .= " " . implode(' ', $join);
            $sql .= " WHERE " . implode(' AND ', $where);
            
            $sql .= " " . $groupBy;

            #echo '<pre>' . print_r([$sql, $sqlArgs],1) . '</pre>';exit;
            $stm = $this->access()->prepare($sql);
            $stm->setFetchMode(\PDO::FETCH_CLASS|\PDO::FETCH_PROPS_LATE, '\\BO\\Dldb\\MySQL\\Entity\\Location');
            $stm->execute($sqlArgs);
            
            $locations = $stm->fetchAll();
            $locationList = new Collection();
            foreach ($locations as $location) {
                $locationList[$location['id']] = $location;
            }
            #echo '<pre>' . print_r($locationList,1) . '</pre>';exit;
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
    public function fetchFromCsv($location_csv, $mixLanguages = false)
    {
        try {
            $sqlArgs = [$this->locale];
            $where = [];
            $join = [];
            if (false === $mixLanguages) {
                $where[] = 'l.locale = ?';
                $sql = 'SELECT data_json FROM location AS l';
            }
            else {
                $where[] = "l.locale='de'";
                $sql = "SELECT 
                IF(l2.id, l2.data_json, l.data_json) AS data_json
                FROM location AS l
                LEFT JOIN location AS l2 ON l2.id = l.id AND l2.locale = ?
                ";
            }

            #$sql = 'SELECT data_json FROM location WHERE locale = ?';

            $ids = explode(',', $location_csv);
            $qm = array_fill(0, count($ids), '?');
            $where[] = 'l.id IN (' . implode(', ', $qm) . ')';
            array_push($sqlArgs, ...$ids);

            $sql .= " " . implode(' ', $join);
            $sql .= " WHERE " . implode(' AND ', $where);

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

    protected function fetchGeoJsonLocations($category, $getAll)
    {
        try {
            $sqlArgs = [$this->locale, $this->locale, $this->locale];
            $sql = 'SELECT 
                l.id, l.name, l.authority_name, l.category_json, 
                c.contact_json, c.address_json, c.geo_json,
                m.url AS meta__url
            FROM 
                location AS l
            LEFT JOIN 
                contact AS c ON c.object_id = l.id AND c.locale = ?
            LEFT JOIN 
                meta AS m ON m.object_id = l.id AND m.locale = ?
            WHERE l.locale = ?';
            if (!empty($category) && false === $getAll) {
                $sqlArgs[] = $category;
                $sql .= ' AND category_identifier = ?';
            }
            $sql .= ' ORDER BY l.category_identifier, l.name';
            
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
     * @todo Refactoring required, functions in this class should return entities, not JSON data
     */
    public function fetchGeoJson($category = null, $getAll = false)
    {
        $locationList = $this->fetchGeoJsonLocations($category, $getAll);
        $geoJson = [];
        // TODO check refactoring: the following lines were ineffective cause the line $geoJson=[] happened afterwards
        //if (!empty($category) && false === $getAll) {
        //    $geoJson['category'] = $category;
        //}
        foreach ($locationList as $location) {
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

    public function readSearchResultList($query, $service_csv = null)
    {
        try {
            $sqlArgs = [$this->locale, $this->locale, $query];
            $sql = "SELECT l.data_json 
            FROM search AS se
            LEFT JOIN location AS l ON l.id = se.object_id AND l.locale = ?
            WHERE 
                se.locale = ? AND MATCH (search_value) AGAINST (? IN NATURAL LANGUAGE MODE)
                AND (search_type IN ('name', 'keywords', 'address')) AND entity_type='location'
             GROUP BY se.object_id
            ";
            /*
            if (!empty($service_csv)) {
                $ids = explode(',', $service_csv);
                $qm = array_fill(0, count($ids), '?');
                $sql .= ' AND se.object_id IN (' . implode(', ', $qm) . ')';
                array_push($sqlArgs, ...$ids);
            }*/
            #print_r($sql);exit;

            $stm = $this->access()->prepare($sql);
            $stm->setFetchMode(\PDO::FETCH_CLASS|\PDO::FETCH_PROPS_LATE, '\\BO\\Dldb\\MySQL\\Entity\\Location');

            $stm->execute($sqlArgs);
            
            $locations = $stm->fetchAll();

            $locationList = new Collection();
            foreach ($locations as $location) {
                $locationList[$location['id']] = $location;
            }
            #echo '<pre>' . print_r($locationList,1) . '</pre>';exit;
            return $locationList;
        }
        catch (\Exception $e) {
            throw $e;
        }
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
