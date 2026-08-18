<?php

namespace BO\Zmsbackend\Slot\Repository;

class Slot extends \BO\Zmsbackend\Query\Base implements \BO\Zmsbackend\Query\MappingInterface
{
    /**
     *
     * @var String TABLE mysql table reference
     */
    const string TABLE = 'slot';

    const string QUERY_OPTIMIZE_SLOT = 'OPTIMIZE TABLE slot;';
    const string QUERY_OPTIMIZE_SLOT_HIERA = 'OPTIMIZE TABLE slot_hiera;';
    const string QUERY_OPTIMIZE_SLOT_PROCESS = 'OPTIMIZE TABLE slot_proces;';
    const string QUERY_OPTIMIZE_PROCESS = 'OPTIMIZE TABLE buerger;';

    const string QUERY_LAST_CHANGED = 'SELECT MAX(updateTimestamp) AS dateString FROM slot;';

    const string QUERY_LAST_CHANGED_AVAILABILITY = '
        SELECT MAX(updateTimestamp) AS dateString FROM slot WHERE availabilityID = :availabilityID AND status="free";';

    const string QUERY_LAST_IN_AVAILABILITY = '
        SELECT CONCAT(year, "-", LPAD(month, 2, "0"), "-", LPAD(day, 2, "0")) AS dateString
        FROM slot
        WHERE availabilityID = :availabilityID AND status="free"
        ORDER BY year DESC, month DESC, day DESC
        LIMIT 1;';

    const string QUERY_OLDEST_VERSION_IN_AVAILABILITY = '
        SELECT `version`
        FROM slot
        WHERE availabilityID = :availabilityID AND status="free"
        ORDER BY `version` ASC
        LIMIT 1;';

    const string QUERY_LAST_CHANGED_SCOPE = '
        SELECT MAX(updateTimestamp) AS dateString FROM slot WHERE scopeID = :scopeID;';

    const string QUERY_INSERT_SLOT_PROCESS = '
        INSERT INTO slot_process
        VALUES(?,?,?) 
    ';

    const string QUERY_SELECT_BY_SCOPE_AND_DAY = '
        SELECT
            s.*
        FROM slot s
        WHERE
            s.scopeID = :scopeID
            AND s.year = :year
            AND s.month = :month
            AND s.day = :day
    ';

    const string QUERY_SELECT_MISSING_PROCESS = '
        SELECT 
          s.slotID,
          b.BuergerID,
          NOW() updateTimestamp
        FROM slot s
          INNER JOIN buerger b ON
            s.year = YEAR(b.Datum)
            AND s.month = MONTH(b.Datum)
            AND s.day = DAY(b.Datum)
            AND s.scopeID = b.StandortID
            AND b.Uhrzeit BETWEEN s.time AND SEC_TO_TIME(TIME_TO_SEC(s.time) + (s.slotTimeInMinutes * 60) - 1)
            AND s.status = "free"
          LEFT JOIN slot_process sp ON b.BuergerID = sp.processID
        WHERE
          sp.processID IS NULL
    ';
    const string QUERY_SELECT_MISSING_PROCESS_BY_SCOPE = '
          AND s.scopeID = :scopeID
    ';


    const string QUERY_INSERT_SLOT_PROCESS_ID = '
        REPLACE INTO slot_process
        SELECT 
          s.slotID,
          b.BuergerID,
          NOW()
        FROM slot s
          INNER JOIN buerger b ON
            s.year = YEAR(b.Datum)
            AND s.month = MONTH(b.Datum)
            AND s.day = DAY(b.Datum)
            AND s.scopeID = b.StandortID
            AND s.status = "free"
            AND b.Uhrzeit BETWEEN s.time AND SEC_TO_TIME(TIME_TO_SEC(s.time) + (s.slotTimeInMinutes * 60) - 1)
        WHERE
          b.BuergerID = :processId
    ';
    const string QUERY_DELETE_SLOT_PROCESS_CANCELLED = '
        DELETE sp 
            FROM slot_process sp LEFT JOIN slot s USING (slotID)
            WHERE (s.status = "cancelled" OR s.status IS NULL)
    ';
    const string QUERY_DELETE_SLOT_PROCESS_CANCELLED_BY_SCOPE = '
                AND s.scopeID = :scopeID
    ';


    const string QUERY_UPDATE_SLOT_MISSING_AVAILABILITY_BY_SCOPE = '
    UPDATE
         slot s
           LEFT JOIN oeffnungszeit a ON s.availabilityID = a.OeffnungszeitID
           SET s.status = "cancelled"
           WHERE
             (
               a.OeffnungszeitID IS NULL
               OR a.end_date < :dateString
             )
             AND s.scopeID = :scopeID
    ';

    const string QUERY_UPDATE_SLOT_MISSING_AVAILABILITY = '
    UPDATE
         slot s
           LEFT JOIN oeffnungszeit a ON s.availabilityID = a.OeffnungszeitID
           SET s.status = "cancelled"
           WHERE
             a.OeffnungszeitID IS NULL
               OR a.end_date < :dateString
    ';

