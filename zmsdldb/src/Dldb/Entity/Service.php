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
     * Checks, if it contains _all_ locations
     * Necessary, cause service A might be in location C but not location D,
     * but service B is in location C and D. On partial check, service a
     * would be valid for location D.
     *
     * @return Bool
     */
    public function containsLocation($location_csv)
    {
        $service = $this->getArrayCopy();
        $locationcompare = explode(',', $location_csv);
        $locationsfound = array();
        foreach ($service['locations'] as $locationinfo) {
            $location_id = $locationinfo['location'];
            if (in_array($location_id, $locationcompare)) {
                $locationsfound[$location_id] = $location_id;
            }
        }
        return count($locationcompare) == count($locationsfound);
    }

    public function hasLocation($location_csv)
    {
        $service = $this->getArrayCopy();
        $locationcompare = explode(',', $location_csv);
        foreach ($service['locations'] as $locationinfo) {
            if (in_array($locationinfo['location'], $locationcompare)) {
                return true;
            }
        }
        return false;
    }

    public function hasAppointments($external = false)
    {
        foreach ($this['locations'] as $location) {
            if (isset($location['appointment']['allowed'])) {
                if ($location['appointment']['allowed']) {
                    if ($external) {
                        return true;
                    } elseif ($location['appointment']['external'] === false) {
                        return true;
                    }
                }
            }
        }
        return false;
    }


    public function isResponsibleForAll()
    {
        return $this['responsibility_all'];
    }

    /**
     * @return Bool
     */
    public function isLocale($locale)
    {
        $service = $this->getArrayCopy();
        return $service['meta']['locale'] == $locale;
    }

    public function getLocations()
    {
        $locations = [];
        foreach ($this['locations'] as $location) {
            $locations[$location['location']] = $location;
        }
        return $locations;
    }
}
