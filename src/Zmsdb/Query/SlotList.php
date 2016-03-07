<?php

namespace BO\Zmsdb\Query;

/**
 * Calculate Slots for available booking times
 */
class SlotList
{
    const QUERY = 'SELECT
            UNIX_TIMESTAMP(CONCAT(b.Datum, " ", b.Uhrzeit)) AS appointment__date,
            s.StandortID AS appointment__scope__id,
            s.mehrfachtermine AS appointment__scope__preferences__appointment__multipleSlotsEnabled,
            DAYOFMONTH(b.Datum) AS `day`,
            MONTH(b.Datum) AS `month`,
            YEAR(b.Datum) AS `year`,
            b.Uhrzeit AS slottime,
            b.Datum AS slotdate,
            GREATEST(0, o.Anzahlterminarbeitsplaetze - o.reduktionTermineImInternet - COUNT(b.Datum))
                AS `freeAppointments__public`,
            GREATEST(0, o.Anzahlterminarbeitsplaetze - o.reduktionTermineCallcenter - COUNT(b.Datum))
                AS `freeAppointments__callcenter`,
            o.Anzahlterminarbeitsplaetze - COUNT(b.Datum)
                AS `freeAppointments__intern`,
            FLOOR(((TIME_TO_SEC(b.Uhrzeit) - TIME_TO_SEC(o.Terminanfangszeit)) / TIME_TO_SEC(o.Timeslot))) AS `slotnr`,
            o.OeffnungszeitID AS availability__id,
            o.erlaubemehrfachslots AS availability__multipleSlotsAllowed,
            o.allexWochen AS availability__repeat__afterWeeks,
            o.jedexteWoche AS availability__repeat__weekOfMonth,
            FLOOR(TIME_TO_SEC(o.Timeslot) / 60) AS availability__slotTimeInMinutes,
            UNIX_TIMESTAMP(o.Startdatum) AS availability__startDate,
            UNIX_TIMESTAMP(o.Endedatum) AS availability__endDate,
            o.Terminanfangszeit	 AS availability__startTime,
            o.Terminendzeit	 AS availability__endTime,
            o.Wochentag & 2 AS availability__weekday__monday,
            o.Wochentag & 4 AS availability__weekday__tuesday,
            o.Wochentag & 8 AS availability__weekday__wednesday,
            o.Wochentag & 16 AS availability__weekday__thursday,
            o.Wochentag & 32 AS availability__weekday__friday,
            o.Wochentag & 64 AS availability__weekday__saturday,
            o.Wochentag & 1 AS availability__weekday__sunday,
            o.Anzahlterminarbeitsplaetze - o.reduktionTermineImInternet AS availability__workstationCount__public,
            o.Anzahlterminarbeitsplaetze - o.reduktionTermineCallcenter AS availability__workstationCount__callcenter,
            o.Anzahlterminarbeitsplaetze AS availability__workstationCount__intern,
            IF(o.Offen_ab, o.Offen_ab, s.Termine_ab) AS availability__bookable__startInDays,
            IF(o.Offen_bis, o.Offen_bis, s.Termine_bis) AS availability__bookable__endInDays
        FROM
            standort s
            LEFT JOIN oeffnungszeit o USING(StandortID)
            LEFT JOIN buerger b ON
                b.StandortID = o.StandortID
                AND o.Wochentag & POW(2, DAYOFWEEK(b.Datum) - 1)
                AND b.Uhrzeit >= o.Terminanfangszeit
                AND b.Uhrzeit <= o.Terminendzeit
                AND b.Datum >= o.Startdatum
                AND b.Datum <= o.Endedatum
        WHERE
            o.StandortID = :scope_id
            AND o.OeffnungszeitID IS NOT NULL
            AND (b.Datum IS  NULL OR b.Datum BETWEEN :start_process AND :end_process)
            AND o.Endedatum >= :start_availability
            AND o.Startdatum <= :end_availability
            AND o.Anzahlterminarbeitsplaetze != 0
        GROUP BY o.OeffnungszeitID, b.Datum, `slotnr`
        HAVING
            -- reduce results cause processing them costs time and here we have a query cache
            FROM_UNIXTIME(appointment__date) BETWEEN
                DATE_ADD(:nowStart, INTERVAL availability__bookable__startInDays DAY)
                AND DATE_ADD(:nowEnd, INTERVAL availability__bookable__endInDays DAY)
        ORDER BY o.OeffnungszeitID, b.Datum, `slotnr`
        ';