    const string QUERY_SELECT_DELETABLE_SLOT_PROCESS = '
        SELECT sp.processID AS processId
            FROM slot_process sp
              LEFT JOIN buerger b ON sp.processID = b.BuergerID
              LEFT JOIN slot s ON sp.slotID = s.slotID
            WHERE (
                b.BuergerID IS NULL
                OR (
                  b.updateTimestamp > sp.updateTimestamp
                  AND (
                    b.Uhrzeit NOT BETWEEN s.time AND SEC_TO_TIME(TIME_TO_SEC(s.time) + (s.slotTimeInMinutes * 60) - 1)
                    OR s.month != MONTH(b.Datum)
                    OR s.day != DAY(b.Datum)
                    OR s.scopeID != b.StandortID
                  )
                )
              ) 
    ';
    const string QUERY_SELECT_DELETABLE_SLOT_PROCESS_BY_SCOPE = '
              AND b.StandortID = :scopeID
    ';

    const string QUERY_DELETE_SLOT_PROCESS_ID = '
        DELETE sp 
            FROM slot_process sp 
            WHERE sp.processID = :processId
    ';

    const string QUERY_UPDATE_SLOT_STATUS = "
        UPDATE slot
          LEFT JOIN (
          SELECT s.slotID,
          IF(s.status IN ('free', 'full'), IF(IFNULL(COUNT(p.slotID), 0) < intern, 'free', 'full'), s.status) newstatus
          FROM slot s
            LEFT JOIN slot_process p ON s.slotID = p.slotID
          GROUP BY s.slotID
          ) calc ON slot.slotID = calc.slotID
        SET
          slot.status = calc.newstatus
        WHERE slot.status != calc.newstatus
";

    const string QUERY_SELECT_SLOT = '
    SELECT slotID FROM slot WHERE
      scopeID = :scopeID
      AND year = :year
      AND month = :month
      AND day = :day
      AND time = :time
      AND availabilityID = :availabilityID
    LIMIT 1
';

    /**
     * Lock multiple appointment slots in one statement.
     * Placeholders for time IN (...) are injected by Slot::lockSlotsForAppointment.
     * ORDER BY slotID prefers a deterministic primary-key lock order.
     */
    public const string QUERY_SELECT_SLOTS_FOR_UPDATE = '
    SELECT slotID FROM slot WHERE
      scopeID = ?
      AND availabilityID = ?
      AND year = ?
      AND month = ?
      AND day = ?
      AND time IN (%s)
    ORDER BY slotID ASC
    FOR UPDATE
';

    const string QUERY_INSERT_ANCESTOR = '
    INSERT INTO slot_hiera SET slotID = :slotID, ancestorID = :ancestorID, ancestorLevel = :ancestorLevel
';

    const string QUERY_DELETE_ANCESTOR = '
    DELETE FROM slot_hiera WHERE slotID = :slotID
';

    const string QUERY_CANCEL_AVAILABILITY = '
        UPDATE slot SET status = "cancelled" WHERE availabilityID = :availabilityID
';

    const string QUERY_CANCEL_AVAILABILITY_BEFORE_BOOKABLE = '
            UPDATE slot SET status = "cancelled" WHERE availabilityID = :availabilityID 
            AND CONCAT(year, "-", LPAD(month, 2, "0"), "-", LPAD(day, 2, "0")) < :providedDate
';

    const string QUERY_CANCEL_AVAILABILITY_AFTER_BOOKABLE = '
            UPDATE slot SET status = "cancelled" WHERE availabilityID = :availabilityID 
            AND CONCAT(year, "-", LPAD(month, 2, "0"), "-", LPAD(day, 2, "0")) > :providedDate
    ';

    const string QUERY_CANCEL_SLOT_OLD_BY_SCOPE = '
    UPDATE slot SET status =  "cancelled" 
        WHERE scopeID = :scopeID AND (
            (year < :year)
            OR (year = :year AND  month < :month) 
            OR (year = :year AND  month = :month AND  day <= :day AND time < :time)
        )
';

    const string QUERY_CANCEL_SLOT_OLD = '
    UPDATE slot SET status =  "cancelled" 
        WHERE (year < :year)
            OR (year = :year AND  month < :month) 
            OR (year = :year AND  month = :month AND  day <= :day AND time < :time)
';

    const string QUERY_DELETE_SLOT_OLD = '
    DELETE FROM slot 
        WHERE (year < :year) 
            OR (year = :year AND  month < :month) 
            OR (year = :year AND  month = :month AND  day < :day)
';

    const string QUERY_DELETE_SLOT_HIERA = '
        DELETE sh 
            FROM slot_hiera sh LEFT JOIN slot s USING(slotID)
            WHERE s.slotID IS NULL
    ';


    /**
     * @return array
     *
     */
    #[\Override]
    public function getEntityMapping()
    {
        return [
        ];
    }

    /**
     * @return (mixed|string)[]
     *
     */
    public function reverseEntityMapping(
        \BO\Zmsentities\Slot $slot,
        \BO\Zmsentities\Availability $availability,
        \DateTimeInterface $date
    ): array {
        $data = array();
        $data['scopeID'] = $availability->scope->id;
        $data['availabilityID'] = $availability->id;
        $data['version'] = $availability->version;
        $data['year'] = $date->format('Y');
        $data['month'] = $date->format('m');
        $data['day'] = $date->format('d');
        $data['time'] = $slot->getTimeString();
        $data['public'] = isset($slot['public']) ? $slot['public'] : $availability->workstationCount['public'];
        $data['intern'] = isset($slot['intern']) ? $slot['intern'] : $availability->workstationCount['intern'];
        $data['status'] = $slot->status;
        $data['slotTimeInMinutes'] = $availability->slotTimeInMinutes;
        return $data;
    }

    public function addConditionSlotId($slotID): static
    {
        $this->query->where('slot.slotID', '=', $slotID);
        return $this;
    }
}
