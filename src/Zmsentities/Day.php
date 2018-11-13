<?php

namespace BO\Zmsentities;

class Day extends Schema\Entity
{
    const PRIMARY = 'day';

    const FULL = 'full';

    const BOOKABLE = 'bookable';

    const NOTBOOKABLE = 'notBookable';

    const RESTRICTED = 'restricted';

    public static $schema = "day.json";

    public function getDefaults()
    {
        return [
            'year' => '',
            'month' => '',
            'day' => '',
            'status' => self::NOTBOOKABLE,
            'freeAppointments' => new Slot(),
        ];
    }

    //@todo freeAppointments could be an array, should be slot entity
    public function __toString()
    {
        $this->freeAppointments = new Slot($this->freeAppointments);
        return "Day {$this->status}@{$this->year}-{$this->month}-{$this->day} with ". $this->freeAppointments;
    }

    public function setDateTime(\DateTimeInterface $dateTime)
    {
        $this['year'] = $dateTime->format('Y');
        $this['month'] = $dateTime->format('m');
        $this['day'] = $dateTime->format('d');
        return $this;
    }

    public function toDateTime()
    {
        $date = Helper\DateTime::createFromFormat('Y-m-d', $this['year'] . '-' . $this['month'] . '-' . $this['day']);
        return Helper\DateTime::create($date);
    }

    public function getAppointmentsByType($slotType)
    {
        return (0 < $this->toProperty()->freeAppointments->{$slotType}->get());
    }

    public function isBookable()
    {
        return ($this->status == self::BOOKABLE);
    }

    public function hasAppointments()
    {
        return ($this->status == self::BOOKABLE || $this->status == self::FULL);
    }

    /**
     * Check if day is bookable
     * The return self or status
     *
     * @return \ArrayObject or String
     */
    public function getWithStatus($slotType, \DateTimeInterface $now)
    {
        $hasAppointments = $this->getAppointmentsByType($slotType);
        if ($this->status != self::RESTRICTED) {
            $this->status = ($hasAppointments) ? self::BOOKABLE : self::FULL;
        }
        // if dayend < todays time + half an hour, it is restricted
        if ($this->toDateTime()->getTimestamp() + 86400 <= $now->getTimestamp() + 1800) {
            $this->status =  self::RESTRICTED;
        }
        return $this;
    }

    public function withAddedDay(Day $day)
    {
        $merged = clone $this;
        if (!$merged->freeAppointments instanceof Slot) {
            $merged->freeAppointments = new Slot($merged->freeAppointments);
        }
        $merged->freeAppointments = $merged->freeAppointments->withAddedSlot($day->freeAppointments);
        return $merged;
    }

    /**
     * Returns an unique string hash per day optimized for b-trees
     */
    public function getDayHash()
    {
        return $this::getCalculatedDayHash($this->day, $this->month, $this->year);
    }

    public static function getCalculatedDayHash($dayNumber, $month, $year)
    {
        $dateHash = str_pad($dayNumber, 2, '0', STR_PAD_LEFT)
            . "-"
            . str_pad($month, 2, '0', STR_PAD_LEFT)
            . "-$year";
        return $dateHash;
    }
}
