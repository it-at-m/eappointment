<?php
namespace BO\Zmsdb\Query;

class OverallCalendar extends Base
{
    const TABLE = 'gesamtkalender';

    const INSERT = '
        INSERT IGNORE INTO gesamtkalender
               (scope_id, time, status)
        VALUES (:scope_id, :time, :status)
    ';

    const UPDATE_STATUS = '
        UPDATE gesamtkalender
           SET status = :status
         WHERE scope_id = :scope_id
           AND time >= :begin
           AND time <  :finish
           AND status IN ("closed", "free")
    ';

    const RESET_RANGE = '
        UPDATE gesamtkalender
           SET status = "closed"
         WHERE scope_id = :scope_id
           AND time >= :begin
           AND time <  :finish
           AND status IN ("closed", "free")
    ';

    const EXISTS_TODAY = '
        SELECT 1
          FROM gesamtkalender
         WHERE scope_id = :scope_id
           AND DATE(time) = CURDATE()
         LIMIT 1
    ';

    const UPDATE_TO_BOOKED = '
        UPDATE gesamtkalender
           SET process_id = :pid,
               slots      = :slots,
               status     = "termin"
         WHERE scope_id = :scope
           AND time     = :time
         LIMIT 1
    ';

    const UPDATE_FOLLOWING_SLOTS = '
    UPDATE gesamtkalender
       SET process_id = :pid,
           slots      = NULL,
           status     = "termin"
     WHERE scope_id = :scope
       AND time >= :start
       AND time <  :end
    ';

    const UNBOOK_PROCESS = '
    UPDATE gesamtkalender
       SET process_id = NULL,
           slots      = NULL,
           status     = "free"
     WHERE scope_id   = :scope_id
       AND process_id = :process_id
    ';

}
