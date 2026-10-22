<?php

namespace BO\Zmsbackend\Process\Repository;

class ProcessStatusFree extends \BO\Zmsbackend\Query\Base
{
    /**
     * Shared free-process query used by zmsadmin / classic free-slot APIs.
     * Count bookings via the slot_process primary key for each required hierarchy slot,
     * then aggregate once per starting slot. The sequence join preserves the number of
     * simultaneous appointments offered by a free slot.
     *
     * see also \BO\Zmsbackend\Day\Repository\Day::QUERY_DAYLIST_JOIN
     */
    const string QUERY_SELECT_PROCESSLIST_DAYS = '
        SELECT
            "free" AS status,
            CONCAT(year, "-", month, "-", day, " ", time) AS appointments__0__date,
            slotsRequired AS appointments__0__slotCount,
            scopeID AS scope__id
        FROM (
            SELECT s.year, s.month, s.day, s.time, s.scopeID,
                IF(a.multiple_slots_allowed, c.slotsRequired, :forceRequiredSlots) AS slotsRequired,
                COUNT(DISTINCT h.slotID) AS ancestorCount,
                IFNULL(GREATEST(0, MIN(
                    CAST(IF(:slotType = "intern", s.intern, IF(:slotType = "public", s.`public`, 0)) AS SIGNED)
                    - (SELECT COUNT(*) FROM slot_process p WHERE p.slotID = h.slotID)
                )), 0) AS free
            FROM
                calendarscope c
                INNER JOIN slot s
                    ON c.scopeID = s.scopeID
                        %s
                        AND s.status = "free"
                LEFT JOIN oeffnungszeit a ON s.availabilityID = a.OeffnungszeitID
                INNER JOIN slot_hiera h ON h.ancestorID = s.slotID
                    AND h.ancestorLevel <= IF(a.multiple_slots_allowed, c.slotsRequired, :forceRequiredSlots)
                INNER JOIN slot s2 ON h.slotID = s2.slotID AND s2.status = "free"
            WHERE NOT EXISTS (
                SELECT 1 FROM closures cc
                WHERE cc.StandortID = s.scopeID AND cc.year = s.year
                    AND cc.month = s.month AND cc.day = s.day
            )
            GROUP BY s.slotID
            HAVING ancestorCount >= slotsRequired
        ) AS available
        INNER JOIN slot_sequence sq ON sq.slotsequence <= available.free
    ';

    /**
     * Citizen calendar-availability free-process query only.
     *
     * Occupancy is pre-aggregated from slot_process (same approach as Day::QUERY_DAYLIST_JOIN_AVAILABILITY).
     *
     * see also \BO\Zmsbackend\Day\Repository\Day::QUERY_DAYLIST_JOIN_AVAILABILITY
     */
    const string QUERY_SELECT_PROCESSLIST_DAYS_AVAILABILITY = '
        SELECT
            -- tmp_avail.*,
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

    const string GROUPBY_SELECT_PROCESSLIST_DAY = 'GROUP BY scope__id, appointments__0__date';

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
}
