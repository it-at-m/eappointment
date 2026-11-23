<?php

namespace BO\Zmsbackend\Status\Service;

use BO\Zmsentities\Status as Entity;

class Status extends \BO\Zmsbackend\Base
{
    /**
     * Fetch status from db
     *
     * @return \BO\Zmsentities\Status
     */
    public function readEntity(\DateTimeImmutable $now, bool $includeProcessStats = true)
    {
        $entity = new Entity();
        $configVariables = $this->readConfigVariables();
        $statusVariables = $this->readStatusVariables();
        $nodeConnections = round($statusVariables['Threads_connected'] / $configVariables['max_connections'], 2);

        $entity['database']['problems'] = $this->getConfigProblems($configVariables);
        $entity['database']['locks'] = $statusVariables['Innodb_row_lock_current_waits'];
        $entity['database']['threads'] = $statusVariables['Threads_connected'];
        $entity['database']['nodeConnections'] = $nodeConnections;
        $entity['database']['clusterStatus'] =
            array_key_exists('wsrep_ready', $statusVariables) ? $statusVariables['wsrep_ready'] : 'OFF';
        $entity['database']['logbin'] =
            array_key_exists('log_bin', $configVariables) ? $configVariables['log_bin'] : 'OFF';
        $entity['processes'] = $includeProcessStats ? $this->readProcessStats($now) : [];

        if ($includeProcessStats && ! $this->isSlotDeleteRunning($now)) {
            $entity['processes'] = $this->readProcessStats($now);
            $outdated = $this->readOutdatedSlots();
            $entity['processes']['outdated'] = $outdated['cnt'];
            $entity['processes']['outdatedOldest'] = $outdated['oldest'];
            $freeSlots = $this->readFreeSlots();
            $entity['processes']['freeSlots'] = $freeSlots['cnt'];
            $entity['overbooking'] = $this->readOverbookedSlotsByDepartment($now);
        }

        $entity['mail'] = $this->readMailStats();
        $entity['sources']['dldb']['last'] = $this->readDdldUpdateStats();
        $entity['processes']['lastCalculate'] = $this->readLastCalculateSlots();
        $entity['useraccounts']['activeSessions'] = $this->getTotalActiveSessions();
        $entity['useraccounts']['departments'] = $this->getActiveSessionsByBehoerdenWithScopes();

        return $entity;
    }

    /**
     * Set only by calculateSlots --delete. Newer than the last finished run, and less than 15 minutes old.
     *
     * @return bool
     */
    protected function isSlotDeleteRunning(\DateTimeImmutable $now): bool
    {
        $config = new \BO\Zmsbackend\Config\Service\Config();
        $started = $config->readProperty('status__calculateSlotsWithDeleteStart');
        $finished = $config->readProperty('status__calculateSlotsLastRun');
        $startedAt = is_string($started) ? strtotime($started) : false;
        if ($startedAt === false) {
            return false;
        }

        return $started > $finished && ($startedAt + (15 * 60)) > $now->getTimestamp();
    }

