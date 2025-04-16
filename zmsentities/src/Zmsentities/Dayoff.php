<?php

namespace BO\Zmsentities;

class Dayoff extends Schema\Entity
{
    public const PRIMARY = 'id';

    public static $schema = "dayoff.json";

    public function setTimestampFromDateformat($fromFormat = 'd.m.Y')
    {
        $dateTime = \DateTimeImmutable::createFromFormat($fromFormat, $this->date, new \DateTimeZone('UTC'));
        if ($dateTime) {
            $this->date = $dateTime->modify('00:00:00')->getTimestamp();
        }
        return $this;
    }

    public function getDateTime()
    {
        return (new \BO\Zmsentities\Helper\DateTime())->setTimestamp($this->date);
    }

    /**
     * Check if dayoff is newer than given time
     *
     * @return bool
     */
    public function isNewerThan(\DateTimeInterface $dateTime, $filterByAvailability = null, $now = null)
    {
        if ($filterByAvailability && !$this->isAffectingAvailability($filterByAvailability, $now)) {
            return false;
        }
        return ($dateTime->getTimestamp() < $this->lastChange);
    }

    public function isAffectingAvailability(Availability $availabiliy, \DateTimeInterface $now)
    {
        return $availabiliy->isBookable($this->getDateTime(), $now);
    }
}
