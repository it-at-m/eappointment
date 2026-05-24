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
            scopeID INT,
            year SMALLINT,
            month TINYINT,
            slotsRequired TINYINT,
            PRIMARY KEY (scopeID, year, month) 
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
            SUM(intern) AS freeAppointments__intern,
            SUM(publicall) AS allAppointments__public,
            SUM(internall) AS allAppointments__intern,
            "sum" AS freeAppointments__type,
            "free" AS allAppointments__type,
            "bookable" AS status,
            IFNULL(GROUP_CONCAT(DISTINCT CASE WHEN public > 0 THEN scopeID END SEPARATOR ","), "") AS scopeIDs
        FROM
        (
            SELECT
                year,
                month,
                day,
                time,
                slotsRequired,
                COUNT(slotID) AS ancestorCount,
                MIN(IF(public > confirmed, public - confirmed, 0)) AS public,
                MIN(CAST(intern AS SIGNED) - confirmed) AS intern,
                MIN(public) AS publicall,
                MIN(intern) AS internall,
                scopeID
            FROM
            (
                SELECT
                    IFNULL(COUNT(p.slotID), 0) AS confirmed,
                    IF(a.erlaubemehrfachslots, c.slotsRequired, :forceRequiredSlots) AS slotsRequired,
                    s.slotID,
                    s.year,
                    s.month,
                    s.day,
                    s.time,
                    s.public,
                    s.intern,
                    cc.id,
                    s.scopeID
                FROM
                    calendarscope c
                    INNER JOIN slot s
                        ON c.scopeID = s.scopeID
                        AND c.year = s.year
                        AND c.month = s.month
                        AND s.status = "free"
                    LEFT JOIN oeffnungszeit a
                        ON s.availabilityID = a.OeffnungszeitID
                    LEFT JOIN slot_hiera h
                        ON h.ancestorID = s.slotID
                        AND h.ancestorLevel <= IF(a.erlaubemehrfachslots, c.slotsRequired, :forceRequiredSlots)
                    LEFT JOIN slot_process p
                        ON h.slotID = p.slotID
                    LEFT JOIN closures cc
                        ON s.scopeID = cc.StandortID
                        AND s.year = cc.year
                        AND s.month = cc.month
                        AND s.day = cc.day
                GROUP BY s.slotID, h.slotID
                HAVING cc.id IS NULL
            ) AS slotaggregate
            GROUP BY slotID, scopeID
            HAVING ancestorCount >= slotsRequired
        ) AS dayaggregate
        GROUP BY year, month, day
        ORDER BY year, month, day;
';
}
