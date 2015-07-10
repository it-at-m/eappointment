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
    
    /**
     * Check if locations are available for defined office
     *
     * @param String $officepath only check for this office
     *
     * @return new self
     */
    public function matchLocationWithOffice($officepath = null)
    {
        foreach ($this['locations'] as $key => $location) {
            if ($location['office'] != $officepath) {
                unset($this['locations'][$key]);
            }
        }
        $data = array(
            'name' => $this['name'],
            'locations' => $this['locations']
        );
        if (count($data['locations'])) {
            return new self($data);
        }
    }
    
    /**
     * Check if locations are available for defined office
     *
     * @param String $officepath only check for this office
     *
     * @return Bool
     */
    public function hasEaId($ea_id = null)
    {
        foreach ($this['locations'] as $key => $location) {
            if ($location['id'] == $ea_id) {
                return true;
            }
        }
        return false;
    }
}
