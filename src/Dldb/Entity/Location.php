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
        $location = $this->getArrayCopy();
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

    /**
     * Check if appointments are available
     *
     * @param Int $service_id only check for this service_id
     * @param Bool $external allow external links, default false
     *
     * @return Bool
     */
    public function hasAppointments($service_id = null, $external = false)
    {
        foreach ($this['services'] as $service) {
            if (array_key_exists('appointment', $service)
                && (null === $service_id || $service['service'] == $service_id)
            ) {
                if ($service['appointment']['allowed']) {
                    if ($external) {
                        return true;
                    } elseif ($service['appointment']['external'] === false) {
                        return true;
                    }
                }
            }
        }
        return false;
    }
}
