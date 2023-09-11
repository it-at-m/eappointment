<?php

namespace BO\Zmsdb\Query;

/**
 *
 * Calculate Slots for available booking times
 */
class Day extends Base
{

    const QUERY_CREATE_TEMPORARY_SCOPELIST = '
        CREATE TEMPORARY TABLE calendarscope (
            scopeID INT PRIMARY KEY,
            year SMALLINT,
            month TINYINT,
            slotsRequired TINYINT
        );
    ';

    const QUERY_INSERT_TEMPORARY_SCOPELIST = '
        INSERT INTO calendarscope SET
            scopeID = :scopeID,
            year = :year,
            month = :month,
            slotsRequired = :slotsRequired;
    ';

    const QUERY_DROP_TEMPORARY_SCOPELIST = 'DROP TEMPORARY TABLE IF EXISTS calendarscope;';

    /**
     * see also ProcessStatusFree::QUERY_SELECT_PROCESSLIST_DAY
     */
    const QUERY_DAYLIST_JOIN = '
        SELECT
                    year,
                    LPAD(month, 2, "0") AS month,
                    LPAD(day, 2, "0") AS day,
                    SUM(public) AS freeAppointments__public,
                    SUM(callcenter) AS freeAppointments__callcenter,
                    SUM(intern) AS freeAppointments__intern,
                    SUM(publicall) AS allAppointments__public,
                    SUM(callcenterall) AS allAppointments__callcenter,
                    SUM(internall) AS allAppointments__intern,
                    "sum" AS freeAppointments__type,
                    "free" AS allAppointments__type,
                    "bookable" AS status
        FROM
        (

        SELECT
          year,
          month,
          day,
          time,
          slotsRequired,
          COUNT(slotID) ancestorCount,
          MIN(IF(public > confirmed, public - confirmed, 0)) AS public,
          MIN(IF(callcenter > confirmed, callcenter - confirmed, 0)) AS callcenter,
          MIN(CAST(intern AS SIGNED) - confirmed) AS intern,
          MIN(public) AS publicall,
          MIN(callcenter) AS callcenterall,
          MIN(intern) AS internall
        FROM
        (

            SELECT
                IFNULL(COUNT(p.slotID), 0) confirmed,
                IF(a.erlaubemehrfachslots, c.slotsRequired, :forceRequiredSlots) slotsRequired,
                s.slotID,
                s.year,
                s.month,
                s.day,
                s.time,
                s.public,
                s.callcenter,
                s.intern
            FROM
                calendarscope c
                INNER JOIN slot s
                    ON c.scopeID = s.scopeID AND c.year = s.year AND c.month = s.month AND s.status = "free"
                LEFT JOIN oeffnungszeit a ON s.availabilityID = a.OeffnungszeitID
                LEFT JOIN slot_hiera h ON h.ancestorID = s.slotID
                    AND h.ancestorLevel <= IF(a.erlaubemehrfachslots, c.slotsRequired, :forceRequiredSlots)
                LEFT JOIN slot_process p ON h.slotID = p.slotID
            GROUP BY s.slotID, h.slotID

        ) AS slotaggregate 
        GROUP BY slotID
        HAVING ancestorCount >= slotsRequired

        ) AS dayaggregate
        GROUP BY year, month, day
        ORDER BY year, month, day
';
}
