<?php

namespace BO\Zmsdb\Query;

class ExchangeWaitingdepartment extends Base
{
    /**
     * @var String TABLE mysql table reference
     */
    const TABLE = 'wartenrstatistik';

    const QUERY_READ_DAY = '
        SELECT *
        FROM ' . self::TABLE . ' as w
            LEFT JOIN ' . Scope::TABLE .' AS s ON w.`standortid` = s.`StandortID`
            LEFT JOIN ' . Department::TABLE .' AS d ON s.`BehoerdenID` = d.`BehoerdenID`
        WHERE d.`BehoerdenID` = :departmentid
            AND `datum` BETWEEN :datestart AND :dateend
        GROUP BY `datum` ASC
        ORDER BY `datum` ASC
    ';

    const QUERY_SUBJECTS = '
        SELECT
            s.`BehoerdenID` as subject,
            MIN(`datum`) AS periodstart,
            MAX(`datum`) AS periodend,
            d.`Name` AS description
        FROM ' . self::TABLE . ' AS w
            LEFT JOIN ' . Scope::TABLE .' AS s ON w.`standortid` = s.`StandortID`
            LEFT JOIN ' . Department::TABLE .' AS d ON s.`BehoerdenID` = d.`BehoerdenID`
        GROUP BY w.`standortid`
        ORDER BY w.`standortid` ASC
    ';

    const QUERY_PERIODLIST_DAY = '
        SELECT
            `datum`
        FROM ' . self::TABLE . ' AS w
            LEFT JOIN ' . Scope::TABLE .' AS s ON w.`standortid` = s.`StandortID`
            LEFT JOIN ' . Department::TABLE .' AS d ON s.`BehoerdenID` = d.`BehoerdenID`
        WHERE
            d.`BehoerdenID` = :departmentid
        ORDER BY `datum` ASC
    ';

    const QUERY_PERIODLIST_MONTH = '
        SELECT DISTINCT
            DATE_FORMAT(`datum`,"%Y-%m") AS date
        FROM ' . self::TABLE . ' AS w
            LEFT JOIN ' . Scope::TABLE .' AS s ON w.`standortid` = s.`StandortID`
            LEFT JOIN ' . Department::TABLE .' AS d ON s.`BehoerdenID` = d.`BehoerdenID`
        WHERE
            d.`BehoerdenID` = :departmentid
        ORDER BY `datum` ASC
    ';

    const QUERY_PERIODLIST_YEAR = '
        SELECT DISTINCT
            DATE_FORMAT(`datum`,"%Y") AS date
        FROM ' . self::TABLE . ' AS w
            LEFT JOIN ' . Scope::TABLE .' AS s ON w.`standortid` = s.`StandortID`
            LEFT JOIN ' . Department::TABLE .' AS d ON s.`BehoerdenID` = d.`BehoerdenID`
        WHERE
            d.`BehoerdenID` = :departmentid
        ORDER BY `datum` ASC
    ';
}
