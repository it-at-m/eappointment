<?php

namespace BO\Zmsbackend\Exchange\Repository;

class ExchangeCapacityscope extends \BO\Zmsbackend\Query\Base
{
    /**
     * @var String TABLE mysql table reference
     */
    const TABLE = 'slot_process';

    /**
     * Time-series metrics for one scope: booked/planned capacity per day, all dates.
     */
    const QUERY_CAPACITY_METRICS_BY_DAY_ALL_DATES = '
    SELECT
        `scopeID` as subjectid,
        CONCAT(year, "-", LPAD(month, 2, 0), "-", LPAD(day, 2, 0)) as date,
        SUM(slotcount),
        SUM(intern),
        SUM(slotbookedminutes),
        SUM(slotplannedminutes),
        SUM(slotcount_public),
        SUM(public),
        SUM(slotbookedminutes_public),
        SUM(slotplannedminutes_public)
    FROM (
        SELECT s.scopeID, s.year, s.month, s.day, s.intern, s.public, s.slotTimeInMinutes,
               COUNT(sp.slotID) as slotcount,
               SUM(CASE WHEN ac.accesslevel = "public" THEN 1 ELSE 0 END) as slotcount_public,
               (COALESCE(s.intern, 0) * COALESCE(s.slotTimeInMinutes, 0)) as slotplannedminutes,
               (COUNT(sp.slotID) * COALESCE(s.slotTimeInMinutes, 0)) as slotbookedminutes,
               (COALESCE(s.public, 0) * COALESCE(s.slotTimeInMinutes, 0)) as slotplannedminutes_public,
               (SUM(CASE WHEN ac.accesslevel = "public" THEN 1 ELSE 0 END) * COALESCE(s.slotTimeInMinutes, 0)) as slotbookedminutes_public
        FROM slot AS s
          LEFT JOIN slot_process as sp USING(slotID)
          LEFT JOIN buerger b ON sp.processID = b.BuergerID
          LEFT JOIN apiclient ac ON b.apiClientID = ac.apiClientID
        WHERE s.`scopeID` = :scopeid AND s.status = "free"
        GROUP BY s.slotID
        ) AS innerquery
    GROUP BY year, month, day
    ORDER BY date ASC
    ';

    /**
     * Time-series metrics for one scope: booked/planned capacity per day, within a date range.
     */
    const QUERY_CAPACITY_METRICS_BY_DAY_IN_DATE_RANGE = '
    SELECT
        `scopeID` as subjectid,
        CONCAT(year, "-", LPAD(month, 2, 0), "-", LPAD(day, 2, 0)) as date,
        SUM(slotcount),
        SUM(intern),
        SUM(slotbookedminutes),
        SUM(slotplannedminutes),
        SUM(slotcount_public),
        SUM(public),
        SUM(slotbookedminutes_public),
        SUM(slotplannedminutes_public)
    FROM (
        SELECT s.scopeID, s.year, s.month, s.day, s.intern, s.public, s.slotTimeInMinutes,
               COUNT(sp.slotID) as slotcount,
               SUM(CASE WHEN ac.accesslevel = "public" THEN 1 ELSE 0 END) as slotcount_public,
               (COALESCE(s.intern, 0) * COALESCE(s.slotTimeInMinutes, 0)) as slotplannedminutes,
               (COUNT(sp.slotID) * COALESCE(s.slotTimeInMinutes, 0)) as slotbookedminutes,
               (COALESCE(s.public, 0) * COALESCE(s.slotTimeInMinutes, 0)) as slotplannedminutes_public,
               (SUM(CASE WHEN ac.accesslevel = "public" THEN 1 ELSE 0 END) * COALESCE(s.slotTimeInMinutes, 0)) as slotbookedminutes_public
        FROM slot AS s
          LEFT JOIN slot_process as sp USING(slotID)
          LEFT JOIN buerger b ON sp.processID = b.BuergerID
          LEFT JOIN apiclient ac ON b.apiClientID = ac.apiClientID
        WHERE s.`scopeID` = :scopeid AND s.status = "free"
          AND CONCAT(s.year, "-", LPAD(s.month, 2, 0), "-", LPAD(s.day, 2, 0))
              BETWEEN :datestart AND :dateend
        GROUP BY s.slotID
        ) AS innerquery
    GROUP BY year, month, day
    ORDER BY date ASC
    ';

    /**
     * Time-series metrics for one scope: booked/planned capacity per clock hour, within a date range.
     */
    const QUERY_CAPACITY_METRICS_BY_HOUR_IN_DATE_RANGE = '
    SELECT
        `scopeID` as subjectid,
        CONCAT(year, "-", LPAD(month, 2, 0), "-", LPAD(day, 2, 0), " ", LPAD(HOUR(`time`), 2, "0"), ":00") as date,
        SUM(slotcount),
        SUM(intern),
        SUM(slotbookedminutes),
        SUM(slotplannedminutes),
        SUM(slotcount_public),
        SUM(public),
        SUM(slotbookedminutes_public),
        SUM(slotplannedminutes_public)
    FROM (
        SELECT s.scopeID, s.year, s.month, s.day, s.time, s.intern, s.public, s.slotTimeInMinutes,
               COUNT(sp.slotID) as slotcount,
               SUM(CASE WHEN ac.accesslevel = "public" THEN 1 ELSE 0 END) as slotcount_public,
               (COALESCE(s.intern, 0) * COALESCE(s.slotTimeInMinutes, 0)) as slotplannedminutes,
               (COUNT(sp.slotID) * COALESCE(s.slotTimeInMinutes, 0)) as slotbookedminutes,
               (COALESCE(s.public, 0) * COALESCE(s.slotTimeInMinutes, 0)) as slotplannedminutes_public,
               (SUM(CASE WHEN ac.accesslevel = "public" THEN 1 ELSE 0 END) * COALESCE(s.slotTimeInMinutes, 0)) as slotbookedminutes_public
        FROM slot AS s
          LEFT JOIN slot_process as sp USING(slotID)
          LEFT JOIN buerger b ON sp.processID = b.BuergerID
          LEFT JOIN apiclient ac ON b.apiClientID = ac.apiClientID
        WHERE s.`scopeID` = :scopeid AND s.status = "free"
          AND CONCAT(s.year, "-", LPAD(s.month, 2, 0), "-", LPAD(s.day, 2, 0))
              BETWEEN :datestart AND :dateend
        GROUP BY s.slotID
        ) AS innerquery
    GROUP BY year, month, day, HOUR(`time`)
    ORDER BY date ASC
    ';

    /**
     * Scope picker list: one row per scope with min/max slot dates and description (no capacity numbers).
     */
    const QUERY_CAPACITY_REPORT_SCOPE_SUBJECT_LIST = '
      SELECT
          scope.`StandortID` as subject,
          MIN(CONCAT(s.year, "-", LPAD(s.month, 2, 0), "-", LPAD(s.day, 2, 0))) AS periodstart,
          MAX(CONCAT(s.year, "-", LPAD(s.month, 2, 0), "-", LPAD(s.day, 2, 0))) AS periodend,
          CONCAT(scope.`Bezeichnung`, " ", scope.`standortinfozeile`) AS description
      FROM ' . \BO\Zmsbackend\Query\Scope::TABLE . ' AS scope
        INNER JOIN slot AS s ON s.scopeID = scope.StandortID
      GROUP BY scope.`StandortID`
      ORDER BY description ASC
    ';
}
