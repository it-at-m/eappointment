<?php

namespace BO\Zmsdb\Query;

class ExchangeSlotscope extends Base
{
    /**
     * @var String TABLE mysql table reference
     */
    const TABLE = 'slot_process';

    const QUERY_READ_REPORT = '
    SELECT
        `scopeID` as subjectid,
        CONCAT(year, "-", LPAD(month, 2, 0), "-", LPAD(day, 2, 0)) as date,
        SUM(slotcount),
        SUM(intern)
    FROM (
        SELECT s.*, COUNT(sp.slotID) as slotcount
        FROM slot AS s
          LEFT JOIN slot_process as sp USING(slotID)
        WHERE s.`scopeID` = :scopeid AND status = "free"
        GROUP BY s.slotID
        ) AS innerquery
    GROUP BY year, month, day
    ORDER BY date ASC
    ';

    const QUERY_READ_REPORT_FILTERED = '
    SELECT
        `scopeID` as subjectid,
        CONCAT(year, "-", LPAD(month, 2, 0), "-", LPAD(day, 2, 0)) as date,
        SUM(slotcount),
        SUM(intern)
    FROM (
        SELECT s.*, COUNT(sp.slotID) as slotcount
        FROM slot AS s
          LEFT JOIN slot_process as sp USING(slotID)
        WHERE s.`scopeID` = :scopeid AND status = "free"
          AND CONCAT(s.year, "-", LPAD(s.month, 2, 0), "-", LPAD(s.day, 2, 0))
              BETWEEN :datestart AND :dateend
        GROUP BY s.slotID
        ) AS innerquery
    GROUP BY year, month, day
    ORDER BY date ASC
    ';

    /**
     * Buckets by clock hour (HOUR(time)), not by slot grid length.
     * Each slot row is counted once; slotTimeInMinutes may differ per scope or availability.
     */
    const QUERY_READ_REPORT_HOURLY = '
    SELECT
        `scopeID` as subjectid,
        CONCAT(year, "-", LPAD(month, 2, 0), "-", LPAD(day, 2, 0), " ", LPAD(HOUR(`time`), 2, "0"), ":00") as date,
        SUM(slotcount),
        SUM(intern)
    FROM (
        SELECT s.scopeID, s.year, s.month, s.day, s.time, s.intern, COUNT(sp.slotID) as slotcount
        FROM slot AS s
          LEFT JOIN slot_process as sp USING(slotID)
        WHERE s.`scopeID` = :scopeid AND s.status = "free"
          AND CONCAT(s.year, "-", LPAD(s.month, 2, 0), "-", LPAD(s.day, 2, 0))
              BETWEEN :datestart AND :dateend
        GROUP BY s.slotID
        ) AS innerquery
    GROUP BY year, month, day, HOUR(`time`)
    ORDER BY date ASC
    ';

    /**
     * Date bounds from all generated slots (incl. future planned), not only booked ones.
     */
    const QUERY_SUBJECTS = '
      SELECT
          scope.`StandortID` as subject,
          MIN(CONCAT(s.year, "-", LPAD(s.month, 2, 0), "-", LPAD(s.day, 2, 0))) AS periodstart,
          MAX(CONCAT(s.year, "-", LPAD(s.month, 2, 0), "-", LPAD(s.day, 2, 0))) AS periodend,
          CONCAT(scope.`Bezeichnung`, " ", scope.`standortinfozeile`) AS description
      FROM ' . Scope::TABLE . ' AS scope
        INNER JOIN slot AS s ON s.scopeID = scope.StandortID
      GROUP BY scope.`StandortID`
      ORDER BY description ASC
    ';
}
