<?php

namespace BO\Zmsbackend\Helper;

use DateTimeImmutable;

/**
 * Berechnung der Wartezeiten pro Standort und Stunde.
 *
 * Liest archivierte Prozesse für ein Datum und speichert die Durchschnittswerte
 * in 'wartenrstatistik'. Anzahl und Wartezeit zählen nur Einträge mit Wartezeit > 0.
 * Wegezeit zählt dieselben Einträge und zusätzlich Terminkunden, die vor dem
 * Terminzeitpunkt aufgerufen wurden (Wartezeit 0, erschienen) und eine Wegezeit haben.
 * Nicht erschienene Termine mit Wartezeit 0 bleiben außen vor.
 * Bereits vorhandene Zeilen für (Standort, Datum) werden aktualisiert.
 */
class CalculateDailyWaitingStatisticByCron extends \BO\Zmsbackend\Base
{
    public function run(DateTimeImmutable $day, bool $commit = false): void
    {
        \App::$log->info('CalculateDailyWaitingStatisticByCron started', ['date' => $day->format('Y-m-d')]);

        $archiveRows = $this->fetchArchiveData($day);
        $statsByScopeDate = $this->processArchiveRows($archiveRows);
        $this->saveStatistics($statsByScopeDate, $commit);

        \App::$log->info('CalculateDailyWaitingStatisticByCron finished', ['date' => $day->format('Y-m-d')]);
    }

    private function fetchArchiveData(DateTimeImmutable $day): array
    {
        $sql = "
            SELECT
              StandortID,
              Datum,
              `Timestamp`,
              mitTermin,
              nicht_erschienen,
              waiting_time,
              way_time
            FROM buergerarchiv
            WHERE Datum = :theDay
              AND StandortID > 0
        ";
        return $this->getReader()->fetchAll($sql, [
            'theDay' => $day->format('Y-m-d'),
        ]);
    }

    private function processArchiveRows(array $archiveRows): array
    {
        $statsByScopeDate = [];

        foreach ($archiveRows as $row) {
            $includeInWaiting = $this->hasPositiveWaitingTime($row);
            $includeInWay = $this->countsForWayTime($row);
            if (!$includeInWaiting && !$includeInWay) {
                continue;
            }

            $scopeId = (int) $row['StandortID'];
            if ($scopeId <= 0) {
                continue;
            }

            [$hour, $type] = $this->determineHourAndType($row);
            if ($hour < 0 || $hour > 23) {
                continue;
            }

            $dateStr = $row['Datum'];
            $this->initializeStatsIfNeeded($statsByScopeDate, $scopeId, $dateStr);

            if ($includeInWaiting) {
                $statsByScopeDate[$scopeId][$dateStr][$hour][$type]['count'] += 1;
                $statsByScopeDate[$scopeId][$dateStr][$hour][$type]['sumWait'] += (float) $row['waiting_time'];
            }
            if ($includeInWay) {
                $wayMins = is_numeric($row['way_time'] ?? null) ? (float) $row['way_time'] : 0.0;
                $statsByScopeDate[$scopeId][$dateStr][$hour][$type]['countWay'] += 1;
                $statsByScopeDate[$scopeId][$dateStr][$hour][$type]['sumWay'] += $wayMins;
            }
        }

        return $statsByScopeDate;
    }

    private function determineHourAndType(array $archiveRecord): array
    {
        $type = ((int) $archiveRecord['mitTermin'] === 1) ? 'termin' : 'spontan';
        $parts = explode(':', (string) $archiveRecord['Timestamp']);
        $hour = isset($parts[0]) && $parts[0] !== '' ? (int) $parts[0] : -1;

        return [$hour, $type];
    }

    private function initializeStatsIfNeeded(array &$statsByScopeDate, int $scopeId, string $dateStr): void
    {
        if (!isset($statsByScopeDate[$scopeId])) {
            $statsByScopeDate[$scopeId] = [];
        }

        if (!isset($statsByScopeDate[$scopeId][$dateStr])) {
            $statsByScopeDate[$scopeId][$dateStr] = [];
            foreach (range(0, 23) as $h) {
                $statsByScopeDate[$scopeId][$dateStr][$h] = [
                    'spontan' => ['count' => 0, 'sumWait' => 0.0, 'countWay' => 0, 'sumWay' => 0.0],
                    'termin'  => ['count' => 0, 'sumWait' => 0.0, 'countWay' => 0, 'sumWay' => 0.0],
                ];
            }
        }
    }

