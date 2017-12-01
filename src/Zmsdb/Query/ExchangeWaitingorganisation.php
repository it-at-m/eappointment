<?php

namespace BO\Zmsdb\Query;

class ExchangeWaitingorganisation extends Base
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
            LEFT JOIN ' . Organisation::TABLE .' AS o ON d.`OrganisationsID` = o.`OrganisationsID`
        WHERE d.`BehoerdenID` = :departmentid
            AND `datum` BETWEEN :datestart AND :dateend
        ORDER BY `datum` ASC
    ';

    const QUERY_SUBJECTS = '
        SELECT
            d.`OrganisationsID` as subject,
            MIN(`datum`) AS periodstart,
            MAX(`datum`) AS periodend,
            o.`Organisationsname` AS description
        FROM ' . self::TABLE . ' AS w
            LEFT JOIN ' . Scope::TABLE .' AS s ON w.`standortid` = s.`StandortID`
            LEFT JOIN ' . Department::TABLE .' AS d ON s.`BehoerdenID` = d.`BehoerdenID`
            LEFT JOIN ' . Organisation::TABLE .' AS o ON d.`OrganisationsID` = o.`OrganisationsID`
        GROUP BY w.`standortid`
        ORDER BY w.`standortid` ASC
    ';

    const QUERY_PERIODLIST_DAY = '
        SELECT
            `datum`
        FROM ' . self::TABLE . ' AS w
            LEFT JOIN ' . Scope::TABLE .' AS s ON w.`standortid` = s.`StandortID`
            LEFT JOIN ' . Department::TABLE .' AS d ON s.`BehoerdenID` = d.`BehoerdenID`
            LEFT JOIN ' . Organisation::TABLE .' AS o ON d.`OrganisationsID` = o.`OrganisationsID`
        WHERE
            o.`OrganisationsID` = :organisationid
        ORDER BY `datum` ASC
    ';

    const QUERY_PERIODLIST_MONTH = '
        SELECT DISTINCT
            DATE_FORMAT(`datum`,"%Y-%m") AS month
        FROM ' . self::TABLE . ' AS w
            LEFT JOIN ' . Scope::TABLE .' AS s ON w.`standortid` = s.`StandortID`
            LEFT JOIN ' . Department::TABLE .' AS d ON s.`BehoerdenID` = d.`BehoerdenID`
            LEFT JOIN ' . Organisation::TABLE .' AS o ON d.`OrganisationsID` = o.`OrganisationsID`
        WHERE
            o.`OrganisationsID` = :organisationid
        ORDER BY `datum` ASC
    ';

    const QUERY_PERIODLIST_YEAR = '
        SELECT DISTINCT
            DATE_FORMAT(`datum`,"%Y") AS year
        FROM ' . self::TABLE . ' AS w
            LEFT JOIN ' . Scope::TABLE .' AS s ON w.`standortid` = s.`StandortID`
            LEFT JOIN ' . Department::TABLE .' AS d ON s.`BehoerdenID` = d.`BehoerdenID`
            LEFT JOIN ' . Organisation::TABLE .' AS o ON d.`OrganisationsID` = o.`OrganisationsID`
        WHERE
            o.`OrganisationsID` = :organisationid
        ORDER BY `datum` ASC
    ';
}
