<?php

namespace BO\Zmsentities;

class Availability extends Schema\Entity
{
    const PRIMARY = 'id';

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
     * Performance costs for modifying time are high, cache the calculated value
     * @var \DateTimeImmutable $startTime
     */
    protected $startTime;

    /**
     * Performance costs for modifying time are high, cache the calculated value
     * @var \DateTimeImmutable $endTime
     */
    protected $endTime;

    /**
     * Set Default values
     */
    public function getDefaults()
    {
        return [
            'id' => null,
            'weekday' => array_fill_keys(self::$weekdayNameList, 0),
            'repeat' => [
                'afterWeeks' => 1,
                'weekOfMonth' => 0,
            ],
            'bookable' => [
                'startInDays' => 1,
                'endInDays' => 60,
            ],
            'workstationCount' => [
                'public' => 0,
                'callcenter' => 0,
                'intern' => 0,
            ],
            'multipleSlotsAllowed' => true,
            'slotTimeInMinutes' => 10,
            'startDate' => 0,
            'endDate' => 0,
            'startTime' => "0:00",
            'endTime' => "23:59",
            'type' => 'appointment'
        ];
    }

    /**
     * Check, if the dateTime contains a day and time given by the settings
     * ATTENTION: Time critical function, keep highly optimized
     *
     * @param \DateTimeInterface $dateTime
     *
     * @return Bool
     */
    public function hasDate(\DateTimeInterface $dateTime, \DateTimeInterface $now)
    {
        //$debugAvailabilityId = 0;
        $dateTime = Helper\DateTime::create($dateTime);

        //if ($this->id == $debugAvailabilityId) {
        //    error_log("true == hasDate(".$dateTime->format('c').") ".$this);
        //}

        if (!$this->isOpened($dateTime, 'appointment')) {
            // Out of date range
            return false;
        }
        if (!$this->isBookable($dateTime, $now)) {
            // out of bookable start and end
            return false;
        }
        return true;
    }

    public function isOpened(\DateTimeInterface $dateTime, $type = 'openinghours')
    {
        // First check weekday, greatest difference on an easy check
        $weekDayName = self::$weekdayNameList[$dateTime->format('w')];
        if (!$this['weekday'][$weekDayName]) {
            // Wrong weekday
            return false;
        }
        if ($this->type != $type) {
            return false;
        }
        if (!$this->hasDay($dateTime)) {
            // Out of date range
            return false;
        }
        if (!$this->hasWeek($dateTime)) {
            // series settings for the week do not match
            return false;
        }
        if ($this->getDuration() > 2 && $this->hasDayOff($dateTime)) {
            return false;
        }
        return true;
    }

    /**
     * Check, if the dateTime is a day covered by availability
     *
     * @param \DateTimeInterface $dateTime
     *
     * @return Bool
     */
    public function hasDay(\DateTimeInterface $dateTime)
    {
        $start = $this->getStartDateTime()->modify('0:00');
        $end = $this->getEndDateTime();
        if ($dateTime->getTimestamp() < $start->getTimestamp() || $dateTime->getTimestamp() > $end->getTimestamp()) {
            // Out of date range
            return false;
        }
        return true;
    }

