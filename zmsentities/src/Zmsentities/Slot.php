<?php

namespace BO\Zmsentities;

class Slot extends Schema\Entity
{
    public static $schema = "slot.json";

    /**
     *  the values represent possible free appointments without confirmed
     *  appointments
     *
     */
    const FREE = 'free';

    /**
     *  the values represent free appointments for a given day. Confirmed and
     *  reserved appointments on processes are substracted.
     */
    const TIMESLICE = 'timeslice';

    /**
     * like timeslice, but for more than one scope
     */
    const SUM = 'sum';

    /**
     * like timeslice, but numbers were reduced due to required slots on a
     * given request
     *
     */
    const REDUCED = 'reduced';

    /**
     * the values represent a unix timestamp to when there are free processes
     *
     */
    const TIMESTAMP = 'timestamp';

    public function getDefaults()
    {
        return [
            'public' => 0,
            'intern' => 0,
            'callcenter' => 0,
            'type' => self::FREE,
        ];
    }

    public function setTime(Helper\DateTime $slotTime)
    {
        $this->time = $slotTime->format('H:i');
    }

    public function hasTime()
    {
        return ($this->toProperty()->time->get()) ? true : false;
    }

    public function getTimeString()
    {
        if (null === $this->toProperty()->time->get()) {
            return '0:00';
        }
        if ($this->toProperty()->time->get() instanceof \DateTimeInterface) {
            return $this['time']->format('H:i');
        }
        return $this->toProperty()->time->get();
    }

    public function removeAppointment()
    {
        if ($this->intern <= 0) {
            throw new Exception\SlotFull("Could not remove another appointment from $this");
        }
        $this->intern = $this->intern - 1;
        if ($this->callcenter > 0) {
            $this->callcenter = $this->callcenter - 1;
        }
        if ($this->public > 0) {
            $this->public = $this->public - 1;
        }
        return $this;
    }

    public function withAddedSlot(Slot $slot)
    {
        $slot = clone $slot;
        $slot->type = 'sum';
        $slot->intern = (($slot->intern > 0) ? $slot->intern : 0) + (($this->intern > 0) ? $this->intern : 0);
        $slot->callcenter = $slot->callcenter + $this->callcenter;
        $slot->public = $slot->public + $this->public;
        return $slot;
    }

    public function __toString()
    {
        return "slot#{$this->type}@"
            . "{$this->getTimeString()}"
            . " p/c/i={$this->public}/{$this->callcenter}/{$this->intern}";
    }

    /**
     * Keep empty, no sub-instances
     * ATTENTION: Keep highly optimized, time critical function
     */
    public function __clone()
    {
    }
}
