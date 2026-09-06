<?php

namespace BO\Zmsentities\Collection;

use BO\Zmsentities\Appointment;

/**
 * @extends Base<\BO\Zmsentities\Appointment>
 */
class AppointmentList extends Base
{
    public const string ENTITY_CLASS = '\BO\Zmsentities\Appointment';

    public function getByDate(mixed $date): Appointment|false
    {
        foreach ($this as $item) {
            if ($item['date'] == $date) {
                return $item;
            }
        }
        return false;
    }

    public function hasDateScope(mixed $date, mixed $scopeId): bool
    {
        $item = $this->getByDate($date);
        if ($item && $item->toProperty()->scope->id->get() == $scopeId) {
            return true;
        }
        return false;
    }

    public function hasAppointment(\BO\Zmsentities\Appointment $appointment): bool
    {
        foreach ($this as $appointmentItem) {
            if ($appointmentItem->isMatching($appointment)) {
                return true;
            }
        }
        return false;
    }

    public function getCalculatedSlotCount(): mixed
    {
        $slotCount = 0;
        foreach ($this as $appointmentItem) {
            $slotCount += $appointmentItem->getSlotCount();
        }
        return $slotCount;
    }
}
