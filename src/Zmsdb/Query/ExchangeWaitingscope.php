<?php

namespace BO\Zmsdb\Query;

class ExchangeWaitingscope extends Base
{
    /**
     * @var String TABLE mysql table reference
     */
    const TABLE = 'wartenrstatistik';

    const QUERY_READ_DAY = '
        SELECT * FROM ' . self::TABLE . '
        WHERE `standortid` = :scopeid
            AND `datum` BETWEEN :datestart AND :dateend
        ORDER BY `datum` ASC
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
            w.standortid as scopeid,
            MIN(w.`datum`) AS periodstart,
            MAX(w.`datum`) AS periodend
          FROM '. self::TABLE .' w
          group by scopeid
        )
            maxAndminDate ON maxAndminDate.`scopeid` = scope.`StandortID` 
        GROUP BY scope.`StandortID`
        ORDER BY scope.`StandortID` ASC
    ';

    const QUERY_PERIODLIST_DAY = '
        SELECT
            `datum`
        FROM ' . self::TABLE . ' AS w
        WHERE `standortid` = :scopeid
        ORDER BY `datum` ASC
    ';

    const QUERY_PERIODLIST_MONTH = '
        SELECT DISTINCT DATE_FORMAT(`datum`,"%Y-%m") AS date
        FROM ' . self::TABLE . ' AS w
        WHERE `standortid` = :scopeid
        ORDER BY `datum` ASC
    ';

    const QUERY_PERIODLIST_YEAR = '
        SELECT DISTINCT
            DATE_FORMAT(`datum`,"%Y") AS date
        FROM ' . self::TABLE . ' AS w
        WHERE `standortid` = :scopeid
        ORDER BY `datum` ASC
    ';
}
