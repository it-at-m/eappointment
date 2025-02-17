<?php

namespace BO\Zmsdb\Query;

class ExchangeRequestorganisation extends Base
{
    /**
     * @var String TABLE mysql table reference
     */
    const TABLE = 'statistik';

    const REQUESTTABLE = 'request';

    const QUERY_READ_REPORT = '
    SELECT
        o.OrganisationsID as subjectid,
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
    FROM ' . Organisation::TABLE . ' o
        INNER JOIN (
            SELECT
                s.anliegenid,
                s.organisationsid,
                COUNT(s.anliegenid) as requestscount,
                AVG(s.bearbeitungszeit) as processingtime,
                s.`datum`
            FROM ' . self::TABLE . ' s
            WHERE s.organisationsid = :organisationid AND s.`datum` BETWEEN :datestart AND :dateend
            GROUP BY s.`datum`, s.anliegenid
        ) as statistikJoin ON statistikJoin.`organisationsid` = o.OrganisationsID
        LEFT JOIN ' . self::REQUESTTABLE . ' r ON r.id = statistikJoin.anliegenid
    WHERE o.`OrganisationsID` = :organisationid AND statistikJoin.`datum` BETWEEN :datestart AND :dateend
    GROUP BY DATE_FORMAT(statistikJoin.`datum`, :groupby), name, statistikJoin.anliegenid
    ORDER BY r.name, statistikJoin.anliegenid
    ';


    const QUERY_SUBJECTS = '
      SELECT
          o.`OrganisationsID` as subject,
          periodstart,
          periodend,
          o.`Organisationsname` AS description
      FROM ' . Organisation::TABLE . ' AS o
          INNER JOIN
            (
              SELECT
                s.`organisationsid` as organisationid,
                MIN(s.`datum`) AS periodstart,
                MAX(s.`datum`) AS periodend
              FROM ' . self::TABLE . ' s
              group by organisationid
            )
          maxAndminDate ON maxAndminDate.`organisationid` = o.`OrganisationsID`
      GROUP BY o.`OrganisationsID`
      ORDER BY o.`OrganisationsID` ASC
    ';

    const QUERY_PERIODLIST_MONTH = '
        SELECT date
        FROM ' . Organisation::TABLE . ' AS o
            INNER JOIN (
              SELECT
                organisationsid,
                DATE_FORMAT(`datum`,"%Y-%m") AS date
              FROM ' . self::TABLE . '
            ) s ON s.organisationsid = o.OrganisationsID
        WHERE s.`organisationsid` = :organisationid
        GROUP BY date
        ORDER BY date ASC
    ';
}
