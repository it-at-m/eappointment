<?php

namespace BO\Zmsdb\Query;

class ExchangeClientorganisation extends Base
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
            s.`organisationsid` as subjectid,
            #date
            s.`datum` as date,
            #notification count
            ( SELECT
                  IFNULL(SUM(n.gesendet), 0)
              FROM abrechnung n
                LEFT JOIN '. Scope::TABLE .' scope ON n.`StandortID` = scope.`StandortID`
                LEFT JOIN '. Department::TABLE .' d ON scope.`BehoerdenID` = d.`BehoerdenID`
                LEFT JOIN '. Organisation::TABLE .' o ON o.`OrganisationsID` = d.`OrganisationsID`
              WHERE
                  o.`OrganisationsID` = s.`organisationsid` AND n.Datum = s.datum
            ) as notificationscount,
            #notfication cost placeholder
            0 as notificationscost,
            #clients count
            ( SELECT
                SUM(a.AnzahlPersonen)
              FROM '. Organisation::TABLE .' o
                LEFT JOIN '. Department::TABLE .' d ON o.OrganisationsID = d.OrganisationsID
                LEFT JOIN '. Scope::TABLE .' scope ON d.BehoerdenID = scope.BehoerdenID
                LEFT JOIN '. ProcessStatusArchived::TABLE .' a ON scope.StandortID = a.StandortID
              WHERE
                o.`OrganisationsID` = s.`organisationsid` AND a.Datum = s.datum AND a.`nicht_erschienen` = 0
            ) as clientscount,

            #clients missed
            ( SELECT
                IFNULL(COUNT(a.nicht_erschienen), 0)
              FROM '. Organisation::TABLE .' o
                LEFT JOIN '. Department::TABLE .' d ON o.OrganisationsID = d.OrganisationsID
                LEFT JOIN '. Scope::TABLE .' scope ON d.BehoerdenID = scope.BehoerdenID
                LEFT JOIN '. ProcessStatusArchived::TABLE .' a ON scope.StandortID = a.StandortID
              WHERE
                o.`OrganisationsID` = s.`organisationsid` AND a.Datum = s.datum AND a.`nicht_erschienen` = 1
          ) as missed,

            #clients with appointment
            ( SELECT
                count(*)
              FROM '. Organisation::TABLE .' o
                LEFT JOIN '. Department::TABLE .' d ON o.OrganisationsID = d.OrganisationsID
                LEFT JOIN '. Scope::TABLE .' scope ON d.BehoerdenID = scope.BehoerdenID
                LEFT JOIN '. ProcessStatusArchived::TABLE .' a ON scope.StandortID = a.StandortID
              WHERE
                  o.`OrganisationsID` = s.`organisationsid` AND
                  a.Datum = s.datum AND
                  a.nicht_erschienen=0 AND
                  a.mitTermin=1
          ) as withappointment,

            #clients missed with appointment
            ( SELECT
                COUNT(*)
              FROM '. Organisation::TABLE .' o
                LEFT JOIN '. Department::TABLE .' d ON o.OrganisationsID = d.OrganisationsID
                LEFT JOIN '. Scope::TABLE .' scope ON d.BehoerdenID = scope.BehoerdenID
                LEFT JOIN '. ProcessStatusArchived::TABLE .' a ON scope.StandortID = a.StandortID
              WHERE
                o.`OrganisationsID` = s.`organisationsid` AND
                a.Datum = s.datum AND
                a.nicht_erschienen=1 AND
                a.mitTermin=1
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
                        FROM '. Organisation::TABLE .' o
                          LEFT JOIN '. Department::TABLE .' d ON o.OrganisationsID = d.OrganisationsID
                          LEFT JOIN '. Scope::TABLE .' scope ON d.BehoerdenID = scope.BehoerdenID
                          LEFT JOIN '. ProcessStatusArchived::TABLE .' a ON scope.StandortID = a.StandortID
                        WHERE
                          o.`OrganisationsID` = s.`organisationsid` AND
                          a.Datum = s.datum AND
                          a.nicht_erschienen = 0
                )
            ) as requestscount

        FROM '. self::TABLE .' AS s
        WHERE s.`organisationsid` = :organisationid AND s.`Datum` BETWEEN :datestart AND :dateend
        GROUP BY s.`datum`
        ORDER BY s.`datum` ASC
    ';


    //fast query from statistic table, but statistic is not up-to-date - 2008 - 2011 not available or complete
    const QUERY_SUBJECTS = '
      SELECT
          o.`OrganisationsID` as subject,
          periodstart,
          periodend,
          o.`Organisationsname` AS description
      FROM '. Organisation::TABLE .' AS o
          INNER JOIN
            (
              SELECT
                s.`organisationsid` AS organisationsid,
                MIN(s.`datum`) AS periodstart,
                MAX(s.`datum`) AS periodend
              FROM '. self::TABLE .' s
              group by organisationsid
            )
          maxAndminDate ON maxAndminDate.`organisationsid` = o.`OrganisationsID`
      GROUP BY o.`OrganisationsID`
      ORDER BY o.`OrganisationsID` ASC
    ';

    const QUERY_PERIODLIST_MONTH = '
        SELECT date
        FROM '. Organisation::TABLE .' AS o
            INNER JOIN (
              SELECT
                organisationsid,
                DATE_FORMAT(`datum`,"%Y-%m") AS date
              FROM '. self::TABLE .'
            ) s ON s.organisationsid = o.`OrganisationsID`
        WHERE o.`OrganisationsID` = :organisationid
        GROUP BY date
        ORDER BY date ASC
    ';

    const QUERY_PERIODLIST_YEAR = '
        SELECT date
        FROM '. Organisation::TABLE .' AS o
            INNER JOIN (
              SELECT
                organisationsid,
                DATE_FORMAT(`datum`,"%Y") AS date
              FROM '. self::TABLE .'
              WHERE StandortID <> 0 AND Datum <> "0000-00-00"
            ) s ON s.organisationsid = o.`OrganisationsID`
        WHERE o.`OrganisationsID` = :organisationid
        GROUP BY date
        ORDER BY date ASC
    ';
}
