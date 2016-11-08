<?php
namespace BO\Zmsdb;

use \BO\Zmsentities\Slot as Entity;
use \BO\Zmsentities\Collection\SlotList as Collection;

class Slot extends Base
{

    /**
     * @return \BO\Zmsentities\Collection\SlotList
     *
     */
    public function readByAppointment(\BO\Zmsentities\Appointment $appointment)
    {
        $availabilityList = (new Availability())->readByAppointment($appointment);
        $slotList = $availabilityList->getSlotList()->withSlotsForAppointment($appointment);
        return $slotList;
    }
}
