<?php

namespace BO\Zmsentities;

class Day extends Schema\Entity
{
    const PRIMARY = 'day';

    const FULL = 'full';

    const BOOKABLE = 'bookable';

    const RESTRICTED = 'restricted';

    public static $schema = "day.json";

    public function getDefaults()
    {
        return [
            'year' => '',
            'month' => '',
            'day' => '',
            'freeAppointments' => new Slot(),
        ];
    }

    //@todo freeAppointments could be an array, should be slot entity
    public function __toString()
    {
        $this->freeAppointments = new Slot($this->freeAppointments);
        return "Day @{$this->year}-{$this->month}-{$this->day} with ". $this->freeAppointments;
    }

    public function toDateTime()
    {
        $date = Helper\DateTime::createFromFormat('Y-m-d', $this['year'] . '-' . $this['month'] . '-' . $this['day']);
        return Helper\DateTime::create($date);
    }

    public function getFreePublicAppointments()
    {
        return $this->toProperty()->freeAppointments->public->get();
    }

    public function isBookable()
    {
        return ($this->status == 'bookable');
    }

    /**
     * Check if day is bookable
     * The return self or status
     *
     * @return \ArrayObject or String
     */
    public function getWithStatus()
    {
        $this->status = self::RESTRICTED;
        $publicAppointments = $this->getFreePublicAppointments();
        if (0 < $publicAppointments) {
            $this->status = self::BOOKABLE;
        } elseif (0 == $publicAppointments) {
            $this->status =  self::FULL;
        }
        return $this;
    }
}
