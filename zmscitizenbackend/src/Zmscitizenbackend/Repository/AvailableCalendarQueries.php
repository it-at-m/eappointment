<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Repository;

class AvailableCalendarQueries
{
    public const int MAX_SLOTS = 25;

    public const string QUERY_CREATE_TEMPORARY_SCOPELIST = '
        CREATE TEMPORARY TABLE calendarscope (
            scopeID INT,
            year SMALLINT,
            month TINYINT,
            slotsRequired TINYINT,
            PRIMARY KEY (scopeID, year, month)
        );
    ';

    public const string QUERY_INSERT_TEMPORARY_SCOPELIST = '
        INSERT INTO calendarscope SET
            scopeID = :scopeID,
            year = :year,
            month = :month,
            slotsRequired = :slotsRequired;
    ';

    public const string QUERY_DROP_TEMPORARY_SCOPELIST = 'DROP TEMPORARY TABLE IF EXISTS calendarscope;';

    /**
     * Citizen calendar-availability daylist only.
     *
     * Occupancy is pre-aggregated from slot_process and hierarchy slots must be free,
     * so multi-service daylists do not invent bookable days.
     *
     * see also QUERY_SELECT_PROCESSLIST_DAYS_AVAILABILITY
     */
    public const string QUERY_DAYLIST_JOIN_AVAILABILITY = '
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
                    IF(a.multiple_slots_allowed, c.slotsRequired, :forceRequiredSlots) AS slotsRequired,
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
                        AND h.ancestorLevel <= IF(a.multiple_slots_allowed, c.slotsRequired, :forceRequiredSlots)
                    INNER JOIN slot s2
                        ON h.slotID = s2.slotID
                        AND s2.status = "free"
                    LEFT JOIN (
                        SELECT p.slotID, COUNT(*) AS confirmed
                        FROM slot_process p
                        INNER JOIN slot s_occ
                            ON s_occ.slotID = p.slotID
                        INNER JOIN calendarscope c_occ
                            ON c_occ.scopeID = s_occ.scopeID
                            AND c_occ.year = s_occ.year
                            AND c_occ.month = s_occ.month
                        GROUP BY p.slotID
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

    /**
     * Shared free-process query used by citizen reserve (same semantics as zmsbackend ProcessFreeUnique).
     */
    public const string QUERY_SELECT_PROCESSLIST_DAYS = '
        SELECT
            "free" AS status,
            CONCAT(year, "-", month, "-", day, " ", time) AS appointments__0__date,
            slotsRequired AS appointments__0__slotCount,
            scopeID AS scope__id
        FROM
            (SELECT
               COUNT(slotID) as ancestorCount,
               IF(MIN(available - confirmed) > 0, MIN(available - confirmed), 0) as free,
               tmp_ancestor.*
            FROM (SELECT
                IFNULL(COUNT(p.slotID), 0) confirmed,
                IF(:slotType = "intern", s.intern,
                    IF(:slotType = "public", s.`public`, 0)
                    ) available,
                IF(a.multiple_slots_allowed, c.slotsRequired, :forceRequiredSlots) slotsRequired,
                s.*,
                cc.id
            FROM
                calendarscope c
                INNER JOIN slot s
                    ON c.scopeID = s.scopeID
                        %s
                        AND s.status = "free"
                LEFT JOIN oeffnungszeit a ON s.availabilityID = a.OeffnungszeitID
                LEFT JOIN slot_hiera h ON h.ancestorID = s.slotID
                    AND h.ancestorLevel <= IF(a.multiple_slots_allowed, c.slotsRequired, :forceRequiredSlots)
                INNER JOIN slot s2 on h.slotID = s2.slotID and s2.status = "free"
                LEFT JOIN slot_process p ON h.slotID = p.slotID
                LEFT JOIN closures cc ON (s.scopeID = cc.StandortID AND s.year = cc.year AND s.month = cc.month and s.day = cc.day)
            GROUP BY s.slotID, h.slotID
            HAVING cc.id IS NULL
            ) AS tmp_ancestor
            GROUP BY slotID
            HAVING ancestorCount >= slotsRequired
            ) AS tmp_avail
            INNER JOIN slot_sequence sq ON sq.slotsequence <= tmp_avail.free
    ';

    /**
     * Citizen calendar-availability free-process query only.
     *
     * Occupancy is pre-aggregated from slot_process (same approach as QUERY_DAYLIST_JOIN_AVAILABILITY).
     */
    public const string QUERY_SELECT_PROCESSLIST_DAYS_AVAILABILITY = '
        SELECT
            "free" AS status,
            CONCAT(year, "-", month, "-", day, " ", time) AS appointments__0__date,
            slotsRequired AS appointments__0__slotCount,
            scopeID AS scope__id
        FROM
            (SELECT
               COUNT(slotID) as ancestorCount,
               IF(MIN(available - confirmed) > 0, MIN(available - confirmed), 0) as free,
               tmp_ancestor.*
            FROM (SELECT
                IFNULL(occ.confirmed, 0) confirmed,
                IF(:slotType = "intern", s.intern,
                    IF(:slotType = "public", s.`public`, 0)
                    ) available,
                IF(a.multiple_slots_allowed, c.slotsRequired, :forceRequiredSlots) slotsRequired,
                s.*,
                cc.id
            FROM
                calendarscope c
                INNER JOIN slot s
                    ON c.scopeID = s.scopeID
                        %s
                        AND s.status = "free"
                LEFT JOIN oeffnungszeit a ON s.availabilityID = a.OeffnungszeitID
                LEFT JOIN slot_hiera h ON h.ancestorID = s.slotID
                    AND h.ancestorLevel <= IF(a.multiple_slots_allowed, c.slotsRequired, :forceRequiredSlots)
                INNER JOIN slot s2 on h.slotID = s2.slotID and s2.status = "free"
                LEFT JOIN (
                    SELECT p.slotID, COUNT(*) AS confirmed
                    FROM slot_process p
                    INNER JOIN slot s_occ
                        ON s_occ.slotID = p.slotID
                    INNER JOIN calendarscope c_occ
                        ON c_occ.scopeID = s_occ.scopeID
                        AND c_occ.year = s_occ.year
                        AND c_occ.month = s_occ.month
                    GROUP BY p.slotID
                ) occ ON occ.slotID = h.slotID
                LEFT JOIN closures cc ON (s.scopeID = cc.StandortID AND s.year = cc.year AND s.month = cc.month and s.day = cc.day)
            WHERE cc.id IS NULL
            ) AS tmp_ancestor
            GROUP BY slotID
            HAVING ancestorCount >= slotsRequired
            ) AS tmp_avail
            INNER JOIN slot_sequence sq ON sq.slotsequence <= tmp_avail.free
    ';

    /**
     * @param list<\DateTimeInterface> $days
     */
    public static function buildDaysCondition(array $days): string
    {
        $sql = 'AND (';
        $sqlPats = [];

        foreach ($days as $day) {
            $sqlPats[] = '(c.year = ' . $day->format('Y') . '
                        AND c.month = ' . $day->format('m') . '
                        AND s.day = ' . $day->format('d') . '
                        AND c.year = s.year
                        AND c.month = s.month)';
        }

        return $sql . implode(' OR ', $sqlPats) . ')';
    }

    /**
     * @param list<string|int> $ids
     * @return array{0: string, 1: array<string, string>}
     */
    public static function idPlaceholders(string $prefix, array $ids): array
    {
        $placeholders = [];
        $params = [];
        foreach (array_values($ids) as $index => $id) {
            $key = $prefix . $index;
            $placeholders[] = ':' . $key;
            $params[$key] = (string) $id;
        }

        return [implode(', ', $placeholders), $params];
    }
}
