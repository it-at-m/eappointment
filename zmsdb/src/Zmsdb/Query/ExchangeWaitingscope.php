<?php

namespace BO\Zmsdb\Query;

class ExchangeWaitingscope extends Base
{
    /**
     * @var String TABLE mysql table reference
     */
    const TABLE = 'wartenrstatistik';

    const WAITING_VALUES = "
        AVG(echte_zeit_ab_00_spontan) as echte_zeit_ab_00_spontan,
        AVG(echte_zeit_ab_01_spontan) as echte_zeit_ab_01_spontan,
        AVG(echte_zeit_ab_02_spontan) as echte_zeit_ab_02_spontan,
        AVG(echte_zeit_ab_03_spontan) as echte_zeit_ab_03_spontan,
        AVG(echte_zeit_ab_04_spontan) as echte_zeit_ab_04_spontan,
        AVG(echte_zeit_ab_05_spontan) as echte_zeit_ab_05_spontan,
        AVG(echte_zeit_ab_06_spontan) as echte_zeit_ab_06_spontan,
        AVG(echte_zeit_ab_07_spontan) as echte_zeit_ab_07_spontan,
        AVG(echte_zeit_ab_08_spontan) as echte_zeit_ab_08_spontan,
        AVG(echte_zeit_ab_09_spontan) as echte_zeit_ab_09_spontan,
        AVG(echte_zeit_ab_10_spontan) as echte_zeit_ab_10_spontan,
        AVG(echte_zeit_ab_11_spontan) as echte_zeit_ab_11_spontan,
        AVG(echte_zeit_ab_12_spontan) as echte_zeit_ab_12_spontan,
        AVG(echte_zeit_ab_13_spontan) as echte_zeit_ab_13_spontan,
        AVG(echte_zeit_ab_14_spontan) as echte_zeit_ab_14_spontan,
        AVG(echte_zeit_ab_15_spontan) as echte_zeit_ab_15_spontan,
        AVG(echte_zeit_ab_16_spontan) as echte_zeit_ab_16_spontan,
        AVG(echte_zeit_ab_17_spontan) as echte_zeit_ab_17_spontan,
        AVG(echte_zeit_ab_18_spontan) as echte_zeit_ab_18_spontan,
        AVG(echte_zeit_ab_19_spontan) as echte_zeit_ab_19_spontan,
        AVG(echte_zeit_ab_20_spontan) as echte_zeit_ab_20_spontan,
        AVG(echte_zeit_ab_21_spontan) as echte_zeit_ab_21_spontan,
        AVG(echte_zeit_ab_22_spontan) as echte_zeit_ab_22_spontan,
        AVG(echte_zeit_ab_23_spontan) as echte_zeit_ab_23_spontan,
        AVG(zeit_ab_00_spontan) as zeit_ab_00_spontan,
        AVG(zeit_ab_01_spontan) as zeit_ab_01_spontan,
        AVG(zeit_ab_02_spontan) as zeit_ab_02_spontan,
        AVG(zeit_ab_03_spontan) as zeit_ab_03_spontan,
        AVG(zeit_ab_04_spontan) as zeit_ab_04_spontan,
        AVG(zeit_ab_05_spontan) as zeit_ab_05_spontan,
        AVG(zeit_ab_06_spontan) as zeit_ab_06_spontan,
        AVG(zeit_ab_07_spontan) as zeit_ab_07_spontan,
        AVG(zeit_ab_08_spontan) as zeit_ab_08_spontan,
        AVG(zeit_ab_09_spontan) as zeit_ab_09_spontan,
        AVG(zeit_ab_10_spontan) as zeit_ab_10_spontan,
        AVG(zeit_ab_11_spontan) as zeit_ab_11_spontan,
        AVG(zeit_ab_12_spontan) as zeit_ab_12_spontan,
        AVG(zeit_ab_13_spontan) as zeit_ab_13_spontan,
        AVG(zeit_ab_14_spontan) as zeit_ab_14_spontan,
        AVG(zeit_ab_15_spontan) as zeit_ab_15_spontan,
        AVG(zeit_ab_16_spontan) as zeit_ab_16_spontan,
        AVG(zeit_ab_17_spontan) as zeit_ab_17_spontan,
        AVG(zeit_ab_18_spontan) as zeit_ab_18_spontan,
        AVG(zeit_ab_19_spontan) as zeit_ab_19_spontan,
        AVG(zeit_ab_20_spontan) as zeit_ab_20_spontan,
        AVG(zeit_ab_21_spontan) as zeit_ab_21_spontan,
        AVG(zeit_ab_22_spontan) as zeit_ab_22_spontan,
        AVG(zeit_ab_23_spontan) as zeit_ab_23_spontan,
        AVG(wegezeit_ab_00_spontan) as wegezeit_ab_00_spontan,
        AVG(wegezeit_ab_01_spontan) as wegezeit_ab_01_spontan,
        AVG(wegezeit_ab_02_spontan) as wegezeit_ab_02_spontan,
        AVG(wegezeit_ab_03_spontan) as wegezeit_ab_03_spontan,
        AVG(wegezeit_ab_04_spontan) as wegezeit_ab_04_spontan,
        AVG(wegezeit_ab_05_spontan) as wegezeit_ab_05_spontan,
        AVG(wegezeit_ab_06_spontan) as wegezeit_ab_06_spontan,
        AVG(wegezeit_ab_07_spontan) as wegezeit_ab_07_spontan,
        AVG(wegezeit_ab_08_spontan) as wegezeit_ab_08_spontan,
        AVG(wegezeit_ab_09_spontan) as wegezeit_ab_09_spontan,
        AVG(wegezeit_ab_10_spontan) as wegezeit_ab_10_spontan,
        AVG(wegezeit_ab_11_spontan) as wegezeit_ab_11_spontan,
        AVG(wegezeit_ab_12_spontan) as wegezeit_ab_12_spontan,
        AVG(wegezeit_ab_13_spontan) as wegezeit_ab_13_spontan,
        AVG(wegezeit_ab_14_spontan) as wegezeit_ab_14_spontan,
        AVG(wegezeit_ab_15_spontan) as wegezeit_ab_15_spontan,
        AVG(wegezeit_ab_16_spontan) as wegezeit_ab_16_spontan,
        AVG(wegezeit_ab_17_spontan) as wegezeit_ab_17_spontan,
        AVG(wegezeit_ab_18_spontan) as wegezeit_ab_18_spontan,
        AVG(wegezeit_ab_19_spontan) as wegezeit_ab_19_spontan,
        AVG(wegezeit_ab_20_spontan) as wegezeit_ab_20_spontan,
        AVG(wegezeit_ab_21_spontan) as wegezeit_ab_21_spontan,
        AVG(wegezeit_ab_22_spontan) as wegezeit_ab_22_spontan,
        AVG(wegezeit_ab_23_spontan) as wegezeit_ab_23_spontan,
        MAX(wartende_ab_00_spontan) as wartende_ab_00_spontan,
        MAX(wartende_ab_01_spontan) as wartende_ab_01_spontan,
        MAX(wartende_ab_02_spontan) as wartende_ab_02_spontan,
        MAX(wartende_ab_03_spontan) as wartende_ab_03_spontan,
        MAX(wartende_ab_04_spontan) as wartende_ab_04_spontan,
        MAX(wartende_ab_05_spontan) as wartende_ab_05_spontan,
        MAX(wartende_ab_06_spontan) as wartende_ab_06_spontan,
        MAX(wartende_ab_07_spontan) as wartende_ab_07_spontan,
        MAX(wartende_ab_08_spontan) as wartende_ab_08_spontan,
        MAX(wartende_ab_09_spontan) as wartende_ab_09_spontan,
        MAX(wartende_ab_10_spontan) as wartende_ab_10_spontan,
        MAX(wartende_ab_11_spontan) as wartende_ab_11_spontan,
        MAX(wartende_ab_12_spontan) as wartende_ab_12_spontan,
        MAX(wartende_ab_13_spontan) as wartende_ab_13_spontan,
        MAX(wartende_ab_14_spontan) as wartende_ab_14_spontan,
        MAX(wartende_ab_15_spontan) as wartende_ab_15_spontan,
        MAX(wartende_ab_16_spontan) as wartende_ab_16_spontan,
        MAX(wartende_ab_17_spontan) as wartende_ab_17_spontan,
        MAX(wartende_ab_18_spontan) as wartende_ab_18_spontan,
        MAX(wartende_ab_19_spontan) as wartende_ab_19_spontan,
        MAX(wartende_ab_20_spontan) as wartende_ab_20_spontan,
        MAX(wartende_ab_21_spontan) as wartende_ab_21_spontan,
        MAX(wartende_ab_22_spontan) as wartende_ab_22_spontan,
        MAX(wartende_ab_23_spontan) as wartende_ab_23_spontan,
        AVG(echte_zeit_ab_00_termin) as echte_zeit_ab_00_termin,
        AVG(echte_zeit_ab_01_termin) as echte_zeit_ab_01_termin,
        AVG(echte_zeit_ab_02_termin) as echte_zeit_ab_02_termin,
        AVG(echte_zeit_ab_03_termin) as echte_zeit_ab_03_termin,
        AVG(echte_zeit_ab_04_termin) as echte_zeit_ab_04_termin,
        AVG(echte_zeit_ab_05_termin) as echte_zeit_ab_05_termin,
        AVG(echte_zeit_ab_06_termin) as echte_zeit_ab_06_termin,
        AVG(echte_zeit_ab_07_termin) as echte_zeit_ab_07_termin,
        AVG(echte_zeit_ab_08_termin) as echte_zeit_ab_08_termin,
        AVG(echte_zeit_ab_09_termin) as echte_zeit_ab_09_termin,
        AVG(echte_zeit_ab_10_termin) as echte_zeit_ab_10_termin,
        AVG(echte_zeit_ab_11_termin) as echte_zeit_ab_11_termin,
        AVG(echte_zeit_ab_12_termin) as echte_zeit_ab_12_termin,
        AVG(echte_zeit_ab_13_termin) as echte_zeit_ab_13_termin,
        AVG(echte_zeit_ab_14_termin) as echte_zeit_ab_14_termin,
        AVG(echte_zeit_ab_15_termin) as echte_zeit_ab_15_termin,
        AVG(echte_zeit_ab_16_termin) as echte_zeit_ab_16_termin,
        AVG(echte_zeit_ab_17_termin) as echte_zeit_ab_17_termin,
        AVG(echte_zeit_ab_18_termin) as echte_zeit_ab_18_termin,
        AVG(echte_zeit_ab_19_termin) as echte_zeit_ab_19_termin,
        AVG(echte_zeit_ab_20_termin) as echte_zeit_ab_20_termin,
        AVG(echte_zeit_ab_21_termin) as echte_zeit_ab_21_termin,
        AVG(echte_zeit_ab_22_termin) as echte_zeit_ab_22_termin,
        AVG(echte_zeit_ab_23_termin) as echte_zeit_ab_23_termin,
        AVG(zeit_ab_00_termin) as zeit_ab_00_termin,
        AVG(zeit_ab_01_termin) as zeit_ab_01_termin,
        AVG(zeit_ab_02_termin) as zeit_ab_02_termin,
        AVG(zeit_ab_03_termin) as zeit_ab_03_termin,
        AVG(zeit_ab_04_termin) as zeit_ab_04_termin,
        AVG(zeit_ab_05_termin) as zeit_ab_05_termin,
        AVG(zeit_ab_06_termin) as zeit_ab_06_termin,
        AVG(zeit_ab_07_termin) as zeit_ab_07_termin,
        AVG(zeit_ab_08_termin) as zeit_ab_08_termin,
        AVG(zeit_ab_09_termin) as zeit_ab_09_termin,
        AVG(zeit_ab_10_termin) as zeit_ab_10_termin,
        AVG(zeit_ab_11_termin) as zeit_ab_11_termin,
        AVG(zeit_ab_12_termin) as zeit_ab_12_termin,
        AVG(zeit_ab_13_termin) as zeit_ab_13_termin,
        AVG(zeit_ab_14_termin) as zeit_ab_14_termin,
        AVG(zeit_ab_15_termin) as zeit_ab_15_termin,
        AVG(zeit_ab_16_termin) as zeit_ab_16_termin,
        AVG(zeit_ab_17_termin) as zeit_ab_17_termin,
        AVG(zeit_ab_18_termin) as zeit_ab_18_termin,
        AVG(zeit_ab_19_termin) as zeit_ab_19_termin,
        AVG(zeit_ab_20_termin) as zeit_ab_20_termin,
        AVG(zeit_ab_21_termin) as zeit_ab_21_termin,
        AVG(zeit_ab_22_termin) as zeit_ab_22_termin,
        AVG(zeit_ab_23_termin) as zeit_ab_23_termin,
        AVG(wegezeit_ab_00_termin) as wegezeit_ab_00_termin,
        AVG(wegezeit_ab_01_termin) as wegezeit_ab_01_termin,
        AVG(wegezeit_ab_02_termin) as wegezeit_ab_02_termin,
        AVG(wegezeit_ab_03_termin) as wegezeit_ab_03_termin,
        AVG(wegezeit_ab_04_termin) as wegezeit_ab_04_termin,
        AVG(wegezeit_ab_05_termin) as wegezeit_ab_05_termin,
        AVG(wegezeit_ab_06_termin) as wegezeit_ab_06_termin,
        AVG(wegezeit_ab_07_termin) as wegezeit_ab_07_termin,
        AVG(wegezeit_ab_08_termin) as wegezeit_ab_08_termin,
        AVG(wegezeit_ab_09_termin) as wegezeit_ab_09_termin,
        AVG(wegezeit_ab_10_termin) as wegezeit_ab_10_termin,
        AVG(wegezeit_ab_11_termin) as wegezeit_ab_11_termin,
        AVG(wegezeit_ab_12_termin) as wegezeit_ab_12_termin,
        AVG(wegezeit_ab_13_termin) as wegezeit_ab_13_termin,
        AVG(wegezeit_ab_14_termin) as wegezeit_ab_14_termin,
        AVG(wegezeit_ab_15_termin) as wegezeit_ab_15_termin,
        AVG(wegezeit_ab_16_termin) as wegezeit_ab_16_termin,
        AVG(wegezeit_ab_17_termin) as wegezeit_ab_17_termin,
        AVG(wegezeit_ab_18_termin) as wegezeit_ab_18_termin,
        AVG(wegezeit_ab_19_termin) as wegezeit_ab_19_termin,
        AVG(wegezeit_ab_20_termin) as wegezeit_ab_20_termin,
        AVG(wegezeit_ab_21_termin) as wegezeit_ab_21_termin,
        AVG(wegezeit_ab_22_termin) as wegezeit_ab_22_termin,
        AVG(wegezeit_ab_23_termin) as wegezeit_ab_23_termin,
        MAX(wartende_ab_00_termin) as wartende_ab_00_termin,
        MAX(wartende_ab_01_termin) as wartende_ab_01_termin,
        MAX(wartende_ab_02_termin) as wartende_ab_02_termin,
        MAX(wartende_ab_03_termin) as wartende_ab_03_termin,
        MAX(wartende_ab_04_termin) as wartende_ab_04_termin,
        MAX(wartende_ab_05_termin) as wartende_ab_05_termin,
        MAX(wartende_ab_06_termin) as wartende_ab_06_termin,
        MAX(wartende_ab_07_termin) as wartende_ab_07_termin,
        MAX(wartende_ab_08_termin) as wartende_ab_08_termin,
        MAX(wartende_ab_09_termin) as wartende_ab_09_termin,
        MAX(wartende_ab_10_termin) as wartende_ab_10_termin,
        MAX(wartende_ab_11_termin) as wartende_ab_11_termin,
        MAX(wartende_ab_12_termin) as wartende_ab_12_termin,
        MAX(wartende_ab_13_termin) as wartende_ab_13_termin,
        MAX(wartende_ab_14_termin) as wartende_ab_14_termin,
        MAX(wartende_ab_15_termin) as wartende_ab_15_termin,
        MAX(wartende_ab_16_termin) as wartende_ab_16_termin,
        MAX(wartende_ab_17_termin) as wartende_ab_17_termin,
        MAX(wartende_ab_18_termin) as wartende_ab_18_termin,
        MAX(wartende_ab_19_termin) as wartende_ab_19_termin,
        MAX(wartende_ab_20_termin) as wartende_ab_20_termin,
        MAX(wartende_ab_21_termin) as wartende_ab_21_termin,
        MAX(wartende_ab_22_termin) as wartende_ab_22_termin,
        MAX(wartende_ab_23_termin) as wartende_ab_23_termin
    ";

