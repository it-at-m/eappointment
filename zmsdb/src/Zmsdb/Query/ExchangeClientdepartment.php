<?php

namespace BO\Zmsdb\Query;

class ExchangeClientdepartment extends Base
{
    /**
     * @var String TABLE mysql table reference
     */
    const TABLE = 'statistik';

    const BATABLE = 'buergeranliegen';

    const NOTIFICATIONSTABLE = 'abrechnung';

    const QUERY_READ_REPORT = '
    SELECT
        MIN(subjectid) as subjectid,
        date,
        notificationscount,
        0 as notificationscost,
        SUM(clientscount) as clientscount,
        SUM(missed) as missed,
        SUM(withappointment) as withappointment,
        SUM(missedwithappointment) as missedwithappointment,
        SUM(requestcount) as requestcount

    FROM (
          SELECT
            d.behoerdenid as subjectid,
            DATE_FORMAT(n.`Datum`, :groupby) as date,
            IFNULL(SUM(n.gesendet), 0) as notificationscount,
            0 as notificationscost,
            0 as clientscount,
            0 as missed,                                    
            0 as withappointment,
            0 as missedwithappointment,
            0 as requestcount
          FROM ' . Department::TABLE . ' d
              LEFT JOIN ' . Scope::TABLE . ' scope ON scope.`BehoerdenID` = d.`BehoerdenID`
              LEFT JOIN abrechnung n ON n.`StandortID` = scope.`StandortID`
          WHERE d.`BehoerdenID` = :departmentid AND n.`Datum` BETWEEN :datestart AND :dateend
          GROUP BY date

    UNION ALL  
          SELECT
              d.behoerdenid as subjectid,
              DATE_FORMAT(a.`Datum`, :groupby) as date,
              0 as notificationscount,
              0 as notificationscost,
              SUM(IF(a.`nicht_erschienen`=0,a.AnzahlPersonen,0)) as clientscount,
              SUM(IF(a.`nicht_erschienen`=1,a.AnzahlPersonen,0)) as missed,
              SUM(IF(a.`nicht_erschienen`=0 AND a.mitTermin=1,a.AnzahlPersonen,0)) as withappointment,
              SUM(IF(a.`nicht_erschienen`=1 AND a.mitTermin=1,a.AnzahlPersonen,0)) as missedwithappointment,
            	0 as requestcount               
            FROM ' . Department::TABLE . ' d
                LEFT JOIN ' . Scope::TABLE . ' scope ON scope.`BehoerdenID` = d.`BehoerdenID`
                LEFT JOIN ' . ProcessStatusArchived::TABLE . ' a ON a.`StandortID` = scope.`StandortID`
            WHERE d.`BehoerdenID` = :departmentid AND a.`Datum` BETWEEN :datestart AND :dateend
              GROUP BY date
    UNION ALL  
          SELECT
              d.behoerdenid as subjectid,
              DATE_FORMAT(a.`Datum`, :groupby) as date,
              0 as notificationscount,
              0 as notificationscost,
              0 as clientscount,
              0 as missed,                                    
              0 as withappointment,
              0 as missedwithappointment,
              COUNT(IF(ba.AnliegenID > 0, ba.AnliegenID, null)) as requestcount
          FROM ' . Department::TABLE . ' as d
                    LEFT JOIN ' . Scope::TABLE . ' as scope ON d.`BehoerdenID` = scope.`BehoerdenID`
                    LEFT JOIN ' . ProcessStatusArchived::TABLE . ' as a ON scope.`StandortID` = a.`StandortID`
                    LEFT JOIN ' . self::BATABLE . ' as ba ON a.BuergerarchivID = ba.BuergerarchivID
                WHERE
                  d.`BehoerdenID` = :departmentid AND
                  a.nicht_erschienen=0 AND
                  a.`Datum` BETWEEN :datestart AND :dateend
            GROUP BY date
          ) as unionresult

    GROUP BY date
    ';


    //fast query from statistic table, but statistic is not up-to-date - 2008 - 2011 not available or complete
    const QUERY_SUBJECTS = '
      SELECT
          d.`BehoerdenID` as subject,
          periodstart,
          periodend,
          o.`Organisationsname` AS organisationname,
          d.`Name` AS description
      FROM ' . Department::TABLE . ' AS d
          INNER JOIN
            (
              SELECT
                s.`behoerdenid` as departmentid,
                MIN(s.`datum`) AS periodstart,
                MAX(s.`datum`) AS periodend
              FROM ' . self::TABLE . ' s
              group by departmentid
            )
          maxAndminDate ON maxAndminDate.`departmentid` = d.`BehoerdenID`
          LEFT JOIN ' . Organisation::TABLE . ' AS o ON d.`OrganisationsID` = o.`OrganisationsID`
      GROUP BY d.`BehoerdenID`
      ORDER BY d.`BehoerdenID` ASC
    ';

    const QUERY_PERIODLIST_MONTH = '
        SELECT date
        FROM ' . Department::TABLE . ' AS d
            INNER JOIN (
              SELECT
                behoerdenid,
                DATE_FORMAT(`datum`,"%Y-%m") AS date
              FROM ' . self::TABLE . '
            ) s ON s.behoerdenid = d.BehoerdenID
        WHERE d.`BehoerdenID` = :departmentid
        GROUP BY date
        ORDER BY date ASC
    ';
}
