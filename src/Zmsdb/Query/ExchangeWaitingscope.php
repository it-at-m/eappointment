<?php

namespace BO\Zmsdb\Query;

class ExchangeWaitingscope extends Base
{
    /**
     * @var String TABLE mysql table reference
     */
    const TABLE = 'wartenrstatistik';

    const WAITING_VALUES = "
        MAX(echte_zeit_ab_00) as echte_zeit_ab_00,MAX(echte_zeit_ab_01) as echte_zeit_ab_01,
        MAX(echte_zeit_ab_02) as echte_zeit_ab_02,MAX(echte_zeit_ab_03) as echte_zeit_ab_03,
        MAX(echte_zeit_ab_04) as echte_zeit_ab_04,MAX(echte_zeit_ab_05) as echte_zeit_ab_05,
        MAX(echte_zeit_ab_06) as echte_zeit_ab_06,MAX(echte_zeit_ab_07) as echte_zeit_ab_07,
        MAX(echte_zeit_ab_08) as echte_zeit_ab_08,MAX(echte_zeit_ab_09) as echte_zeit_ab_09,
        MAX(echte_zeit_ab_10) as echte_zeit_ab_10,MAX(echte_zeit_ab_11) as echte_zeit_ab_11,
        MAX(echte_zeit_ab_12) as echte_zeit_ab_12,MAX(echte_zeit_ab_13) as echte_zeit_ab_13,
        MAX(echte_zeit_ab_14) as echte_zeit_ab_14,MAX(echte_zeit_ab_15) as echte_zeit_ab_15,
        MAX(echte_zeit_ab_16) as echte_zeit_ab_16,MAX(echte_zeit_ab_17) as echte_zeit_ab_17,
        MAX(echte_zeit_ab_18) as echte_zeit_ab_18,MAX(echte_zeit_ab_19) as echte_zeit_ab_19,
        MAX(echte_zeit_ab_20) as echte_zeit_ab_20,MAX(echte_zeit_ab_21) as echte_zeit_ab_21,
        MAX(echte_zeit_ab_22) as echte_zeit_ab_22,MAX(echte_zeit_ab_23) as echte_zeit_ab_23,
        MAX(zeit_ab_00) as zeit_ab_00,MAX(zeit_ab_01) as zeit_ab_01,MAX(zeit_ab_02) as zeit_ab_02,
        MAX(zeit_ab_03) as zeit_ab_03,MAX(zeit_ab_04) as zeit_ab_04,MAX(zeit_ab_05) as zeit_ab_05,
        MAX(zeit_ab_06) as zeit_ab_06,MAX(zeit_ab_07) as zeit_ab_07,MAX(zeit_ab_08) as zeit_ab_08,
        MAX(zeit_ab_09) as zeit_ab_09,MAX(zeit_ab_10) as zeit_ab_10,MAX(zeit_ab_11) as zeit_ab_11,
        MAX(zeit_ab_12) as zeit_ab_12,MAX(zeit_ab_13) as zeit_ab_13,MAX(zeit_ab_14) as zeit_ab_14,
        MAX(zeit_ab_15) as zeit_ab_15,MAX(zeit_ab_16) as zeit_ab_16,MAX(zeit_ab_17) as zeit_ab_17,
        MAX(zeit_ab_18) as zeit_ab_18,MAX(zeit_ab_19) as zeit_ab_19,MAX(zeit_ab_20) as zeit_ab_20,
        MAX(zeit_ab_21) as zeit_ab_21,MAX(zeit_ab_22) as zeit_ab_22,MAX(zeit_ab_23) as zeit_ab_23,
        MAX(wartende_ab_00) as wartende_ab_00,MAX(wartende_ab_01) as wartende_ab_01,
        MAX(wartende_ab_02) as wartende_ab_02,MAX(wartende_ab_03) as wartende_ab_03,
        MAX(wartende_ab_04) as wartende_ab_04,MAX(wartende_ab_05) as wartende_ab_05,
        MAX(wartende_ab_06) as wartende_ab_06,MAX(wartende_ab_07) as wartende_ab_07,
        MAX(wartende_ab_08) as wartende_ab_08,MAX(wartende_ab_09) as wartende_ab_09,
        MAX(wartende_ab_10) as wartende_ab_10,MAX(wartende_ab_11) as wartende_ab_11,
        MAX(wartende_ab_12) as wartende_ab_12,MAX(wartende_ab_13) as wartende_ab_13,
        MAX(wartende_ab_14) as wartende_ab_14,MAX(wartende_ab_15) as wartende_ab_15,
        MAX(wartende_ab_16) as wartende_ab_16,MAX(wartende_ab_17) as wartende_ab_17,
        MAX(wartende_ab_18) as wartende_ab_18,MAX(wartende_ab_19) as wartende_ab_19,
        MAX(wartende_ab_20) as wartende_ab_20,MAX(wartende_ab_21) as wartende_ab_21,
        MAX(wartende_ab_22) as wartende_ab_22,MAX(wartende_ab_23) as wartende_ab_23
    ";

    const QUERY_READ_DAY = '
        SELECT *, datum as date FROM ' . self::TABLE . '
        WHERE `standortid` = :scopeid
            AND `datum` BETWEEN :datestart AND :dateend
        GROUP BY `date`
        ORDER BY `date` ASC
    ';

    //PLEASE REMEMBER THE REALY COOL DYNAMIC VERSION
    const QUERY_READ_MONTH = "
        SELECT
      		DATE_FORMAT(`datum`, '%Y-%m') as date,
      		". self::WAITING_VALUES ."
      	FROM ". self::TABLE ."
      	WHERE
      		`standortid` = :scopeid AND
      		`datum` BETWEEN :datestart AND :dateend
      	GROUP BY date
      	ORDER BY date ASC
    ";

    const QUERY_READ_QUARTER = "
        SELECT
          CONCAT(YEAR(w.`datum`),'-',QUARTER(w.`datum`)) as datum,
          ". self::WAITING_VALUES ."
        FROM ". self::TABLE ." w
        WHERE
          w.`standortid` = :scopeid AND
          w.`datum` BETWEEN :datestart AND :dateend
        GROUP BY CONCAT(YEAR(w.`datum`),'-',QUARTER(w.`datum`))
        ORDER BY CONCAT(YEAR(w.`datum`),'-',QUARTER(w.`datum`)) ASC
    ";

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