    /**
     * Slots from today through the next 180 days where appointments exceed the opening's
     * current Arbeitsplätze. The time grid and the repeat rules are the opening's, as on
     * the availability day view, so a cancelled slot row still counts. Overlapping openings
     * at the same time add up. Grouped like active sessions.
     * A consistent read on the reader: no FOR UPDATE, so it does not take row locks.
     * Skipped while calculateSlots --delete is running, same as the other slot scans.
     *
     * @return array{total: int, departments: array<int|string, array{name: string, overbookedSlots: int, scopes: array<int|string, array{name: string, overbookedSlots: int}>}>}
     */
    protected function readOverbookedSlotsByDepartment(\DateTimeImmutable $now): array
    {
        $rows = $this->getReader()->fetchAll(
            'SELECT beh.BehoerdenID AS departmentId,
                    beh.Name AS departmentName,
                    st.StandortID AS scopeId,
                    st.Bezeichnung AS scopeName,
                    COUNT(*) AS overbookedSlots
             FROM (
                 SELECT occ.scope_id
                 FROM (
                     SELECT covered.scope_id, covered.d, covered.t,
                            COUNT(DISTINCT covered.BuergerID) AS occupied
                     FROM (
                         SELECT b.StandortID AS scope_id, b.Datum AS d, b.BuergerID,
                                ADDTIME(b.Uhrzeit, SEC_TO_TIME(steps.n * sized.slot_seconds)) AS t
                         FROM buerger b
                         JOIN (
                             SELECT b2.BuergerID, MIN(TIME_TO_SEC(o.time_slot)) AS slot_seconds
                             FROM buerger b2
                             JOIN standort st2 ON st2.StandortID = b2.StandortID
                             JOIN oeffnungszeit o ON o.scope_id = b2.StandortID
                              AND b2.Datum BETWEEN o.start_date AND o.end_date
                              AND b2.Uhrzeit >= o.appointment_start_time
                              AND b2.Uhrzeit < o.appointment_end_time
                              AND o.time_slot > "00:00:00"
                              AND (o.weekday & (1 << (DAYOFWEEK(b2.Datum) - 1))) <> 0
                              AND (
                                   (o.every_x_weeks > 0 AND (
                                       o.every_x_weeks = 1
                                       OR MOD(DATEDIFF(
                                           DATE_SUB(b2.Datum, INTERVAL WEEKDAY(b2.Datum) DAY),
                                           DATE_SUB(o.start_date, INTERVAL WEEKDAY(o.start_date) DAY)
                                       ), o.every_x_weeks * 7) = 0
                                   ))
                                   OR (o.every_other_week > 0 AND (
                                       (o.every_other_week < 5 AND CEIL(DAY(b2.Datum) / 7) = o.every_other_week)
                                       OR (o.every_other_week >= 5 AND DAY(b2.Datum) > DAY(LAST_DAY(b2.Datum)) - 7)
                                   ))
                                   OR (o.every_x_weeks = 0 AND o.every_other_week = 0)
                              )
                              AND NOT (
                                   DATEDIFF(o.end_date, o.start_date) > 1
                                   AND EXISTS (
                                       SELECT 1 FROM feiertage f
                                       WHERE f.Datum = b2.Datum
                                         AND (f.BehoerdenID = st2.BehoerdenID OR f.BehoerdenID = 0)
                                   )
                              )
                             WHERE b2.Datum >= :fromDay
                               AND b2.Datum < :untilDay
                               AND b2.status <> "fake"
                               AND (b2.istFolgeterminvon IS NULL OR b2.istFolgeterminvon = 0)
                             GROUP BY b2.BuergerID
                         ) sized ON sized.BuergerID = b.BuergerID
                         JOIN (
                             SELECT ones.n + tens.n * 10 + hundreds.n * 100 AS n
                             FROM (
                                 SELECT 0 n UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3
                                 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6
                                 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9
                             ) ones
                             JOIN (
                                 SELECT 0 n UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3
                                 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6
                                 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9
                             ) tens
                             JOIN (SELECT 0 n UNION ALL SELECT 1 UNION ALL SELECT 2) hundreds
                         ) steps ON steps.n < IFNULL(b.hatFolgetermine, 0) + 1
                         WHERE b.status <> "fake"
                           AND (b.istFolgeterminvon IS NULL OR b.istFolgeterminvon = 0)
                     ) covered
                     GROUP BY covered.scope_id, covered.d, covered.t
                 ) occ
                 JOIN oeffnungszeit op ON op.scope_id = occ.scope_id
                    AND occ.d BETWEEN op.start_date AND op.end_date
                    AND occ.t >= op.appointment_start_time
                    AND occ.t < op.appointment_end_time
                    AND op.appointment_start_time > "00:00:00"
                    AND (op.weekday & (1 << (DAYOFWEEK(occ.d) - 1))) <> 0
                    AND (
                         (op.every_x_weeks > 0 AND (
                             op.every_x_weeks = 1
                             OR MOD(DATEDIFF(
                                 DATE_SUB(occ.d, INTERVAL WEEKDAY(occ.d) DAY),
                                 DATE_SUB(op.start_date, INTERVAL WEEKDAY(op.start_date) DAY)
                             ), op.every_x_weeks * 7) = 0
                         ))
                         OR (op.every_other_week > 0 AND (
                             (op.every_other_week < 5 AND CEIL(DAY(occ.d) / 7) = op.every_other_week)
                             OR (op.every_other_week >= 5 AND DAY(occ.d) > DAY(LAST_DAY(occ.d)) - 7)
                         ))
                         OR (op.every_x_weeks = 0 AND op.every_other_week = 0)
                    )
                 JOIN standort scope_row ON scope_row.StandortID = occ.scope_id
                 WHERE NOT (
                     DATEDIFF(op.end_date, op.start_date) > 1
                     AND EXISTS (
                         SELECT 1 FROM feiertage f
                         WHERE f.Datum = occ.d
                           AND (f.BehoerdenID = scope_row.BehoerdenID OR f.BehoerdenID = 0)
                     )
                 )
                 GROUP BY occ.scope_id, occ.d, occ.t, occ.occupied
                 HAVING occ.occupied > SUM(op.appointment_workstation_count)
             ) overbooked
             INNER JOIN standort st ON st.StandortID = overbooked.scope_id
             INNER JOIN behoerde beh ON beh.BehoerdenID = st.BehoerdenID
             GROUP BY beh.BehoerdenID, beh.Name, st.StandortID, st.Bezeichnung',
            [
                'fromDay' => $now->format('Y-m-d'),
                'untilDay' => $now->modify('+180 days')->format('Y-m-d'),
            ]
        );

        $departments = [];
        $total = 0;
        foreach ($rows as $row) {
            $departmentId = $row['departmentId'];
            $scopeId = $row['scopeId'];
            $overbookedSlots = (int) $row['overbookedSlots'];
            if (!isset($departments[$departmentId])) {
                $departments[$departmentId] = [
                    'name' => $row['departmentName'],
                    'overbookedSlots' => 0,
                    'scopes' => [],
                ];
            }
            $departments[$departmentId]['scopes'][$scopeId] = [
                'name' => $row['scopeName'],
                'overbookedSlots' => $overbookedSlots,
            ];
            $departments[$departmentId]['overbookedSlots'] += $overbookedSlots;
            $total += $overbookedSlots;
        }

        return [
            'total' => $total,
            'departments' => $departments,
        ];
    }

