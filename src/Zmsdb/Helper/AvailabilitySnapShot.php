<?php

namespace BO\Zmsdb\Helper;

class AvailabilitySnapShot
{
    public function __construct(
        \BO\Zmsentities\Availability $availability,
        \DateTimeInterface $dateTime
    ) {
        if (! $availability->scope instanceof \BO\Zmsentities\Scope) {
            throw new \Exception("Invalid Scope for AvailabilitySnapShot");
        }
        if (! $availability->scope->dayoff instanceof \BO\Zmsentities\Collection\DayoffList) {
            throw new \Exception("Invalid DayoffList for AvailabilitySnapShot");
        }
        $this->availability = clone $availability;
        $this->dateTime = $dateTime;
    }

    public function hasOutdatedAvailability()
    {
        // Lower compared date by one second to make a "<=" comparision
        return $this->availability->isNewerThan($this->dateTime->modify("-1 second"));
    }

    public function hasOutdatedScope()
    {
        return $this->availability->scope->isNewerThan($this->dateTime);
    }

    public function hasOutdatedDayoff()
    {
        // It is sufficient to check, if current availability with dateTime is affected
        // if a proposedChange could be affected, new slots have to be calculated, so checking already calculated slots
        // is sufficient
        return $this->availability->scope->dayoff->isNewerThan($this->dateTime, $this->availability, $this->dateTime);
    }

    public function hasBookableDateTime(\DateTimeInterface $proposedDateTime)
    {
        return $this->availability->hasDate($proposedDateTime, $this->dateTime);
    }

    public function getLastBookableDateTime()
    {
        return $this->availability->getBookableEnd($this->dateTime);
    }

    public function isOpenedOnLastBookableDay()
    {
        return $this->availability->isOpenedOnDate($this->availability->getBookableEnd($this->dateTime));
    }

    public function isTimeOpenedOnLastBookableDay()
    {
        return $this->availability->isOpened(
            $this->availability->getBookableEnd($this->dateTime)->modify($this->dateTime->format('H:i:s'))
        );
    }

    public function hasBookableDateTimeAfter(\DateTimeInterface $proposedDateTime)
    {
        return $this->availability->hasDateBetween(
            $proposedDateTime,
            $this->availability->getBookableEnd($this->dateTime),
            $this->dateTime
        );
    }
}
