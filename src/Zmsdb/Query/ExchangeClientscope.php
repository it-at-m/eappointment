<?php

namespace BO\Zmsdb\Query;

class ExchangeClientscope extends Base
{
    /**
     * @var String TABLE mysql table reference
     */
    const TABLE = 'statistik';

    const QUERY_READ_REPORT = '
      SELECT
          #subjectid
          a.`standortid` as subjectid,

          #date
          a.`datum` as date,

          #notification count
          ( SELECT
                IFNULL(SUM(n.gesendet), 0)
            FROM abrechnung n
            WHERE
                n.`StandortID` = a.`StandortID` AND n.`Datum` = a.`datum`
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
          ( SELECT
                (COUNT(IF(ba.AnliegenID > 0, ba.AnliegenID, null)) - SUM(a.`nicht_erschienen`=1 AND a.`mitTermin`=1))
            FROM buergeranliegen ba
            WHERE ba.`BuergerarchivID` IN (
                SELECT buergerarchiv.BuergerarchivID
                FROM buergerarchiv
                WHERE
                    buergerarchiv.`StandortID` = a.`standortid` AND
                    buergerarchiv.`Datum` = a.`datum`
            )
          ) as requestscount,

          #scope name
          s.Bezeichnung as scopename,

          #department name
          d.Name as departmentname,

          #organisation name
          o.Organisationsname as organisationname

      FROM ' . ProcessStatusArchived::TABLE .' AS a
          LEFT JOIN '. Scope::TABLE .' AS s ON a.`StandortID` = s.`StandortID`
          LEFT JOIN '. Department::TABLE .' AS d ON d.`BehoerdenID` = s.`BehoerdenID`
          LEFT JOIN '. Organisation::TABLE .' AS o ON o.`OrganisationsID` = d.`OrganisationsID`
      WHERE a.`StandortID` = :scopeid AND a.`Datum` BETWEEN :datestart AND :dateend
      GROUP BY a.`Datum`
      ORDER BY a.`datum` ASC
    ';

    const QUERY_SUBJECTS = '
      SELECT
          scope.`StandortID` as subject,
          periodstart,
          periodend,
          CONCAT(scope.`Bezeichnung`, " ", scope.`standortinfozeile`) AS description
      FROM '. SCOPE::TABLE .' AS scope
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
