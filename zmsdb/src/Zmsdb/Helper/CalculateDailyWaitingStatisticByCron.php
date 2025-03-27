<?php

namespace BO\Zmsdb\Helper;

use BO\Zmsdb\Base;
use DateTimeImmutable;

/**
 * Berechnung der Wartezeiten pro Standort und Stunde,
 *
 * Liest alle 'buerger'-Einträge für ein Datum, filtert stornierte/ohne Wartezeit,
 * korrigiert StandortIDs aus Anmerkungen und speichert die Durchschnittswerte
 * in 'wartenrstatistik'.
 */
class CalculateDailyWaitingStatisticByCron extends Base
{
    public function run(DateTimeImmutable $day, bool $commit = false)
    {
        echo "CalculateDailyWaitingStatisticByCron->run for date={$day->format('Y-m-d')}\n";

        $buergerRows = $this->fetchBuergerData($day);
        $statsByScopeDate = $this->processBuergerRows($buergerRows);
        $this->saveStatistics($statsByScopeDate, $commit);

        echo "Done collecting stats for {$day->format('Y-m-d')}\n";
    }

    // Alle 'buerger'-Einträge für das Datum laden (außer stornierte bzw. gelöschte).
    private function fetchBuergerData(DateTimeImmutable $day): array
    {
        $sql = "
            SELECT
              BuergerID,
              StandortID,
              Datum,
              Uhrzeit,
              wsm_aufnahmezeit,
              wartezeit,
              wegezeit,
              Name,
              Anmerkung,
              custom_text_field
            FROM buerger
            WHERE Datum = :theDay
              AND Name NOT IN ('(abgesagt)')  
        ";
        return $this->getReader()->fetchAll($sql, [
            'theDay' => $day->format('Y-m-d'),
        ]);
    }


    private function processBuergerRows(array $buergerRows): array
    {
        $statsByScopeDate = [];

        foreach ($buergerRows as $br) {
            // Wenn wartezeit NULL oder leer ist => storniert oder hatte keine echte Wartezeit => überspringen
            if (empty($br['wartezeit'])) {
                continue;
            }

            $scopeId = $this->determineValidScopeId($br);
            if ($scopeId <= 0) {
                continue;
            }

            [$hour, $type] = $this->determineHourAndType($br);

            $waitMins = $this->timeToMinutes($br['wartezeit']);
            $wayMins = is_numeric($br['wegezeit']) ? round($br['wegezeit'] / 60, 2) : 0.0;

            $dateStr = $br['Datum'];
            $this->initializeStatsIfNeeded($statsByScopeDate, $scopeId, $dateStr);

            // Eintrag zur Stats hinzufügen
            $statsByScopeDate[$scopeId][$dateStr][$hour][$type]['count'] += 1;
            $statsByScopeDate[$scopeId][$dateStr][$hour][$type]['sumWait'] += $waitMins;
            $statsByScopeDate[$scopeId][$dateStr][$hour][$type]['sumWay'] += $wayMins;
        }

        return $statsByScopeDate;
    }

    // StandortID korrigieren, falls 0. => Wir parsen bei Bedarf aus Anmerkung / custom_text_field.
    private function determineValidScopeId(array $buergerRecord): int
    {
        $scopeId = (int)$buergerRecord['StandortID'];
        if ($scopeId <= 0) {
            $parsedScope = $this->extractScopeFromAnmerkung(
                $buergerRecord['Anmerkung'],
                $buergerRecord['custom_text_field']
            );

            if ($parsedScope) {
                $scopeId = (int)$parsedScope;
            }
        }

        return $scopeId;
    }

    // Unterscheidung zwischen "spontan" und "termin"
    // - Wenn 'Uhrzeit'=='00:00:00', behandeln wir es als spontan angekommen => Stunde aus wsm_aufnahmezeit
    private function determineHourAndType(array $buergerRecord): array
    {
        $type = 'termin';
        $hourStr = $buergerRecord['Uhrzeit'];

        if ($buergerRecord['Uhrzeit'] === '00:00:00') {
            $type = 'spontan';
            $hourStr = $buergerRecord['wsm_aufnahmezeit'];
        }

        $parts = explode(':', $hourStr);
        $hour = (int)$parts[0];

        return [$hour, $type];
    }

