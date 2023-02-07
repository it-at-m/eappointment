<?php
/**
 * @package ClientDldb
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/
namespace BO\Dldb\MySQL;

use \BO\Dldb\MySQL\Collection\Authorities as Collection;
use \BO\Dldb\Elastic\Authority as Base;

/**
 */
class Authority extends Base
{

    /**
     * fetch locations for a list of service and group by authority
     *
     * @return Collection\Authorities
     */
    public function fetchList($servicelist = [])
    {
        try {
            $authorityList = new Collection();

            $sqlArgs = [];
            
            if (!empty($servicelist)) {
                $sqlArgs = ['de',$this->locale];
                $questionMarks = array_fill(0, count($servicelist), '?');

                $sql = "SELECT a.data_json
                FROM authority_service AS aservice
                LEFT JOIN authority AS a ON a.id = aservice.authority_id AND a.locale = ?
                WHERE aservice.locale = ? AND aservice.service_id IN (" . implode(', ', $questionMarks) . ")";
                
                array_push($sqlArgs, ...$servicelist);
               
                $stm = $this->access()->prepare($sql);
                $stm->execute($sqlArgs);
                $stm->fetchAll(\PDO::FETCH_FUNC, function ($data_json) use ($authorityList) {
                    $authority = new \BO\Dldb\MySQL\Entity\Authority();
                    $authority->offsetSet('data_json', $data_json);
                    $authorityList[$authority['id']] = $authority;
                    $authority->clearLocations();
                });

                $sqlArgs = [$this->locale];
                $questionMarks = array_fill(0, count($servicelist), '?');

                $sql = "SELECT ls.location_id AS id
                    FROM location_service ls
                    -- LEFT JOIN location AS l ON l.id = ls.location_id AND l.locale = ?
                    WHERE ls.locale = ? AND ls.service_id IN (" . implode(', ', $questionMarks) . ")
                    GROUP BY ls.location_id 
                    ";

                array_push($sqlArgs, ...$servicelist);
                
                $stm = $this->access()->prepare($sql);
                $stm->setFetchMode(\PDO::FETCH_OBJ);
                $stm->execute($sqlArgs);

                $locations = $stm->fetchAll();
                $locationsIds = [];

                foreach ($locations as $location) {
                    $locationsIds[] = $location->id;
                }
 
                $locations = $this->access()->fromLocation($this->locale)
                    ->fetchFromCsv(implode(',', $locationsIds), true);
                
                foreach ($locations as $location) {
                    $authorityList[$location['authority']['id']]->addLocation($location);
                }
            } else {
                $sqlArgs = ['de'];
                $sql = 'SELECT data_json FROM authority WHERE locale = ?';
                $stm = $this->access()->prepare($sql);
                $stm->execute($sqlArgs);
                $stm->fetchAll(\PDO::FETCH_FUNC, function ($data_json) use ($authorityList) {
                    $authority = new \BO\Dldb\MySQL\Entity\Authority();
                    $authority->offsetSet('data_json', $data_json);
                    $authorityList[$authority['id']] = $authority;
                    $authority->clearLocations();
                });

                $locations = $this->access()->fromLocation($this->locale)->fetchList(false, true);
                
                foreach ($locations as $location) {
                    $authorityList[$location['authority']['id']]->addLocation($location);
                }
            }
            return $authorityList;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * fetch locations for a list of service and group by authority
     *
     * @return Collection\Authorities
     */
    public function fetchId($authorityid)
    {
        try {
            $sqlArgs = [$this->locale, $authorityid];
            $sqlArgs = ['de', $authorityid];
            
            $sql = 'SELECT data_json FROM authority WHERE locale = ? AND id = ?';
            $stm = $this->access()->prepare($sql);
            $stm->setFetchMode(\PDO::FETCH_CLASS|\PDO::FETCH_PROPS_LATE, '\\BO\\Dldb\\MySQL\\Entity\\Authority');
            $stm->execute($sqlArgs);

            $stm->execute($sqlArgs);
            if (!$stm || ($stm && $stm->rowCount() == 0)) {
                return false;
            }
            $authority = $stm->fetch();
            return $authority;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     *
     * @return Collection
     */
    public function readListByOfficePath($officepath)
    {
        $authorityList = new Collection();

        $locations = $this->access()->fromLocation($this->locale)->fetchListByOffice($officepath);

        foreach ($locations as $location) {
            $authorityList->addLocation($location);
        }

        return $authorityList;
    }
}
