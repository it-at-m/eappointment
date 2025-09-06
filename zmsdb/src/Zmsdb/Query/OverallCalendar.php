<?php

namespace BO\Zmsdb\Query;

class OverallCalendar extends Base
{
    const TABLE = 'overall_calendar';

    const UPSERT_MULTI = '
        INSERT INTO overall_calendar
               (scope_id, availability_id, time, seat, status)
        VALUES %s
        ON DUPLICATE KEY UPDATE
            status = CASE
                       WHEN status = "termin" THEN "termin"
                       ELSE VALUES(status)
                     END,
        
            availability_id = CASE
                                WHEN status = "termin"
                                  THEN availability_id
                                ELSE VALUES(availability_id)
                              END,
        
            updated_at = CURRENT_TIMESTAMP
        ';

    const CANCEL_AVAILABILITY = '
        UPDATE overall_calendar
           SET status         = "cancelled",
               availability_id= NULL,
               updated_at     = CURRENT_TIMESTAMP
         WHERE scope_id       = :scope_id
           AND availability_id= :availability_id
           AND status         = "free"
    ';

    const PURGE_MISSING_AVAIL_BY_SCOPE = '
        UPDATE overall_calendar g
           LEFT JOIN availability a
                  ON g.availability_id = a.OeffnungszeitID
           SET g.status         = "cancelled",
               g.availability_id= NULL,
               g.updated_at     = CURRENT_TIMESTAMP
         WHERE ( a.OeffnungszeitID IS NULL
                 OR a.Endedatum      < :dateString )
           AND g.scope_id   = :scopeID
           AND g.status    <> "termin"
    ';


    const DELETE_ALL_BEFORE = '
        DELETE FROM overall_calendar
         WHERE time < :threshold
    ';

    const FIND_FREE_SEAT = '
        SELECT seat
          FROM overall_calendar
         WHERE scope_id = :scope
           AND time >= :start
           AND time <  :end
           AND status = "free"
         GROUP BY seat
        HAVING COUNT(*) = :units
         ORDER BY seat
         LIMIT 1
    ';

    const BLOCK_SEAT_RANGE = '
        UPDATE overall_calendar
           SET process_id = :pid,
               slots      = CASE
                              WHEN time = :start THEN :units
                              ELSE NULL
                            END,
               status     = "termin"
         WHERE scope_id = :scope
           AND seat     = :seat
           AND time    >= :start
           AND time    <  :end
    ';

    const UNBOOK_PROCESS = '
        UPDATE overall_calendar g
        LEFT  JOIN availability a
               ON g.availability_id = a.OeffnungszeitID
           SET g.process_id  = NULL,
               g.slots       = NULL,
               g.status      = CASE
                                 WHEN a.OeffnungszeitID IS NULL
                                      OR a.Endedatum < CURDATE()
                                    THEN "cancelled"
                                WHEN g.seat > IFNULL(a.Anzahlterminarbeitsplaetze,1)
                                    THEN "cancelled"
                                 ELSE "free"
                               END,
               g.updated_at  = CURRENT_TIMESTAMP
         WHERE g.scope_id    = :scope_id
           AND g.process_id  = :process_id
    ';

    const SELECT_RANGE = '
        SELECT g.scope_id, g.time, g.availability_id, g.seat, g.status, g.process_id, g.slots, g.updated_at, 
               s.Bezeichnung as scope_name, s.standortkuerzel as scope_short
          FROM overall_calendar g
          JOIN scope s ON g.scope_id = s.StandortID
         WHERE g.scope_id IN (%s)            
           AND g.time BETWEEN :from AND :until
         ORDER BY g.scope_id, g.time, g.seat
    ';

    const SELECT_RANGE_UPDATED = '
        SELECT g.scope_id, g.time, g.availability_id, g.seat, g.status, g.process_id, g.slots, g.updated_at, 
               s.Bezeichnung as scope_name, s.standortkuerzel as scope_short
          FROM overall_calendar g
          JOIN scope s ON g.scope_id = s.StandortID
         WHERE g.scope_id IN (%s)
           AND g.time BETWEEN :from AND :until
           AND g.updated_at > :updatedAfter
         ORDER BY g.scope_id, g.time, g.seat
    ';
}
