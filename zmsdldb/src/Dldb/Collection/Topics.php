<?php
/**
 * @package Dldb
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Dldb\Collection;

class Topics extends Base
{
    public function getNames()
    {
        $nameList = array();
        foreach ($this as $topic) {
            $nameList[$topic['id']] = $topic['name'];
        }
        return $nameList;
    }
}