    /**
     * Check, if the dateTime is a dayoff date
     *
     * @param \DateTimeInterface $dateTime
     *
     * @return Bool
     */
    public function hasDayOff(\DateTimeInterface $dateTime)
    {
        if (isset($this['scope']['dayoff'])) {
            $timeStamp = $dateTime->getTimestamp();
            foreach ($this['scope']['dayoff'] as $dayOff) {
                if ($dayOff['date'] == $timeStamp) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Check, if the dateTime contains a week given by the week repetition settings
     *
     * @param \DateTimeInterface $dateTime
     *
     * @return Bool
     */
    public function hasWeek(\DateTimeInterface $dateTime)
    {
        $dateTime = Helper\DateTime::create($dateTime);
        $start = $this->getStartDateTime();
        if ($this['repeat']['afterWeeks']
            && 0 === floor($dateTime->getWeeks() - $start->getWeeks()) % $this['repeat']['afterWeeks']
        ) {
            return true;
        }
        if ($this['repeat']['weekOfMonth']
            && (
                $dateTime->isWeekOfMonth($this['repeat']['weekOfMonth'])
                // On a value of 5, always take the last week
                || ($this['repeat']['weekOfMonth'] >= 5 && $dateTime->isLastWeekOfMonth())
            )
        ) {
            return true;
        }
        return false;
    }

    /**
     * Get DateTimeInterface for start time of availability
     *
     * @return \DateTimeInterface
     */
    public function getStartDateTime()
    {
        if (!$this->startTime) {
            $this->startTime = Helper\DateTime::create()
                ->setTimestamp($this['startDate'])
                ->modify('today ' .  $this['startTime']);
        }
        return $this->startTime;
    }

    /**
     * Get DateTimeInterface for end time of availability
     *
     * @return \DateTimeInterface
     */
    public function getEndDateTime()
    {
        if (!$this->endTime) {
            $this->endTime = Helper\DateTime::create()
                ->setTimestamp($this['endDate'])
                ->modify('today ' .  $this['endTime']);
        }
        return $this->endTime;
    }

    /**
     * Get duration of availability
     *
     * @return integer
     */
    public function getDuration()
    {
        $startTime = $this->getStartDateTime();
        $endTime = $this->getEndDateTime();
        return (int)$endTime->diff($startTime)->format("%a");
    }

    /**
     * Get DateTimeInterface for start booking time of availability
     *
     * @param \DateTimeInterface $now relative time to compare booking settings
     *
     * @return \DateTimeInterface
     */
    public function getBookableStart(\DateTimeInterface $now)
    {
        $now = Helper\DateTime::create($now);
        $availabilityStart = Helper\Property::create($this)->bookable->startInDays->get();
        if (null !== $availabilityStart) {
            return $now->modify('+' . $availabilityStart . 'days');
        }
        $scopeStart = Helper\Property::create($this)->scope->preferences->appointment->startInDaysDefault->get();
        if (null !== $scopeStart) {
            return $now->modify('+' . $scopeStart . 'days');
        }
        throw new \BO\Zmsentities\Exception\ProcessBookableFailed(
            "Undefined start time for booking, try to set the scope properly"
        );
    }

    /**
     * Get DateTimeInterface for end booking time of availability
     *
     * @param \DateTimeInterface $now relative time to compare booking settings
     *
     * @return \DateTimeInterface
     */
    public function getBookableEnd(\DateTimeInterface $now)
    {
        $now = Helper\DateTime::create($now);
        $availabilityStart = Helper\Property::create($this)->bookable->endInDays->get();
        if (null !== $availabilityStart) {
            return $now->modify('+' . $availabilityStart . 'days');
        }
        $scopeStart = Helper\Property::create($this)->scope->preferences->appointment->endInDaysDefault->get();
        if (null !== $scopeStart) {
            return $now->modify('+' . $scopeStart . 'days');
        }
        throw new \BO\Zmsentities\Exception\ProcessBookableFailed(
            "Undefined end time for booking, try to set the scope properly"
        );
    }

    /**
     * Check, if the dateTime contains is within the bookable range (usually for public access)
     *
     * @param \DateTimeInterface $dateTime
     * @param \DateTimeInterface $now relative time to compare booking settings
     *
     * @return Bool
     */
    public function isBookable(\DateTimeInterface $dateTime, \DateTimeInterface $now)
    {
        $dateTime = Helper\DateTime::create($dateTime);
        $start = $this->getBookableStart($now);
        $end = $this->getBookableEnd($now);
        if ($dateTime->getTimestamp() < $start->getTimestamp()) {
            //error_log($dateTime->format('c').'<'.$start->format('c'). " " . $this);
            return false;
        }
        if ($dateTime->getTimestamp() > $end->getTimestamp()) {
            //error_log($dateTime->format('c').'>'.$end->format('c'). " " . $this);
            return false;
        }
        return true;
    }

    /**
     * Creates a list of slots available on a valid day
     *
     * @return Array of arrays with the keys time, public, callcenter, intern
     */
    public function getSlotList()
    {
        $startTime = Helper\DateTime::create($this['startTime']);
        $stopTime = Helper\DateTime::create($this['endTime']);
        $slotList = new Collection\SlotList();
        $slotInstance = new Slot($this['workstationCount']);
        if ($this['slotTimeInMinutes'] > 0) {
            do {
                $slot = clone $slotInstance;
                $slot->setTime($startTime);
                $slotList[] = $slot;
                $startTime = $startTime->modify('+' . $this['slotTimeInMinutes'] . 'minute');
                // Only add a slot, if at least a minute is left, otherwise do not ("<" instead "<=")
            } while ($startTime->getTimestamp() < $stopTime->getTimestamp());
        }
        return $slotList;
    }

    /**
     * Update workstationCount to number of calculated appointments
     *
     * @return self cloned
     */
    public function withCalculatedSlots()
    {
        $availability = clone $this;
        $startTime = Helper\DateTime::create($this['startTime']);
        $stopTime = Helper\DateTime::create($this['endTime']);
        $openingSeconds = $stopTime->getTimestamp() - $startTime->getTimestamp();
        $openingMinutes = floor($openingSeconds / 60);
        $slices = 0;
        if ($this['slotTimeInMinutes'] > 0) {
            $slices = floor($openingMinutes / $this['slotTimeInMinutes']);
        }
        $slot = new Slot([
            'type' => Slot::FREE,
            'intern' => $this['workstationCount']['intern'] * $slices,
            'callcenter' => $this['workstationCount']['callcenter'] * $slices,
            'public' => $this['workstationCount']['public'] * $slices,
        ]);
        $availability['workstationCount'] = $slot;
        return $availability;
    }

    public function __toString()
    {
        $info = "Availability #" . $this['id'];
        $info .= " starting " . $this->startDate . $this->getStartDateTime()->format(' Y-m-d');
        $info .= "||now+" . $this['bookable']['startInDays'] . " ";
        $info .= " until " . $this->getEndDateTime()->format('Y-m-d');
        $info .= "||now+" . $this['bookable']['endInDays'] . " ";
        if ($this['repeat']['afterWeeks']) {
            $info .= " every " . $this['repeat']['afterWeeks'] . " week(s)";
        }
        if ($this['repeat']['weekOfMonth']) {
            $info .= " each " . $this['repeat']['weekOfMonth'] . ". weekOfMonth";
        }
        $info .= " on ";
        $weekdays = array_filter($this['weekday'], function ($value) {
            return $value > 0;
        });
        $info .= implode(',', array_keys($weekdays));
        $info .= " from " . $this->getStartDateTime()->format('H:i');
        $info .= " to " . $this->getEndDateTime()->format('H:i');
        $info .= " using " . $this['slotTimeInMinutes'] . "min slots";
        $info .= " with p{$this['workstationCount']['public']}/";
        $info .= "c{$this['workstationCount']['callcenter']}/";
        $info .= "i{$this['workstationCount']['intern']}";
        $day = $this->getSlotList()->getSummerizedSlot();
        $info .= " day $day";
        return $info;
    }

    public function offsetSet($index, $value)
    {
        $this->startTime = null;
        $this->endTime = null;
        return parent::offsetSet($index, $value);
    }
}
