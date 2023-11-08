<?php

namespace BO\Zmsdb\Query;

class ExchangeNotificationorganisation extends Base
{
    /**
     * @var String TABLE mysql table reference
     */
    const TABLE = 'abrechnung';

    const QUERY_READ_REPORT = '
        SELECT
            o.`OrganisationsID` as subjectid,
            DATE_FORMAT(n.`Datum`, :groupby) as date,
            o.`Organisationsname` as organisationname,
            d.`Name` as departmentname,
            CONCAT(s.`Bezeichnung`, " ", s.`standortkuerzel`) AS scopename,
            IFNULL(SUM(n.`gesendet`), 0) as notificationscount
        FROM '. Organisation::TABLE .' AS o
            LEFT JOIN '. Department::TABLE .' d ON d.`OrganisationsID` = o.`OrganisationsID`
            LEFT JOIN '. Scope::TABLE .' s ON d.`BehoerdenID` = s.`BehoerdenID`
            LEFT JOIN '. self::TABLE .' n ON
                s.`StandortID` = n.`StandortID` AND
                n.`Datum` BETWEEN :datestart AND :dateend
        WHERE
            o.`OrganisationsID` = :organisationid 
        GROUP BY d.`BehoerdenID`
        ORDER BY date, departmentname, scopename
    ';

    const QUERY_SUBJECTS = '
        SELECT
            o.`OrganisationsID` as subject,
            MIN(n.`Datum`) AS periodstart,
            MAX(n.`Datum`) AS periodend,
            o.`Organisationsname` AS description
        FROM '. self::TABLE .' AS n
            LEFT JOIN '. Scope::TABLE .' AS s ON n.`standortid` = s.`StandortID`
            LEFT JOIN '. Department::TABLE .' AS d ON s.`BehoerdenID` = d.`BehoerdenID`
            INNER JOIN '. Organisation::TABLE .' AS o ON d.`OrganisationsID` = o.`OrganisationsID`
        GROUP BY o.`OrganisationsID`
        ORDER BY o.`OrganisationsID` ASC
    ';

    const QUERY_PERIODLIST_MONTH = '
        SELECT
            DATE_FORMAT(n.datum,"%Y-%m") AS date
        FROM '. self::TABLE .' AS n
            LEFT JOIN '. Scope::TABLE .' AS s ON n.`standortid` = s.`StandortID`
            LEFT JOIN '. Department::TABLE .' AS d ON s.`BehoerdenID` = d.`BehoerdenID`
            INNER JOIN '. Organisation::TABLE .' AS o ON d.`OrganisationsID` = o.`OrganisationsID`
        WHERE o.`OrganisationsID` = :organisationid
        GROUP BY date
        ORDER BY date ASC
    ';
}
