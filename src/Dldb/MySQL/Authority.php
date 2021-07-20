<?php
/**
 * @package ClientDldb
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/
namespace BO\Dldb\MySQL;

use \BO\Dldb\MySQL\Entity\Authority as Entity,
    \BO\Dldb\MySQL\Entity\Location as LocationEntity,   
    \BO\Dldb\MySQL\Collection\Authorities as Collection,
    \BO\Dldb\Elastic\Authority as Base,
    \BO\Dldb\MySQL\Location AS LocationAccess
;

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

        $authorityList = new Collection();
        $sqlArgs = [$this->locale];
        
        if (!empty($servicelist)) {
            $sqlArgs[] = $this->locale;
            $qm = array_fill(0, count($servicelist), '?');

            $sql = "SELECT l.data_json 
                FROM location_service ls
                LEFT JOIN location AS l ON l.id = ls.location_id AND l.locale = ?
                WHERE ls.locale = ? AND ls.service_id IN (" . implode(', ', $qm) . ")
                GROUP BY ls.location_id 
                ORDER BY l.name";

            array_push($sqlArgs, ...$servicelist);
            
            $stm = $this->access()->prepare($sql);
            $stm->setFetchMode(\PDO::FETCH_CLASS|\PDO::FETCH_PROPS_LATE, '\\BO\\Dldb\\MySQL\\Entity\\Location');
            $stm->execute($sqlArgs);

            $locations = $stm->fetchAll();
            
        }
        else {
            $locations = $this->access()->fromLocation($this->locale)->fetchList();
        }

        $authorityList = new Collection();
        foreach ($locations as $location) {
            $authorityList->addLocation($location);
        }
        return $authorityList;

    }
}