    private function saveStatistics(array $statsByScopeDate, bool $commit): void
    {
        foreach ($statsByScopeDate as $scopeId => $dateArray) {
            foreach ($dateArray as $dateStr => $hoursData) {
                $this->updateStatisticsValues((int) $scopeId, $dateStr, $hoursData, $commit);
            }
        }
    }

    private function ensureStatisticsRow(int $scopeId, string $dateStr): int
    {
        $existingId = $this->fetchValue(
            'SELECT wartenrstatistikid
             FROM wartenrstatistik
             WHERE standortid = :sid
               AND datum = :d
             ORDER BY wartenrstatistikid ASC
             LIMIT 1',
            [
                'sid' => $scopeId,
                'd' => $dateStr,
            ]
        );

        if ($existingId) {
            return (int) $existingId;
        }

        $this->perform(
            'INSERT INTO wartenrstatistik (standortid, datum) VALUES (:sid, :d)',
            [
                'sid' => $scopeId,
                'd' => $dateStr,
            ]
        );

        return (int) $this->getWriter()->lastInsertId();
    }

    private function updateStatisticsValues(int $scopeId, string $dateStr, array $hoursData, bool $commit): void
    {
        $updateParams = [];
        $updateCols = [];

        foreach (range(0, 23) as $hour) {
            $this->addHourUpdateColumns($updateCols, $updateParams, $hour, $hoursData, 'spontan');
            $this->addHourUpdateColumns($updateCols, $updateParams, $hour, $hoursData, 'termin');
        }

        if (!$commit) {
            \App::$log->info('[DRY RUN] update scope statistics', [
                'scopeId' => $scopeId,
                'date' => $dateStr,
            ]);
            return;
        }

        $rowId = $this->ensureStatisticsRow($scopeId, $dateStr);
        $updateParams['id'] = $rowId;

        $sqlUpdate = sprintf(
            'UPDATE wartenrstatistik
             SET %s
             WHERE wartenrstatistikid = :id
             LIMIT 1',
            implode(', ', $updateCols)
        );

        $this->perform($sqlUpdate, $updateParams);
    }

    private function addHourUpdateColumns(
        array &$updateCols,
        array &$updateParams,
        int $hour,
        array $hoursData,
        string $type
    ): void {
        $hourSuffix = $this->hourSuffixForStatistic($type);
        $colWaitCount = sprintf('hour_%02d_waiting_count_%s', $hour, $hourSuffix);
        $colWaitTime = sprintf('hour_%02d_waiting_time_%s', $hour, $hourSuffix);
        $colWayTime = sprintf('hour_%02d_way_time_%s', $hour, $hourSuffix);

        $count = $hoursData[$hour][$type]['count'];
        $countWay = $hoursData[$hour][$type]['countWay'];
        $avgWait = ($count > 0)
            ? round($hoursData[$hour][$type]['sumWait'] / $count, 2)
            : 0.0;
        $avgWay = ($countWay > 0)
            ? round($hoursData[$hour][$type]['sumWay'] / $countWay, 2)
            : 0.0;

        $updateCols[] = "`$colWaitCount` = :$colWaitCount";
        $updateCols[] = "`$colWaitTime` = :$colWaitTime";
        $updateCols[] = "`$colWayTime` = :$colWayTime";

        $updateParams[$colWaitCount] = $count;
        $updateParams[$colWaitTime] = $avgWait;
        $updateParams[$colWayTime] = $avgWay;
    }

    private function hourSuffixForStatistic(string $type): string
    {
        return $type === 'termin' ? 'appointment' : 'spontaneous';
    }

    private function hasPositiveWaitingTime(array $row): bool
    {
        return is_numeric($row['waiting_time'] ?? null) && (float) $row['waiting_time'] > 0;
    }

    /**
     * Way time uses every row that already counts for waiting time, plus appointment
     * customers called before their slot. Those are stored with waiting time 0 and must
     * stay out of the waiting average. A missed appointment with waiting time 0 is not
     * such a call; its stored way time is 0 and must not lower the average.
     */
    private function countsForWayTime(array $row): bool
    {
        if ($this->hasPositiveWaitingTime($row)) {
            return true;
        }

        return (int) ($row['mitTermin'] ?? 0) === 1
            && (int) ($row['nicht_erschienen'] ?? 0) === 0
            && is_numeric($row['way_time'] ?? null);
    }
}
