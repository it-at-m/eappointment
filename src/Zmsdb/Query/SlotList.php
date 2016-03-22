<?php

namespace BO\Zmsdb\Query;

use BO\Zmsentities\Helper\DateTime;

/**
 * Calculate Slots for available booking times
 */
class SlotList
{
    const QUERY = 'SELECT

            -- collect some important settings, especially from the scope, use the appointment key
            UNIX_TIMESTAMP(CONCAT(b.Datum, " ", b.Uhrzeit)) AS appointment__date,
            s.StandortID AS appointment__scope__id,
            s.mehrfachtermine AS appointment__scope__preferences__appointment__multipleSlotsEnabled,

            -- results are used slots, collect some information to match calculated open slots
            DAYOFMONTH(b.Datum) AS `day`,
            MONTH(b.Datum) AS `month`,
            YEAR(b.Datum) AS `year`,
            b.Uhrzeit AS slottime,
            b.Datum AS slotdate,

            -- as grouped by slot, we can calculate available free appointments
            GREATEST(0, o.Anzahlterminarbeitsplaetze - o.reduktionTermineImInternet - COUNT(b.Datum))
                AS `freeAppointments__public`,
            GREATEST(0, o.Anzahlterminarbeitsplaetze - o.reduktionTermineCallcenter - COUNT(b.Datum))
                AS `freeAppointments__callcenter`,
            o.Anzahlterminarbeitsplaetze - COUNT(b.Datum)
                AS `freeAppointments__intern`,

            -- calculate the incrementing slotnr for the availability
            FLOOR(((TIME_TO_SEC(b.Uhrzeit) - TIME_TO_SEC(o.Terminanfangszeit)) / TIME_TO_SEC(o.Timeslot))) AS `slotnr`,

            -- collect settings for the availability to calculate missing slots
            o.OeffnungszeitID AS availability__id,
            o.erlaubemehrfachslots AS availability__multipleSlotsAllowed,
            o.allexWochen AS availability__repeat__afterWeeks,
            o.jedexteWoche AS availability__repeat__weekOfMonth,
            FLOOR(TIME_TO_SEC(o.Timeslot) / 60) AS availability__slotTimeInMinutes,
            UNIX_TIMESTAMP(o.Startdatum) AS availability__startDate,
            UNIX_TIMESTAMP(o.Endedatum) AS availability__endDate,
            o.Terminanfangszeit	 AS availability__startTime,
            o.Terminendzeit	 AS availability__endTime,

            -- weekday is saved bitwise
            o.Wochentag & 2 AS availability__weekday__monday,
            o.Wochentag & 4 AS availability__weekday__tuesday,
            o.Wochentag & 8 AS availability__weekday__wednesday,
            o.Wochentag & 16 AS availability__weekday__thursday,
            o.Wochentag & 32 AS availability__weekday__friday,
            o.Wochentag & 64 AS availability__weekday__saturday,
            o.Wochentag & 1 AS availability__weekday__sunday,

            -- calculate available slots, do not use reduction values
            o.Anzahlterminarbeitsplaetze - o.reduktionTermineImInternet AS availability__workstationCount__public,
            o.Anzahlterminarbeitsplaetze - o.reduktionTermineCallcenter AS availability__workstationCount__callcenter,
            o.Anzahlterminarbeitsplaetze AS availability__workstationCount__intern,

            -- availability overwrites scope settings if greater zero
            IF(o.Offen_ab, o.Offen_ab, s.Termine_ab) AS availability__bookable__startInDays,
            IF(o.Offen_bis, o.Offen_bis, s.Termine_bis) AS availability__bookable__endInDays
        FROM
            standort s
            LEFT JOIN oeffnungszeit o USING(StandortID)
            LEFT JOIN buerger b ON b.StandortID = o.StandortID

        WHERE
            o.StandortID = :scope_id
            AND o.OeffnungszeitID IS NOT NULL

            -- ignore slots out of date range
            AND (b.Datum IS  NULL OR b.Datum BETWEEN :start_process AND :end_process)

            -- ignore availability out of date range
            AND o.Endedatum >= :start_availability
            AND o.Startdatum <= :end_availability

            -- ignore availability without appointment slots
            AND o.Anzahlterminarbeitsplaetze != 0

            -- match weekday
            AND o.Wochentag & POW(2, DAYOFWEEK(b.Datum) - 1)

