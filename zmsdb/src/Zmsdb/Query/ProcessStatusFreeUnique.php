<?php

namespace BO\Zmsdb\Query;

/**
 * Query class for getting unique free slots
 */
class ProcessStatusFreeUnique extends Base
{
    const QUERY_SELECT_PROCESSLIST_DAYS = '
        SELECT DISTINCT
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
                    AND h.ancestorLevel <= IF(a.erlaubemehrfachslots, c.slotsRequired, :forceRequiredSlots)
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

    const GROUPBY_SELECT_PROCESSLIST_DAY = 'GROUP BY scope__id, appointments__0__date';

    public static function buildDaysCondition($days)
    {
        $condition = '';
        if (is_array($days)) {
            $condition = 'AND (';
            $dayConditions = [];
            foreach ($days as $day) {
                $dayConditions[] = sprintf(
                    '(s.year = %d AND s.month = %d AND s.day = %d)',
                    $day->format('Y'),
                    $day->format('n'),
                    $day->format('j')
                );
            }
            $condition .= implode(' OR ', $dayConditions) . ')';
        }
        return $condition;
    }
} 