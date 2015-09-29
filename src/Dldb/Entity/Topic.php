<?php
/**
 * @package Dldb
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Dldb\Entity;

/**
  * Helper for topics export
  *
  */
class Topic extends Base
{

    public function getServiceIds()
    {
        $serviceIds = array();
        foreach ($this['relation']['services'] as $service) {
            $serviceIds[] = $service['id'];
        }
        return $serviceIds;
    }

    public function isLinked()
    {
        return ($this['relation']['navi'] || count($this['relation']['navi']));
    }
}
