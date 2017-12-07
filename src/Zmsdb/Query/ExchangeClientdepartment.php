<?php

namespace BO\Zmsdb\Query;

class ExchangeClientdepartment extends Base
{
    /**
     * @var String TABLE mysql table reference
     */
    const TABLE = 'statistik';

    const BATABLE = 'buergeranliegen';

    // from buergerarchiv slow
    /*
    const QUERY_READ_REPORT = '
        SELECT
            #subjectid
            d.BehoerdenID as subjectid,

            #date
            a.`datum` as date,

            #notification count
            ( SELECT
                  IFNULL(SUM(n.gesendet), 0)
              FROM abrechnung n
                LEFT JOIN '. Scope::TABLE .' scope ON n.`StandortID` = scope.`StandortID`
                LEFT JOIN '. Department::TABLE .' d ON scope.`BehoerdenID` = d.`BehoerdenID`
              WHERE
                  n.Datum = a.`datum` AND d.`BehoerdenID` = :departmentid
            ) as notificationscount,

            #notfication cost placeholder
            0 as notificationscost,

            #clients count
            (SUM(a.AnzahlPersonen) - SUM(a.`nicht_erschienen`=1)) as clientscount,

            #clients missed
            IFNULL(SUM(a.`nicht_erschienen`=1), 0) as missed,

            #clients with appointment
            (SUM(a.`mitTermin`=1) - SUM(a.`nicht_erschienen`=1 AND a.`mitTermin`=1)) as withappointment,

            #clients missed with appointment
            IFNULL(SUM(a.`nicht_erschienen`=1 AND a.`mitTermin`=1), 0) as missedwithappointment,

            #requests count
            (
                SELECT
                    COUNT(IF(ba.AnliegenID > 0, ba.AnliegenID, null))
                      FROM '. self::BATABLE .' ba
                      WHERE
                            ba.`BuergerarchivID` IN (
                        SELECT
                            a2.BuergerarchivID
                              FROM '. Department::TABLE .' d
                                    LEFT JOIN '. Scope::TABLE .' scope
                              ON scope.`BehoerdenID` = d.`BehoerdenID`
                                      LEFT JOIN '. ProcessStatusArchived::TABLE .' a2
                              ON a2.`StandortID` = scope.`StandortID`
                        WHERE
                                    d.`BehoerdenID` = :departmentid AND
                            a2.Datum = a.Datum AND
                            a2.nicht_erschienen = 0
                    )
            ) as requestscount
        FROM '. Department::TABLE .' AS d
            LEFT JOIN '. Scope::TABLE .' scope ON scope.`BehoerdenID` = d.`BehoerdenID`
            LEFT JOIN '. ProcessStatusArchived::TABLE .' a ON a.`StandortID` = scope.`StandortID`
        WHERE d.`BehoerdenID` = :departmentid AND a.`Datum` BETWEEN :datestart AND :dateend
        GROUP BY a.`Datum`,d.`BehoerdenID`
        ORDER BY a.`datum` ASC
    ';
    */

    const QUERY_READ_REPORT = '
        SELECT
            #subjectid
            s.`behoerdenid` as subjectid,
            #date
            s.`datum` as date,
            #notification count
            ( SELECT
                  IFNULL(SUM(n.gesendet), 0)
              FROM abrechnung n
                LEFT JOIN '. Scope::TABLE .' scope ON n.`StandortID` = scope.`StandortID`
                LEFT JOIN '. Department::TABLE .' d ON scope.`BehoerdenID` = d.`BehoerdenID`
              WHERE
                  d.`BehoerdenID` = s.`behoerdenid` AND n.Datum = s.datum
            ) as notificationscount,
            #notfication cost placeholder
            0 as notificationscost,
            #clients count
            ( SELECT
                SUM(a.AnzahlPersonen)
              FROM '. Department::TABLE .' d
                LEFT JOIN '. Scope::TABLE .' scope ON d.BehoerdenID = scope.BehoerdenID
                LEFT JOIN '. ProcessStatusArchived::TABLE .' a ON a.StandortID = scope.StandortID
              WHERE
                d.`BehoerdenID` = s.`behoerdenid` AND a.Datum = s.datum AND a.`nicht_erschienen` = 0
            ) as clientscount,

