<?php

namespace BO\Zmsdb\Query;

class ExchangeRequestscope extends Base
{
    /**
     * @var String TABLE mysql table reference
     */
    const TABLE = 'statistik';

    const REQUESTTABLE = 'request';

    const QUERY_READ_REPORT = '
    SELECT
        s.`standortid` as scopeid,
        s.`behoerdenid` as departmentid,
        s.`organisationsid` as organisationid,
        DATE_FORMAT(s.`datum`, :groupby) as date,
        (
            CASE
              WHEN s.anliegenid = -1 THEN "Dienstleistung wurde nicht erfasst"
                      WHEN s.anliegenid = 0 THEN "Dienstleistung konnte nicht erbracht werden"
                      ELSE r.name
                END
            ) as name,
        COUNT(s.anliegenid) as requestscount,
        AVG(s.bearbeitungszeit) as procssingtime
    FROM '. self::TABLE .' AS s
        LEFT JOIN '. self::REQUESTTABLE .' as r ON r.id = s.anliegenid
    WHERE s.`standortid` = :scopeid AND s.`Datum` BETWEEN :datestart AND :dateend
    GROUP BY DATE_FORMAT(s.`datum`, :groupby), s.anliegenid
    ORDER BY r.name, s.anliegenid
    ';

    const QUERY_SUBJECTS = '
      SELECT
          scope.`StandortID` as subject,
          periodstart,
          periodend,
          CONCAT(scope.`Bezeichnung`, " ", scope.`standortinfozeile`) AS description
      FROM '. Scope::TABLE .' AS scope
          INNER JOIN
            (
              SELECT
                s.standortid as scopeid,
                MIN(s.`datum`) AS periodstart,
                MAX(s.`datum`) AS periodend
              FROM '. self::TABLE .' s
              group by scopeid
            )
          maxAndminDate ON maxAndminDate.`scopeid` = scope.`StandortID`
      GROUP BY scope.`StandortID`
      ORDER BY scope.`StandortID` ASC
    ';

    const QUERY_PERIODLIST_MONTH = '
        SELECT date
        FROM '. Scope::TABLE .' AS scope
            INNER JOIN (
              SELECT
                `StandortID`,
                DATE_FORMAT(`Datum`,"%Y-%m") AS date
              FROM '. self::TABLE .'
            ) s ON scope.`StandortID` = s.`standortid`
        WHERE scope.`StandortID` = :scopeid
        GROUP BY date
        ORDER BY date ASC
    ';
}
