<?php

namespace BO\Zmsdb\Query;

class ExchangeWaitingscope extends Base
{
    /**
     * @var String TABLE mysql table reference
     */
    const TABLE = 'wartenrstatistik';

    const WAITING_VALUES = "
        AVG(hour_00_waiting_time_spontaneous) as hour_00_waiting_time_spontaneous,
        AVG(hour_01_waiting_time_spontaneous) as hour_01_waiting_time_spontaneous,
        AVG(hour_02_waiting_time_spontaneous) as hour_02_waiting_time_spontaneous,
        AVG(hour_03_waiting_time_spontaneous) as hour_03_waiting_time_spontaneous,
        AVG(hour_04_waiting_time_spontaneous) as hour_04_waiting_time_spontaneous,
        AVG(hour_05_waiting_time_spontaneous) as hour_05_waiting_time_spontaneous,
        AVG(hour_06_waiting_time_spontaneous) as hour_06_waiting_time_spontaneous,
        AVG(hour_07_waiting_time_spontaneous) as hour_07_waiting_time_spontaneous,
        AVG(hour_08_waiting_time_spontaneous) as hour_08_waiting_time_spontaneous,
        AVG(hour_09_waiting_time_spontaneous) as hour_09_waiting_time_spontaneous,
        AVG(hour_10_waiting_time_spontaneous) as hour_10_waiting_time_spontaneous,
        AVG(hour_11_waiting_time_spontaneous) as hour_11_waiting_time_spontaneous,
        AVG(hour_12_waiting_time_spontaneous) as hour_12_waiting_time_spontaneous,
        AVG(hour_13_waiting_time_spontaneous) as hour_13_waiting_time_spontaneous,
        AVG(hour_14_waiting_time_spontaneous) as hour_14_waiting_time_spontaneous,
        AVG(hour_15_waiting_time_spontaneous) as hour_15_waiting_time_spontaneous,
        AVG(hour_16_waiting_time_spontaneous) as hour_16_waiting_time_spontaneous,
        AVG(hour_17_waiting_time_spontaneous) as hour_17_waiting_time_spontaneous,
        AVG(hour_18_waiting_time_spontaneous) as hour_18_waiting_time_spontaneous,
        AVG(hour_19_waiting_time_spontaneous) as hour_19_waiting_time_spontaneous,
        AVG(hour_20_waiting_time_spontaneous) as hour_20_waiting_time_spontaneous,
        AVG(hour_21_waiting_time_spontaneous) as hour_21_waiting_time_spontaneous,
        AVG(hour_22_waiting_time_spontaneous) as hour_22_waiting_time_spontaneous,
        AVG(hour_23_waiting_time_spontaneous) as hour_23_waiting_time_spontaneous,
        AVG(hour_00_estimated_waiting_time_spontaneous) as hour_00_estimated_waiting_time_spontaneous,
        AVG(hour_01_estimated_waiting_time_spontaneous) as hour_01_estimated_waiting_time_spontaneous,
        AVG(hour_02_estimated_waiting_time_spontaneous) as hour_02_estimated_waiting_time_spontaneous,
        AVG(hour_03_estimated_waiting_time_spontaneous) as hour_03_estimated_waiting_time_spontaneous,
        AVG(hour_04_estimated_waiting_time_spontaneous) as hour_04_estimated_waiting_time_spontaneous,
        AVG(hour_05_estimated_waiting_time_spontaneous) as hour_05_estimated_waiting_time_spontaneous,
        AVG(hour_06_estimated_waiting_time_spontaneous) as hour_06_estimated_waiting_time_spontaneous,
        AVG(hour_07_estimated_waiting_time_spontaneous) as hour_07_estimated_waiting_time_spontaneous,
        AVG(hour_08_estimated_waiting_time_spontaneous) as hour_08_estimated_waiting_time_spontaneous,
        AVG(hour_09_estimated_waiting_time_spontaneous) as hour_09_estimated_waiting_time_spontaneous,
        AVG(hour_10_estimated_waiting_time_spontaneous) as hour_10_estimated_waiting_time_spontaneous,
        AVG(hour_11_estimated_waiting_time_spontaneous) as hour_11_estimated_waiting_time_spontaneous,
        AVG(hour_12_estimated_waiting_time_spontaneous) as hour_12_estimated_waiting_time_spontaneous,
        AVG(hour_13_estimated_waiting_time_spontaneous) as hour_13_estimated_waiting_time_spontaneous,
        AVG(hour_14_estimated_waiting_time_spontaneous) as hour_14_estimated_waiting_time_spontaneous,
        AVG(hour_15_estimated_waiting_time_spontaneous) as hour_15_estimated_waiting_time_spontaneous,
        AVG(hour_16_estimated_waiting_time_spontaneous) as hour_16_estimated_waiting_time_spontaneous,
        AVG(hour_17_estimated_waiting_time_spontaneous) as hour_17_estimated_waiting_time_spontaneous,
        AVG(hour_18_estimated_waiting_time_spontaneous) as hour_18_estimated_waiting_time_spontaneous,
        AVG(hour_19_estimated_waiting_time_spontaneous) as hour_19_estimated_waiting_time_spontaneous,
        AVG(hour_20_estimated_waiting_time_spontaneous) as hour_20_estimated_waiting_time_spontaneous,
        AVG(hour_21_estimated_waiting_time_spontaneous) as hour_21_estimated_waiting_time_spontaneous,
        AVG(hour_22_estimated_waiting_time_spontaneous) as hour_22_estimated_waiting_time_spontaneous,
        AVG(hour_23_estimated_waiting_time_spontaneous) as hour_23_estimated_waiting_time_spontaneous,
        AVG(hour_00_way_time_spontaneous) as hour_00_way_time_spontaneous,
        AVG(hour_01_way_time_spontaneous) as hour_01_way_time_spontaneous,
        AVG(hour_02_way_time_spontaneous) as hour_02_way_time_spontaneous,
        AVG(hour_03_way_time_spontaneous) as hour_03_way_time_spontaneous,
        AVG(hour_04_way_time_spontaneous) as hour_04_way_time_spontaneous,
        AVG(hour_05_way_time_spontaneous) as hour_05_way_time_spontaneous,
        AVG(hour_06_way_time_spontaneous) as hour_06_way_time_spontaneous,
        AVG(hour_07_way_time_spontaneous) as hour_07_way_time_spontaneous,
        AVG(hour_08_way_time_spontaneous) as hour_08_way_time_spontaneous,
        AVG(hour_09_way_time_spontaneous) as hour_09_way_time_spontaneous,
        AVG(hour_10_way_time_spontaneous) as hour_10_way_time_spontaneous,
        AVG(hour_11_way_time_spontaneous) as hour_11_way_time_spontaneous,
        AVG(hour_12_way_time_spontaneous) as hour_12_way_time_spontaneous,
        AVG(hour_13_way_time_spontaneous) as hour_13_way_time_spontaneous,
        AVG(hour_14_way_time_spontaneous) as hour_14_way_time_spontaneous,
        AVG(hour_15_way_time_spontaneous) as hour_15_way_time_spontaneous,
        AVG(hour_16_way_time_spontaneous) as hour_16_way_time_spontaneous,
        AVG(hour_17_way_time_spontaneous) as hour_17_way_time_spontaneous,
        AVG(hour_18_way_time_spontaneous) as hour_18_way_time_spontaneous,
        AVG(hour_19_way_time_spontaneous) as hour_19_way_time_spontaneous,
        AVG(hour_20_way_time_spontaneous) as hour_20_way_time_spontaneous,
        AVG(hour_21_way_time_spontaneous) as hour_21_way_time_spontaneous,
        AVG(hour_22_way_time_spontaneous) as hour_22_way_time_spontaneous,
        AVG(hour_23_way_time_spontaneous) as hour_23_way_time_spontaneous,
        MAX(hour_00_waiting_count_spontaneous) as hour_00_waiting_count_spontaneous,
        MAX(hour_01_waiting_count_spontaneous) as hour_01_waiting_count_spontaneous,
        MAX(hour_02_waiting_count_spontaneous) as hour_02_waiting_count_spontaneous,
        MAX(hour_03_waiting_count_spontaneous) as hour_03_waiting_count_spontaneous,
        MAX(hour_04_waiting_count_spontaneous) as hour_04_waiting_count_spontaneous,
        MAX(hour_05_waiting_count_spontaneous) as hour_05_waiting_count_spontaneous,
        MAX(hour_06_waiting_count_spontaneous) as hour_06_waiting_count_spontaneous,
        MAX(hour_07_waiting_count_spontaneous) as hour_07_waiting_count_spontaneous,
        MAX(hour_08_waiting_count_spontaneous) as hour_08_waiting_count_spontaneous,
        MAX(hour_09_waiting_count_spontaneous) as hour_09_waiting_count_spontaneous,
        MAX(hour_10_waiting_count_spontaneous) as hour_10_waiting_count_spontaneous,
        MAX(hour_11_waiting_count_spontaneous) as hour_11_waiting_count_spontaneous,
        MAX(hour_12_waiting_count_spontaneous) as hour_12_waiting_count_spontaneous,
        MAX(hour_13_waiting_count_spontaneous) as hour_13_waiting_count_spontaneous,
        MAX(hour_14_waiting_count_spontaneous) as hour_14_waiting_count_spontaneous,
        MAX(hour_15_waiting_count_spontaneous) as hour_15_waiting_count_spontaneous,
        MAX(hour_16_waiting_count_spontaneous) as hour_16_waiting_count_spontaneous,
        MAX(hour_17_waiting_count_spontaneous) as hour_17_waiting_count_spontaneous,
        MAX(hour_18_waiting_count_spontaneous) as hour_18_waiting_count_spontaneous,
        MAX(hour_19_waiting_count_spontaneous) as hour_19_waiting_count_spontaneous,
        MAX(hour_20_waiting_count_spontaneous) as hour_20_waiting_count_spontaneous,
        MAX(hour_21_waiting_count_spontaneous) as hour_21_waiting_count_spontaneous,
        MAX(hour_22_waiting_count_spontaneous) as hour_22_waiting_count_spontaneous,
        MAX(hour_23_waiting_count_spontaneous) as hour_23_waiting_count_spontaneous,
        AVG(hour_00_waiting_time_appointment) as hour_00_waiting_time_appointment,
        AVG(hour_01_waiting_time_appointment) as hour_01_waiting_time_appointment,
        AVG(hour_02_waiting_time_appointment) as hour_02_waiting_time_appointment,
        AVG(hour_03_waiting_time_appointment) as hour_03_waiting_time_appointment,
        AVG(hour_04_waiting_time_appointment) as hour_04_waiting_time_appointment,
        AVG(hour_05_waiting_time_appointment) as hour_05_waiting_time_appointment,
        AVG(hour_06_waiting_time_appointment) as hour_06_waiting_time_appointment,
        AVG(hour_07_waiting_time_appointment) as hour_07_waiting_time_appointment,
        AVG(hour_08_waiting_time_appointment) as hour_08_waiting_time_appointment,
        AVG(hour_09_waiting_time_appointment) as hour_09_waiting_time_appointment,
        AVG(hour_10_waiting_time_appointment) as hour_10_waiting_time_appointment,
        AVG(hour_11_waiting_time_appointment) as hour_11_waiting_time_appointment,
        AVG(hour_12_waiting_time_appointment) as hour_12_waiting_time_appointment,
        AVG(hour_13_waiting_time_appointment) as hour_13_waiting_time_appointment,
        AVG(hour_14_waiting_time_appointment) as hour_14_waiting_time_appointment,
        AVG(hour_15_waiting_time_appointment) as hour_15_waiting_time_appointment,
        AVG(hour_16_waiting_time_appointment) as hour_16_waiting_time_appointment,
        AVG(hour_17_waiting_time_appointment) as hour_17_waiting_time_appointment,
        AVG(hour_18_waiting_time_appointment) as hour_18_waiting_time_appointment,
        AVG(hour_19_waiting_time_appointment) as hour_19_waiting_time_appointment,
        AVG(hour_20_waiting_time_appointment) as hour_20_waiting_time_appointment,
        AVG(hour_21_waiting_time_appointment) as hour_21_waiting_time_appointment,
        AVG(hour_22_waiting_time_appointment) as hour_22_waiting_time_appointment,
        AVG(hour_23_waiting_time_appointment) as hour_23_waiting_time_appointment,
        AVG(hour_00_estimated_waiting_time_appointment) as hour_00_estimated_waiting_time_appointment,
        AVG(hour_01_estimated_waiting_time_appointment) as hour_01_estimated_waiting_time_appointment,
        AVG(hour_02_estimated_waiting_time_appointment) as hour_02_estimated_waiting_time_appointment,
        AVG(hour_03_estimated_waiting_time_appointment) as hour_03_estimated_waiting_time_appointment,
        AVG(hour_04_estimated_waiting_time_appointment) as hour_04_estimated_waiting_time_appointment,
        AVG(hour_05_estimated_waiting_time_appointment) as hour_05_estimated_waiting_time_appointment,
        AVG(hour_06_estimated_waiting_time_appointment) as hour_06_estimated_waiting_time_appointment,
        AVG(hour_07_estimated_waiting_time_appointment) as hour_07_estimated_waiting_time_appointment,
        AVG(hour_08_estimated_waiting_time_appointment) as hour_08_estimated_waiting_time_appointment,
        AVG(hour_09_estimated_waiting_time_appointment) as hour_09_estimated_waiting_time_appointment,
        AVG(hour_10_estimated_waiting_time_appointment) as hour_10_estimated_waiting_time_appointment,
        AVG(hour_11_estimated_waiting_time_appointment) as hour_11_estimated_waiting_time_appointment,
        AVG(hour_12_estimated_waiting_time_appointment) as hour_12_estimated_waiting_time_appointment,
        AVG(hour_13_estimated_waiting_time_appointment) as hour_13_estimated_waiting_time_appointment,
        AVG(hour_14_estimated_waiting_time_appointment) as hour_14_estimated_waiting_time_appointment,
        AVG(hour_15_estimated_waiting_time_appointment) as hour_15_estimated_waiting_time_appointment,
        AVG(hour_16_estimated_waiting_time_appointment) as hour_16_estimated_waiting_time_appointment,
        AVG(hour_17_estimated_waiting_time_appointment) as hour_17_estimated_waiting_time_appointment,
        AVG(hour_18_estimated_waiting_time_appointment) as hour_18_estimated_waiting_time_appointment,
        AVG(hour_19_estimated_waiting_time_appointment) as hour_19_estimated_waiting_time_appointment,
        AVG(hour_20_estimated_waiting_time_appointment) as hour_20_estimated_waiting_time_appointment,
        AVG(hour_21_estimated_waiting_time_appointment) as hour_21_estimated_waiting_time_appointment,
        AVG(hour_22_estimated_waiting_time_appointment) as hour_22_estimated_waiting_time_appointment,
        AVG(hour_23_estimated_waiting_time_appointment) as hour_23_estimated_waiting_time_appointment,
        AVG(hour_00_way_time_appointment) as hour_00_way_time_appointment,
        AVG(hour_01_way_time_appointment) as hour_01_way_time_appointment,
        AVG(hour_02_way_time_appointment) as hour_02_way_time_appointment,
        AVG(hour_03_way_time_appointment) as hour_03_way_time_appointment,
        AVG(hour_04_way_time_appointment) as hour_04_way_time_appointment,
        AVG(hour_05_way_time_appointment) as hour_05_way_time_appointment,
        AVG(hour_06_way_time_appointment) as hour_06_way_time_appointment,
        AVG(hour_07_way_time_appointment) as hour_07_way_time_appointment,
        AVG(hour_08_way_time_appointment) as hour_08_way_time_appointment,
        AVG(hour_09_way_time_appointment) as hour_09_way_time_appointment,
        AVG(hour_10_way_time_appointment) as hour_10_way_time_appointment,
        AVG(hour_11_way_time_appointment) as hour_11_way_time_appointment,
        AVG(hour_12_way_time_appointment) as hour_12_way_time_appointment,
        AVG(hour_13_way_time_appointment) as hour_13_way_time_appointment,
        AVG(hour_14_way_time_appointment) as hour_14_way_time_appointment,
        AVG(hour_15_way_time_appointment) as hour_15_way_time_appointment,
        AVG(hour_16_way_time_appointment) as hour_16_way_time_appointment,
        AVG(hour_17_way_time_appointment) as hour_17_way_time_appointment,
        AVG(hour_18_way_time_appointment) as hour_18_way_time_appointment,
        AVG(hour_19_way_time_appointment) as hour_19_way_time_appointment,
        AVG(hour_20_way_time_appointment) as hour_20_way_time_appointment,
        AVG(hour_21_way_time_appointment) as hour_21_way_time_appointment,
        AVG(hour_22_way_time_appointment) as hour_22_way_time_appointment,
        AVG(hour_23_way_time_appointment) as hour_23_way_time_appointment,
        MAX(hour_00_waiting_count_appointment) as hour_00_waiting_count_appointment,
        MAX(hour_01_waiting_count_appointment) as hour_01_waiting_count_appointment,
        MAX(hour_02_waiting_count_appointment) as hour_02_waiting_count_appointment,
        MAX(hour_03_waiting_count_appointment) as hour_03_waiting_count_appointment,
        MAX(hour_04_waiting_count_appointment) as hour_04_waiting_count_appointment,
        MAX(hour_05_waiting_count_appointment) as hour_05_waiting_count_appointment,
        MAX(hour_06_waiting_count_appointment) as hour_06_waiting_count_appointment,
        MAX(hour_07_waiting_count_appointment) as hour_07_waiting_count_appointment,
        MAX(hour_08_waiting_count_appointment) as hour_08_waiting_count_appointment,
        MAX(hour_09_waiting_count_appointment) as hour_09_waiting_count_appointment,
        MAX(hour_10_waiting_count_appointment) as hour_10_waiting_count_appointment,
        MAX(hour_11_waiting_count_appointment) as hour_11_waiting_count_appointment,
        MAX(hour_12_waiting_count_appointment) as hour_12_waiting_count_appointment,
        MAX(hour_13_waiting_count_appointment) as hour_13_waiting_count_appointment,
        MAX(hour_14_waiting_count_appointment) as hour_14_waiting_count_appointment,
        MAX(hour_15_waiting_count_appointment) as hour_15_waiting_count_appointment,
        MAX(hour_16_waiting_count_appointment) as hour_16_waiting_count_appointment,
        MAX(hour_17_waiting_count_appointment) as hour_17_waiting_count_appointment,
        MAX(hour_18_waiting_count_appointment) as hour_18_waiting_count_appointment,
        MAX(hour_19_waiting_count_appointment) as hour_19_waiting_count_appointment,
        MAX(hour_20_waiting_count_appointment) as hour_20_waiting_count_appointment,
        MAX(hour_21_waiting_count_appointment) as hour_21_waiting_count_appointment,
        MAX(hour_22_waiting_count_appointment) as hour_22_waiting_count_appointment,
        MAX(hour_23_waiting_count_appointment) as hour_23_waiting_count_appointment
    ";
    const QUERY_READ_DAY = "
        SELECT
            standortid,
            `datum` AS datum,
            " . self::WAITING_VALUES . "
        FROM " . self::TABLE . "
        WHERE
            `standortid` IN (:scopeid) AND
            `datum` BETWEEN :datestart AND :dateend
        GROUP BY `datum`
        ORDER BY `datum` ASC
    ";

