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
class Authority extends Base
{

    public static function create($name)
    {
        $data = array(
            'name' => $name,
            'locations' => new \BO\Dldb\Collection\Locations(),
        );
        return new self($data);
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
        foreach ($this['locations'] as $location) {
            if ($location->hasAppointments($serviceCsv, $external)) {
                return true;
            }
        }
        return false;
    }
}
