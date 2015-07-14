<?php
/**
 * @package Dldb
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Dldb\Collection;

class Services extends Base
{
    public function getIds()
    {
        $idList = array();
        foreach ($this as $service) {
            $idList[] = $service['id'];
        }
        return $idList;
    }

    public function getNames()
    {
        $nameList = array();
        foreach ($this as $service) {
            $nameList[$service['id']] = $service['name'];
        }
        return $nameList;
    }

    public function getCSV($serviceList = null)
    {
        return implode(',', $this->getIds($serviceList));
    }
}
