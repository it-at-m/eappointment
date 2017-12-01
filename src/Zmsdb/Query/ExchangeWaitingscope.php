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
            w.`standortid` as subject,
            MIN(`datum`) AS periodstart,
            MAX(`datum`) AS periodend,
            CONCAT(`Bezeichnung`, " ", `standortinfozeile`) AS description
        FROM ' . self::TABLE . ' AS w
            LEFT JOIN ' . Scope::TABLE .' AS s ON w.`standortid` = s.`StandortID`
        GROUP BY w.`standortid`
        ORDER BY w.`standortid` ASC
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