    /**
     * @var array $slotData Single result row from the query
     */
    protected $slotData = null;

    /**
     * @var \BO\Zmsentities\Availability $availability
     */
    protected $availability = null;

    /**
     * @var array $slots
     */
    protected $slots = [];

    public function __construct(
        array $slotData = ['availability__id' => null],
        \DateTimeImmutable $start = null,
        \DateTimeImmutable $stop = null
    ) {
        $this->setSlotData($slotData);
        if (isset($this->availability['id'])) {
            $this->createSlots($start, $stop);
            $this->addSlotData($slotData);
        }
    }

    public static function getQuery()
    {
        return self::QUERY;
    }

    public static function getParameters($scopeId, \DateTime $monthDateTime)
    {
        $now = new \DateTimeImmutable();
        $parameters = [
            'scope_id' => $scopeId,
            'start_process' => $monthDateTime->format('Y-m-1'),
            'end_process' => $monthDateTime->format('Y-m-t'),
            'start_availability' => $monthDateTime->format('Y-m-1'),
            'end_availability' => $monthDateTime->format('Y-m-t'),
            'nowStart' => $now->format('Y-m-d'),
            'nowEnd' => $now->format('Y-m-d'),
        ];
        return $parameters;
    }

    public function setSlotData(array $slotData)
    {
        $this->slotData = $slotData;
        $availability = [];
        foreach ($slotData as $key => $value) {
            if (0 === strpos($key, 'availability__')) {
                $newkey = str_replace('availability__', '', $key);
                $availability[$newkey] = $value;
            }
        }
        $this->availability = new \BO\Zmsentities\Availability($availability);
        return $this;
    }

    public function isSameAvailability(array $slotData)
    {
        return $this->slotData['availability__id'] == $slotData['availability__id']
            //&& $this->slotData['day'] == $slotData['day']
            //&& $this->slotData['month'] == $slotData['month']
            //&& $this->slotData['year'] == $slotData['year']
        ;
    }

    public function addSlotData(array $slotData)
    {
        if (isset($slotData['slotnr'])) {
            $slotnumber = $slotData['slotnr'];
            $slotdate = $slotData['slotdate'];
            $slot =& $this->slots[$slotdate][$slotnumber];
            if (isset($slot['slottime'])) {
                throw new \Exception(
                    "Found two database entries for the same slot $slotdate #$slotnumber @" . $slotData['slottime']
                );
            }
            $slot['slottime'] = $slotData['slottime'];
            $slot['public'] -= $slotData['availability__workstationCount__public'];
            $slot['public'] += $slotData['freeAppointments__public'];
            $slot['callcenter'] -= $slotData['availability__workstationCount__callcenter'];
            $slot['callcenter'] += $slotData['freeAppointments__callcenter'];
            $slot['intern'] -= $slotData['availability__workstationCount__intern'];
            $slot['intern'] += $slotData['freeAppointments__intern'];
        }
        return $this;
    }

    public function addToCalendar(\BO\Zmsentities\Calendar $calendar)
    {
        foreach ($this->slots as $date => $slotList) {
            $datetime = new \DateTimeImmutable($date);
            $day = $calendar->getDayByDateTime($datetime);
            foreach ($slotList as $slotInfo) {
                $day['freeAppointments']['public'] += $slotInfo['public'];
                $day['freeAppointments']['intern'] += $slotInfo['intern'];
                $day['freeAppointments']['callcenter'] += $slotInfo['callcenter'];
            }
        }
        //var_dump($this->slots);
        return $calendar;
    }