    // Falls für diesen StandortID das Datum noch nicht existiert
    private function initializeStatsIfNeeded(array &$statsByScopeDate, int $scopeId, string $dateStr): void
    {
        if (!isset($statsByScopeDate[$scopeId])) {
            $statsByScopeDate[$scopeId] = [];
        }

        if (!isset($statsByScopeDate[$scopeId][$dateStr])) {
            $statsByScopeDate[$scopeId][$dateStr] = [];
            foreach (range(0, 23) as $h) {
                $statsByScopeDate[$scopeId][$dateStr][$h] = [
                    'spontan' => ['count' => 0, 'sumWait' => 0.0, 'sumWay' => 0.0],
                    'termin'  => ['count' => 0, 'sumWait' => 0.0, 'sumWay' => 0.0],
                ];
            }
        }
    }

    private function saveStatistics(array $statsByScopeDate, bool $commit): void
    {
        foreach ($statsByScopeDate as $scopeId => $dateArray) {
            foreach ($dateArray as $dateStr => $hoursData) {
                $this->insertStatisticsRow($scopeId, $dateStr, $commit);
                $this->updateStatisticsValues($scopeId, $dateStr, $hoursData, $commit);
            }
        }
    }

    //Eine Zeile in wartenrstatistik für jeden (Standort, Datum) einfügen
    private function insertStatisticsRow(int $scopeId, string $dateStr, bool $commit): void
    {
        if ($commit) {
            $insertSql = "
                INSERT IGNORE INTO wartenrstatistik (standortid, datum)
                VALUES (:sid, :d)
            ";
            $this->perform($insertSql, [
                'sid' => $scopeId,
                'd'   => $dateStr
            ]);
        }
    }

    private function updateStatisticsValues(int $scopeId, string $dateStr, array $hoursData, bool $commit): void
    {
        // Eine einzelne UPDATE-Anweisung für alle 24 Stundenspalten erstellen
        $updateParams = [
            'sid' => $scopeId,
            'd'   => $dateStr
        ];
        $updateCols = [];

        // Für jede Stunde 0..23 Spalten für "spontan" und "termin" füllen
        foreach (range(0, 23) as $hour) {
            $this->addHourUpdateColumns($updateCols, $updateParams, $hour, $hoursData, 'spontan');
            $this->addHourUpdateColumns($updateCols, $updateParams, $hour, $hoursData, 'termin');
        }

        $sqlUpdate = sprintf(
            "UPDATE wartenrstatistik
             SET %s
             WHERE standortid = :sid
               AND datum = :d
             LIMIT 1",
            implode(', ', $updateCols)
        );

        if ($commit) {
            $this->perform($sqlUpdate, $updateParams);
        } else {
            echo "[DRY RUN] update scope=$scopeId, date=$dateStr with stats.\n";
        }
    }

    private function addHourUpdateColumns(
        array &$updateCols,
        array &$updateParams,
        int $hour,
        array $hoursData,
        string $type
    ): void {
        $colWaitCount = sprintf('wartende_ab_%02d_%s', $hour, $type);
        $colWaitTime = sprintf('echte_zeit_ab_%02d_%s', $hour, $type);
        $colWayTime = sprintf('wegezeit_ab_%02d_%s', $hour, $type);

        $count = $hoursData[$hour][$type]['count'];
        $avgWait = ($count > 0)
            ? round($hoursData[$hour][$type]['sumWait'] / $count, 2)
            : 0.0;
        $avgWay = ($count > 0)
            ? round($hoursData[$hour][$type]['sumWay'] / $count, 2)
            : 0.0;

        $updateCols[] = "`$colWaitCount` = :$colWaitCount";
        $updateCols[] = "`$colWaitTime` = :$colWaitTime";
        $updateCols[] = "`$colWayTime` = :$colWayTime";

        $updateParams[$colWaitCount] = $count;
        $updateParams[$colWaitTime] = $avgWait;
        $updateParams[$colWayTime] = $avgWay;
    }

    private function extractScopeFromAnmerkung(?string $anmerkung, ?string $customText): ?int
    {
        if (!$anmerkung && !$customText) {
            return null;
        }
        $pattern = "/'StandortID' => '(\d+)'/";
        foreach ([$anmerkung, $customText] as $txt) {
            if (preg_match($pattern, (string)$txt, $matches)) {
                return (int)$matches[1];
            }
        }
        return null;
    }

    private function timeToMinutes(?string $timeStr): float
    {
        if (!$timeStr || $timeStr === '00:00:00') {
            return 0.0;
        }
        $parts = explode(':', $timeStr);
        if (count($parts) !== 3) {
            return 0.0;
        }
        $h = (int)$parts[0];
        $m = (int)$parts[1];
        $s = (int)$parts[2];
        $totalSeconds = $h * 3600 + $m * 60 + $s;
        return round($totalSeconds / 60, 2);
    }
}
