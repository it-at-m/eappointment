<?php

namespace BO\Zmsdb\Query;

/**
 *
 */
class ProcessStatusFree extends Base
{
    /**
     * see also Day::QUERY_DAYLIST_JOIN
     */
    const QUERY_SELECT_PROCESSLIST_DAYS = '
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
                IFNULL(COUNT(p.slotID), 0) confirmed,
                IF(:slotType = "intern", s.intern,
                  IF(:slotType = "callcenter", s.callcenter,
                    IF (:slotType = "public", s.`public`, 0 )
                  )
                ) available,
                IF(a.erlaubemehrfachslots, c.slotsRequired, :forceRequiredSlots) slotsRequired,
                s.*
            FROM
                calendarscope c
                INNER JOIN slot s
                    ON c.scopeID = s.scopeID
                        %s
                        AND s.status = "free"
                LEFT JOIN oeffnungszeit a ON s.availabilityID = a.OeffnungszeitID
                LEFT JOIN slot_hiera h ON h.ancestorID = s.slotID
                    AND h.ancestorLevel <= IF(a.erlaubemehrfachslots, c.slotsRequired, :forceRequiredSlots)
                INNER JOIN slot s2 on h.slotID = s2.slotID and s2.status = "free"
                LEFT JOIN slot_process p ON h.slotID = p.slotID
            GROUP BY s.slotID, h.slotID
            ) AS tmp_ancestor
            GROUP BY slotID
            HAVING ancestorCount >= slotsRequired
            ) AS tmp_avail 
            INNER JOIN slot_sequence sq ON sq.slotsequence <= tmp_avail.free
    ';
    const GROUPBY_SELECT_PROCESSLIST_DAY = 'GROUP BY scope__id, appointments__0__date';

    public static function buildDaysCondition($days)
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
