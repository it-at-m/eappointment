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
        s.`behoerdenid` as subjectid,
        DATE_FORMAT(s.`datum`, :groupby) as date,
        IF(MIN(notification.total), MIN(notification.total), 0) as notificationscount,
        0 as notificationscost,
        IF(MIN(clientscount.total),MIN(clientscount.total),0) as clientscount,
        IF(MIN(clientscount.missed),MIN(clientscount.missed),0) as missed,
        IF(MIN(clientscount.withappointment),MIN(clientscount.withappointment),0) as withappointment,
        IF(MIN(clientscount.missedwithappointment),MIN(clientscount.missedwithappointment),0) as missedwithappointment,
        IF(MIN(requestscount.total),MIN(requestscount.total),0) as requestcount

    FROM '. self::TABLE .' AS s
        LEFT JOIN (
          SELECT
            DATE_FORMAT(n.`Datum`, :groupby) as date,
            IFNULL(SUM(n.gesendet), 0) as total
          FROM '. Department::TABLE .' d
              LEFT JOIN '. Scope::TABLE .' scope ON scope.`BehoerdenID` = d.`BehoerdenID`
              LEFT JOIN abrechnung n ON n.`StandortID` = scope.`StandortID`
          WHERE d.`BehoerdenID` = :departmentid AND n.`Datum` BETWEEN :datestart AND :dateend
          GROUP BY date
		) as notification ON notification.date =  DATE_FORMAT(s.`datum`, :groupby)

        LEFT JOIN (
          SELECT
            DATE_FORMAT(a.`Datum`, :groupby) as date,
                SUM(IF(a.`nicht_erschienen`=0,a.AnzahlPersonen,0)) as total,
                SUM(IF(a.`nicht_erschienen`=1,a.AnzahlPersonen,0)) as missed,
                SUM(IF(a.`nicht_erschienen`=0 AND a.mitTermin=1,a.AnzahlPersonen,0)) as withappointment,
                SUM(IF(a.`nicht_erschienen`=1 AND a.mitTermin=1,a.AnzahlPersonen,0)) as missedwithappointment
            FROM '. Department::TABLE .' d
                LEFT JOIN '. Scope::TABLE .' scope ON scope.`BehoerdenID` = d.`BehoerdenID`
                LEFT JOIN '. ProcessStatusArchived::TABLE .' a ON a.`StandortID` = scope.`StandortID`
            WHERE d.`BehoerdenID` = :departmentid AND a.`Datum` BETWEEN :datestart AND :dateend
              GROUP BY date
          ) as clientscount ON clientscount.date = DATE_FORMAT(s.`datum`, :groupby)

          LEFT JOIN (
            SELECT
              DATE_FORMAT(a.`Datum`, :groupby) as date,
                COUNT(IF(ba.AnliegenID > 0, ba.AnliegenID, null)) as total
                FROM '. Department::TABLE .' as d
                    LEFT JOIN '. Scope::TABLE .' as scope ON d.`BehoerdenID` = scope.`BehoerdenID`
                    LEFT JOIN '. ProcessStatusArchived::TABLE .' as a ON scope.`StandortID` = a.`StandortID`
                    LEFT JOIN '. self::BATABLE .' as ba ON a.BuergerarchivID = ba.BuergerarchivID
                WHERE
                  d.`BehoerdenID` = :departmentid AND
                  a.nicht_erschienen=0 AND
                  a.`Datum` BETWEEN :datestart AND :dateend
            GROUP BY date
          ) as requestscount ON requestscount.date = DATE_FORMAT(s.`datum`, :groupby)

    WHERE s.`behoerdenid` = :departmentid AND s.`datum` BETWEEN :datestart AND :dateend
    GROUP BY DATE_FORMAT(s.`datum`, :groupby)
    ';


    //fast query from statistic table, but statistic is not up-to-date - 2008 - 2011 not available or complete
    const QUERY_SUBJECTS = '
      SELECT
          d.`BehoerdenID` as subject,
          periodstart,
          periodend,
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
