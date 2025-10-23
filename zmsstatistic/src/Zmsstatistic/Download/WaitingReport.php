<?php

/**
 * @package zmsstatistic
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsstatistic\Download;

use BO\Zmsentities\Exchange as ReportEntity;
use BO\Zmsstatistic\Helper\Download;
use BO\Zmsstatistic\Helper\ReportHelper;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class WaitingReport extends Base
{
    protected $reportPartsTermin = [
        'waitingtime_termin' => 'Durchschnittliche Wartezeit in Min. (Terminkunden)',
        'waitingcount_termin' => 'Wartende Terminkunden',
        'waytime_termin' => 'Durchschnittliche Wegezeit in Min. (Terminkunden)',
    ];

    protected $reportPartsSpontan = [
        'waitingtime' => 'Durchschnittliche Wartezeit in Min. (Spontankunden)',
        'waitingcount' => 'Wartende Spontankunden',
        'waytime' => 'Durchschnittliche Wegezeit in Min. (Spontankunden)',
    ];

    /**
     * @SuppressWarnings(Param)
     * @return ResponseInterface
     */
    public function readResponse(
        RequestInterface $request,
        ResponseInterface $response,
        array $args
    ) {
        $title = 'waitingstatistic_' . $args['period'];
        $download = (new Download($request))->setSpreadSheet($title);
        $spreadsheet = $download->getSpreadSheet();

        // Blatt 1: Terminkunden
        $spreadsheet->getActiveSheet()->setTitle('Terminkunden');
        $spreadsheet->setActiveSheetIndexByName('Terminkunden');
        $this->writeInfoHeader($args, $spreadsheet);
        foreach ($args['reports'] as $report) {
            $this->writeWaitingReport($report, $spreadsheet, /*isTermin*/ true, 'dd.MM.yyyy');
        }

        // Blatt 2: Spontankunden
        $spreadsheet->createSheet()->setTitle('Spontankunden');
        $spreadsheet->setActiveSheetIndexByName('Spontankunden');
        $this->writeInfoHeader($args, $spreadsheet);
        foreach ($args['reports'] as $report) {
            $this->writeWaitingReport($report, $spreadsheet, /*isTermin*/ false, 'dd.MM.yyyy');
        }

        // Für den Download das erste Blatt aktiv lassen
        $spreadsheet->setActiveSheetIndexByName('Terminkunden');

        return $download->writeDownload($response);
    }

    public function writeWaitingReport(
        ReportEntity $report,
        Spreadsheet $spreadsheet,
        bool $isTermin,
        $datePatternCol = 'dd.MM.yyyy',
    ) {
        $sheet = $spreadsheet->getActiveSheet();
        $this->writeHeader($report, $sheet, $datePatternCol);
        $this->writeTotals($report, $sheet, $isTermin);
        if ($isTermin) {
            $parts = $this->reportPartsTermin;
        } else {
            $parts = $this->reportPartsSpontan;
        }
        foreach ($parts as $partName => $headline) {
            $this->writeReportPart($report, $sheet, $partName, $headline);
        }

        return $spreadsheet;
    }

    public function writeHeader(ReportEntity $report, $sheet, $datePatternCol)
    {
        $reportHeader = [];
        $reportHeader[] = null;
        $reportHeader[] = 'Max.';
        $dates = array_keys($report->data);
        sort($dates);
        foreach ($dates as $date) {
            if (! in_array($date, static::$ignoreColumns)) {
                $date = $this->getFormatedDates($this->setDateTime($date), $datePatternCol);
                $reportHeader[] = $date;
            }
        }
        $startRow = $sheet->getHighestRow() + 2;
        $sheet->fromArray($reportHeader, null, 'A' . $startRow);
        // Datumszellen (ab B) in dieser Zeile fett schreiben
        $lastColIdx = count($reportHeader);              // Anzahl der geschriebenen Zellen
        if ($lastColIdx >= 2) {
            $start = "B{$startRow}";
            $end   = Coordinate::stringFromColumnIndex($lastColIdx) . $startRow;
            $sheet->getStyle("$start:$end")->getFont()->setBold(true);
        }
    }

    public function writeTotals(ReportEntity $report, $sheet, bool $isTermin)
    {
        $entity = clone $report;
        $totals = array_pop($entity->data);

        if ($isTermin) {
            $keys = [
                'max' => 'max_waitingtime_termin',
                'avg' => 'average_waitingtime_termin',
                'avg_way' => 'average_waytime_termin',
            ];
        } else {
            $keys = [
                'max' => 'max_waitingtime',
                'avg' => 'average_waitingtime',
                'avg_way' => 'average_waytime',
            ];
        }
        $reportTotal['max'][] = 'Stunden-Max (Spaltenmaximum) der Wartezeit in Min.';
        $reportTotal['average'][] = 'Stundendurchschnitt (Spalten) der Wartezeit in Min.';
        $reportTotal['average_waytime'][] = 'Stundendurchschnitt (Spalten) der Wegezeit in Min.';

        $reportTotal['max'][] = ReportHelper::formatTimeValue($totals[$keys['max']]);
        $reportTotal['average'][] = ReportHelper::formatTimeValue($totals[$keys['avg']]);
        $reportTotal['average_waytime'][] = ReportHelper::formatTimeValue($totals[$keys['avg_way']]);

        foreach ($entity->data as $entry) {
            $reportTotal['max'][] = ReportHelper::formatTimeValue($entry[$keys['max']]);
            $reportTotal['average'][] = ReportHelper::formatTimeValue($entry[$keys['avg']]);
            $reportTotal['average_waytime'][] = ReportHelper::formatTimeValue($entry[$keys['avg_way']]);
        }
        $sheet->fromArray($reportTotal, null, 'A' . ($sheet->getHighestRow() + 1));
    }

    public function writeReportPart(ReportEntity $report, $sheet, $rangeName, $headline)
    {
        $entity = clone $report;
        $totals = $entity->data['max'];
        unset($entity->data['max']);
        $reportData['headline'] = ['Zeitabschnitte','Tagesmaximum (Zeilenmaximum)',$headline];
        $formatAsTime = strpos($rangeName, 'waitingcount') === false;
        foreach ($entity->data as $entry) {
            foreach ($entry as $hour => $item) {
                if (5 < $hour && 19 > $hour) {
                    if (! isset($reportData[$hour])) {
                        $reportData[$hour] = [];
                    }
                    $range = $hour . '-' . ($hour + 1) . ' Uhr';
                    if (! in_array($range, $reportData[$hour])) {
                        $reportData[$hour][] = $range;
                        $totalValue = $totals[$hour][$rangeName];
                        $reportData[$hour][] = $formatAsTime
                            ? ReportHelper::formatTimeValue($totalValue)
                            : $totalValue;
                    }
                    $value = $item[$rangeName] ?? '-';
                    $reportData[$hour][] = $formatAsTime
                        ? ReportHelper::formatTimeValue($value)
                        : $value;
                }
            }
        }
        $startRow = $sheet->getHighestRow() + 2;
        $sheet->fromArray($reportData, null, 'A' . $startRow);
        $sheet->getStyle("A{$startRow}:C{$startRow}")->getFont()->setBold(true);
    }
}
