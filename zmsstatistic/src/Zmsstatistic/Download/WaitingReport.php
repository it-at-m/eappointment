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
    private const CUSTOMER_TYPE_GESAMT = 'gesamt';
    private const CUSTOMER_TYPE_TERMIN = 'termin';
    private const CUSTOMER_TYPE_SPONTAN = 'spontan';

    protected $reportPartsGesamt = [
        'waitingtime_total' => 'Durchschnittliche Wartezeit in Min. (Gesamt)',
        'waitingcount_total' => 'Wartende Gesamtkunden',
        'waytime_total' => 'Durchschnittliche Wegezeit in Min. (Gesamt)',
    ];

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
    private function createAndPopulateSheet(
        Spreadsheet $spreadsheet,
        string $sheetTitle,
        array $args,
        string $customerType,
        bool $isFirstSheet = false
    ): void {
        if (!$isFirstSheet) {
            $spreadsheet->createSheet()->setTitle($sheetTitle);
        } else {
            $spreadsheet->getActiveSheet()->setTitle($sheetTitle);
        }
        $spreadsheet->setActiveSheetIndexByName($sheetTitle);
        $this->writeInfoHeader($args, $spreadsheet);
        foreach ($args['reports'] as $report) {
            $this->writeWaitingReport($report, $spreadsheet, $customerType, 'dd.MM.yyyy');
        }
    }

    public function readResponse(
        RequestInterface $request,
        ResponseInterface $response,
        array $args
    ) {
        $title = 'waitingstatistic_' . $args['period'];
        $download = (new Download($request))->setSpreadSheet($title);
        $spreadsheet = $download->getSpreadSheet();

        $this->createAndPopulateSheet($spreadsheet, 'Gesamt', $args, self::CUSTOMER_TYPE_GESAMT, true);
        $this->createAndPopulateSheet($spreadsheet, 'Terminkunden', $args, self::CUSTOMER_TYPE_TERMIN);
        $this->createAndPopulateSheet($spreadsheet, 'Spontankunden', $args, self::CUSTOMER_TYPE_SPONTAN);

        // Für den Download das erste Blatt aktiv lassen
        $spreadsheet->setActiveSheetIndexByName('Gesamt');

        return $download->writeDownload($response);
    }

    private function assertValidCustomerType(string $customerType): void
    {
        $validTypes = [
            self::CUSTOMER_TYPE_GESAMT,
            self::CUSTOMER_TYPE_TERMIN,
            self::CUSTOMER_TYPE_SPONTAN,
        ];

        if (!in_array($customerType, $validTypes, true)) {
            throw new \InvalidArgumentException(
                "Invalid customer type: {$customerType}. Must be one of: " . implode(', ', $validTypes)
            );
        }
    }
    
    public function writeWaitingReport(
        ReportEntity $report,
        Spreadsheet $spreadsheet,
        string $customerType,
        $datePatternCol = 'dd.MM.yyyy',
    ) {
        $this->assertValidCustomerType($customerType);

        $sheet = $spreadsheet->getActiveSheet();
        $this->writeHeader($report, $sheet, $datePatternCol);
        $this->writeTotals($report, $sheet, $customerType);
        if ($customerType === self::CUSTOMER_TYPE_TERMIN) {
            $parts = $this->reportPartsTermin;
        } elseif ($customerType === self::CUSTOMER_TYPE_SPONTAN) {
            $parts = $this->reportPartsSpontan;
        } else {
            $parts = $this->reportPartsGesamt;
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

    private function getCustomerTypeKeys(string $customerType): array
    {
        $this->assertValidCustomerType($customerType);
        $keyMappings = [
            self::CUSTOMER_TYPE_TERMIN => [
                'max' => 'max_waitingtime_termin',
                'avg' => 'average_waitingtime_termin',
                'avg_way' => 'average_waytime_termin',
            ],
            self::CUSTOMER_TYPE_SPONTAN => [
                'max' => 'max_waitingtime',
                'avg' => 'average_waitingtime',
                'avg_way' => 'average_waytime',
            ],
            self::CUSTOMER_TYPE_GESAMT => [
                'max' => 'max_waitingtime_total',
                'avg' => 'average_waitingtime_total',
                'avg_way' => 'average_waytime_total',
            ],
        ];

        return $keyMappings[$customerType];
    }

    public function writeTotals(ReportEntity $report, $sheet, string $customerType)
    {
        $this->assertValidCustomerType($customerType);

        $entity = clone $report;
        $totals = $entity->data['max'];
        unset($entity->data['max']);

        $keys = $this->getCustomerTypeKeys($customerType);

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
