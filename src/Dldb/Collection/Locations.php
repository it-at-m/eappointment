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
     * @param String $serviceCsv only check for this serviceCsv
     * @param Bool $external allow external links, default false
     */
    public function getLocationsWithAppointmentsFor($serviceCsv = null, $external = false)
    {
        $list = new self();
        foreach ($this as $location) {
            if ($location->hasAppointments($serviceCsv, $external)) {
                $list[] = $location;
            }
        }
        return $list;
    }

    public function getIds($locationList = null)
    {
        $idList = array();
        $locationList = ($locationList !== null) ? $locationList : $this;
        foreach ($locationList as $location) {
            $idList[] = $location['id'];
        }
        return $idList;
    }

    public function getCSV($locationList = null)
    {
        return implode(',', $this->getIds($locationList));
    }
}
