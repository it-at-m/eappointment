<?php

namespace BO\Zmsbackend\Exchange\Repository;

class ExchangeCapacityscope extends \BO\Zmsbackend\Query\Base
{
    /**
     * @var String TABLE mysql table reference
     */
    const string TABLE = 'slot_process';

    /**
     * Scope picker list. Min/max dates are index lookups per scope, not a scan of every slot.
     */
    const string QUERY_CAPACITY_REPORT_SCOPE_SUBJECT_LIST = '
      SELECT
          scope.`StandortID` as subject,
          (
              SELECT CONCAT(s.year, "-", LPAD(s.month, 2, "0"), "-", LPAD(s.day, 2, "0"))
              FROM slot AS s
              WHERE s.scopeID = scope.`StandortID`
              ORDER BY s.year, s.month, s.day
              LIMIT 1
          ) AS periodstart,
          (
              SELECT CONCAT(s.year, "-", LPAD(s.month, 2, "0"), "-", LPAD(s.day, 2, "0"))
              FROM slot AS s
              WHERE s.scopeID = scope.`StandortID`
              ORDER BY s.year DESC, s.month DESC, s.day DESC
              LIMIT 1
          ) AS periodend,
          CONCAT(scope.`Bezeichnung`, " ", scope.`standortinfozeile`) AS description
      FROM ' . \BO\Zmsbackend\Query\Scope::TABLE . ' AS scope
      HAVING periodstart IS NOT NULL
      ORDER BY description ASC
    ';

    /**
     * Planned totals from slot, booked totals from appointments on those slots.
     * All scopes are one statement. Bookings are reached from those slots, not from every row in slot_process.
     *
     * @param array<int, int> $scopeIds
     * @param array<string, int> $parameters
     */
    public static function buildCapacityMetricsQuery(
        array $scopeIds,
        ?\DateTimeInterface $dateStart,
        ?\DateTimeInterface $dateEnd,
        string $period,
        array &$parameters
    ): string {
        $parameters = [];
        $plannedFilter = self::slotFilterSql('s', 'o', $scopeIds, $dateStart, $dateEnd, $parameters);
        $bookedFilter = self::slotFilterSql('s', 'i', $scopeIds, $dateStart, $dateEnd, $parameters);
        $hourly = $period === 'hour';
        $dateExpression = $hourly
            ? 'CONCAT(s.year, "-", LPAD(s.month, 2, "0"), "-", LPAD(s.day, 2, "0"), " ", '
                . 'LPAD(HOUR(s.`time`), 2, "0"), ":00")'
            : 'CONCAT(s.year, "-", LPAD(s.month, 2, "0"), "-", LPAD(s.day, 2, "0"))';
        $groupBy = $hourly
            ? 's.scopeID, s.year, s.month, s.day, HOUR(s.`time`)'
            : 's.scopeID, s.year, s.month, s.day';

        return '
        SELECT
            planned.subjectid,
            planned.date,
            COALESCE(booked.slotcount, 0),
            planned.plannedcount,
            COALESCE(booked.bookedminutes, 0),
            planned.plannedminutes,
            COALESCE(booked.slotcount_public, 0),
            planned.plannedpublic,
            COALESCE(booked.bookedminutes_public, 0),
            planned.plannedminutes_public
        FROM (
            SELECT
                s.scopeID as subjectid,
                ' . $dateExpression . ' as date,
                SUM(s.intern) as plannedcount,
                SUM(COALESCE(s.intern, 0) * COALESCE(s.slotTimeInMinutes, 0)) as plannedminutes,
                SUM(s.`public`) as plannedpublic,
                SUM(COALESCE(s.`public`, 0) * COALESCE(s.slotTimeInMinutes, 0)) as plannedminutes_public
            FROM slot AS s
            WHERE ' . $plannedFilter . '
            GROUP BY ' . $groupBy . '
        ) AS planned
        LEFT JOIN (
            SELECT
                s.scopeID as subjectid,
                ' . $dateExpression . ' as date,
                COUNT(*) as slotcount,
                SUM(COALESCE(s.slotTimeInMinutes, 0)) as bookedminutes,
                SUM(CASE WHEN ac.accesslevel = "public" THEN 1 ELSE 0 END) as slotcount_public,
                SUM(CASE WHEN ac.accesslevel = "public" THEN COALESCE(s.slotTimeInMinutes, 0) ELSE 0 END)
                    as bookedminutes_public
            FROM slot AS s
            STRAIGHT_JOIN slot_process AS sp ON sp.slotID = s.slotID
            LEFT JOIN buerger b ON sp.processID = b.BuergerID
            LEFT JOIN apiclient ac ON b.apiClientID = ac.apiClientID
            WHERE ' . $bookedFilter . '
            GROUP BY ' . $groupBy . '
        ) AS booked ON booked.subjectid = planned.subjectid AND booked.date = planned.date
        ORDER BY planned.date ASC, FIELD(planned.subjectid, ' . implode(', ', array_map('intval', $scopeIds)) . ')
        ';
    }

