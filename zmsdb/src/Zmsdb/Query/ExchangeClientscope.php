<?php

namespace BO\Zmsdb\Query;

class ExchangeClientscope extends Base
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
            StandortID as subjectid,        
            IFNULL(DATE_FORMAT(`Datum`, :groupby), 0) as date,
            IFNULL(SUM(gesendet), 0) as notificationscount,
            0 as notificationscost,
            0 AS clientscount,
            0 AS missed,
            0 AS withappointment,
            0 AS missedwithappointment,
            0 AS requestcount
          FROM ' . self::NOTIFICATIONSTABLE . '
          WHERE `StandortID` = :scopeid AND `Datum` BETWEEN :datestart AND :dateend
          GROUP BY date

      UNION ALL
          SELECT
            StandortID as subjectid,          
            IFNULL(DATE_FORMAT(`Datum`, :groupby), 0) as date,
            0 AS notificationscount,
            0 as notificationscost,
            SUM(IF(`nicht_erschienen`=0,AnzahlPersonen,0)) as clientscount,
            SUM(IF(`nicht_erschienen`=1,AnzahlPersonen,0)) as missed,
            SUM(IF(`nicht_erschienen`=0 AND mitTermin=1,AnzahlPersonen,0)) as withappointment,
            SUM(IF(`nicht_erschienen`=1 AND mitTermin=1,AnzahlPersonen,0)) as missedwithappointment,
            0 AS requestcount
            FROM ' . ProcessStatusArchived::TABLE . '
            WHERE `StandortID` = :scopeid AND `Datum` BETWEEN :datestart AND :dateend
              GROUP BY date

      UNION ALL
          SELECT
            StandortID as subjectid,
            IFNULL(DATE_FORMAT(`Datum`, :groupby), 0) as date,
            0 AS notificationscount,
            0 as notificationscost,
            0 AS clientscount,
            0 AS missed,
            0 AS withappointment,
            0 AS missedwithappointment,
            COUNT(IF(ba.AnliegenID > 0, ba.AnliegenID, null)) as requestcount
            FROM ' . ProcessStatusArchived::TABLE . ' a
              LEFT JOIN ' . self::BATABLE . ' as ba ON a.BuergerarchivID = ba.BuergerarchivID
            WHERE `StandortID` = :scopeid AND `Datum` BETWEEN :datestart AND :dateend AND nicht_erschienen=0
            GROUP BY date
      ) as unionresult
      GROUP BY date;  
    ';

    const QUERY_SUBJECTS = '
      SELECT
          scope.`StandortID` as subject,
          periodstart,
          periodend,
          CONCAT(scope.`Bezeichnung`, " ", scope.`standortinfozeile`) AS description
      FROM ' . Scope::TABLE . ' AS scope
          INNER JOIN
            (
              SELECT
                s.standortid as scopeid,
                MIN(s.`datum`) AS periodstart,
                MAX(s.`datum`) AS periodend
              FROM ' . self::TABLE . ' s
              group by scopeid
            )
          maxAndminDate ON maxAndminDate.`scopeid` = scope.`StandortID`
      GROUP BY scope.`StandortID`
      ORDER BY scope.`StandortID` ASC
    ';

    const QUERY_PERIODLIST_MONTH = '
        SELECT date
        FROM ' . Scope::TABLE . ' AS scope
            INNER JOIN (
              SELECT
                `StandortID`,
                DATE_FORMAT(`Datum`,"%Y-%m") AS date
              FROM ' . self::TABLE . '
            ) s ON scope.`StandortID` = s.`standortid`
        WHERE scope.`StandortID` = :scopeid
        GROUP BY date
        ORDER BY date ASC
    ';
}
