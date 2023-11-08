<?php

namespace BO\Zmsdb\Query;

class ExchangeRequestdepartment extends Base
{
    /**
     * @var String TABLE mysql table reference
     */
    const TABLE = 'statistik';

    const REQUESTTABLE = 'request';

    const QUERY_READ_REPORT = '
    SELECT
        d.BehoerdenID as departmentid,
        statistikJoin.`organisationsid` as organisationid,
        DATE_FORMAT(statistikJoin.`datum`, :groupby) as date,
        (
            CASE
              WHEN statistikJoin.anliegenid = -1 THEN "Dienstleistung wurde nicht erfasst"
              WHEN statistikJoin.anliegenid = 0 THEN "Dienstleistung konnte nicht erbracht werden"
              ELSE r.name
            END
        ) as name,
        SUM(statistikJoin.requestscount) as requestscount
 FROM '. Department::TABLE .' d
        INNER JOIN (
          SELECT
            s.anliegenid,
            s.behoerdenid,
            s.organisationsid,
        COUNT(s.anliegenid) as requestscount,
        s.`datum`
      FROM '. self::TABLE .' s
      WHERE s.behoerdenid = :departmentid AND s.`datum` BETWEEN :datestart AND :dateend
      GROUP BY s.`datum`, s.anliegenid
        ) as statistikJoin ON statistikJoin.`behoerdenid` = d.BehoerdenID
        LEFT JOIN '. self::REQUESTTABLE .' r ON r.id = statistikJoin.anliegenid
    WHERE d.`behoerdenid` = :departmentid AND statistikJoin.`datum` BETWEEN :datestart AND :dateend
    GROUP BY DATE_FORMAT(statistikJoin.`datum`, :groupby), name, statistikJoin.anliegenid
    ORDER BY r.name, statistikJoin.anliegenid
    ';

    const QUERY_SUBJECTS = '
      SELECT
          d.`BehoerdenID` as subject,
          periodstart,
          periodend,
          o.`Organisationsname` AS organisationname,
          d.`Name` AS description
      FROM '. Department::TABLE .' AS d
          INNER JOIN
            (
              SELECT
                s.`behoerdenid` as departmentid,
                MIN(s.`datum`) AS periodstart,
                MAX(s.`datum`) AS periodend
              FROM '. self::TABLE .' s
              group by departmentid
            )
          maxAndminDate ON maxAndminDate.`departmentid` = d.`BehoerdenID`
          LEFT JOIN ' . Organisation::TABLE .' AS o ON d.`OrganisationsID` = o.`OrganisationsID`
      GROUP BY d.`BehoerdenID`
      ORDER BY d.`BehoerdenID` ASC
    ';

    const QUERY_PERIODLIST_MONTH = '
        SELECT date
        FROM '. Department::TABLE .' AS d
            INNER JOIN (
              SELECT
                behoerdenid,
                DATE_FORMAT(`datum`,"%Y-%m") AS date
              FROM '. self::TABLE .'
            ) s ON s.behoerdenid = d.BehoerdenID
        WHERE d.`BehoerdenID` = :departmentid
        GROUP BY date
        ORDER BY date ASC
    ';
}
