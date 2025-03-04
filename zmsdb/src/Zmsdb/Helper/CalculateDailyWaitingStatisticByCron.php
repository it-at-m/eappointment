<?php

namespace BO\Zmsdb\Helper;

use BO\Zmsdb\Base;
use \DateTimeImmutable;

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

        // Alle 'buerger'-Einträge für das Datum laden (außer stornierte bzw. gelöschte).
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
        $buergerRows = $this->getReader()->fetchAll($sql, [
            'theDay' => $day->format('Y-m-d'),
        ]);

        $statsByScopeDate = [];

        foreach ($buergerRows as $br) {
            // (2a) Wenn wartezeit NULL oder leer ist => storniert oder hatte keine echte Wartezeit => überspringen
            if (empty($br['wartezeit'])) {
                continue;
            }

            // StandortID korrigieren, falls 0. => Wir parsen bei Bedarf aus Anmerkung / custom_text_field.
            $finalScope = (int)$br['StandortID'];
            if ($finalScope <= 0) {
                $parsedScope = $this->extractScopeFromAnmerkung($br['Anmerkung'], $br['custom_text_field']);
                if ($parsedScope) {
                    $finalScope = (int)$parsedScope;
                } else {
                    continue;
                }
            }

            // Unterscheidung zwischen "spontan" und "termin"
            // - Wenn 'Uhrzeit'=='00:00:00', behandeln wir es als spontan angekommen => Stunde aus wsm_aufnahmezeit
            $type = 'termin';
            $hourStr = $br['Uhrzeit'];
            if ($br['Uhrzeit'] === '00:00:00') {
                $type = 'spontan';
                $hourStr = $br['wsm_aufnahmezeit'];
            }
            // Nur die Stunde (0..23) aus dem Zeitstring extrahieren
            $parts = explode(':', $hourStr);
            $hour  = (int)$parts[0];

            $waitMins = $this->timeToMinutes($br['wartezeit']);

            $wayMins = is_numeric($br['wegezeit']) ? round($br['wegezeit'] / 60, 2) : 0.0;

            $dateStr = $br['Datum'];
            // Falls für diesen StandortID das Datum noch nicht existiert
            if (!isset($statsByScopeDate[$finalScope])) {
                $statsByScopeDate[$finalScope] = [];
            }

            if (!isset($statsByScopeDate[$finalScope][$dateStr])) {
                // Falls das Datum neu ist, wird für jede Stunde von 0 bis 23 ein neuer Eintrag erzeugt => keine Null Werte
                $statsByScopeDate[$finalScope][$dateStr] = [];
                foreach (range(0, 23) as $h) {
                    $statsByScopeDate[$finalScope][$dateStr][$h] = [
                        'spontan' => ['count'=>0, 'sumWait'=>0.0, 'sumWay'=>0.0],
                        'termin'  => ['count'=>0, 'sumWait'=>0.0, 'sumWay'=>0.0],
                    ];
                }
            }

            $statsByScopeDate[$finalScope][$dateStr][$hour][$type]['count']   += 1;
            $statsByScopeDate[$finalScope][$dateStr][$hour][$type]['sumWait'] += $waitMins;
            $statsByScopeDate[$finalScope][$dateStr][$hour][$type]['sumWay']  += $wayMins;
        }

        //     Wir müssen (INSERT IGNORE) eine Zeile in wartenrstatistik für jeden (Standort, Datum) einfügen,
        //    then update all hour columns.
        foreach ($statsByScopeDate as $scopeId => $dateArray) {
            foreach ($dateArray as $dateStr => $hoursData) {

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

                // Eine einzelne UPDATE-Anweisung für alle 24 Stundenspalten erstellen
                $updateParams = [
                    'sid' => $scopeId,
                    'd'   => $dateStr
                ];
                $updateCols = [];

                // Für jede Stunde 0..23 Spalten für "spontan" und "termin" füllen
                foreach (range(0, 23) as $hour) {
                    // Dynamische Spaltennamen für spontane Kunden
                    $colWaitCountSp = sprintf('wartende_ab_%02d_spontan', $hour);
                    $colWaitTimeSp  = sprintf('echte_zeit_ab_%02d_spontan', $hour);
                    $colWayTimeSp   = sprintf('wegezeit_ab_%02d_spontan', $hour);

                    $countSp = $hoursData[$hour]['spontan']['count'];
                    $avgWaitSp= ($countSp > 0)
                        ? round($hoursData[$hour]['spontan']['sumWait'] / $countSp, 2)
                        : 0.0;
                    $avgWaySp= ($countSp > 0)
                        ? round($hoursData[$hour]['spontan']['sumWay'] / $countSp, 2)
                        : 0.0;

                    $updateCols[] = "`$colWaitCountSp` = :$colWaitCountSp";
                    $updateCols[] = "`$colWaitTimeSp`  = :$colWaitTimeSp";
                    $updateCols[] = "`$colWayTimeSp`   = :$colWayTimeSp";

                    $updateParams[$colWaitCountSp] = $countSp;
                    $updateParams[$colWaitTimeSp]  = $avgWaitSp;
                    $updateParams[$colWayTimeSp]   = $avgWaySp;

                    // termin
                    $colWaitCountTe = sprintf('wartende_ab_%02d_termin', $hour);
                    $colWaitTimeTe  = sprintf('echte_zeit_ab_%02d_termin', $hour);
                    $colWayTimeTe   = sprintf('wegezeit_ab_%02d_termin', $hour);

                    $countTe = $hoursData[$hour]['termin']['count'];
                    $avgWaitTe= ($countTe > 0)
                        ? round($hoursData[$hour]['termin']['sumWait'] / $countTe, 2)
                        : 0.0;
                    $avgWayTe= ($countTe > 0)
                        ? round($hoursData[$hour]['termin']['sumWay'] / $countTe, 2)
                        : 0.0;

                    $updateCols[] = "`$colWaitCountTe` = :$colWaitCountTe";
                    $updateCols[] = "`$colWaitTimeTe`  = :$colWaitTimeTe";
                    $updateCols[] = "`$colWayTimeTe`   = :$colWayTimeTe";

                    $updateParams[$colWaitCountTe] = $countTe;
                    $updateParams[$colWaitTimeTe]  = $avgWaitTe;
                    $updateParams[$colWayTimeTe]   = $avgWayTe;
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
        }

        echo "Done collecting stats for {$day->format('Y-m-d')}\n";
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