    /**
     * Get total active sessions where SessionID is not null or empty and sessionExpiry is in the future
     *
     * @return int
     */
    protected function getTotalActiveSessions()
    {
        $result = $this->getReader()->fetchOne(
            'SELECT COUNT(n.SessionID) as totalActiveSessions
             FROM nutzer n
             WHERE n.SessionID IS NOT NULL
             AND n.SessionID != ""
             AND n.sessionExpiry > UNIX_TIMESTAMP()'
        );

        return (int) $result['totalActiveSessions'];
    }

    /**
     * Get active sessions grouped by BehoerdenID with scopes, excluding expired sessions
     *
     * @return array
     */
    protected function getActiveSessionsByBehoerdenWithScopes()
    {
        $result = $this->getReader()->fetchAll(
            'SELECT b.BehoerdenID, b.Name as BehoerdeName, 
                    s.StandortID, s.Bezeichnung as StandortName,
                    COALESCE(SUM(CASE 
                        WHEN n.sessionExpiry > UNIX_TIMESTAMP()
                        THEN 1 ELSE 0 END), 0) as activeSessions
             FROM behoerde b
             LEFT JOIN standort s ON b.BehoerdenID = s.BehoerdenID
             LEFT JOIN nutzer n ON s.StandortID = n.StandortID
             GROUP BY b.BehoerdenID, b.Name, s.StandortID, s.Bezeichnung'
        );

        $activeSessionsByBehoerden = [];

        foreach ($result as $row) {
            $behoerdenID = $row['BehoerdenID'];
            $standortID = $row['StandortID'];

            if (!isset($activeSessionsByBehoerden[$behoerdenID])) {
                $activeSessionsByBehoerden[$behoerdenID] = [
                    'activeSessions' => 0,
                    'name' => $row['BehoerdeName'],
                    'scopes' => []
                ];
            }

            $activeSessionsByBehoerden[$behoerdenID]['scopes'][$standortID] = [
                'activeSessions' => (int) $row['activeSessions'],
                'name' => $row['StandortName']
            ];

            $activeSessionsByBehoerden[$behoerdenID]['activeSessions'] += (int) $row['activeSessions'];
        }

        return $activeSessionsByBehoerden;
    }

