<?php

namespace BO\Zmsdb\Query;

class ExchangeWaitingowner extends Base
{
    /**
     * @var String TABLE mysql table reference
     */
    const TABLE = 'wartenrstatistik';

    const QUERY_READ_DAY = '
        SELECT
            w.`datum`,
            '. ExchangeWaitingscope::WAITING_VALUES .'
        FROM ' . self::TABLE . ' as w
            LEFT JOIN ' . Scope::TABLE .' AS s ON w.`standortid` = s.`StandortID`
            LEFT JOIN ' . Department::TABLE .' AS d ON s.`BehoerdenID` = d.`BehoerdenID`
            LEFT JOIN ' . Owner::TABLE .' AS own ON own.`KundenID` = d.`KundenID`
        WHERE
            own.`KundenID` = :ownerid
            AND `datum` BETWEEN :datestart AND :dateend
        GROUP BY w.`datum` ASC
        ORDER BY w.`datum` ASC
    ';

    //PLEASE REMEMBER THE REALY COOL DYNAMIC VERSION
    const QUERY_READ_MONTH = '
        SELECT
      		DATE_FORMAT(w.`datum`, "%Y-%m") as datum,
      		'. ExchangeWaitingscope::WAITING_VALUES .'
        FROM '. self::TABLE .' as w
            LEFT JOIN ' . Scope::TABLE .' AS s ON w.`standortid` = s.`StandortID`
            LEFT JOIN ' . Department::TABLE .' AS d ON s.`BehoerdenID` = d.`BehoerdenID`
            LEFT JOIN ' . Owner::TABLE .' AS own ON own.`KundenID` = d.`KundenID`
      	WHERE
      		own.`KundenID` = :ownerid 
            AND w.`datum` BETWEEN :datestart AND :dateend
      	GROUP BY DATE_FORMAT(w.`datum`, "%Y-%m")
      	ORDER BY DATE_FORMAT(w.`datum`, "%Y-%m") ASC
    ';

    const QUERY_READ_QUARTER = '
        SELECT
          CONCAT(YEAR(w.`datum`),"-",QUARTER(w.`datum`)) as datum,
          '. ExchangeWaitingscope::WAITING_VALUES .'
        FROM '. self::TABLE .' as w
              LEFT JOIN ' . Scope::TABLE .' AS s ON w.`standortid` = s.`StandortID`
              LEFT JOIN ' . Department::TABLE .' AS d ON s.`BehoerdenID` = d.`BehoerdenID`
              LEFT JOIN ' . Owner::TABLE .' AS own ON own.`KundenID` = d.`KundenID`
        WHERE
            own.`KundenID` = :ownerid AND
            w.`datum` BETWEEN :datestart AND :dateend
        GROUP BY CONCAT(YEAR(w.`datum`),"-",QUARTER(w.`datum`))
        ORDER BY CONCAT(YEAR(w.`datum`),"-",QUARTER(w.`datum`)) ASC
    ';

    const QUERY_SUBJECTS = '
        SELECT
            own.`KundenID` as subject,
            MIN(`datum`) AS periodstart,
            MAX(`datum`) AS periodend,
            own.`Kundenname` AS description
        FROM ' . self::TABLE . ' AS w
            LEFT JOIN ' . Scope::TABLE .' AS s ON w.`standortid` = s.`StandortID`
            LEFT JOIN ' . Department::TABLE .' AS d ON s.`BehoerdenID` = d.`BehoerdenID`
            LEFT JOIN ' . Owner::TABLE .' AS own ON own.`KundenID` = d.`KundenID`
        GROUP BY own.`KundenID`
        ORDER BY own.`KundenID` ASC, periodstart DESC
    ';

    const QUERY_PERIODLIST_DAY = '
        SELECT
            `datum`
        FROM ' . self::TABLE . ' AS w
            LEFT JOIN ' . Scope::TABLE .' AS s ON w.`standortid` = s.`StandortID`
            LEFT JOIN ' . Department::TABLE .' AS d ON s.`BehoerdenID` = d.`BehoerdenID`
            LEFT JOIN ' . Owner::TABLE .' AS own ON own.`KundenID` = d.`KundenID`
        WHERE
            own.`KundenID` = :ownerid
        ORDER BY `datum` ASC
    ';

    const QUERY_PERIODLIST_MONTH = '
        SELECT DISTINCT
            DATE_FORMAT(`datum`,"%Y-%m") AS date
        FROM ' . self::TABLE . ' AS w
            LEFT JOIN ' . Scope::TABLE .' AS s ON w.`standortid` = s.`StandortID`
            LEFT JOIN ' . Department::TABLE .' AS d ON s.`BehoerdenID` = d.`BehoerdenID`
            LEFT JOIN ' . Owner::TABLE .' AS own ON own.`KundenID` = d.`KundenID`
        WHERE
            own.`KundenID` = :ownerid
        ORDER BY `datum` ASC
    ';
}
