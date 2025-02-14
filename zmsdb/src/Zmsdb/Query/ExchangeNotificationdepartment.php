<?php

namespace BO\Zmsdb\Query;

class ExchangeNotificationdepartment extends Base
{
    /**
     * @var String TABLE mysql table reference
     */
    const TABLE = 'abrechnung';

    const QUERY_READ_REPORT = '
        SELECT
            d.`BehoerdenID` as subjectid,
            DATE_FORMAT(n.`Datum`, :groupby) as date,
            o.`Organisationsname` as organisationname,
            d.`Name` as departmentname,
            CONCAT(s.`Bezeichnung`, " ", s.`standortkuerzel`) AS scopename,
            IFNULL(SUM(n.`gesendet`), 0) as notificationscount
        FROM ' . Department::TABLE . ' AS d
            LEFT JOIN ' . Organisation::TABLE . ' o ON o.`OrganisationsID` = d.`OrganisationsID`
            LEFT JOIN ' . Scope::TABLE . ' s ON d.`BehoerdenID` = s.`BehoerdenID`
            LEFT JOIN
                ' . self::TABLE . ' n ON s.`StandortID` = n.`StandortID` AND n.`Datum` BETWEEN :datestart AND :dateend
        WHERE
            s.`BehoerdenID` = :departmentid 
        GROUP BY scopename
        ORDER BY date, scopename
    ';

    const QUERY_SUBJECTS = '
      SELECT
          d.`BehoerdenID` as subject,
          MIN(n.`Datum`) AS periodstart,
          MAX(n.`Datum`) AS periodend,
          o.`Organisationsname` AS organisationname,
          d.`Name` AS description
      FROM ' . self::TABLE . ' AS n
          LEFT JOIN ' . Scope::TABLE . ' AS s ON n.`standortid` = s.`StandortID`
          INNER JOIN ' . Department::TABLE . ' AS d ON s.`BehoerdenID` = d.`BehoerdenID`
          LEFT JOIN ' . Organisation::TABLE . ' AS o ON d.`OrganisationsID` = o.`OrganisationsID`
      GROUP BY d.`BehoerdenID`
      ORDER BY d.`BehoerdenID` ASC
    ';

    const QUERY_PERIODLIST_MONTH = '
        SELECT
            DATE_FORMAT(n.datum,"%Y-%m") AS date
        FROM ' . self::TABLE . ' AS n
            LEFT JOIN ' . Scope::TABLE . ' AS s ON n.`standortid` = s.`StandortID`
            LEFT JOIN ' . Department::TABLE . ' AS d ON s.`BehoerdenID` = d.`BehoerdenID`
        WHERE d.`BehoerdenID` = :departmentid
        GROUP BY date
        ORDER BY date ASC
    ';
}