    /**
     * Get the configuration problems
     *
     * @return string
     */
    public function getConfigProblems(array $configVariables)
    {
        $problems = [];
        if ($configVariables['tmp_table_size'] < 32000000) {
            $problems[] = 'tmp_table_size should be at least 32MB';
        }
        if ($configVariables['max_heap_table_size'] < 32000000) {
            $problems[] = 'max_heap_table_size should be at least 32MB';
        }
        return implode('; ', $problems);
    }

    /**
     * Get the dldb update status
     *
     * @return array
     */
    protected function readDdldUpdateStats()
    {
        $stats = $this->getReader()->fetchOne(
            'SELECT value FROM config WHERE name = "sources_dldb_last"'
        );
        return $stats['value'];
    }

    /**
     * Get outdated slots
     *
     * @return array
     */
    protected function readOutdatedSlots()
    {
        $stats = $this->getReader()->fetchOne(
            'SELECT COUNT(*) cnt, MIN(a.updated_at) oldest
             FROM slot s
             LEFT JOIN oeffnungszeit a ON s.availabilityID = a.OeffnungszeitID
             WHERE s.updateTimestamp < a.updated_at AND s.status = "free"'
        );
        return $stats;
    }

    /**
     * Get free slots count
     *
     * @return array
     */
    protected function readFreeSlots()
    {
        $stats = $this->getReader()->fetchOne(
            'SELECT SUM(intern) cnt FROM slot s WHERE s.status = "free"'
        );
        return $stats;
    }

    /**
     * Get last calculate slots information
     *
     * @return array
     */
    protected function readLastCalculateSlots()
    {
        $stats = $this->getReader()->fetchOne(
            'SELECT value FROM config WHERE name = "status__calculateSlotsLastRun"'
        );
        return $stats['value'];
    }

    /**
     * Get mail stats
     *
     * @return array
     */
    protected function readMailStats()
    {
        $stats = $this->getReader()->fetchOne(
            'SELECT COUNT(id) as queueCount, UNIX_TIMESTAMP() - MIN(createTimestamp) as oldestSeconds, 
                UNIX_TIMESTAMP() - MAX(createTimestamp) as newestSeconds
             FROM mailqueue'
        );
        return $stats;
    }

