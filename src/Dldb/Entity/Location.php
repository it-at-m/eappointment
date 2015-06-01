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
     * @var Int $service_id
     *
     * @return FALSE or Array
     */
    public function getServiceInfo($service_id)
    {
        foreach ($this['services'] as $service) {
            if ($service['service'] == $service_id) {
                return $service;
            }
        }
        return false;
    }

    /**
     * Check if appointments are available
     *
     * @param String $serviceCsv only check for this serviceCsv
     * @param Bool $external allow external links, default false
     *
     * @return Bool
     */
    public function hasAppointments($serviceCsv = null, $external = false)
    {
        if ($this->containsService($serviceCsv)) {
            $serviceList = explode(',', $serviceCsv);
            $servicecount = array();
            foreach ($serviceList as $service_id) {
                $service = $this->getServiceInfo($service_id);
                if (array_key_exists('appointment', $service)) {
                    if ($service['appointment']['allowed']) {
                        if ($external) {
                            $servicecount[$service_id] = $service_id;
                        } elseif ($service['appointment']['external'] === false) {
                            $servicecount[$service_id] = $service_id;
                        }
                    }
                }
            }
            return count($servicecount) == count($serviceList);
        }
        return false;
    }
}
