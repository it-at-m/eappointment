<?php

namespace BO\Zmsdb\Query;

use BO\Zmsentities\Helper\DateTime;
use BO\Zmsentities\Slot;

/**
 *
 * @SuppressWarnings(CouplingBetweenObjects)
 * Calculate Slots for available booking times
 */
class SlotList extends Base
{

    const QUERY = 'SELECT

            -- collect some important settings, especially from the scope, use the appointment key
            CONCAT(b.Datum, " ", b.Uhrzeit) AS appointment__date,
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
            o.Startdatum AS availability__startDate,
            o.Endedatum AS availability__endDate,
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
            LEFT JOIN buerger b ON
                (
                    b.StandortID = o.StandortID

                    -- match weekday
                    AND o.Wochentag & POW(2, DAYOFWEEK(b.Datum) - 1)

                    -- match week
                    AND (
                        (
                            o.allexWochen
                            -- The following line would be correct by logic, but does not work :-/
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
                        OR (o.allexWochen = 0 AND o.jedexteWoche = 0)
                    )

                    -- ignore slots out of date range
                    AND b.Datum BETWEEN :start_process AND :end_process

                    -- match time and date
                    AND b.Uhrzeit >= o.Terminanfangszeit
                    AND b.Uhrzeit < o.Terminendzeit
                    AND b.Datum >= o.Startdatum
                    AND b.Datum <= o.Endedatum

                    -- match day off
                    AND (
                        b.Datum NOT IN (
                            SELECT Datum FROM feiertage f WHERE f.BehoerdenID = s.BehoerdenID OR f.BehoerdenID = 0
                        )
                        -- ignore day off if availabilty is valid for two or less days
                        OR UNIX_TIMESTAMP(o.Endedatum) - UNIX_TIMESTAMP(o.Startdatum) < 172800
                    )
                )
        WHERE
            s.StandortID = :scope_id
            AND o.OeffnungszeitID IS NOT NULL

            -- ignore availability out of date range
            AND o.Endedatum >= :start_availability
            AND o.Startdatum <= :end_availability

            -- ignore availability on midnight
            AND o.Terminanfangszeit != "00:00:00"
            AND o.Terminendzeit != "00:00:00"

            -- ignore availability without appointment slots
            AND o.Anzahlterminarbeitsplaetze != 0
        GROUP BY o.OeffnungszeitID, b.Datum, `slotnr`
        HAVING
            -- reduce results cause processing them costs time even with query cache
            (
                appointment__date BETWEEN
                    DATE_ADD(:nowStart, INTERVAL availability__bookable__startInDays DAY)
                    -- appointment__date includes midnight time, so take the following day to include the last day
                    AND DATE_ADD(:nowEnd, INTERVAL availability__bookable__endInDays + 1 DAY)
                AND
                (
                    slotdate !=  DATE_ADD(:nowCompare, INTERVAL availability__bookable__endInDays DAY)
                    OR availability__startTime < :nowTime
                )
            )
            OR appointment__date IS NULL

        -- ordering is important for processing later on (slot reduction)
        ORDER BY o.OeffnungszeitID, b.Datum, `slotnr`
        ';

    /**
     *
     * @var array $slotData Single result row from the query
     */
    protected $slotData = null;

    /**
     *
     * @var \BO\Zmsentities\Scope $scope
     */
    protected $scope = null;

    /**
     *
     * @var \BO\Zmsentities\Availability $availability
     */
    protected $availability = null;

    /**
     *
     * @var Array $slots
     */
    protected $slots = array();

    public function __construct(
        array $slotData = ['availability__id' => null],
        \DateTimeImmutable $start = null,
        \DateTimeImmutable $stop = null,
        \DateTimeInterface $now = null,
        \BO\Zmsentities\Availability $availability = null,
        \BO\Zmsentities\Scope $scope = null
    ) {
        $this->availability = $availability;
        $this->scope = $scope;
        $this->setSlotData($slotData);
        if ($this->availability && isset($this->availability['id'])) {
            $this->createSlots($start, $stop, $now);
            $this->addQueryData($slotData);
        }
    }

    public static function getQuery()
    {
        return self::QUERY;
    }

    public static function getParametersMonth($scopeId, \DateTimeInterface $monthDateTime, \DateTimeInterface $now)
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
            'nowCompare' => $now->format('Y-m-d'),
            'nowTime' => $now->format('H:i:s'),
        ];
        return $parameters;
    }

    public static function getParametersDay($scopeId, \DateTimeInterface $dateTime, \DateTimeInterface $now)
    {
        $now = DateTime::create($now);
        $dateTime = DateTime::create($dateTime);
        //\App::$log->error("FreeProcess", [$dateTime->format('c')]);
        $parameters = [
            'scope_id' => $scopeId,
            'start_process' => $dateTime->format('Y-m-d'),
            'end_process' => $dateTime->format('Y-m-d'),
            'start_availability' => $dateTime->format('Y-m-d'),
            'end_availability' => $dateTime->format('Y-m-d'),
            'nowStart' => $now->format('Y-m-d'),
            'nowEnd' => $now->format('Y-m-d'),
            'nowCompare' => $now->format('Y-m-d'),
            'nowTime' => $now->format('H:i:s'),
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
            $availability = [ ];
            foreach ($slotData as $key => $value) {
                if (0 === strpos($key, 'availability__')) {
                    $newkey = str_replace('availability__', '', $key);
                    $availability[$newkey] = $value;
                }
            }
            $this->availability = new \BO\Zmsentities\Availability($availability);
        }
        if (null !== $this->scope) {
            $this->availability['scope'] = $this->scope;
        }
        return $this;
    }

    /**
     * add data from a mysql result set
     * @see self::QUERY
     *
     */
    public function addQueryData(array $slotData)
    {
        if (isset($slotData['slotnr'])) {
            $slotnumber = $slotData['slotnr'];
            $slotdate = $slotData['slotdate'];
            if (!isset($this->slots[$slotdate])) {
                $slotDebug = "$slotdate #$slotnumber @" . $slotData['slottime'] . " on " . $this->availability;
                throw new \BO\Zmsdb\Exception\SlotDataWithoutPreGeneratedSlot(
                    "Found database entry without a generated date for $slotDebug"
                );
            }
            $slotList = $this->slots[$slotdate];
            $slot = $slotList->getSlot($slotnumber);
            if (null === $slot) {
                $slotDebug = "$slotdate #$slotnumber @" . $slotData['slottime'] . " on " . $this->availability;
                // error_log("Debugdata: Found database entry without a pre-generated slot $slotDebug");
                throw new \BO\Zmsdb\Exception\SlotDataWithoutPreGeneratedSlot(
                    "Found database entry without a pre-generated slot $slotDebug"
                );
            }
            //if ($slot->type !== Slot::FREE) {
                // We do not throw an exception, cause availability slotTime might have changed
            //}
            $slotList[$slotnumber] = $this->getCalculatedSlot($slot, $slotData);
        } elseif (isset($slotData['availability__id'])) {
            // Only availability data for available slots, do nothing
        } else {
            throw new \BO\Zmsdb\Exception\SlotDataEmpty("Found empty slot: " . var_export($slotData, true));
        }
        return $this;
    }

    protected function getCalculatedSlot(Slot $slot, $slotData)
    {
        $slot->public += $slotData['freeAppointments__public'] -
            $slotData['availability__workstationCount__public'];
        $slot->callcenter += $slotData['freeAppointments__callcenter'] -
            $slotData['availability__workstationCount__callcenter'];
        $slot->intern += $slotData['freeAppointments__intern'] -
            $slotData['availability__workstationCount__intern'];
        $slot->time = (new DateTime($slotData['slottime']))->format('H:i');
        $slot->type = Slot::TIMESLICE;
        return $slot;
    }

    public function addToCalendar(
        \BO\Zmsentities\Calendar $calendar,
        \DateTimeInterface $now,
        $freeProcessesDate,
        $slotType = 'public',
        $slotsRequired = 1
    ) {
        $nowDate = $now->format('Y-m-d');
        foreach ($this->slots as $date => $slotList) {
            if ($nowDate == $date) {
                $slotList = ('intern' != $slotType) ? $slotList->withTimeGreaterThan($now, $slotType) : $slotList;
                $this->slots[$date] = $slotList;
            }
            $this->addFreeProcessesToCalendar($calendar, $freeProcessesDate, $date, $slotType, $slotsRequired);
            $datetime = new \DateTimeImmutable($date);
            $day = $calendar->getDayByDateTime($datetime);
            $day['freeAppointments'] = $slotList->getSummerizedSlot($day['freeAppointments']);
            $day->getWithStatus($slotType, $now);
        }
        return $calendar;
    }

    protected function addFreeProcessesToCalendar(
        \BO\Zmsentities\Calendar $calendar,
        $freeProcessesDate,
        $date,
        $slotType = 'public',
        $slotsRequired = 1
    ) {
        if (null !== $freeProcessesDate && $date == $freeProcessesDate->format('Y-m-d')) {
            $freeProcesses = $this->getFreeProcesses($calendar, $freeProcessesDate, $slotType, $slotsRequired);
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
        $slotType = 'public',
        $slotsRequired = 1
    ) {
        $selectedDate = $freeProcessesDate->format('Y-m-d');
        $slotList = $this->slots[$selectedDate];
        return $slotList->getFreeProcesses(
            $selectedDate,
            $this->scope,
            $this->availability,
            $slotType,
            $calendar['requests'],
            $slotsRequired
        );
    }

    /**
     * Create slots based on availability
     */
    public function createSlots(\DateTimeInterface $startDate, \DateTimeInterface $stopDate, \DateTimeInterface $now)
    {
        $startDate = ($startDate < $now) ? $now->modify('00:00:00') : $startDate;
        $stopDate = $stopDate->modify('00:00:00');
        $time = DateTime::create($startDate);
        $slotlist = $this->availability->getSlotList();
        do {
            $date = $time->format('Y-m-d');
            if ($this->availability->hasDate($time, $now)) {
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
            foreach ($this->slots as $date => $slotList) {
                $reduced = $slotList->withReducedSlots($slotsRequired);
                $this->slots[$date] = $reduced;
            }
        }
        return $this;
    }

    public function postProcess($data)
    {
        $data[$this->getPrefixed("appointment__date")] = strtotime($data[$this->getPrefixed("appointment__date")]);
        $data[$this->getPrefixed("availability__startDate")] =
            strtotime($data[$this->getPrefixed("availability__startDate")]);
        $data[$this->getPrefixed("availability__endDate")] =
            strtotime($data[$this->getPrefixed("availability__endDate")]);
        return $data;
    }

    public function __toString()
    {
        return "Query_SlotList: {$this->availability} {$this->scope}";
    }
}
