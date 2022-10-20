<?php

namespace BO\Zmsdb\Query;

class Slot extends Base implements MappingInterface
{
    /**
     *
     * @var String TABLE mysql table reference
     */
    const TABLE = 'slot';

    const QUERY_OPTIMIZE_SLOT = 'OPTIMIZE TABLE slot;';
    const QUERY_OPTIMIZE_SLOT_HIERA = 'OPTIMIZE TABLE slot_hiera;';
    const QUERY_OPTIMIZE_SLOT_PROCESS = 'OPTIMIZE TABLE slot_proces;';
    const QUERY_OPTIMIZE_PROCESS = 'OPTIMIZE TABLE buerger;';

    const QUERY_LAST_CHANGED = 'SELECT MAX(updateTimestamp) AS dateString FROM slot;';

    const QUERY_LAST_CHANGED_AVAILABILITY = '
        SELECT MAX(updateTimestamp) AS dateString FROM slot WHERE availabilityID = :availabilityID AND status="free";';

    const QUERY_LAST_CHANGED_SCOPE = '
        SELECT MAX(updateTimestamp) AS dateString FROM slot WHERE scopeID = :scopeID;';

    const QUERY_INSERT_SLOT_PROCESS = '
        INSERT INTO slot_process
        VALUES(?,?,?) 
    ';

    const QUERY_SELECT_BY_SCOPE_AND_DAY = '
        SELECT
            s.*
        FROM slot s
        WHERE
            s.scopeID = :scopeID
            AND s.year = :year
            AND s.month = :month
            AND s.day = :day
    ';

    const QUERY_SELECT_MISSING_PROCESS = '
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
    const QUERY_SELECT_MISSING_PROCESS_BY_SCOPE = '
          AND s.scopeID = :scopeID
    ';


    const QUERY_INSERT_SLOT_PROCESS_ID = '
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
    const QUERY_DELETE_SLOT_PROCESS_CANCELLED = '
        DELETE sp 
            FROM slot_process sp LEFT JOIN slot s USING (slotID)
            WHERE (s.status = "cancelled" OR s.status IS NULL)
    ';
    const QUERY_DELETE_SLOT_PROCESS_CANCELLED_BY_SCOPE = '
                AND s.scopeID = :scopeID
    ';


    const QUERY_UPDATE_SLOT_MISSING_AVAILABILITY_BY_SCOPE = '
    UPDATE
         slot s
           LEFT JOIN oeffnungszeit a ON s.availabilityID = a.OeffnungszeitID
           SET s.status = "cancelled"
           WHERE
             (
               a.OeffnungszeitID IS NULL
               OR a.Endedatum < :dateString
             )
             AND s.scopeID = :scopeID
    ';

    const QUERY_UPDATE_SLOT_MISSING_AVAILABILITY = '
    UPDATE
         slot s
           LEFT JOIN oeffnungszeit a ON s.availabilityID = a.OeffnungszeitID
           SET s.status = "cancelled"
           WHERE
             a.OeffnungszeitID IS NULL
               OR a.Endedatum < :dateString
    ';

    const QUERY_SELECT_DELETABLE_SLOT_PROCESS = '
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
    const QUERY_SELECT_DELETABLE_SLOT_PROCESS_BY_SCOPE = '
              AND b.StandortID = :scopeID
    ';

    const QUERY_DELETE_SLOT_PROCESS_ID = '
        DELETE sp 
            FROM slot_process sp 
            WHERE sp.processID = :processId
    ';

    const QUERY_UPDATE_SLOT_STATUS = "
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

    const QUERY_SELECT_SLOT = '
    SELECT slotID FROM slot WHERE
      scopeID = :scopeID
      AND year = :year
      AND month = :month
      AND day = :day
      AND time = :time
      AND availabilityID = :availabilityID
    LIMIT 1
';

    const QUERY_INSERT_ANCESTOR = '
    INSERT INTO slot_hiera SET slotID = :slotID, ancestorID = :ancestorID, ancestorLevel = :ancestorLevel
';

    const QUERY_DELETE_ANCESTOR = '
    DELETE FROM slot_hiera WHERE slotID = :slotID
';

    const QUERY_CANCEL_AVAILABILITY = '
        UPDATE slot SET status = "cancelled" WHERE availabilityID = :availabilityID
';

    const QUERY_CANCEL_SLOT_OLD_BY_SCOPE = '
    UPDATE slot SET status =  "cancelled" 
        WHERE scopeID = :scopeID AND (
            (year < :year)
            OR (year = :year AND  month < :month) 
            OR (year = :year AND  month = :month AND  day <= :day AND time < :time)
        )
';

    const QUERY_CANCEL_SLOT_OLD = '
    UPDATE slot SET status =  "cancelled" 
        WHERE (year < :year)
            OR (year = :year AND  month < :month) 
            OR (year = :year AND  month = :month AND  day <= :day AND time < :time)
';

    const QUERY_DELETE_SLOT_OLD = '
    DELETE FROM slot 
        WHERE (year < :year) 
            OR (year = :year AND  month < :month) 
            OR (year = :year AND  month = :month AND  day < :day)
';

    const QUERY_DELETE_SLOT_HIERA = '
        DELETE sh 
            FROM slot_hiera sh LEFT JOIN slot s USING(slotID)
            WHERE s.slotID IS NULL
    ';


    public function getEntityMapping()
    {
        return [
        ];
    }

    public function reverseEntityMapping(
        \BO\Zmsentities\Slot $slot,
        \BO\Zmsentities\Availability $availability,
        \DateTimeInterface $date
    ) {
        $data = array();
        $data['scopeID'] = $availability->scope->id;
        $data['availabilityID'] = $availability->id;
        $data['year'] = $date->format('Y');
        $data['month'] = $date->format('m');
        $data['day'] = $date->format('d');
        $data['time'] = $slot->getTimeString();
        $data['public'] = isset($slot['public']) ? $slot['public'] : $availability->workstationCount['public'];
        $data['callcenter'] = isset($slot['callcenter']) ?
            $slot['callcenter'] : $availability->workstationCount['callcenter'];
        $data['intern'] = isset($slot['intern']) ? $slot['intern'] : $availability->workstationCount['intern'];
        $data['status'] = $slot->status;
        $data['slotTimeInMinutes'] = $availability->slotTimeInMinutes;
        return $data;
    }

    public function addConditionSlotId($slotID)
    {
        $this->query->where('slot.slotID', '=', $slotID);
        return $this;
    }
}
