<?php

namespace BO\Zmsentities;

/**
 * @SuppressWarnings(Complexity)
 * @SuppressWarnings(Coupling)
 * @SuppressWarnings(PublicMethod)
 *
 */
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
     * @var \DateTimeImmutable $startTimeCache
     */
    protected $startTimeCache;

    /**
     * Performance costs for modifying time are high, cache the calculated value
     * @var \DateTimeImmutable $endTimeCache
     */
    protected $endTimeCache;

    /**
     * Set Default values
     */
    public function getDefaults()
    {
        return [
            'id' => 0,
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
            'lastChange' => 0,
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
     * Check, if the dateTime contains a day given by the settings
     * ATTENTION: Time critical function, keep highly optimized
     * Compared to isOpened() the Booking time is checked too
     *
     * @param \DateTimeInterface $dateTime
     *
     * @return Bool
     */
    public function hasDate(\DateTimeInterface $dateTime, \DateTimeInterface $now)
    {
        $dateTime = Helper\DateTime::create($dateTime);
        if (!$this->isOpenedOnDate($dateTime)
            || !$this->isBookable($dateTime, $now)
        ) {
            // Out of date range
            return false;
        }
        return true;
    }

    public function hasBookableDates(\DateTimeInterface $now)
    {
        if ($this->workstationCount['intern'] <= 0) {
            return false;
        }
        if ($this->getEndDateTime()->getTimestamp() < $now->getTimestamp()) {
            return false;
        }
        $stopDate = $this->getBookableEnd($now);
        if ($this->getStartDateTime()->getTimestamp() > $stopDate->getTimestamp()) {
            return false;
        }
        return $this->hasDateBetween($this->getBookableStart($now), $this->getBookableEnd($now), $now);
    }

    /**
     * Check, if the dateTime contains a day
     * ATTENTION: Time critical function, keep highly optimized
     *
     * @param \DateTimeInterface $dateTime
     * @param String $type of "openinghours", "appointment" or false to ignore type
     *
     * @return Bool
     */
    public function isOpenedOnDate(\DateTimeInterface $dateTime, $type = false)
    {
        $dateTime = Helper\DateTime::create($dateTime);
        if (!$this->hasWeekDay($dateTime)
            || ($type !== false && $this->type != $type)
            || !$this->hasDay($dateTime)
            || !$this->hasWeek($dateTime)
            || ($this->getDuration() > 2 && $this->hasDayOff($dateTime))
        ) {
            // Out of date range
            return false;
        }
        return true;
    }

    /**
     * Check if date and time is in availability
     * Compared to hasDate() the time of the day is checked, but not booking time
     *
     * @param \DateTimeInterface $dateTime
     * @param String $type of "openinghours", "appointment" or false to ignore type
     *
     */
    public function isOpened(\DateTimeInterface $dateTime, $type = false)
    {
        return (!$this->isOpenedOnDate($dateTime, $type) || !$this->hasTime($dateTime)) ? false : true;
    }

    public function hasWeekDay(\DateTimeInterface $dateTime)
    {
        $weekDayName = self::$weekdayNameList[$dateTime->format('w')];
        if (!$this['weekday'][$weekDayName]) {
            // Wrong weekday
            return false;
        }
        return true;
    }

    public function hasAppointment(Appointment $appointment)
    {
        $dateTime = $appointment->toDateTime();
        $isOpenedStart = $this->isOpened($dateTime, false);
        $duration = $this->slotTimeInMinutes * $appointment->slotCount;
        $endTime = $dateTime->modify("+" . $duration . "minutes")
            ->modify("-1 second"); // To allow the last slot for an appointment
        $isOpenedEnd = $this->isOpened($endTime, false);
        return ($isOpenedStart && $isOpenedEnd);
    }

    /**
     * Check, if the dateTime is a time covered by availability
     *
     * @param \DateTimeInterface $dateTime
     *
     * @return Bool
     */
    public function hasTime(\DateTimeInterface $dateTime)
    {
        $start = $this->getStartDateTime()->getSecondsOfDay();
        $end = $this->getEndDateTime()->getSecondsOfDay();
        $compare = Helper\DateTime::create($dateTime)->getSecondsOfDay();
        if ($start > $compare || $end <= $compare) {
            // Out of time range
            return false;
        }
        return true;
    }

    public function getAvailableSecondsPerDay($type = "intern")
    {
        $start = $this->getStartDateTime()->getSecondsOfDay();
        $end = $this->getEndDateTime()->getSecondsOfDay();
        return ($end - $start) * $this->workstationCount[$type];
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
        $start = $this->getStartDateTime()->modify('0:00:00');
        $end = $this->getEndDateTime()->modify('23:59:59');
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
            $timeStamp = $dateTime->format('Y-m-d');
            foreach ($this['scope']['dayoff'] as $dayOff) {
                if (date('Y-m-d', $dayOff['date']) == $timeStamp) {
                    return true;
                }
            }
        } else {
            throw new Exception\DayoffMissing();
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
        $monday = "monday this week";
        if ($this['repeat']['afterWeeks']
            && ($this['repeat']['afterWeeks'] == 1
                || 0 ===
                    $dateTime->modify($monday)->diff($start->modify($monday))->days
                 % ($this['repeat']['afterWeeks'] * 7)
            )
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
        if (!$this['repeat']['weekOfMonth'] && !$this['repeat']['afterWeeks']) {
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
        if (!$this->startTimeCache) {
            $this->startTimeCache = Helper\DateTime::create()
                ->setTimestamp($this['startDate'])
                ->modify('today ' .  $this['startTime']);
        }
        return $this->startTimeCache;
    }

    /**
     * Get DateTimeInterface for end time of availability
     *
     * @return \DateTimeInterface
     */
    public function getEndDateTime()
    {
        if (!$this->endTimeCache) {
            $this->endTimeCache = Helper\DateTime::create()
                ->setTimestamp($this['endDate'])
                ->modify('today ' .  $this['endTime']);
        }
        return $this->endTimeCache;
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
        $time = $this->getStartDateTime()->format('H:i:s');
        if (null !== $availabilityStart) {
            return $now->modify('+' . $availabilityStart . 'days')->modify($time);
        }
        $scopeStart = Helper\Property::create($this)->scope->preferences->appointment->startInDaysDefault->get();
        if (null !== $scopeStart) {
            return $now->modify('+' . $scopeStart . 'days')->modify($time);
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
        $availabilityEnd = Helper\Property::create($this)->bookable->endInDays->get();
        $time = $this->getEndDateTime()->format('H:i:s');
        if (null !== $availabilityEnd) {
            return $now->modify('+' . $availabilityEnd . 'days')->modify($time);
        }
        $scopeEnd = Helper\Property::create($this)->scope->preferences->appointment->endInDaysDefault->get();
        if (null !== $scopeEnd) {
            return $now->modify('+' . $scopeEnd . 'days')->modify($time);
        }
        throw new \BO\Zmsentities\Exception\ProcessBookableFailed(
            "Undefined end time for booking, try to set the scope properly"
        );
    }

    /**
     * Check, if the dateTime contains is within the bookable range (usually for public access)
     * The current time is used to compare the start Time of the availability
     *
     * @param \DateTimeInterface $dateTime
     * @param \DateTimeInterface $now relative time to compare booking settings
     *
     * @return Bool
     */
    public function isBookable(\DateTimeInterface $bookableDate, \DateTimeInterface $now)
    {
        if (!$this->hasDay($bookableDate)) {
            return false;
        }
        $bookableCurrentTime = $bookableDate->modify($now->format('H:i:s'));
        Helper\DateTime::create($bookableDate)->getTimestamp() + Helper\DateTime::create($now)->getSecondsOfDay();
        $startDate = $this->getBookableStart($now)->modify('00:00:00');

        if ($bookableCurrentTime->getTimestamp() < $startDate->getTimestamp()) {
            //error_log("START " . $bookableCurrentTime->format('c').'<'.$startDate->format('c'). " " . $this);
            return false;
        }
        $endDate = $this->getBookableEnd($now)->modify('23:59:59');
        if ($bookableCurrentTime->getTimestamp() > $endDate->getTimestamp()) {
            //error_log("END " . $bookableCurrentTime->format('c').'>'.$endDate->format('c'). " " . $this);
            return false;
        }
        if ($bookableDate->format('Y-m-d') == $endDate->format('Y-m-d')
            && $now->format('Y-m-d') != $this->getEndDateTime()->format('Y-m-d')
        ) {
            // Avoid releasing all appointments on midnight, allow smaller contingents distributed over the day
            $delayedStart = $this->getBookableEnd($now)->modify($this->getStartDateTime()->format('H:i:s'));
            if ($bookableCurrentTime->getTimestamp() < $delayedStart->getTimestamp()) {
                //error_log(
                //    sprintf("DELAY %s<%s", $bookableCurrentTime->format('c'), $delayedStart->format('c'))
                //    ." $this"
                //);
                return false;
            }
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

    public function getSlotTimeInMinutes() 
    {
        return $this['slotTimeInMinutes'];
    }


    /**
     * Check, if a day between two dates is included
     *
     * @return Array of arrays with the keys time, public, callcenter, intern
     */
    public function hasDateBetween(\DateTimeInterface $startTime, \DateTimeInterface $stopTime, \DateTimeInterface $now)
    {
        if ($startTime->getTimestamp() < $now->getTimestamp()) {
            $startTime = $now;
        }
        if ($stopTime->getTimestamp() < $now->getTimestamp()) {
            return false;
        }
        do {
            if ($this->hasDate($startTime, $now)) {
                return true;
            }
            $startTime = $startTime->modify('+1 day');
        } while ($startTime->getTimestamp() <= $stopTime->getTimestamp());
        return false;
    }


    public function validateStartTime(\DateTimeInterface $today, \DateTimeInterface $tomorrow, \DateTimeInterface $startDate, \DateTimeInterface $endDate, \DateTimeInterface $selectedDate, String $kind)
    {
        $errorList = [];
        
        $startTime = $startDate->setTime(0, 0);
        $startHour = ($startDate->format('H'));
        $endHour = (int)$endDate->format('H');
        $startMinute = (int)$startDate->format('i');
        $endMinute = (int)$endDate->format('i');
        $isFuture = ($kind && $kind === 'future');

        if (
            !$isFuture &&
            $selectedDate->getTimestamp() > $today->getTimestamp() &&
            $startTime->getTimestamp() > $selectedDate->setTime(0, 0)->getTimestamp()
        ) {
            $errorList[] = [
                'type' => 'startTimeFuture',
                'message' => "Das Startdatum der Öffnungszeit muss vor dem " . $tomorrow->format('d.m.Y') . " liegen."
            ];
        }
        
        if (($startHour == 0 && $startMinute == 0) || ($endHour == 0 && $endMinute == 0)) {
            $errorList[] = [
                'type' => 'startOfDay',
                'message' => 'Die Uhrzeit darf nicht "00:00" sein.'
            ];
        }
        
        return $errorList;
    }
    
    
    public function validateEndTime(\DateTimeInterface $today, \DateTimeInterface $yesterday, \DateTimeInterface $startDate, \DateTimeInterface $endDate, \DateTimeInterface $selectedDate)
    {
        $errorList = [];
        
        $startHour = (int)$startDate->format('H');
        $endHour = (int)$endDate->format('H');
        $startMinute = (int)$startDate->format('i');
        $endMinute = (int)$endDate->format('i');
        $dayMinutesStart = ($startHour * 60) + $startMinute;
        $dayMinutesEnd = ($endHour * 60) + $endMinute;
        $startTimestamp = $startDate->getTimestamp();
        $endTimestamp = $endDate->getTimestamp();
    
        // Check if end time is before start time
        if ($dayMinutesEnd <= $dayMinutesStart) {
            $errorList[] = [
                'type' => 'endTime',
                'message' => 'Die Uhrzeit "von" muss kleiner der Uhrzeit "bis" sein.'
            ];
        } elseif ($startTimestamp >= $endTimestamp) {
            $errorList[] = [
                'type' => 'endTime',
                'message' => 'Das Startdatum muss vor dem Enddatum sein.'
            ];
        }
        
        return $errorList;
    }
    
    
    public function validateOriginEndTime(\DateTimeInterface $today, \DateTimeInterface $yesterday, \DateTimeInterface $startDate, \DateTimeInterface $endDate, \DateTimeInterface $selectedDate, String $kind)
    {
        $errorList = [];
        $endHour = (int) $endDate->format('H');
        $endMinute = (int) $endDate->format('i');
        $endDateTime = (clone $endDate)->setTime($endHour, $endMinute);
        $endTimestamp = $endDateTime->getTimestamp();
        $isOrigin = ($kind && $kind === 'origin');
    
        // Validate that end date is after the selected date
        if (!$isOrigin && $selectedDate->getTimestamp() > $today->getTimestamp() && $endDate < $selectedDate->setTime(0, 0)) {
            $errorList[] = [
                'type' => 'endTimeFuture',
                'message' => "Das Enddatum der Öffnungszeit muss nach dem " . $yesterday->format('d.m.Y') . " liegen."
            ];
        }
    
        // Validate that end time is not in the past
        if (!$isOrigin && $endTimestamp < $today->getTimestamp()) {
            $errorList[] = [
                'type' => 'endTimePast',
                'message' => 'Öffnungszeiten in der Vergangenheit lassen sich nicht bearbeiten '
                    . '(Die aktuelle Zeit "' . $today->format('d.m.Y H:i') . ' Uhr" liegt nach dem Terminende am "'
                    . $endDateTime->format('d.m.Y H:i') . ' Uhr").'
            ];
        }
    
        return $errorList;
    }
    
    
    public function validateType(String $kind)
    {
        $errorList = [];
        if (empty($kind)) {
            $errorList[] = [
                'type' => 'type',
                'message' => 'Typ erforderlich'
            ];
        }
        return $errorList;
    }
    
    public function validateSlotTime(\DateTimeInterface $startDate, \DateTimeInterface $endDate)
    {
        $errorList = [];
        $slotTime = $this['slotTimeInMinutes'];
        $startTimestamp = $startDate->getTimestamp();
        $endTimestamp = $endDate->getTimestamp();
    
        $slotAmount = ($endTimestamp - $startTimestamp) / 60 % $slotTime;
        if ($slotAmount > 0) {
            $errorList[] = [
                'type' => 'slotCount',
                'message' => 'Zeitschlitze müssen sich gleichmäßig in der Öffnungszeit aufteilen lassen.'
            ];
        }
    
        return $errorList;
    }
    
    public function validateAll(\DateTimeInterface $today, \DateTimeInterface $yesterday, \DateTimeInterface $tomorrow, \DateTimeInterface $startDate, \DateTimeInterface $endDate, \DateTimeInterface $selectedDate, String $kind)
    {
        $errorList = array_merge(
            $this->validateStartTime($today, $tomorrow, $startDate, $endDate, $selectedDate, $kind),
            $this->validateEndTime($today, $yesterday, $startDate, $endDate, $selectedDate),
            $this->validateOriginEndTime($today, $yesterday, $startDate, $endDate, $selectedDate, $kind),
            $this->validateType($kind),
            $this->validateSlotTime($startDate, $endDate)
        );
    
        return $errorList;
    }
    
    
    /**
     * Get problems on configuration of this availability
     *
     * @return Collection\ProcessList with processes in status "conflict"
     */
    public function getConflict()
    {
        $start = $this->getStartDateTime()->getSecondsOfDay();
        $end = $this->getEndDateTime()->getSecondsOfDay();
        $minutesPerDay = floor(($end - $start) / 60);
        if ($minutesPerDay % $this->slotTimeInMinutes > 0) {
            $conflict = new Process();
            $conflict->status = 'conflict';
            $appointment = $conflict->getFirstAppointment();
            $appointment->availability = $this;
            $appointment->date = $this->getStartDateTime()->getTimestamp();
            $conflict->amendment =
                "Der eingestellte Zeitschlitz von {$this->slotTimeInMinutes} Minuten"
                . " sollte in die eingestellte Uhrzeit passen.";
            return $conflict;
        }
        return false;
    }

    /**
     * Check of a different availability has the same opening configuration
     *
     */
    public function isMatchOf(Availability $availability)
    {
        return ($this->type != $availability->type
            || $this->startTime != $availability->startTime
            || $this->endTime != $availability->endTime
            || $this->startDate != $availability->startDate
            || $this->endDate != $availability->endDate
            || $this->repeat['afterWeeks'] != $availability->repeat['afterWeeks']
            || $this->repeat['weekOfMonth'] != $availability->repeat['weekOfMonth']
            || (bool)$this->weekday['monday'] != (bool)$availability->weekday['monday']
            || (bool)$this->weekday['tuesday'] != (bool)$availability->weekday['tuesday']
            || (bool)$this->weekday['wednesday'] != (bool)$availability->weekday['wednesday']
            || (bool)$this->weekday['thursday'] != (bool)$availability->weekday['thursday']
            || (bool)$this->weekday['friday'] != (bool)$availability->weekday['friday']
            || (bool)$this->weekday['saturday'] != (bool)$availability->weekday['saturday']
            || (bool)$this->weekday['sunday'] != (bool)$availability->weekday['sunday']
        ) ? false : true;
    }

    public function hasSharedWeekdayWith(Availability $availability)
    {
        return ($this->type == $availability->type
            && (bool)$this->weekday['monday'] != (bool)$availability->weekday['monday']
            && (bool)$this->weekday['tuesday'] != (bool)$availability->weekday['tuesday']
            && (bool)$this->weekday['wednesday'] != (bool)$availability->weekday['wednesday']
            && (bool)$this->weekday['thursday'] != (bool)$availability->weekday['thursday']
            && (bool)$this->weekday['friday'] != (bool)$availability->weekday['friday']
            && (bool)$this->weekday['saturday'] != (bool)$availability->weekday['saturday']
            && (bool)$this->weekday['sunday'] != (bool)$availability->weekday['sunday']
        ) ? false : true;
    }

    /**
     * Get overlaps on daytime
     * This functions does not check, if two availabilities are openend on the same day!
     *
     * @param Availability $availability for comparision
     *
     * @return Collection\ProcessList with processes in status "conflict"
     *
     *
     */

    /*
    1
    Case 01:  |-----|
              |-----|
                 2

                 1
    Case 02:  |-----|
                 |-----|
                    2

                    1
    Case 03:     |-----|
              |-----|
                 2

                   1
    Case 04:  |---------|
                |-----|
                   2

                   1
    Case 05:    |-----|
              |---------|
                   2

                 1
    Case 06:  |-----|
                      |-----|
                         2

                         1
    Case 07:          |-----|
              |-----|
                 2

                 1
    Case 08:  |-----|
                    |-----|
                       2

                       1
    Case 09:        |-----|
              |-----|
                 2

                 1
    Case 10:     |
              |-----|
                 2

                 1
    Case 11:  |-----|
                 |
                 2

              1
    Case 12:  |
              |-----|
                 2

                    1
    Case 13:        |
              |-----|
                 2

                 1
    Case 14:  |-----|
              |
              2

                 1
    Case 15:  |-----|
                    |
                    2

              1
    Case 16:  |
              |
              2

            |                         |    Operlap    |     Overlap
      Case  |         Example         | Open Interval | Closed Interval
    --------|-------------------------|---------------|-----------------
    Case 01 | 09:00-11:00 09:00-11:00 |      Yes      |        Yes
    Case 02 | 09:00-11:00 10:00-12:00 |      Yes      |        Yes
    Case 03 | 10:00-12:00 09:00-11:00 |      Yes      |        Yes
    Case 04 | 09:00-12:00 10:00-11:00 |      Yes      |        Yes
    Case 05 | 10:00-11:00 09:00-12:00 |      Yes      |        Yes
    Case 06 | 09:00-10:00 11:00-12:00 |      No       |        No
    Case 07 | 11:00-12:00 09:00-10:00 |      No       |        No
    Case 08 | 09:00-10:00 10:00-11:00 |      No       |        Yes
    Case 09 | 10:00-11:00 09:00-10:00 |      No       |        Yes
    Case 10 | 10:00-10:00 09:00-11:00 |      Yes      |        Yes
    Case 11 | 09:00-11:00 10:00-10:00 |      Yes      |        Yes
    Case 12 | 09:00-09:00 09:00-10:00 |      No       |        Yes
    Case 13 | 10:00-10:00 09:00-10:00 |      No       |        Yes
    Case 14 | 09:00-10:00 09:00-09:00 |      No       |        Yes
    Case 15 | 09:00-10:00 10:00-10:00 |      No       |        Yes
    Case 16 | 09:00-09:00 09:00-09:00 |      No       |        Yes
    */

    public function getTimeOverlaps(Availability $availability, \DateTimeInterface $currentDate)
    {
        $processList = new Collection\ProcessList();
        if ($availability->id != $this->id
            && $availability->type == $this->type
            && $this->hasSharedWeekdayWith($availability)
        ) {
            $processTemplate = new Process();
            $processTemplate->status = 'conflict';
            $appointment = $processTemplate->getFirstAppointment();
            $appointment->availability = $this;
            $appointment->date = $this->getStartDateTime()->getTimestamp();

            $existingDateRange = $this->getStartDateTime()->format('d.m.Y') . ' - ' . $this->getEndDateTime()->format('d.m.Y');
            $newDateRange = $availability->getStartDateTime()->format('d.m.Y') . ' - ' . $availability->getEndDateTime()->format('d.m.Y');
            
            $existingTimeRange = $this->getStartDateTime()->format('H:i') . ' - ' . $this->getEndDateTime()->format('H:i');
            $newTimeRange = $availability->getStartDateTime()->format('H:i') . ' - ' . $availability->getEndDateTime()->format('H:i');

            $isEqual = ($this->getStartDateTime()->getSecondsOfDay() == $availability->getStartDateTime()->getSecondsOfDay() &&
                        $this->getEndDateTime()->getSecondsOfDay() == $availability->getEndDateTime()->getSecondsOfDay());
            if ($isEqual) {
                $process = clone $processTemplate;
                $process->amendment = "Konflikt: Zwei Öffnungszeiten sind gleich.\n"
                                    . "Bestehende Öffnungszeit:&thinsp;&thinsp;[$newDateRange, $newTimeRange]\n"
                                    . "Neue Öffnungszeit:&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;[$existingDateRange, $existingTimeRange]";
                $process->getFirstAppointment()->date = $availability
                    ->getStartDateTime()
                    ->modify($currentDate->format("Y-m-d"))
                    ->getTimestamp();
                $processList->addEntity($process);
            }
            elseif ($availability->getStartDateTime()->getSecondsOfDay() < $this->getEndDateTime()->getSecondsOfDay() &&
                    $this->getStartDateTime()->getSecondsOfDay() < $availability->getEndDateTime()->getSecondsOfDay()) {
                $process = clone $processTemplate;
                $process->amendment = "Konflikt: Eine neue Öffnungszeit überschneidet sich mit einer bestehenden Öffnungszeit.\n"
                                    . "Bestehende Öffnungszeit:&thinsp;&thinsp;[$newDateRange, $newTimeRange]\n"
                                    . "Neue Öffnungszeit:&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;[$existingDateRange, $existingTimeRange]";
                $process->getFirstAppointment()->date = $availability
                    ->getStartDateTime()
                    ->modify($currentDate->format("Y-m-d"))
                    ->getTimestamp();
                $processList->addEntity($process);
            }
        }
    
        return $processList;
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

    public function withScope(\BO\Zmsentities\Scope $scope)
    {
        $availability = clone $this;
        $availability->scope = $scope;
        return $availability;
    }

    public function __toString()
    {
        $info = "Availability.".$this['type']." #" . $this['id'];
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

    /**
     * Delete cache on changes
     *
     */
    public function offsetSet($index, $value)
    {
        $this->startTimeCache = null;
        $this->endTimeCache = null;
        return parent::offsetSet($index, $value);
    }

    /**
     * Check if availability is newer than given time
     *
     * @return bool
     */
    public function isNewerThan(\DateTimeInterface $dateTime)
    {
        return ($dateTime->getTimestamp() < $this->lastChange);
    }

    /**
     * Reduce data of dereferenced entities to a required minimum
     *
     */
    public function withLessData(array $keepArray = [])
    {
        $entity = clone $this;
        if (! in_array('repeat', $keepArray)) {
            unset($entity['repeat']);
        }
        if (! in_array('id', $keepArray)) {
            unset($entity['id']);
        }
        if (! in_array('bookable', $keepArray)) {
            unset($entity['bookable']);
        }
        if (! in_array('workstationCount', $keepArray)) {
            unset($entity['workstationCount']);
        }
        if (! in_array('multipleSlotsAllowed', $keepArray)) {
            unset($entity['multipleSlotsAllowed']);
        }
        if (! in_array('lastChange', $keepArray)) {
            unset($entity['lastChange']);
        }
        if (! in_array('slotTimeInMinutes', $keepArray)) {
            unset($entity['slotTimeInMinutes']);
        }
        if (! in_array('description', $keepArray)) {
            unset($entity['description']);
        }

        return $entity;
    }
}
