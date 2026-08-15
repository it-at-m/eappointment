<?php

namespace BO\Zmsbackend\Calendar\Repository;

class OverviewCalendar extends \BO\Zmsbackend\Query\Base
{
    const string TABLE = 'overview_calendar';

    const string INSERT_ONE = "
        INSERT INTO overview_calendar
            (scope_id, process_id, status, starts_at, ends_at)
        VALUES
            (:scope_id, :process_id, :status, :starts_at, :ends_at)
    ";

    const string CANCEL_BY_PROCESS = "
        UPDATE overview_calendar
           SET status     = 'cancelled',
               updated_at = CURRENT_TIMESTAMP
         WHERE process_id = :process_id
           AND status    <> 'cancelled'
";

    const string UPDATE_BY_PROCESS = "
        UPDATE overview_calendar
           SET scope_id   = :scope_id,
               starts_at  = :starts_at,
               ends_at    = :ends_at,
               updated_at = CURRENT_TIMESTAMP
         WHERE process_id = :process_id
           AND status     = 'confirmed'
    ";

    const string SELECT_MAX_UPDATED = "
        SELECT MAX(updated_at) AS max_updated
          FROM overview_calendar
         WHERE scope_id IN (%s)
           AND ends_at   > :from
           AND starts_at < :until
    ";

    const string SELECT_MAX_UPDATED_GLOBAL = "
        SELECT MAX(updated_at) AS max_updated
          FROM overview_calendar
         WHERE scope_id IN (%s)
    ";

    const string SELECT_RANGE = "
        SELECT b.id, b.scope_id, b.process_id, b.status,
               b.starts_at, b.ends_at, b.updated_at,
               p.displayNumber AS display_number,
               s.Bezeichnung   AS scope_name,
               s.standortkuerzel AS scope_short
          FROM overview_calendar b
          JOIN standort s ON b.scope_id = s.StandortID
          LEFT JOIN buerger p ON b.process_id = p.BuergerID
         WHERE b.scope_id IN (%s)
           AND b.ends_at   > :from
           AND b.starts_at < :until
           AND b.status = 'confirmed'
         ORDER BY b.scope_id, b.starts_at, b.ends_at, b.id
    ";

    const string SELECT_RANGE_UPDATED = "
        SELECT b.id, b.scope_id, b.process_id, b.status,
               b.starts_at, b.ends_at, b.updated_at,
               p.displayNumber AS display_number,
               s.Bezeichnung   AS scope_name,
               s.standortkuerzel AS scope_short
          FROM overview_calendar b
          JOIN standort s ON b.scope_id = s.StandortID
          LEFT JOIN buerger p ON b.process_id = p.BuergerID
         WHERE b.scope_id IN (%s)
           AND b.ends_at   > :from
           AND b.starts_at < :until
           AND b.updated_at > :updatedAfter
           AND b.status IN ('confirmed','cancelled')
         ORDER BY b.scope_id, b.starts_at, b.ends_at, b.id
    ";

    const string SELECT_CHANGED_PIDS_SINCE = "
        SELECT DISTINCT process_id
          FROM overview_calendar
         WHERE scope_id IN (%s)
           AND updated_at > :updatedAfter
    ";

    const string DELETE_ALL_BEFORE_END = "
        DELETE FROM overview_calendar
         WHERE ends_at < :threshold
    ";
}
