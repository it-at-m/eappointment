<?php

namespace BO\Zmsdb\Query;

/**
 *
 * Calculate Slots for available booking times
 */
class Calendar extends Base
{

    const QUERY_CALENDAR_BOOKABLEEND = "
        SELECT 
            MAX(CONCAT(slot.year, '-', LPAD(slot.month, 2, '0'), '-', LPAD(slot.day, 2, '0'))) as bookableEnd
        FROM
            calendarscope c
            LEFT JOIN slot USING(scopeID)
        WHERE
            slot.status = 'free'
        ;
    ";
}
