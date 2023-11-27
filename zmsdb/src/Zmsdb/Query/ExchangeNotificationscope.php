<?php

namespace BO\Zmsdb\Query;

class ExchangeNotificationscope extends Base
{
    /**
     * @var String TABLE mysql table reference
     */
    const TABLE = 'abrechnung';

    const QUERY_READ_REPORT = '
      SELECT
          s.`StandortID` as subjectid,
          DATE_FORMAT(n.`Datum`, :groupby) as date,
          o.Organisationsname as organisationname,
          d.Name as departmentname,
          CONCAT(s.`Bezeichnung`, " ", s.`standortkuerzel`) AS scopename,
          IFNULL(SUM(n.gesendet), 0) as notificationscount
      FROM '. self::TABLE .' AS n
          LEFT JOIN '. Scope::TABLE .' s ON n.StandortID = s.StandortID
          LEFT JOIN '. Department::TABLE .' d ON d.BehoerdenID = s.BehoerdenID
          LEFT JOIN '. Organisation::TABLE .' o ON o.OrganisationsID = d.OrganisationsID
      WHERE n.`StandortID` = :scopeid AND n.`Datum` BETWEEN :datestart AND :dateend
      GROUP BY subjectid
    ';

    const QUERY_SUBJECTS = '
        SELECT
            s.`StandortID` as subject,
            MIN(n.`Datum`) AS periodstart,
            MAX(n.`Datum`) AS periodend,
            s.`Bezeichnung` AS description
        FROM '. self::TABLE .' AS n
            INNER JOIN '. Scope::TABLE .' AS s ON n.`standortid` = s.`StandortID`
        GROUP BY s.`StandortID`
        ORDER BY s.`StandortID` ASC
    ';

    const QUERY_PERIODLIST_MONTH = '
        SELECT
            DATE_FORMAT(n.datum,"%Y-%m") AS date
        FROM '. self::TABLE .' AS n
        WHERE n.`StandortID` = :scopeid
        GROUP BY date
        ORDER BY date ASC
    ';
}
