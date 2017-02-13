<?php

namespace BO\Zmsentities;

class Dayoff extends Schema\Entity
{
    const PRIMARY = 'id';

    public static $schema = "dayoff.json";

    public function setTimestampFromDateformat($fromFormat = 'd.m.Y')
    {
        $dateTime = \DateTimeImmutable::createFromFormat($fromFormat, $this->date);
        $this->date = $dateTime->modify('00:00:00')->getTimestamp();
        return $this;
    }
}
