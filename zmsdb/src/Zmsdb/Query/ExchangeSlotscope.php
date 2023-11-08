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

    const QUERY_SUBJECTS = '
      SELECT
          scope.`StandortID` as subject,
          MIN(CONCAT(year, "-", LPAD(month, 2, 0), "-", LPAD(day, 2, 0))) AS periodstart,
          MAX(CONCAT(year, "-", LPAD(month, 2, 0), "-", LPAD(day, 2, 0))) AS periodend,
          CONCAT(scope.`Bezeichnung`, " ", scope.`standortinfozeile`) AS description
      FROM '. Scope::TABLE .' AS scope
        LEFT JOIN slot AS s ON s.scopeID = scope.StandortID
        LEFT JOIN slot_process AS sp USING(slotID)
      WHERE sp.slotID IS NOT NULL
      GROUP BY scope.`StandortID`
      ORDER BY description ASC
    ';
}
