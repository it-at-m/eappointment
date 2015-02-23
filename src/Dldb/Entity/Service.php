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
class Service extends Base
{

    /**
     * @return Bool
     */
    public function containsLocation($location_csv)
    {
        $service = $this->getData();
        $locationcompare = explode(',', $location_csv);
        foreach ($service['locations'] as $locationinfo) {
            if (in_array($locationinfo['location'], $locationcompare)) {
                return true;
            }
        }
        return false;
    }
}