    //PLEASE REMEMBER THE REALY COOL DYNAMIC VERSION
    const QUERY_READ_MONTH = "
        SELECT
            standortid,
            DATE_FORMAT(`datum`, '%Y-%m') as datum,
            " . self::WAITING_VALUES . "
        FROM " . self::TABLE . "
        WHERE
            `standortid` = :scopeid AND
            `datum` BETWEEN :datestart AND :dateend
        GROUP BY DATE_FORMAT(`datum`, '%Y-%m')
        ORDER BY DATE_FORMAT(`datum`, '%Y-%m') ASC
    ";

    const QUERY_READ_QUARTER = "
        SELECT
            standortid,
            CONCAT(YEAR(w.`datum`),'-',QUARTER(w.`datum`)) as datum,
            " . self::WAITING_VALUES . "
        FROM " . self::TABLE . " w
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
        FROM ' . Scope::TABLE . ' AS scope
            INNER JOIN
              (
          SELECT
            w.standortid as scopeid,
            MIN(w.`datum`) AS periodstart,
            MAX(w.`datum`) AS periodend
          FROM ' . self::TABLE . ' w
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
        $hourSuffix = $withAppointment ? 'appointment' : 'spontaneous';

        $query = sprintf(
            "SELECT
                `hour_%s_estimated_waiting_time_%s` AS waitingcalculated,
                `hour_%s_waiting_count_%s` AS waitingcount,
                `hour_%s_waiting_time_%s` AS waitingtime,
                `hour_%s_way_time_%s` AS waytime
             FROM %s
             WHERE `standortid` = :scopeid
                AND `datum` = :date
                AND :hour IS NOT NULL
            ",
            $date->format('H'),
            $hourSuffix,
            $date->format('H'),
            $hourSuffix,
            $date->format('H'),
            $hourSuffix,
            $date->format('H'),
            $hourSuffix,
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
        $hourSuffix = $withAppointment ? 'appointment' : 'spontaneous';

        $query = sprintf(
            "UPDATE %s
             SET
                `hour_%s_estimated_waiting_time_%s`= :waitingcalculated,
                `hour_%s_waiting_count_%s` = :waitingcount,
                `hour_%s_waiting_time_%s` = :waitingtime,
                `hour_%s_way_time_%s` = :waytime
             WHERE `standortid` = :scopeid
                AND `datum` = :date
                AND :hour IS NOT NULL
            ",
            self::TABLE,
            $date->format('H'),
            $hourSuffix,
            $date->format('H'),
            $hourSuffix,
            $date->format('H'),
            $hourSuffix,
            $date->format('H'),
            $hourSuffix
        );
        return $query;
    }
}