    /**
     * Distinct slot lengths in the same range, for the duration hint.
     *
     * @param array<int, int> $scopeIds
     * @param array<string, int> $parameters
     */
    public static function buildScopeSlotTimeQuery(
        array $scopeIds,
        ?\DateTimeInterface $dateStart,
        ?\DateTimeInterface $dateEnd,
        array &$parameters
    ): string {
        $parameters = [];
        $filter = self::slotFilterSql('s', 't', $scopeIds, $dateStart, $dateEnd, $parameters);

        return '
            SELECT
                s.scopeID as subjectid,
                s.slotTimeInMinutes as slotminutes,
                TRIM(CONCAT(IFNULL(scopeprovider.name, ""), " ", IFNULL(scope.standortkuerzel, ""))) as scopename
            FROM slot AS s
            INNER JOIN ' . \BO\Zmsbackend\Query\Scope::TABLE . ' AS scope
                ON scope.StandortID = s.scopeID
            LEFT JOIN ' . \BO\Zmsbackend\Provider\Repository\Provider::TABLE . ' AS scopeprovider
                ON scope.InfoDienstleisterID = scopeprovider.id
                AND scope.source = scopeprovider.source
            WHERE ' . $filter . '
            GROUP BY s.scopeID, s.slotTimeInMinutes, scopename
        ';
    }

    /**
     * @param array<int, int> $scopeIds
     * @param array<string, int> $parameters
     */
    private static function slotFilterSql(
        string $alias,
        string $parameterPrefix,
        array $scopeIds,
        ?\DateTimeInterface $dateStart,
        ?\DateTimeInterface $dateEnd,
        array &$parameters
    ): string {
        $filter = self::scopeInClause($alias, $parameterPrefix, $scopeIds, $parameters)
            . ' AND ' . $alias . '.status = "free"';

        if ($dateStart === null || $dateEnd === null) {
            return $filter;
        }

        return $filter . ' AND (' . self::monthRangeSql(
            $alias,
            $parameterPrefix,
            $dateStart,
            $dateEnd,
            $parameters
        ) . ')';
    }

    /**
     * @param array<int, int> $scopeIds
     * @param array<string, int> $parameters
     */
    private static function scopeInClause(
        string $alias,
        string $parameterPrefix,
        array $scopeIds,
        array &$parameters
    ): string {
        $placeholders = [];
        foreach (array_values($scopeIds) as $index => $scopeId) {
            $name = $parameterPrefix . 'scope' . $index;
            $parameters[$name] = $scopeId;
            $placeholders[] = ':' . $name;
        }

        return $alias . '.scopeID IN (' . implode(', ', $placeholders) . ')';
    }

    /**
     * @param array<string, int> $parameters
     */
    private static function monthRangeSql(
        string $alias,
        string $parameterPrefix,
        \DateTimeInterface $dateStart,
        \DateTimeInterface $dateEnd,
        array &$parameters
    ): string {
        $start = \DateTimeImmutable::createFromInterface($dateStart)->setTime(0, 0);
        $end = \DateTimeImmutable::createFromInterface($dateEnd)->setTime(0, 0);
        if ($end < $start) {
            return '0';
        }

        $clauses = [];
        $cursor = $start->modify('first day of this month');
        $lastMonth = $end->modify('first day of this month');
        $index = 0;
        while ($cursor <= $lastMonth) {
            $dayStart = $cursor->format('Y-m') === $start->format('Y-m') ? (int) $start->format('j') : 1;
            $dayEnd = $cursor->format('Y-m') === $end->format('Y-m')
                ? (int) $end->format('j')
                : (int) $cursor->format('t');
            $yearKey = $parameterPrefix . 'y' . $index;
            $monthKey = $parameterPrefix . 'm' . $index;
            $dayStartKey = $parameterPrefix . 'ds' . $index;
            $dayEndKey = $parameterPrefix . 'de' . $index;
            $parameters[$yearKey] = (int) $cursor->format('Y');
            $parameters[$monthKey] = (int) $cursor->format('n');
            $parameters[$dayStartKey] = $dayStart;
            $parameters[$dayEndKey] = $dayEnd;
            $clauses[] = sprintf(
                '(%1$s.year = :%2$s AND %1$s.month = :%3$s AND %1$s.day BETWEEN :%4$s AND :%5$s)',
                $alias,
                $yearKey,
                $monthKey,
                $dayStartKey,
                $dayEndKey
            );
            $cursor = $cursor->modify('+1 month');
            $index++;
        }

        return implode(' OR ', $clauses);
    }
}
