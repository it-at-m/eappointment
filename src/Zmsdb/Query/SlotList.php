<?php

namespace BO\Zmsdb\Query;

use BO\Zmsentities\Helper\DateTime;
use BO\Zmsentities\Slot;

/**
 * Calculate Slots for available booking times
 */
class SlotList
{
    const QUERY = 'SELECT

            -- collect some important settings, especially from the scope, use the appointment key
            FLOOR(UNIX_TIMESTAMP(CONCAT(b.Datum, " ", b.Uhrzeit))) AS appointment__date,
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
            FLOOR(UNIX_TIMESTAMP(o.Startdatum)) AS availability__startDate,
            FLOOR(UNIX_TIMESTAMP(o.Endedatum)) AS availability__endDate,
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
            AND (b.Datum IS NULL OR b.Datum BETWEEN :start_process AND :end_process)

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
                    -- The following line would be correct by logic, but does not work :-(
                        AND FLOOR(
                            (FLOOR(UNIX_TIMESTAMP(b.Datum))
                            - FLOOR(UNIX_TIMESTAMP(o.Startdatum)))
                            / 86400
                            / 7
                        ) % o.allexWochen = 0
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
            AND b.Uhrzeit < o.Terminendzeit
            AND b.Datum >= o.Startdatum
            AND b.Datum <= o.Endedatum

            -- match day off
            AND b.Datum NOT IN (SELECT Datum FROM feiertage f WHERE f.BehoerdenID = s.BehoerdenID OR f.BehoerdenID = 0)

        GROUP BY o.OeffnungszeitID, b.Datum, `slotnr`
        HAVING
            -- reduce results cause processing them costs time even with query cache
            FROM_UNIXTIME(appointment__date) BETWEEN
                DATE_ADD(:nowStart, INTERVAL availability__bookable__startInDays DAY)
                AND DATE_ADD(:nowEnd, INTERVAL availability__bookable__endInDays + 1 DAY)

        -- ordering is important for processing later on (slot reduction)
        ORDER BY o.OeffnungszeitID, b.Datum, `slotnr`
        ';

    /**
     * @var array $slotData Single result row from the query
     */
    protected $slotData = null;

    /**
     * @var \BO\Zmsentities\Scope $scope
     */
    protected $scope = null;

    /**
     * @var \BO\Zmsentities\Availability $availability
     */
    protected $availability = null;

    /**
     * @var Array $slots
     */
    protected $slots = array();

    public function __construct(
        array $slotData = ['availability__id' => null],
        \DateTimeImmutable $start = null,
        \DateTimeImmutable $stop = null,
        \BO\Zmsentities\Availability $availability = null,
        \BO\Zmsentities\Scope $scope = null
    ) {
        $this->availability = $availability;
        $this->scope = $scope;
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

    public static function getParameters($scopeId, \DateTimeInterface $monthDateTime, \DateTimeInterface $now)
    {
        $now = DateTime::create($now);
        $monthDateTime = DateTime::create($monthDateTime);
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

    /**
     * To avoid a db query for availability,
     * we use the scope data to add missing values
     * and try to use availability data in query result
     */
    public function setSlotData(array $slotData)
    {
        $this->slotData = $slotData;
        if (null === $this->availability) {
            $availability = [];
            foreach ($slotData as $key => $value) {
                if (0 === strpos($key, 'availability__')) {
                    $newkey = str_replace('availability__', '', $key);
                    $availability[$newkey] = $value;
                }
            }
            $this->availability = new \BO\Zmsentities\Availability($availability);
        }
        $this->availability['scope'] = $this->scope;
        return $this;
    }

    public function addSlotData(array $slotData)
    {
        if (isset($slotData['slotnr'])) {
            $slotnumber = $slotData['slotnr'];
            $slotdate = $slotData['slotdate'];
            $slotList = (!isset($this->slots[$slotdate])) ?
                new \BO\Zmsentities\Collection\SlotList() :
                $this->slots[$slotdate];

            //check in entity collection if slot exists => if not then ignore it
            //I want to create a test if dayoff matches with processes, to avoid such exceptions
            $slot = $slotList->getSlot($slotnumber);
            if (null === $slot) {
                $slotDebug = "$slotdate #$slotnumber @" . $slotData['slottime'] . " on " . $this->availability;
                error_log("Debugdata: Found database entry without a pre-generated slot $slotDebug");
                throw new \Exception(
                    "Found database entry without a pre-generated slot $slotDebug"
                );
            }

            if ($slot->type !== Slot::FREE) {
                $slotDebug = "$slotdate #$slotnumber @" . $slotData['slottime'] . " on " . $this->availability;
                error_log("Debugdata: Found two database entries for the same slot $slotDebug <=> $slot");
                throw new \Exception(
                    "Found two database entries for the same slot $slotDebug <=> ".$slot
                );
            }

            $slot->public +=
                    $slotData['freeAppointments__public'] - $slotData['availability__workstationCount__public'];
            $slot->callcenter +=
                    $slotData['freeAppointments__callcenter'] - $slotData['availability__workstationCount__callcenter'];
            $slot->intern +=
                    $slotData['freeAppointments__intern'] - $slotData['availability__workstationCount__intern'];
            $slot->time = new DateTime($slotData['slottime']);
            $slot->type = Slot::TIMESLICE;
            $slotList[$slotnumber] = $slot;
        } else {
            throw new \Exception(
                "Found empty slot: " . var_export($slotData, true)
            );
        }
        return $this;
    }

    public function addToCalendar(\BO\Zmsentities\Calendar $calendar, $freeProcessesDate, $slotType = 'public')
    {
        foreach ($this->slots as $date => $slotList) {
            $this->addFreeProcessesToCalendar($calendar, $freeProcessesDate, $date, $slotType);
            $datetime = new \DateTimeImmutable($date);
            $day = $calendar->getDayByDateTime($datetime);
            $day['freeAppointments'] = $slotList->getSummerizedSlot($day['freeAppointments']);
        }
        return $calendar;
    }

    protected function addFreeProcessesToCalendar(
        \BO\Zmsentities\Calendar $calendar,
        $freeProcessesDate,
        $date,
        $slotType = 'public'
    ) {
        if (null !== $freeProcessesDate && $date == $freeProcessesDate->format('Y-m-d')) {
            $freeProcesses = $this->getFreeProcesses($calendar, $freeProcessesDate, $slotType);
            foreach ($freeProcesses as $process) {
                if ($process instanceof \BO\Zmsentities\Process) {
                    $calendar['freeProcesses']->addEntity($process);
                }
            }
        }
    }

    /**
     * TODO Unterscheidung nach intern/callcenter/public sollte erst nach der API erfolgen!
     */
    public function getFreeProcesses(
        \BO\Zmsentities\Calendar $calendar,
        \DateTimeImmutable $freeProcessesDate = null,
        $slotType = 'public'
    ) {

        $selectedDate = $freeProcessesDate->format('Y-m-d');
        $slotList = $this->slots[$selectedDate];
        return $slotList->getFreeProcesses(
            $selectedDate,
            $this->scope,
            $this->availability,
            $slotType,
            $calendar['requests']
        );
    }

    /**
     * Create slots based on availability
     */
    public function createSlots(
        \DateTimeInterface $startDate,
        \DateTimeInterface $stopDate
    ) {
        $time = DateTime::create($startDate);
        $slotlist = $this->availability->getSlotList();
        do {
            $date = $time->format('Y-m-d');
            if ($this->availability->hasDate($time)) {
                $this->slots[$date] = clone $slotlist;
            }
            $time = $time->modify('+1day');
        } while ($time->getTimestamp() <= $stopDate->getTimestamp());
    }

    public function isSameAvailability(array $slotData)
    {
        return $this->slotData['availability__id'] == $slotData['availability__id'];
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
        if (count($this->slots) && $slotsRequired > 1) {
            foreach ($this->slots as $slotList) {
                $slotLength = count($slotList);
                for ($slotIndex = 0; $slotIndex < $slotLength; $slotIndex++) {
                    if ($slotIndex + $slotsRequired < $slotLength) {
                        for ($slotRelative = 1; $slotRelative < $slotsRequired; $slotRelative++) {
                            if ($slotIndex + $slotRelative < $slotLength) {
                                $slotList->takeLowerSlotValue($slotIndex, $slotIndex + $slotRelative);
                            }
                        }
                    } else {
                        $slotList->setEmptySlotValues($slotIndex);
                    }
                }
            }
        }
        return $this;
    }
}