            -- match week
            AND (
                (
                    o.allexWochen
                    AND ((UNIX_TIMESTAMP(b.Datum) - UNIX_TIMESTAMP(o.Startdatum)) / 86400 / 7) % o.allexWochen != 0
                )
                OR (
                    o.jedexteWoche
                    AND (
                        CEIL(DAYOFMONTH(b.Datum) / 7) = o.jedexteWoche
                        OR (
                            o.jedexteWoche = 5
                            AND CEIL(LAST_DAY(b.Datum) / 7) = CEIL(DAYOFMONTH(b.Datum) / 7)
                        )
                    )
                )
            )

            -- match time and date
            AND b.Uhrzeit >= o.Terminanfangszeit
            AND b.Uhrzeit <= o.Terminendzeit
            AND b.Datum >= o.Startdatum
            AND b.Datum <= o.Endedatum
        GROUP BY o.OeffnungszeitID, b.Datum, `slotnr`
        HAVING
            -- reduce results cause processing them costs time even with query cache
            FROM_UNIXTIME(appointment__date) BETWEEN
                DATE_ADD(:nowStart, INTERVAL availability__bookable__startInDays DAY)
                AND DATE_ADD(:nowEnd, INTERVAL availability__bookable__endInDays DAY)

        -- ordering is important for processing later on (slot reduction)
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
            $slotDebug = "$slotdate #$slotnumber @" . $slotData['slottime']
                . " (Avail.#" . $this->slotData['availability__id'] . ")";
            if (!isset($this->slots[$slotdate][$slotnumber])) {
                throw new \Exception(
                    "Found database entry without a pre-generated slot $slotDebug"
                );
            }
            $slot =& $this->slots[$slotdate][$slotnumber];
            if (isset($slot['slottime'])) {
                throw new \Exception(
                    "Found two database entries for the same slot $slotDebug"
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

    public function addToCalendar(\BO\Zmsentities\Calendar $calendar, $freeProcessesDate)
    {
        foreach ($this->slots as $date => $slotList) {
            if (null !== $freeProcessesDate && $date == $freeProcessesDate->format('Y-m-d')) {
                $calendar['freeProcesses'] = $this->addFreeProcesses($calendar, $freeProcessesDate);
            }
            $datetime = new \DateTimeImmutable($date);
            $day = $calendar->getDayByDateTime($datetime);
            foreach ($slotList as $slotInfo) {
                $day['freeAppointments']['public'] += $slotInfo['public'];
                $day['freeAppointments']['intern'] += $slotInfo['intern'];
                $day['freeAppointments']['callcenter'] += $slotInfo['callcenter'];
            }
        }
        return $calendar;
    }

    /**
     * TODO Unterscheidung nach intern/callcenter/public sollte erst nach der API erfolgen!
     */
    public function addFreeProcesses(
        \BO\Zmsentities\Calendar $calendar,
        \DateTimeImmutable $freeProcessesDate = null,
        $slotType = 'public'
    ) {

        $scopeReader = new \BO\Zmsdb\Scope();
        $freeProcesses = array();
        $selectedDate = $freeProcessesDate->format('Y-m-d');
        $slotList = $this->slots[$selectedDate];
        $scope = $scopeReader->readEntity($this->slotData['appointment__scope__id'], 2);
        foreach ($slotList as $slotInfo) {
            if ($slotInfo[$slotType] > 0) {
                $appointment = new \BO\Zmsentities\Appointment();
                $appointmentDateTime = \DateTime::createFromFormat(
                    'Y-m-d H:i',
                    $selectedDate .' '. $slotInfo['time']
                );
                $appointment['scope'] = $scope;
                $appointment['availability'] = $this->availability;
                $appointment['date'] = $appointmentDateTime->format('U');
                $appointment['slotCount'] = $slotInfo[$slotType];

                $process = new \BO\Zmsentities\Process();
                $process['scope'] = $scope;
                $process['requests'] = $calendar['requests'];
                $process->addAppointment($appointment);

                $freeProcesses[] = $process;
            }
        }
        return $freeProcesses;
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
        foreach (['public', 'intern', 'callcenter'] as $type) {
            $slotA[$type] = $slotA[$type] < $slotB[$type] ? $slotA[$type] : $slotB[$type];
        }
        return $slotA;
    }

    /**
     * Create slots based on availability
     */
    public function createSlots(
        \DateTimeInterface $startDate,
        \DateTimeInterface $stopDate
    ) {
        $time = DateTime::create($startDate);
        do {
            $date = $time->format('Y-m-d');
            if ($this->availability->hasDate($time)) {
                $this->slots[$date] = $this->availability->getSlotList();
            }
            $time = $time->modify('+1day');
        } while ($time->getTimestamp() <= $stopDate->getTimestamp());
    }
}
