<?php
/**
 * @package Dldb
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Dldb\Entity;

/**
  * Helper for service export
  *
  */
class Location extends Base
{

    /**
     * @return Bool
     */
    public function containsService($service_csv)
    {
        $location = $this->getData();
        $servicecompare = explode(',', $service_csv);
        $servicecount = array();
        foreach ($location['services'] as $serviceinfo) {
            $service_id = $serviceinfo['service'];
            if (in_array($service_id, $servicecompare)) {
                $servicecount[$service_id] = $service_id;
            }
        }
        return count($servicecount) == count($servicecompare);
    }
}