    /**
     * Get process statistics
     *
     * @return array
     */
    protected function readProcessStats(\DateTimeImmutable $now)
    {
        $midnight = $now->modify('00:00:00')->getTimestamp();
        $last7days = $now->modify('-7 days 00:00:00')->getTimestamp();

        $processStats = $this->getReader()->fetchOne(
            'SELECT
                COALESCE(SUM(CASE WHEN b.status = "blocked" THEN 1 ELSE 0 END), 0) AS blocked,
                COALESCE(SUM(CASE WHEN b.status = "called" THEN 1 ELSE 0 END), 0) AS called,
                COALESCE(SUM(CASE WHEN b.status = "confirmed" THEN 1 ELSE 0 END), 0) AS confirmed,
                COALESCE(SUM(CASE WHEN b.status = "pending" THEN 1 ELSE 0 END), 0) AS pending,
                COALESCE(SUM(CASE WHEN b.status = "deleted" THEN 1 ELSE 0 END), 0) AS deleted,
                COALESCE(SUM(CASE WHEN b.status = "missed" THEN 1 ELSE 0 END), 0) AS missed,
                COALESCE(SUM(CASE WHEN b.status = "parked" THEN 1 ELSE 0 END), 0) AS parked,
                COALESCE(SUM(CASE WHEN b.status = "reserved" THEN 1 ELSE 0 END), 0) AS reserved,
                COALESCE(SUM(
                    CASE
                        WHEN b.external_user_id IS NOT NULL AND b.external_user_id != ""
                        THEN 1 ELSE 0
                    END
                ), 0) AS withExternalUserId,
                COALESCE(SUM(
                    CASE
                        WHEN b.status = "confirmed"
                            AND b.external_user_id IS NOT NULL
                            AND b.external_user_id != ""
                        THEN 1 ELSE 0
                    END
                ), 0) AS confirmedWithExternalUserId,
                global.sinceMidnight,
                global.last7days,
                global.lastInsert
             FROM buerger AS b
             CROSS JOIN (
                SELECT
                    COALESCE(SUM(
                        CASE
                            WHEN IPTimeStamp > ' . intval($midnight) . ' 
                                 AND StandortID != 0
                                 AND vorlaeufigeBuchung = 0
                                 AND Abholer = 0
                            THEN 1 ELSE 0
                        END
                    ), 0) AS sinceMidnight,
                    COALESCE(SUM(
                        CASE
                            WHEN IPTimeStamp > ' . intval($last7days) . ' 
                                 AND StandortID != 0
                                 AND vorlaeufigeBuchung = 0
                                 AND Abholer = 0
                            THEN 1 ELSE 0
                        END
                    ), 0) AS last7days,
                    FROM_UNIXTIME(MAX(IPTimeStamp)) AS lastInsert
                FROM buerger
                WHERE istFolgeterminvon IS NULL OR istFolgeterminvon = 0
             ) AS global
             WHERE b.istFolgeterminvon IS NULL OR b.istFolgeterminvon = 0'
        );
        $processStats['confirmedWithMultipleServices'] = $this->readConfirmedWithMultipleServices();
        return $processStats;
    }

    /**
     * Confirmed appointments whose requests list more than one distinct service.
     * Starts from buerger.status (indexed) and looks up buergeranliegen by BuergerID,
     * so the cost follows current confirmed bookings rather than the full request history.
     */
    protected function readConfirmedWithMultipleServices(): int
    {
        $row = $this->getReader()->fetchOne(
            'SELECT COUNT(*) AS confirmedWithMultipleServices
             FROM (
                 SELECT ba.BuergerID
                 FROM buerger b
                 INNER JOIN buergeranliegen ba ON ba.BuergerID = b.BuergerID
                 WHERE b.status = "confirmed"
                   AND (b.istFolgeterminvon IS NULL OR b.istFolgeterminvon = 0)
                   AND ba.AnliegenID > 0
                 GROUP BY ba.BuergerID
                 HAVING COUNT(DISTINCT ba.AnliegenID) > 1
             ) AS multi'
        );

        return (int) ($row['confirmedWithMultipleServices'] ?? 0);
    }

    /**
     * Fetch MySQL config variables
     *
     * @return array
     */
    protected function readConfigVariables()
    {
        return $this->getReader()->fetchPairs('SHOW VARIABLES');
    }

    /**
     * Fetch MySQL status variables
     *
     * @return array
     */
    protected function readStatusVariables()
    {
        return $this->getReader()->fetchPairs('SHOW STATUS');
    }
}
