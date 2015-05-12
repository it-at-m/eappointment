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
     * @param Int $service_id only check for this service_id
     * @param Bool $external allow external links, default false
     *
     * @return Bool
     */
    public function hasAppointments($service_id = null, $external = false)
    {
        foreach ($this['locations'] as $location) {
            if ($location->hasAppointments($service_id, $external)) {
                return true;
            }
        }
        return false;
    }
}
