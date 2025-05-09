<?php

namespace BO\Zmsdb\Query;

class OverallCalendar extends Base
{
    const TABLE = 'gesamtkalender';

    const INSERT = '
        INSERT IGNORE INTO gesamtkalender
               (scope_id, time, seat, status)
        VALUES (:scope_id, :time, :seat, :status)
    ';

    const DELETE_FREE_RANGE = '
        DELETE FROM gesamtkalender
         WHERE scope_id = :scope_id
           AND status   = "free"
           AND time    >= :begin
           AND time    <  :finish
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
        SELECT scope_id, time, seat, status, process_id, slots, updated_at
          FROM gesamtkalender
         WHERE scope_id IN (%s)            
           AND time BETWEEN :from AND :until
         ORDER BY scope_id, time, seat
    ';

    const SELECT_RANGE_UPDATED = '
        SELECT scope_id, time, seat, status, process_id, slots, updated_at
          FROM gesamtkalender
         WHERE scope_id IN (%s)
           AND time BETWEEN :from AND :until
           AND updated_at > :updatedAfter
         ORDER BY scope_id, time, seat
    ';
}
