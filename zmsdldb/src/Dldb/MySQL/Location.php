<?php

/**
 * @package ClientDldb
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Dldb\MySQL;

use BO\Dldb\MySQL\Entity\Location as Entity;
use BO\Dldb\MySQL\Collection\Locations as Collection;
use BO\Dldb\Elastic\Location as Base;

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
                $stm->setFetchMode(\PDO::FETCH_CLASS | \PDO::FETCH_PROPS_LATE, '\\BO\\Dldb\\MySQL\\Entity\\Location');
                $stm->execute($sqlArgs);
                if (!$stm || ($stm && $stm->rowCount() == 0)) {
                    return false;
                }
                $service = $stm->fetch();
                return $service;
            }
            return false;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function fetchList($service_csv = false, $mixLanguages = false)
    {
        # COALESCE(l2.data_json, l.data_json) AS data_json
        # IF(l2.id, l2.data_json, l.data_json) AS data_json
        try {
            $sqlArgs = [$this->locale];
            $where = [];
            $join = [];
            $groupBy = '';
            if (false === $mixLanguages) {
                $where[] = 'l.locale = ?';
                $sql = 'SELECT data_json FROM location AS l';
            } else {
                $where[] = "l.locale='de'";
                $sql = "SELECT 
                COALESCE(l2.data_json, l.data_json) AS data_json
                FROM location AS l
                LEFT JOIN location AS l2 ON l2.id = l.id AND l2.locale = ?
                ";
            }

            if (!empty($service_csv)) {
                #$sqlArgs[] = $this->locale;
                $ids = explode(',', $service_csv);
                $questionMarks = array_fill(0, count($ids), '?');
                $join[] = 'LEFT JOIN location_service AS ls ON ls.location_id = l.id';# AND ls.locale = ?';

                $where[] = "ls.service_id IN (" . implode(', ', $questionMarks) . ")";
                $groupBy = 'GROUP BY l.id';
                array_push($sqlArgs, ...$ids);
            }
            $sql .= " " . implode(' ', $join);
            $sql .= " WHERE " . implode(' AND ', $where);
            $sql .= " " . $groupBy;

            $locationList = new Collection();

            $stm = $this->access()->prepare($sql);
            $stm->execute($sqlArgs);
            $stm->fetchAll(\PDO::FETCH_FUNC, function ($data_json) use ($locationList) {
                $location = new \BO\Dldb\MySQL\Entity\Location();
                $location->offsetSet('data_json', $data_json);

                $locationList[$location['id']] = $location;
            });

            return $locationList;
        } catch (\Exception $e) {
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
            } else {
                $where[] = "l.locale='de'";
                $sql = "SELECT 
                COALESCE(l2.data_json, l.data_json) AS data_json
                FROM location AS l
                LEFT JOIN location AS l2 ON l2.id = l.id AND l2.locale = ?
                ";
            }
            $where[] = "l.category_identifier = ?";
            $sqlArgs[] = $office;

            $sql .= " " . implode(' ', $join);
            $sql .= " WHERE " . implode(' AND ', $where);

            $sql .= " " . $groupBy;

            $locationList = new Collection();
            $stm = $this->access()->prepare($sql);
            $stm->execute($sqlArgs);
            $stm->fetchAll(\PDO::FETCH_FUNC, function ($data_json) use ($locationList) {
                $location = new \BO\Dldb\MySQL\Entity\Location();
                $location->offsetSet('data_json', $data_json);

                $locationList[$location['id']] = $location;
            });

            return $locationList;
        } catch (\Exception $e) {
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
            if (false === $mixLanguages) {
                $where[] = 'l.locale = ?';
                $sql = 'SELECT data_json FROM location AS l';
            } else {
                $where[] = "l.locale='de'";
                $sql = "SELECT 
                COALESCE(l2.data_json, l.data_json) AS data_json
                FROM location AS l
                LEFT JOIN location AS l2 ON l2.id = l.id AND l2.locale = ?
                ";
            }

            $ids = explode(',', $location_csv);
            $questionMarks = array_fill(0, count($ids), '?');
            $where[] = 'l.id IN (' . implode(', ', $questionMarks) . ')';
            array_push($sqlArgs, ...$ids);

            $sql .= " WHERE " . implode(' AND ', $where);

            $locationList = new Collection();

            $stm = $this->access()->prepare($sql);
            $stm->execute($sqlArgs);
            $stm->fetchAll(\PDO::FETCH_FUNC, function ($data_json) use ($locationList) {
                $location = new \BO\Dldb\MySQL\Entity\Location();
                $location->offsetSet('data_json', $data_json);

                $locationList[$location['id']] = $location;
            });

            return $locationList;
        } catch (\Exception $e) {
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

            $locationList = new Collection();

            $stm = $this->access()->prepare($sql);
            $stm->execute($sqlArgs);
            $stm->fetchAll(\PDO::FETCH_FUNC, function (
                $id,
                $name,
                $authority_name,
                $category_json,
                $contact_json,
                $address_json,
                $geo_json,
                $meta__url
            ) use ($locationList) {
                $location = new \BO\Dldb\MySQL\Entity\Location();
                $location->offsetSet('id', $id);
                $location->offsetSet('name', $name);
                $location->offsetSet('authority_name', $authority_name);
                $location->offsetSet('category_json', $category_json);
                $location->offsetSet('contact_json', $contact_json);
                $location->offsetSet('address_json', $address_json);
                $location->offsetSet('geo_json', $geo_json);
                $location->offsetSet('meta__url', $meta__url);

                $locationList[$location['id']] = $location;
            });

            return $locationList;
        } catch (\Exception $e) {
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
            #$query = '+' . implode(' +', explode(' ', $query));
            $sqlArgs = [$this->locale, $this->locale, $query];
            $sql = "SELECT l.data_json 
            FROM search AS se
            LEFT JOIN location AS l ON l.id = se.object_id AND l.locale = ?
            WHERE 
                se.locale = ? AND MATCH (search_value) AGAINST (? IN BOOLEAN MODE)
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

            $locationList = new Collection();

            $stm = $this->access()->prepare($sql);
            $stm->execute($sqlArgs);
            $stm->fetchAll(\PDO::FETCH_FUNC, function ($data_json) use ($locationList) {
                $location = new \BO\Dldb\MySQL\Entity\Location();
                $location->offsetSet('data_json', $data_json);

                $locationList[$location['id']] = $location;
            });

            return $locationList;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function fetchLocationsForCompilation($authoritys = [], $locations = [])
    {
        try {
            $sqlArgs = [$this->locale];

            $sql = "SELECT 
                l.data_json
            FROM location AS l
            ";
            $where = ['l.locale = ?'];

            if (!empty($authoritys)) {
                $where[] = 'l.authority_id IN (' . implode(',', $authoritys) . ')';
            }

            if (!empty($locations)) {
                $where[] = 'l.id IN (' . implode(',', $locations) . ')';
            }

            $sql .= ' WHERE ' . implode(' AND ', $where);

            $locationList = new Collection();

            $stm = $this->access()->prepare($sql);
            $stm->execute($sqlArgs);
            $stm->fetchAll(\PDO::FETCH_FUNC, function ($data_json) use ($locationList) {
                $location = new \BO\Dldb\MySQL\Entity\Location();
                $location->offsetSet('data_json', $data_json);

                $locationList[$location['id']] = $location;
            });

            return $locationList;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