    const QUERY_READ_DAY = "
        SELECT
            `datum` AS datum,
            " . self::WAITING_VALUES . "
        FROM " . self::TABLE . "
        WHERE
            `standortid` = :scopeid AND
            `datum` BETWEEN :datestart AND :dateend
        GROUP BY `datum`
        ORDER BY `datum` ASC
    ";

    //PLEASE REMEMBER THE REALY COOL DYNAMIC VERSION
    const QUERY_READ_MONTH = "
        SELECT
            DATE_FORMAT(`datum`, '%Y-%m') as datum,
            ". self::WAITING_VALUES ."
        FROM ". self::TABLE ."
        WHERE
            `standortid` = :scopeid AND
            `datum` BETWEEN :datestart AND :dateend
        GROUP BY DATE_FORMAT(`datum`, '%Y-%m')
        ORDER BY DATE_FORMAT(`datum`, '%Y-%m') ASC
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
        ORDER BY scope.`StandortID` ASC, periodstart DESC
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

    const QUERY_CREATE = '
        INSERT INTO ' . self::TABLE . ' SET
            `standortid` = :scopeid,
            `datum` = :date
    ';

    /**
     * For backward compatibility on db table optimization, we have to convert the field name
     * Drawback: No prepared statement using the date
     */
    public static function getQuerySelectByDateTime(\DateTimeInterface $date, bool $withAppointment = false)
    {
        $suffix = $withAppointment ? 'termin' : 'spontan';

        $query = sprintf(
            "SELECT
                `zeit_ab_%s_%s` AS waitingcalculated,
                `wartende_ab_%s_%s` AS waitingcount,
                `echte_zeit_ab_%s_%s` AS waitingtime,
                `wegezeit_ab_%s_%s` AS waytime
             FROM %s
             WHERE `standortid` = :scopeid
                AND `datum` = :date
                AND :hour IS NOT NULL
            ",
            $date->format('H'),
            $suffix,
            $date->format('H'),
            $suffix,
            $date->format('H'),
            $suffix,
            $date->format('H'),
            $suffix,
            self::TABLE
        );
        return $query;
    }

    /**
     * For backward compatibility on db table optimization, we have to convert the field name
     * Drawback: No prepared statement using the date
     */
    public static function getQueryUpdateByDateTime(\DateTimeInterface $date, bool $withAppointment = false)
    {
        $suffix = $withAppointment ? 'termin' : 'spontan';

        $query = sprintf(
            "UPDATE %s
             SET
                `zeit_ab_%s_%s`= :waitingcalculated,
                `wartende_ab_%s_%s` = :waitingcount,
                `echte_zeit_ab_%s_%s` = :waitingtime,
                `wegezeit_ab_%s_%s` = :waytime
             WHERE `standortid` = :scopeid
                AND `datum` = :date
                AND :hour IS NOT NULL
            ",
            self::TABLE,
            $date->format('H'),
            $suffix,
            $date->format('H'),
            $suffix,
            $date->format('H'),
            $suffix,
            $date->format('H'),
            $suffix
        );
        return $query;
    }
}
