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
        $availability = (new Availability())->readByAppointment($appointment);
        $slotList = $availability->getSlotList()->withSlotsForAppointment($appointment);
        return $slotList;
    }
}
