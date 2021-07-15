<?php
/**
 * @package ClientDldb
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/
namespace BO\Dldb\MySQL;

use \BO\Dldb\MySQL\Entity\Service as Entity,
    \BO\Dldb\MySQL\Collection\Services as Collection,
    \BO\Dldb\Elastic\Service AS Base
;
use Error;

/**
 * @SuppressWarnings(Coupling)
 */
class Service extends Base 
{
    /**
     *
     * @return Collection
     */
    public function fetchList($location_csv = false)
    {
        try {
            $sqlArgs = [$this->locale];
            $sql = 'SELECT id,name, data_json FROM service WHERE locale = ?';

            if (!empty($location_csv)) {
                $ids = explode(',', $location_csv);
                $qm = array_fill(0, count($ids), '?');
                $sql .= ' AND id IN (' . implode(', ', $qm) . ')';
                array_push($sqlArgs, ...$ids);
            }

            $stm = $this->access()->prepare($sql);
            $stm->execute($sqlArgs);
            
            $entitys = $stm->fetchAll(\PDO::FETCH_CLASS|\PDO::FETCH_PROPS_LATE, '\\BO\\Dldb\\MySQL\\Entity\\Service');

            $servicelist = new Collection($entitys);
            #echo '<pre>' . print_r($servicelist,1) . '</pre>';exit;
            return $servicelist;
        }
        catch (\Exception $e) {
            throw $e;
        }
    }
}
