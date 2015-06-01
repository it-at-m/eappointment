<?php
/**
 * @package Dldb
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Dldb\Collection;

class Authorities extends Base
{

    public function addLocation(\BO\Dldb\Entity\Location $location)
    {
        if (array_key_exists('authority', $location)) {
            $this->addAuthority($location['authority']['id'], $location['authority']['name']);
            $this[$location['authority']['id']]['locations'][] = $location;
        }
        return $this;
    }

    public function addAuthority($authority_id, $name)
    {
        if (!$this->hasAuthority($authority_id)) {
            $authority = \BO\Dldb\Entity\Authority::create($name);
            $this[$authority_id] = $authority;
        }
        return $this;
    }

    public function hasAuthority($authority_id)
    {
        return array_key_exists($authority_id, $this);
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
        foreach ($this as $authority) {
            if ($authority->hasAppointments($serviceCsv, $external)) {
                return true;
            }
        }
        return false;
    }

    /**
     * remove locations if no appointment is available
     *
     * @param String $serviceCsv only check for this serviceCsv
     * @param Bool $external allow external links, default false
     *
     * @return self
     */
    public function removeLocationsWithoutAppointments($serviceCsv = null, $external = false)
    {
        $authorityIterator = $this->getIterator();
        foreach ($authorityIterator as $key => $authority) {
            if ($authority->hasAppointments($serviceCsv, $external)) {
                $locationIterator = $authority['locations']->getIterator();
                foreach ($locationIterator as $subkey => $location) {
                    if (!$location->hasAppointments($serviceCsv, $external)) {
                        $locationIterator->offsetUnset($subkey);
                    }
                }
            } else {
                var_dump($authority['name']);
                $authorityIterator->offsetUnset($key);
            }
        }
        return $this;
    }
}
