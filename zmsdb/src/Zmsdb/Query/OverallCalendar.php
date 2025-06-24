<?php

namespace BO\Zmsdb\Query;

class OverallCalendar extends Base
{
    const TABLE = 'gesamtkalender';

    const INSERT_MULTI = '
        INSERT IGNORE INTO gesamtkalender
            (scope_id, availability_id, time, seat, status)
        VALUES %s
    ';

    const DELETE_FREE_RANGE = '
        DELETE FROM gesamtkalender
         WHERE scope_id = :scope_id
           AND availability_id = :availability_id  
           AND status   = "free"
           AND time    >= :begin
           AND time    <  :finish
    ';

    const DELETE_ALL_BEFORE = '
        DELETE FROM gesamtkalender
         WHERE time < :threshold
    ';

    const FIND_FREE_SEAT = '
        SELECT seat
          FROM gesamtkalender
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
        UPDATE gesamtkalender
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
        UPDATE gesamtkalender
           SET process_id = NULL,
               slots      = NULL,
               status     = "free"
         WHERE scope_id   = :scope_id
           AND process_id = :process_id
    ';

    const SELECT_RANGE = '
        SELECT g.scope_id, g.time, g.availability_id, g.seat, g.status, g.process_id, g.slots, g.updated_at, 
               s.Bezeichnung as scope_name, s.standortkuerzel as scope_short
          FROM gesamtkalender g
          JOIN standort s ON g.scope_id = s.StandortID
         WHERE g.scope_id IN (%s)            
           AND g.time BETWEEN :from AND :until
         ORDER BY g.scope_id, g.time, g.seat
    ';

    const SELECT_RANGE_UPDATED = '
        SELECT g.scope_id, g.time, g.availability_id, g.seat, g.status, g.process_id, g.slots, g.updated_at, 
               s.Bezeichnung as scope_name, s.standortkuerzel as scope_short
          FROM gesamtkalender g
          JOIN standort s ON g.scope_id = s.StandortID
         WHERE g.scope_id IN (%s)
           AND g.time BETWEEN :from AND :until
           AND g.updated_at > :updatedAfter
         ORDER BY g.scope_id, g.time, g.seat
    ';
}
