<?php
/**
 * @package Dldb
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Dldb\Collection;

class Locations extends Base
{
    /**
     * find locations with appointments
     *
     */
    public function getLocationsWithAppointmentsFor($service_id = null, $external = false)
    {
        $list = new self();
        foreach ($this as $location) {
            if ($location->hasAppointments($service_id, $external)) {
                $list[] = $location;
            }
        }
        return $list;
    }

    public function getIds()
    {
        $idList = array();
        foreach ($this as $location) {
            $idList[] = $location['id'];
        }
        return $idList;
    }

    public function getCSV()
    {
        return implode(',', $this->getIds());
    }
}
