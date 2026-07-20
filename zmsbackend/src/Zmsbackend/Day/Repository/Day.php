<?php

namespace BO\Zmsbackend\Day\Repository;

/**
 *
 * Calculate Slots for available booking times
 */
class Day extends \BO\Zmsbackend\Query\Base
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
     * see also \BO\Zmsbackend\Process\Repository\ProcessStatusFree::QUERY_SELECT_PROCESSLIST_DAYS
     *
     * Occupancy is pre-aggregated from slot_process so the inner query does not need
     * GROUP BY s.slotID, h.slotID. That matters more when slotsRequired is high
     * (multi-service), because slot_hiera returns more rows per starting slot.
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
                    IFNULL(occ.confirmed, 0) AS confirmed,
                    IF(a.erlaubemehrfachslots, c.slotsRequired, :forceRequiredSlots) AS slotsRequired,
                    s.slotID,
                    s.year,
                    s.month,
                    s.day,
                    s.time,
                    s.public,
                    s.intern,
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
                    LEFT JOIN (
                        SELECT slotID, COUNT(*) AS confirmed
                        FROM slot_process
                        GROUP BY slotID
                    ) occ
                        ON occ.slotID = h.slotID
                    LEFT JOIN closures cc
                        ON s.scopeID = cc.StandortID
                        AND s.year = cc.year
                        AND s.month = cc.month
                        AND s.day = cc.day
                WHERE cc.id IS NULL
            ) AS slotaggregate
            GROUP BY slotID, scopeID
            HAVING ancestorCount >= slotsRequired
        ) AS dayaggregate
        GROUP BY year, month, day
        ORDER BY year, month, day;
    ';
}
