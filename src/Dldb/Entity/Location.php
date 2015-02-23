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
        foreach ($location['services'] as $serviceinfo) {
            if (in_array($serviceinfo['service'], $servicecompare)) {
                return true;
            }
        }
        return false;
    }
}
