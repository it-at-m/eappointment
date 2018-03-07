<?php

namespace BO\Zmsdb\Query;

/**
 *
 * Calculate Slots for available booking times
 */
class Day extends Base
{

    const QUERY_CREATE_TEMPORARY_SCOPELIST = '
        CREATE TEMPORARY TABLE calendarscope ( scopeID INT, year SMALLINT, month TINYINT, slotsRequired TINYINT );
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
     * Different levels of join from inner to outer:
     *  - r: confirmed appointment counts per time-slot
     *  - s: calculated free slots per time slot
     *  - d: inner join on slotsRequired and take the lowest values of the joined time slots
     *  - l: group and summerize per day
     */
    const QUERY_DAYLIST = '
        SELECT 
            year,
            month,
            day,
            SUM(public) AS freeAppointments__public,
            SUM(callcenter) AS freeAppointments__callcenter,
            SUM(intern) AS freeAppointments__intern,
            "bookable" AS status
        FROM (
            SELECT 
                d.scopeID,
                d.year,
                d.month,
                d.day,
                d.time,
                MIN(a.public) AS public,
                MIN(a.callcenter) AS callcenter,
                MIN(a.intern) AS intern
            FROM slot d
            INNER JOIN (
                SELECT
                    s.scopeID,
                    s.year,
                    s.month,
                    s.day,
                    s.time,
                    s.availabilityID,
                    IF(s.public >= IFNULL(r.confirmed, 0), s.public - IFNULL(r.confirmed, 0), 0) AS public,
                    IF(s.callcenter >= IFNULL(r.confirmed, 0), s.callcenter - IFNULL(r.confirmed, 0), 0) AS callcenter,
                    CAST(s.intern AS SIGNED) - IFNULL(r.confirmed, 0) AS intern,
                    r.confirmed
                FROM slot s 
                    LEFT JOIN (
                        SELECT scopeID, year, month, day, time, COUNT(*) confirmed 
                        FROM slot_process
                        WHERE scopeID = :scopeID AND year = :year AND month = :month
                        GROUP BY scopeID, year, month, day, time
                    ) r ON 
                        r.scopeID = s.scopeID 
                        AND r.year = s.year 
                        AND r.month = s.month 
                        AND r.day = s.day
                        AND r.time = s.time
                WHERE s.scopeID = :scopeID AND s.year = :year AND s.month = :month
            ) a ON 
                d.scopeID = a.scopeID
                AND d.year = a. year
                AND d.month = a.month
                AND d.day = a.day
                AND a.time BETWEEN d.time AND SEC_TO_TIME(TIME_TO_SEC(d.time) + (:slotsRequired * d.slotTimeInMinutes * 60)-1)
            WHERE d.scopeID = :scopeID AND d.year = :year AND d.month = :month
            GROUP BY d.year, d.month, d.day, d.time
        ) l
        GROUP BY year, month, day
    ';
    
    const QUERY_DAYLIST_JOIN = '
        SELECT
                    year,
                    month,
                    day,
                    SUM(public) AS freeAppointments__public,
                    SUM(callcenter) AS freeAppointments__callcenter,
                    SUM(intern) AS freeAppointments__intern,
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
          MIN(CAST(intern AS SIGNED) - confirmed) AS intern
        FROM
        (

            SELECT
                IFNULL(COUNT(p.slotID), 0) confirmed,
                c.slotsRequired,
                s.*
            FROM
                calendarscope c
                INNER JOIN slot s
                    ON c.scopeID = s.scopeID AND c.year = s.year AND c.month = s.month AND s.status = "free"
                LEFT JOIN slot_hiera h ON h.ancestorID = s.slotID AND h.ancestorLevel <= c.slotsRequired
                LEFT JOIN slot_process p ON h.slotID = p.slotID
            GROUP BY s.slotID, h.slotID

        ) AS slotaggregate 
        GROUP BY slotID
        HAVING ancestorCount >= slotsRequired

        ) AS dayaggregate
        GROUP BY year, month, day
        ORDER BY year, month, day
';
    const QUERY_MONTH = '
        SELECT
            IF((public - IFNULL(confirmed, 0)) > 0, public - IFNULL(confirmed, 0), 0 )
                AS freeAppointments__public,
            IF((callcenter - IFNULL(confirmed, 0)) > 0, callcenter - IFNULL(confirmed, 0), 0 ) 
                AS freeAppointments__callcenter, 
            IFNULL(intern - confirmed, intern)
                AS freeAppointments__intern,
            s.year AS year,
            s.month AS month,
            s.day AS day,
            "bookable" AS status
        FROM (
        SELECT SUM(type = "public") public,
            SUM(type = "callcenter" OR type = "public") callcenter,
            COUNT(type) intern,
            scopeID,
            year,
            month,
            day
        FROM slot
        WHERE 
            year = :year
            AND month = :month
            AND scopeID = :scopeID
        GROUP BY scopeID,
            year,
            month,
            day
        ) AS s 
        LEFT JOIN (
        SELECT COUNT(*) confirmed, YEAR(Datum) as year, MONTH(Datum) as month, DAY(Datum) as day, StandortID as scopeID
        FROM buerger
        WHERE 
            YEAR(Datum) = :year
            AND MONTH(Datum) = :month
            AND StandortID = :scopeID
        GROUP BY Datum, StandortID
        ) AS p ON 
            s.year = p.year 
            AND s.month = p.month 
            AND s.day = p.day 
            AND s.scopeID = p.scopeID
    ';
}
