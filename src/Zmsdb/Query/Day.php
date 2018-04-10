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
     * see also ProcessStatusFree::QUERY_SELECT_PROCESSLIST_DAY
     */
    const QUERY_DAYLIST_JOIN = '
        SELECT
                    year,
                    month,
                    day,
                    SUM(public) AS freeAppointments__public,
                    SUM(callcenter) AS freeAppointments__callcenter,
                    SUM(intern) AS freeAppointments__intern,
                    "sum" AS freeAppointments__type,
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
          MAX(IF(public > confirmed, public - confirmed, 0)) AS public,
          MAX(IF(callcenter > confirmed, callcenter - confirmed, 0)) AS callcenter,
          MAX(CAST(intern AS SIGNED) - confirmed) AS intern
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
