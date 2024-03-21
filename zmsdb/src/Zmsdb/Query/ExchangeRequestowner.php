<?php

namespace BO\Zmsdb\Query;

class ExchangeRequestowner extends Base
{
    /**
     * @var String TABLE mysql table reference
     */
    const TABLE = 'statistik';

    const REQUESTTABLE = 'request';

    const QUERY_READ_REPORT = '
    SELECT
        o.KundenID as subjectid,
        DATE_FORMAT(statistikJoin.`datum`, :groupby) as date,
        (
            CASE
              WHEN statistikJoin.anliegenid = -1 THEN "Dienstleistung wurde nicht erfasst"
              WHEN statistikJoin.anliegenid = 0 THEN "Dienstleistung konnte nicht erbracht werden"
              ELSE r.name
            END
        ) as name,
        SUM(statistikJoin.requestscount) as requestscount,
        AVG(statistikJoin.processingtime) as processingtime
    FROM '. Organisation::TABLE .' o
        INNER JOIN (
            SELECT
                s.anliegenid,
                s.kundenid,
                COUNT(s.anliegenid) as requestscount,
                AVG(s.bearbeitungszeit) as processingtime,
                s.`datum`
            FROM '. self::TABLE .' s
            WHERE s.kundenid = :ownerid AND s.`datum` BETWEEN :datestart AND :dateend
            GROUP BY s.`datum`, s.anliegenid
        ) as statistikJoin ON statistikJoin.`kundenid` = o.KundenID
        LEFT JOIN '. self::REQUESTTABLE .' r ON r.id = statistikJoin.anliegenid
    WHERE o.`KundenID` = :ownerid AND statistikJoin.`datum` BETWEEN :datestart AND :dateend
    GROUP BY DATE_FORMAT(statistikJoin.`datum`, :groupby), name, statistikJoin.anliegenid
    ORDER BY r.name, statistikJoin.anliegenid
    ';    

    const QUERY_SUBJECTS = '
      SELECT
          o.`KundenID` as subject,
          periodstart,
          periodend,
          o.`Organisationsname` AS description
      FROM '. Organisation::TABLE .' AS o
          INNER JOIN
            (
              SELECT
                s.`kundenid` as ownerid,
                MIN(s.`datum`) AS periodstart,
                MAX(s.`datum`) AS periodend
              FROM '. self::TABLE .' s
              group by ownerid
            )
          maxAndminDate ON maxAndminDate.`ownerid` = o.`KundenID`
      GROUP BY o.`KundenID`
      ORDER BY o.`KundenID` ASC
    ';

    const QUERY_PERIODLIST_MONTH = '
        SELECT date
        FROM '. Organisation::TABLE .' AS o
            INNER JOIN (
              SELECT
                kundenid,
                DATE_FORMAT(`datum`,"%Y-%m") AS date
              FROM '. self::TABLE .'
            ) s ON s.kundenid = o.KundenID
        WHERE s.`kundenid` = :ownerid
        GROUP BY date
        ORDER BY date ASC
    ';
}
