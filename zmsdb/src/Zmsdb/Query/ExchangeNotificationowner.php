<?php

namespace BO\Zmsdb\Query;

class ExchangeNotificationowner extends Base
{
    /**
     * @var String TABLE mysql table reference
     */
    const TABLE = 'abrechnung';

    const QUERY_READ_REPORT = '
        SELECT
            o.`KundenID` as subjectid,
            IF(n.`Datum`, DATE_FORMAT(n.`Datum`, :groupby), "") as date,
            IF(o.`Kundenname` <> "", o.`Kundenname`, "test") as ownernname,
            IF(org.`Organisationsname` <> "", org.`Organisationsname`, "test") as organisationname,
            IF(d.`Name` <> "", d.`Name`, "test") as departmentname,
            IF(s.`Bezeichnung` <> "", CONCAT(s.`Bezeichnung`, " ", s.`standortkuerzel`), "test") AS scopename,
            IFNULL(SUM(n.`gesendet`), 0) as notificationscount
        FROM ' . Owner::TABLE . ' AS o
            LEFT JOIN ' . Organisation::TABLE . ' org ON org.`KundenID` = o.`KundenID`
            LEFT JOIN ' . Department::TABLE . ' d ON d.`OrganisationsID` = org.`OrganisationsID`
            LEFT JOIN ' . Scope::TABLE . ' s ON d.`BehoerdenID` = s.`BehoerdenID`
            LEFT JOIN ' . self::TABLE . ' n ON
                s.`StandortID` = n.`StandortID` AND
                n.`Datum` BETWEEN :datestart AND :dateend
        WHERE
            org.`KundenID` = :ownerid 
        GROUP BY d.`BehoerdenID`
        ORDER BY date, departmentname, scopename
    ';

    const QUERY_SUBJECTS = '
        SELECT
            o.`KundenID` as subject,
            MIN(n.`Datum`) AS periodstart,
            MAX(n.`Datum`) AS periodend,
            o.`Kundenname` AS description
        FROM ' . self::TABLE . ' AS n
            LEFT JOIN ' . Scope::TABLE . ' AS s ON n.`standortid` = s.`StandortID`
            LEFT JOIN ' . Department::TABLE . ' AS d ON s.`BehoerdenID` = d.`BehoerdenID`
            INNER JOIN ' . Owner::TABLE . ' AS o ON d.`KundenID` = o.`KundenID`
        GROUP BY o.`KundenID`
        ORDER BY o.`KundenID` ASC
    ';

    const QUERY_PERIODLIST_MONTH = '
        SELECT
            DATE_FORMAT(n.datum,"%Y-%m") AS date
        FROM ' . self::TABLE . ' AS n
            LEFT JOIN ' . Scope::TABLE . ' AS s ON n.`standortid` = s.`StandortID`
            LEFT JOIN ' . Department::TABLE . ' AS d ON s.`BehoerdenID` = d.`BehoerdenID`
            INNER JOIN ' . Owner::TABLE . ' AS o ON d.`KundenID` = o.`KundenID`
        WHERE o.`KundenID` = :ownerid
        GROUP BY date
        ORDER BY date ASC
    ';
}
