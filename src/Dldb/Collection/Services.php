<?php
/**
 * @package Dldb
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Dldb\Collection;

class Services extends Base
{
    public function getIds($serviceList = null)
    {
        $idList = array();
        $serviceList = ($serviceList !== null) ? $serviceList : $this;
        foreach ($serviceList as $service) {
            $idList[] = $service['id'];
        }
        return $idList;
    }
    
    public function getCSV($serviceList = null)
    {
        return implode(',', $this->getIds($serviceList));
    }
}
