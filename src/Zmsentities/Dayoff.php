<?php

namespace BO\Zmsentities;

class Dayoff extends Schema\Entity
{
    const PRIMARY = 'id';

    public static $schema = "dayoff.json";

    public function setTimestampFromDateformat($fromFormat = 'd.m.Y')
    {
        $dateTime = \DateTimeImmutable::createFromFormat($fromFormat, $this->date, new \DateTimeZone('UTC'));
        $this->date = $dateTime->modify('00:00:00')->getTimestamp();
        return $this;
    }

    public function getDateTime()
    {
        return (new \BO\Zmsentities\Helper\DateTime())->setTimestamp($this->date);
    }
}
