<?php
/**
 * @package ClientDldb
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/
namespace BO\Dldb\MySQL;

use \BO\Dldb\MySQL\Entity\Service as Entity;
use \BO\Dldb\MySQL\Collection\Services as Collection;
use \BO\Dldb\Elastic\Service as Base
;
use Error;

/**
 * @SuppressWarnings(Coupling)
 */
class Service extends Base
{
    /**
     *
     * @return Entity\Service
     */
    public function fetchId($service_id)
    {
        try {
            if ($service_id) {
                $sqlArgs = [$this->locale, (int)$service_id];
                $sql = 'SELECT data_json FROM service WHERE locale = ? AND id = ?';

                $stm = $this->access()->prepare($sql);
                $stm->setFetchMode(\PDO::FETCH_CLASS|\PDO::FETCH_PROPS_LATE, '\\BO\\Dldb\\MySQL\\Entity\\Service');
                $stm->execute($sqlArgs);
                if (!$stm || ($stm && $stm->rowCount() == 0)) {
                    return false;
                }
                $service = $stm->fetch();
                #echo '<pre>' . print_r($service,1) . '</pre>';exit;
                return $service;
            }
            return false;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     *
     * @return Collection
     */
    public function fetchList($location_csv = false, $mixLanguages = false)
    {
        try {
            $sqlArgs = [$this->locale];
            $where = [];
            $join = [];
            $groupBy = '';
            if (false === $mixLanguages) {
                $where[] = 's.locale = ?';
                $sql = 'SELECT data_json FROM service AS s ';
            } else {
                $where[] = "s.locale='de'";
                $sql = "SELECT 
                IF(s2.id, s2.data_json, s.data_json) AS data_json
                FROM service AS s
                LEFT JOIN service AS s2 ON s2.id = s.id AND s2.locale = ?
                ";
            }

            if (!empty($location_csv)) {
                #$sqlArgs[] = $this->locale;
                $ids = explode(',', $location_csv);
                $qm = array_fill(0, count($ids), '?');
                $join[] = 'LEFT JOIN location_service AS ls ON ls.service_id = s.id';# AND ls.locale = ?';

                $where[] = "ls.location_id IN (" . implode(', ', $qm) . ")";
                $groupBy = 'GROUP BY s.id';
                array_push($sqlArgs, ...$ids);
            }
            $sql .= " " . implode(' ', $join);
            $sql .= " WHERE " . implode(' AND ', $where);
            $sql .= " " . $groupBy;

            #echo '<pre>' . print_r([$sql, $sqlArgs],1) . '</pre>';exit;

            $stm = $this->access()->prepare($sql);
            $stm->setFetchMode(\PDO::FETCH_CLASS|\PDO::FETCH_PROPS_LATE, '\\BO\\Dldb\\MySQL\\Entity\\Service');
            $stm->execute($sqlArgs);
            
            $services = $stm->fetchAll();
            $serviceList = new Collection();
            foreach ($services as $service) {
                $serviceList[$service['id']] = $service;
            }
            #echo '<pre>' . print_r($serviceList,1) . '</pre>';exit;
            return $serviceList;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     *
     * @return Collection\Services
     */
    public function fetchFromCsv($service_csv, $mixLanguages = false)
    {
        try {
            $sqlArgs = [$this->locale];
            $where = [];
            $join = [];
            
            if (false === $mixLanguages) {
                $where[] = 's.locale = ?';
                $sql = 'SELECT data_json FROM service AS s ';
            } else {
                $where[] = "s.locale='de'";
                $sql = "SELECT 
                IF(s2.id, s2.data_json, s.data_json) AS data_json
                FROM service AS s
                LEFT JOIN service AS s2 ON s2.id = s.id AND s2.locale = ?
                ";
            }
            
            $ids = explode(',', $service_csv);
            $qm = array_fill(0, count($ids), '?');
            
            $where[] = 's.id IN (' . implode(', ', $qm) . ')';
            array_push($sqlArgs, ...$ids);

            $sql .= " " . implode(' ', $join);
            $sql .= " WHERE " . implode(' AND ', $where);
            
            $stm = $this->access()->prepare($sql);
            $stm->setFetchMode(\PDO::FETCH_CLASS|\PDO::FETCH_PROPS_LATE, '\\BO\\Dldb\\MySQL\\Entity\\Service');
            $stm->execute($sqlArgs);
            
            $services = $stm->fetchAll();

            $serviceList = new Collection();
            foreach ($services as $service) {
                $serviceList[$service['id']] = $service;
            }
            return $serviceList;
        } catch (\Exception $e) {
            throw $e;
        }
    }


    public function fetchListRelated($service_id)
    {
        try {
            $service = $this->fetchId($service_id);
            $serviceList = new Collection();
            if (!$service) {
                return $serviceList;
            }
            $leika = str_split(substr(strval($service['leika']), 0, 11));
            $leika[0] = '_';
            $leika[1] = '_';

            $leika = implode($leika);
            
            $sqlArgs = [$this->locale, $service['leika'], $leika.'%'];
            
            $sql = 'SELECT data_json FROM service WHERE locale = ? AND leika != ? AND leika LIKE ?';

            $stm = $this->access()->prepare($sql);
            $stm->setFetchMode(\PDO::FETCH_CLASS|\PDO::FETCH_PROPS_LATE, '\\BO\\Dldb\\MySQL\\Entity\\Service');
            $stm->execute($sqlArgs);

            $services = $stm->fetchAll();

            
            foreach ($services as $service) {
                $serviceList[$service['id']] = $service;
            }
            return $serviceList;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function readSearchResultList($query, $service_csv = null)
    {
        try {
            $sqlArgs = [$this->locale, $this->locale, $query];
            $sql = "SELECT s.data_json 
            FROM search AS se
            LEFT JOIN service AS s ON s.id = se.object_id AND s.locale = ?
            WHERE 
                se.locale = ? AND MATCH (search_value) AGAINST (? IN NATURAL LANGUAGE MODE)
                AND (search_type IN ('name', 'keywords')) AND entity_type='service'
            GROUP BY se.object_id
            ";

            if (!empty($service_csv)) {
                $ids = explode(',', $service_csv);
                $qm = array_fill(0, count($ids), '?');
                $sql .= ' AND se.object_id IN (' . implode(', ', $qm) . ')';
                array_push($sqlArgs, ...$ids);
            }
            #print_r($sql);exit;

            $stm = $this->access()->prepare($sql);
            $stm->setFetchMode(\PDO::FETCH_CLASS|\PDO::FETCH_PROPS_LATE, '\\BO\\Dldb\\MySQL\\Entity\\Service');

            $stm->execute($sqlArgs);
            
            $services = $stm->fetchAll();

            $serviceList = new Collection();
            foreach ($services as $service) {
                $serviceList[$service['id']] = $service;
            }

            return $serviceList;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
