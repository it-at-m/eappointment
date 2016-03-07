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

    /**
     * Set Default values
     */
    public function getDefaults()
    {
        return [
            'weekday' => array_fill_keys(self::$weekdayNameList, 0),
        ];
    }

    public function hasDate(\DateTimeInterface $dateTime)
    {
        $weekDayName = self::$weekdayNameList[$dateTime->format('w')];
        $start = $this->getStartDateTime();
        $end = $this->getEndDateTime();
        if (!$this['weekday'][$weekDayName]) {
            // Wrong weekday
            return false;
        }
        if ($dateTime->getTimestamp() < $start->getTimestamp() || $dateTime->getTimestamp() > $end->getTimestamp()) {
            // Out of date range
            return false;
        }
        // TODO: implement afterWeeks and weekOfMonth

        return true;
    }

    public function getStartDateTime()
    {
        $time = new \DateTime();
        $time->setTimestamp($this['startDate']);
        $time->modify('today ' .  $this['startTime']);
        return $time;
    }

    public function getEndDateTime()
    {
        $time = new \DateTime();
        $time->setTimestamp($this['endDate']);
        $time->modify('today ' .  $this['endTime']);
        return $time;
    }
}
