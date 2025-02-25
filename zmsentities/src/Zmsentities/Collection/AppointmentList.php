<?php

namespace BO\Zmsentities\Collection;

use BO\Zmsentities\Appointment;

class AppointmentList extends Base
{
    public const ENTITY_CLASS = '\BO\Zmsentities\Appointment';

    public function getByDate($date)
    {
        foreach ($this as $item) {
            if ($item['date'] == $date) {
                return $item instanceof Appointment ? $item : new Appointment($item);
            }
        }
        return false;
    }

    public function hasDateScope($date, $scopeId)
    {
        $item = $this->getByDate($date);
        if ($item && $item->toProperty()->scope->id->get() == $scopeId) {
            return true;
        }
        return false;
    }

    public function hasAppointment(\BO\Zmsentities\Appointment $appointment)
    {
        foreach ($this as $appointmentItem) {
            if ($appointmentItem->isMatching($appointment)) {
                return true;
            }
        }
        return false;
    }

    public function getCalculatedSlotCount()
    {
        $slotCount = 0;
        foreach ($this as $appointmentItem) {
            $slotCount += $appointmentItem->getSlotCount();
        }
        return $slotCount;
    }
}
