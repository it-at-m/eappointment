<?php

namespace BO\Zmsdb\Query;

class ExchangeClientdepartment extends Base
{
    /**
     * @var String TABLE mysql table reference
     */
    const TABLE = 'statistik';

    const BATABLE = 'buergeranliegen';

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
                s.behoerdenid as departmentid,
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
        SELECT DISTINCT DATE_FORMAT(`datum`,"%Y-%m") AS date
        FROM ' . self::TABLE . ' AS s
        WHERE `standortid` = :scopeid
        ORDER BY `datum` ASC
    ';

    const QUERY_PERIODLIST_YEAR = '
        SELECT DISTINCT
            DATE_FORMAT(`datum`,"%Y") AS date
        FROM ' . self::TABLE . ' AS s
        WHERE `standortid` = :scopeid
        ORDER BY `datum` ASC
    ';
}
