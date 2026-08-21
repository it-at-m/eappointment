<?php

namespace BO\Zmsbackend\Exchange\Repository;

use BO\Zmsentities\Exchange;

class ExchangeRequestscope extends \BO\Zmsbackend\Query\Base
{
    /**
     * @var String TABLE mysql table reference
     */
    const string TABLE = 'statistik';

    const string REQUESTTABLE = 'request';

    /**
     * Aggregate raw statistik rows by day and request first so the request
     * name join and DATE_FORMAT grouping run on a small result, not every fact row.
     * Inner GROUP BY uses the (standortid, datum) index; outer DATE_FORMAT is cheap.
     */
    const QUERY_READ_REPORT = '
    SELECT
        MIN(agg.standortid) as scopeid,
        MIN(agg.behoerdenid) as departmentid,
        MIN(agg.organisationsid) as organisationid,
        DATE_FORMAT(agg.datum, :groupby) as date,
        (
            CASE
              WHEN agg.anliegenid = -1 THEN \'' . Exchange::REQUEST_STAT_NAME_UNCATEGORIZED . '\'
              WHEN agg.anliegenid = 0 THEN \'' . Exchange::REQUEST_STAT_NAME_NONEXISTENT . '\'
              ELSE r.name
            END
        ) as name,
        SUM(agg.requestscount) as requestscount,
        SUM(agg.processingtime * agg.requestscount) / NULLIF(SUM(agg.requestscount), 0) as processingtime
    FROM (
        SELECT
            s.anliegenid,
            MIN(s.standortid) as standortid,
            MIN(s.behoerdenid) as behoerdenid,
            MIN(s.organisationsid) as organisationsid,
            COUNT(*) as requestscount,
            AVG(s.processing_time) as processingtime,
            s.datum
        FROM ' . self::TABLE . ' AS s
        WHERE s.standortid IN (:scopeid) AND s.datum BETWEEN :datestart AND :dateend
        GROUP BY s.datum, s.anliegenid
    ) as agg
        LEFT JOIN ' . self::REQUESTTABLE . ' as r ON r.id = agg.anliegenid
    GROUP BY date, name, agg.anliegenid
    ORDER BY name
    ';

    const QUERY_SUBJECTS = '
      SELECT
          scope.`StandortID` as subject,
          periodstart,
          periodend,
          CONCAT(scope.`Bezeichnung`, " ", scope.`standortinfozeile`) AS description
      FROM ' . \BO\Zmsbackend\Query\Scope::TABLE . ' AS scope
          INNER JOIN
            (
              SELECT
                s.standortid as scopeid,
                MIN(s.`datum`) AS periodstart,
                MAX(s.`datum`) AS periodend
              FROM ' . self::TABLE . ' s
              group by scopeid
            )
          maxAndminDate ON maxAndminDate.`scopeid` = scope.`StandortID`
      GROUP BY scope.`StandortID`
      ORDER BY scope.`StandortID` ASC
    ';

    const QUERY_PERIODLIST_MONTH = '
        SELECT DATE_FORMAT(`datum`, "%Y-%m") AS date
        FROM ' . self::TABLE . '
        WHERE standortid = :scopeid
        GROUP BY date
        ORDER BY date ASC
    ';
}
