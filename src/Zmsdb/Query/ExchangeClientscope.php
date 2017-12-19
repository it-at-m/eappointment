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
        #subjectid
        s.`standortid` as subjectid,
        #date
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
            DATE_FORMAT(`Datum`, :groupby) as date,
            IFNULL(SUM(gesendet), 0) as total
          FROM '. self::NOTIFICATIONSTABLE .'
          WHERE `StandortID` = :scopeid AND `Datum` BETWEEN :datestart AND :dateend
          GROUP BY date
        ) as notification ON notification.date =  DATE_FORMAT(s.`datum`, :groupby)

        LEFT JOIN (
          SELECT
            DATE_FORMAT(`Datum`, :groupby) as date,
                SUM(IF(`nicht_erschienen`=0,AnzahlPersonen,0)) as total,
                SUM(IF(`nicht_erschienen`=1,AnzahlPersonen,0)) as missed,
                SUM(IF(`nicht_erschienen`=0 AND mitTermin=1,AnzahlPersonen,0)) as withappointment,
                SUM(IF(`nicht_erschienen`=1 AND mitTermin=1,AnzahlPersonen,0)) as missedwithappointment
            FROM '. ProcessStatusArchived::TABLE .'
            WHERE `StandortID` = :scopeid AND `Datum` BETWEEN :datestart AND :dateend
              GROUP BY date
          ) as clientscount ON clientscount.date = DATE_FORMAT(s.`datum`, :groupby)

          LEFT JOIN (
            SELECT
              DATE_FORMAT(`Datum`, :groupby) as date,
                COUNT(IF(ba.AnliegenID > 0, ba.AnliegenID, null)) as total
            FROM '. ProcessStatusArchived::TABLE .' a
              LEFT JOIN '. self::BATABLE .' as ba ON a.BuergerarchivID = ba.BuergerarchivID
            WHERE `StandortID` = :scopeid AND `Datum` BETWEEN :datestart AND :dateend AND nicht_erschienen=0
            GROUP BY date
          ) as requestscount ON requestscount.date = DATE_FORMAT(s.`datum`, :groupby)

    WHERE s.`standortid` = :scopeid AND s.`Datum` BETWEEN :datestart AND :dateend
    GROUP BY DATE_FORMAT(s.`datum`, :groupby)
    ';

    const QUERY_SUBJECTS = '
      SELECT
          scope.`StandortID` as subject,
          periodstart,
          periodend,
          CONCAT(scope.`Bezeichnung`, " ", scope.`standortinfozeile`) AS description
      FROM '. Scope::TABLE .' AS scope
          INNER JOIN
            (
              SELECT
                s.standortid as scopeid,
                MIN(s.`datum`) AS periodstart,
                MAX(s.`datum`) AS periodend
              FROM '. self::TABLE .' s
              group by scopeid
            )
          maxAndminDate ON maxAndminDate.`scopeid` = scope.`StandortID`
      GROUP BY scope.`StandortID`
      ORDER BY scope.`StandortID` ASC
    ';

    const QUERY_PERIODLIST_MONTH = '
        SELECT date
        FROM '. Scope::TABLE .' AS scope
            INNER JOIN (
              SELECT
                `StandortID`,
                DATE_FORMAT(`Datum`,"%Y-%m") AS date
              FROM '. self::TABLE .'
            ) s ON scope.`StandortID` = s.`standortid`
        WHERE scope.`StandortID` = :scopeid
        GROUP BY date
        ORDER BY date ASC
    ';
}