            #clients missed
            ( SELECT
                IFNULL(COUNT(a.nicht_erschienen), 0)
              FROM '. Department::TABLE .' d
                LEFT JOIN '. Scope::TABLE .' scope ON d.BehoerdenID = scope.BehoerdenID
                LEFT JOIN '. ProcessStatusArchived::TABLE .' a ON a.StandortID = scope.StandortID
              WHERE
                d.`BehoerdenID` = s.`behoerdenid` AND a.Datum = s.datum AND a.`nicht_erschienen` = 1
          ) as missed,

            #clients with appointment
            ( SELECT
                count(*)
              FROM '. Department::TABLE .' d
                LEFT JOIN '. Scope::TABLE .' scope ON d.BehoerdenID = scope.BehoerdenID
                LEFT JOIN '. ProcessStatusArchived::TABLE .' a ON a.StandortID = scope.StandortID
              WHERE
                  d.`BehoerdenID` = s.`behoerdenid` AND a.Datum = s.datum AND a.nicht_erschienen=0 AND a.mitTermin=1
          ) as withappointment,

            #clients missed with appointment
            ( SELECT
                COUNT(*)
              FROM '. Department::TABLE .' d
                LEFT JOIN '. Scope::TABLE .' scope ON d.BehoerdenID = scope.BehoerdenID
                LEFT JOIN '. ProcessStatusArchived::TABLE .' a ON a.StandortID = scope.StandortID
              WHERE
                d.`BehoerdenID` = s.`behoerdenid` AND a.Datum = s.datum AND a.nicht_erschienen=1 AND a.mitTermin=1
          ) as missedwithappointment,
      #requests count
          (
                SELECT
                    COUNT(IF(ba.AnliegenID > 0, ba.AnliegenID, null))
                FROM '. self::BATABLE .' ba
                WHERE
                    ba.`BuergerarchivID` IN (
                        SELECT
                            a.BuergerarchivID
                        FROM '. Department::TABLE .' d
                            LEFT JOIN '. Scope::TABLE .' scope ON d.BehoerdenID = scope.BehoerdenID
                            LEFT JOIN '. ProcessStatusArchived::TABLE .' a ON a.StandortID = scope.StandortID
                        WHERE
                          d.`BehoerdenID` = s.`behoerdenid` AND
                            a.Datum = s.datum AND
                            a.nicht_erschienen = 0
                )
            ) as requestscount

        FROM '. self::TABLE .' AS s
        WHERE s.`behoerdenid` = :departmentid AND s.`Datum` BETWEEN :datestart AND :dateend
        GROUP BY s.`datum`
        ORDER BY s.`datum` ASC
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
    /*
    const QUERY_SUBJECTS = '
      SELECT
          subject,
          periodstart,
          periodend,
          d.`Name` AS description
      FROM '. Department::TABLE .' AS d
          INNER JOIN (
            SELECT
              s.`behoerdenid` as subject,
              MIN(a.`Datum`) AS periodstart,
              MAX(a.`Datum`) AS periodend
            FROM standort s
              INNER JOIN buergerarchiv a ON a.StandortID = s.StandortID AND a.Datum <> "0000-00-00"
            GROUP BY subject
          ) minmaxjoin ON minmaxjoin.subject = d.BehoerdenID
    ';
    */

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

    const QUERY_PERIODLIST_YEAR = '
        SELECT date
        FROM '. Department::TABLE .' AS d
            INNER JOIN (
              SELECT
                behoerdenid,
                DATE_FORMAT(`datum`,"%Y") AS date
              FROM '. self::TABLE .'
            ) s ON s.behoerdenid = d.BehoerdenID
        WHERE d.`BehoerdenID` = :departmentid
        GROUP BY date
        ORDER BY date ASC
    ';
}
