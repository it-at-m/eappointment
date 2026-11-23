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
     * current Arbeitsplätze. The database only reads that date range plus the openings.
     * The time grid is compared here, as on the availability day view, so a cancelled
     * slot row still counts. Overlapping openings at the same time add up.
     * Grouped like active sessions. A consistent read: no FOR UPDATE, so it does not take row locks.
     * Skipped while calculateSlots --delete is running, same as the other slot scans.
     *
     * @return array{total: int, departments: array<int|string, array{name: string, overbookedSlots: int, scopes: array<int|string, array{name: string, overbookedSlots: int}>}>}
     */
    protected function readOverbookedSlotsByDepartment(\DateTimeImmutable $now): array
    {
        $fromDay = $now->format('Y-m-d');
        $untilDay = $now->modify('+180 days')->format('Y-m-d');
        $counts = $this->countOverbookedSlots(
            $this->readOverbookingAppointments($fromDay, $untilDay),
            $this->readOverbookingOpenings($fromDay, $untilDay),
            $this->readOverbookingHolidays($fromDay, $untilDay)
        );

        $departments = [];
        $total = 0;
        foreach ($counts as $row) {
            $departmentId = $row['departmentId'];
            $scopeId = $row['scopeId'];
            $overbookedSlots = $row['overbookedSlots'];
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
     * @return array<int, array{id: int|string, scopeId: int|string, date: string, startSeconds: int, slots: int}>
     */
    protected function readOverbookingAppointments(string $fromDay, string $untilDay): array
    {
        return $this->getReader()->fetchAll(
            'SELECT b.BuergerID AS id,
                    b.StandortID AS scopeId,
                    b.Datum AS date,
                    TIME_TO_SEC(b.Uhrzeit) AS startSeconds,
                    IFNULL(b.hatFolgetermine, 0) + 1 AS slots
             FROM buerger b
             WHERE b.Datum >= :fromDay
               AND b.Datum < :untilDay
               AND b.status <> "fake"
               AND (b.istFolgeterminvon IS NULL OR b.istFolgeterminvon = 0)',
            [
                'fromDay' => $fromDay,
                'untilDay' => $untilDay,
            ]
        );
    }

    /**
     * @return array<int|string, array<int, array<string, mixed>>>
     */
    protected function readOverbookingOpenings(string $fromDay, string $untilDay): array
    {
        $rows = $this->getReader()->fetchAll(
            'SELECT o.scope_id AS scopeId,
                    st.Bezeichnung AS scopeName,
                    st.BehoerdenID AS departmentId,
                    beh.Name AS departmentName,
                    o.start_date AS startDate,
                    o.end_date AS endDate,
                    TIME_TO_SEC(o.appointment_start_time) AS startSeconds,
                    TIME_TO_SEC(o.appointment_end_time) AS endSeconds,
                    TIME_TO_SEC(o.time_slot) AS slotSeconds,
                    o.weekday AS weekday,
                    o.every_x_weeks AS everyWeeks,
                    o.every_other_week AS weekOfMonth,
                    o.appointment_workstation_count AS capacity
             FROM oeffnungszeit o
             INNER JOIN standort st ON st.StandortID = o.scope_id
             INNER JOIN behoerde beh ON beh.BehoerdenID = st.BehoerdenID
             WHERE o.appointment_start_time > "00:00:00"
               AND o.time_slot > "00:00:00"
               AND o.end_date >= :fromDay
               AND o.start_date < :untilDay',
            [
                'fromDay' => $fromDay,
                'untilDay' => $untilDay,
            ]
        );
        $openings = [];
        foreach ($rows as $row) {
            $start = new \DateTimeImmutable($row['startDate']);
            $row['spanDays'] = (int) $start->diff(new \DateTimeImmutable($row['endDate']))->days;
            $row['startMondayTs'] = $start->modify('monday this week')->getTimestamp();
            $openings[$row['scopeId']][] = $row;
        }

        return $openings;
    }

    /**
     * @return array<string, true>
     */
    protected function readOverbookingHolidays(string $fromDay, string $untilDay): array
    {
        $rows = $this->getReader()->fetchAll(
            'SELECT Datum AS date, BehoerdenID AS departmentId
             FROM feiertage
             WHERE Datum >= :fromDay AND Datum < :untilDay',
            [
                'fromDay' => $fromDay,
                'untilDay' => $untilDay,
            ]
        );
        $holidays = [];
        foreach ($rows as $row) {
            $holidays[$row['date'] . '|' . $row['departmentId']] = true;
        }

        return $holidays;
    }

    /**
     * @param array<int, array<string, mixed>> $appointments
     * @param array<int|string, array<int, array<string, mixed>>> $openingsByScope
     * @param array<string, true> $holidays
     * @return array<int, array{departmentId: int|string, departmentName: string, scopeId: int|string, scopeName: string, overbookedSlots: int}>
     */
    protected function countOverbookedSlots(array $appointments, array $openingsByScope, array $holidays): array
    {
        $occupied = [];
        $capacity = [];
        $scopeInfo = [];
        $days = [];
        $openingsOnDate = [];
        foreach ($appointments as $appointment) {
            $key = $appointment['scopeId'] . '|' . $appointment['date'];
            if (!isset($openingsOnDate[$key])) {
                $openingsOnDate[$key] = $this->openingsCoveringDate(
                    $openingsByScope[$appointment['scopeId']] ?? [],
                    $this->overbookingDay((string) $appointment['date'], $days),
                    $holidays
                );
            }
            $this->markAppointmentSlots($appointment, $openingsOnDate[$key], $occupied, $capacity, $scopeInfo);
        }

        return $this->overbookedSlotRows($occupied, $capacity, $scopeInfo);
    }

    /**
     * @param array<int, array<string, mixed>> $openings
     * @param array{bit: int, mondayTs: int, day: int, lastDay: int, date: string} $day
     * @param array<string, true> $holidays
     * @return array<int, array<string, mixed>>
     */
    protected function openingsCoveringDate(array $openings, array $day, array $holidays): array
    {
        $covering = [];
        foreach ($openings as $opening) {
            if ($this->openingCoversDate($opening, $day, $holidays)) {
                $covering[] = $opening;
            }
        }

        return $covering;
    }

    /**
     * @param array<string, mixed> $appointment
     * @param array<int, array<string, mixed>> $openings
     * @param array<string, int> $occupied
     * @param array<string, int> $capacity
     * @param array<int|string, array<string, mixed>> $scopeInfo
     */
    protected function markAppointmentSlots(
        array $appointment,
        array $openings,
        array &$occupied,
        array &$capacity,
        array &$scopeInfo
    ): void {
        $placed = $this->openingsAtStart($openings, (int) $appointment['startSeconds']);
        if ($placed === null) {
            return;
        }
        $start = (int) $appointment['startSeconds'];
        $steps = min(300, (int) $appointment['slots']);
        for ($step = 0; $step < $steps; $step++) {
            $time = $start + $step * $placed['slotSeconds'];
            if ($time >= 86400) {
                break;
            }
            $match = $this->capacityAtTime($placed['openings'], $time);
            if ($match === null) {
                continue;
            }
            $key = $appointment['scopeId'] . '|' . $appointment['date'] . '|' . $time;
            $occupied[$key] = ($occupied[$key] ?? 0) + 1;
            $capacity[$key] = $match['capacity'];
            $scopeInfo[$appointment['scopeId']] = $match['opening'];
        }
    }

    /**
     * @param array<int, array<string, mixed>> $openings
     * @return array{slotSeconds: int, openings: array<int, array<string, mixed>>}|null
     */
    protected function openingsAtStart(array $openings, int $start): ?array
    {
        $active = [];
        $slotSeconds = null;
        foreach ($openings as $opening) {
            if ($start < (int) $opening['startSeconds'] || $start >= (int) $opening['endSeconds']) {
                continue;
            }
            $active[] = $opening;
            $seconds = (int) $opening['slotSeconds'];
            if ($slotSeconds === null || $seconds < $slotSeconds) {
                $slotSeconds = $seconds;
            }
        }
        if ($slotSeconds === null || $slotSeconds <= 0) {
            return null;
        }

        return ['slotSeconds' => $slotSeconds, 'openings' => $active];
    }

    /**
     * @param array<int, array<string, mixed>> $openings
     * @return array{capacity: int, opening: array<string, mixed>}|null
     */
    protected function capacityAtTime(array $openings, int $time): ?array
    {
        $sum = 0;
        $matched = null;
        foreach ($openings as $opening) {
            if ($time < (int) $opening['startSeconds'] || $time >= (int) $opening['endSeconds']) {
                continue;
            }
            $sum += (int) $opening['capacity'];
            $matched = $opening;
        }
        if ($matched === null) {
            return null;
        }

        return ['capacity' => $sum, 'opening' => $matched];
    }

    /**
     * @param array<string, int> $occupied
     * @param array<string, int> $capacity
     * @param array<int|string, array<string, mixed>> $scopeInfo
     * @return array<int, array{departmentId: int|string, departmentName: string, scopeId: int|string, scopeName: string, overbookedSlots: int}>
     */
    protected function overbookedSlotRows(array $occupied, array $capacity, array $scopeInfo): array
    {
        $counts = [];
        foreach ($occupied as $key => $count) {
            if ($count <= $capacity[$key]) {
                continue;
            }
            $scopeId = explode('|', (string) $key, 2)[0];
            $counts[$scopeId] = ($counts[$scopeId] ?? 0) + 1;
        }
        $rows = [];
        foreach ($counts as $scopeId => $overbookedSlots) {
            $info = $scopeInfo[$scopeId];
            $rows[] = [
                'departmentId' => $info['departmentId'],
                'departmentName' => $info['departmentName'],
                'scopeId' => $scopeId,
                'scopeName' => $info['scopeName'],
                'overbookedSlots' => $overbookedSlots,
            ];
        }

        return $rows;
    }

    /**
     * @param array<string, array{bit: int, mondayTs: int, day: int, lastDay: int, date: string}> $days
     * @return array{bit: int, mondayTs: int, day: int, lastDay: int, date: string}
     */
    protected function overbookingDay(string $date, array &$days): array
    {
        if (!isset($days[$date])) {
            $day = new \DateTimeImmutable($date);
            $days[$date] = [
                'date' => $date,
                'bit' => 1 << (int) $day->format('w'),
                'mondayTs' => $day->modify('monday this week')->getTimestamp(),
                'day' => (int) $day->format('j'),
                'lastDay' => (int) $day->format('t'),
            ];
        }

        return $days[$date];
    }

    /**
     * @param array<string, mixed> $opening
     * @param array{bit: int, mondayTs: int, day: int, lastDay: int, date: string} $day
     * @param array<string, true> $holidays
     */
    protected function openingCoversDate(array $opening, array $day, array $holidays): bool
    {
        if ($day['date'] < $opening['startDate'] || $day['date'] > $opening['endDate']) {
            return false;
        }
        if (((int) $opening['weekday'] & $day['bit']) === 0) {
            return false;
        }
        if (
            (int) $opening['spanDays'] > 1
            && (isset($holidays[$day['date'] . '|0']) || isset($holidays[$day['date'] . '|' . $opening['departmentId']]))
        ) {
            return false;
        }

        return $this->openingRepeatsOn($opening, $day);
    }

    /**
     * @param array<string, mixed> $opening
     * @param array{bit: int, mondayTs: int, day: int, lastDay: int, date: string} $day
     */
    protected function openingRepeatsOn(array $opening, array $day): bool
    {
        $everyWeeks = (int) $opening['everyWeeks'];
        $weekOfMonth = (int) $opening['weekOfMonth'];
        $mondayDistance = (int) round(($day['mondayTs'] - (int) $opening['startMondayTs']) / 86400);
        if ($everyWeeks > 0 && ($everyWeeks === 1 || $mondayDistance % ($everyWeeks * 7) === 0)) {
            return true;
        }
        if ($weekOfMonth > 0 && $weekOfMonth < 5 && (int) ceil($day['day'] / 7) === $weekOfMonth) {
            return true;
        }
        if ($weekOfMonth >= 5 && $day['day'] > $day['lastDay'] - 7) {
            return true;
        }

        return $everyWeeks === 0 && $weekOfMonth === 0;
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
