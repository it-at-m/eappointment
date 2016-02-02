<?php

namespace BO\Zmsentities;

class Availability extends Schema\Entity
{
    public static $schema = "availability.json";

    /**
     * @var array $weekday english localized weekdays to avoid problems with setlocale()
     */
    protected static $weekdayNameList = [
        'sunday',
        'monday',
        'tuesday',
        'wednesday',
        'thursday',
        'friday',
        'saturday'
    ];

    public function hasDate(\DateTimeInterface $dateTime)
    {
        $weekDayName = self::$weekdayNameList[$dateTime->format('w')];
        if (!$this['weekday'][$weekDayName]) {
            return false;
        }
        // TODO: implement afterWeeks and weekOfMonth
        return true;
    }
}
