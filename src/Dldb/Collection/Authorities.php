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
     * @param Int $service_id only check for this service_id
     * @param Bool $external allow external links, default false
     *
     * @return Bool
     */
    public function hasAppointments($service_id = null, $external = false)
    {
        foreach ($this as $authority) {
            if ($authority->hasAppointments($service_id, $external)) {
                return true;
            }
        }
        return false;
    }
}
