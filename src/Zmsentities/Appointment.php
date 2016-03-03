<?php

namespace BO\Zmsentities;

class Appointment extends Schema\Entity
{
    public static $schema = "appointment.json";

    public function toDate($lang)
    {
        return ($lang == 'en') ? date('l F d, Y', $this->date) : strftime("%A %d. %B %Y", $this->date);
    }

    public function toTime($lang)
    {
        $suffix = ($lang == 'en') ? ' o\'clock' : ' Uhr';
        return date('H:i', $this->date) . $suffix;
    }

    public function addDate($date)
    {
        $this->date = $date;
        return $this;
    }

    public function addScope($scopeId)
    {
        $this->scope['id'] = $scopeId;
        return $this;
    }

    public function addSlotCount()
    {
        $this->slotCount += 1;
        return $this;
    }
}