    /**
     * TODO Unterscheidung nach intern/callcenter/public sollte erst nach der API erfolgen!
     */
    public function addFreeProcesses(\BO\Zmsentities\Calendar $calendar, $slotType = 'public')
    {
        $scopeReader = new \BO\Zmsdb\Scope();
        $datestr = $calendar['firstDay']['year'].'-'.$calendar['firstDay']['month'].'-'.$calendar['firstDay']['day'];
        $selectedDate = \DateTime::createFromFormat('Y-m-d', $datestr)->format('Y-m-d');

        $calendar['freeProcesses'] = array();
        foreach ($this->slots as $date => $slotList) {
            if ($date == $selectedDate) {
                $scope = $scopeReader->readEntity($this->slotData['appointment__scope__id'], 1);
                foreach ($slotList as $slotInfo) {
                    if ($slotInfo[$slotType] > 0) {
                        $appointment = new \BO\Zmsentities\Appointment();
                        $appointmentDateTime = \DateTime::createFromFormat(
                            'Y-m-d H:i',
                            $selectedDate .' '. $slotInfo['time']
                        );
                        $appointment['scope'] = $scope;
                        $appointment['date'] = $appointmentDateTime->format('U');
                        $appointment['slotCount'] = $slotInfo[$slotType];

                        $process = new \BO\Zmsentities\Process();
                        $process['scope'] = $scope;
                        $process['requests'] = $calendar['requests'];
                        $process->addAppointment($appointment);

                        $calendar['freeProcesses'][] = $process;
                    }
                }
            }

        }
        return $calendar;
    }

    /**
     * Reduce available slots
     * On given amount of required slots reduce the amount of available slots by comparing continous slots available
     *
     * @param Int $slotsRequired
     * @return self
     */
    public function toReducedBySlots($slotsRequired)
    {
        $slotsRequired = $slotsRequired;
        if (count($this->slots) && $slotsRequired > 1) {
            foreach ($this->slots as $date => $slotList) {
                $slotLength = count($slotList);
                $slotKeys = array_keys($slotList);
                sort($slotKeys);
                for ($slotIndex = 0; $slotIndex < $slotLength; $slotIndex++) {
                    if ($slotIndex + $slotsRequired < $slotLength) {
                        for ($slotRelative = 1; $slotRelative < $slotsRequired; $slotRelative++) {
                            if ($slotIndex + $slotRelative < $slotLength) {
                                $this->slots[$date][$slotKeys[$slotIndex]] = self::takeLowerSlotValue(
                                    $this->slots[$date][$slotKeys[$slotIndex]],
                                    $this->slots[$date][$slotKeys[$slotIndex + $slotRelative]]
                                );
                            }
                        }
                    } else {
                        $this->slots[$date][$slotIndex]['public'] = 0;
                        $this->slots[$date][$slotIndex]['intern'] = 0;
                        $this->slots[$date][$slotIndex]['callcenter'] = 0;
                    }
                }
            }
        }
        return $this;
    }

    /**
     * Compare two slots and return the lower values
     * @param array $slotA
     * @param array $slotB
     * @return array $slotA modified
     */
    protected static function takeLowerSlotValue($slotA, $slotB)
    {
        if (!isset($slotB['public']) || !isset($slotA['public'])) {
            var_dump("takeLowerSlotValue");
            //var_dump($slotA);
            //var_dump($slotB);
        }
        foreach (['public', 'intern', 'callcenter'] as $type) {
            $slotA[$type] = $slotA[$type] < $slotB[$type] ? $slotA[$type] : $slotB[$type];
        }
        return $slotA;
    }

    /**
     * Create slots based on availability
     */
    public function createSlots(
        \DateTimeImmutable $startDate,
        \DateTimeImmutable $stopDate
    ) {
        $startDate = new \DateTime($startDate->format('c'));
        do {
            $startTime = new \DateTime($this->availability['startTime']);
            $stopTime = new \DateTime($this->availability['endTime']);
            $date = $startDate->format('Y-m-d');
            if ($this->availability->hasDate($startDate)) {
                $slotnr = 0;
                do {
                    $this->slots[$date][$slotnr] = [
                        'time' => $startTime->format('H:i'),
                        'public' => $this->availability['workstationCount']['public'],
                        'callcenter' => $this->availability['workstationCount']['callcenter'],
                        'intern' => $this->availability['workstationCount']['intern'],
                    ];
                    $startTime->modify('+' . $this->availability['slotTimeInMinutes'] . 'minute');
                    $slotnr++;
                } while ($startTime->getTimestamp() <= $stopTime->getTimestamp());
            }
            $startDate->modify('+1day');
            $date = $startDate->format('Y-m-d');
        } while ($startDate->getTimestamp() <= $stopDate->getTimestamp());
    }
}
