<?php
/**
 * @package zmsstatistic
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsstatistic\Download;

use BO\Zmsentities\Exchange as ReportEntity;
use BO\Zmsstatistic\Helper\Download;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class ClientReport extends Base
{
    /**
     * @SuppressWarnings(Param)
     * @return ResponseInterface
     */
    public function readResponse(
        RequestInterface $request,
        ResponseInterface $response,
        array $args
    ) {
        $title = 'clientstatistic_'. $args['period'];
        $download = (new Download($request))->setSpreadSheet($title);
        $spreadsheet = $download->getSpreadSheet();
        $spreadsheet = $this->writeInfoHeader($args, $spreadsheet);
        if ($args['reports']) {
            foreach ($args['reports'] as $report) {
                if ('month' == $report->period) {
                    $spreadsheet = $this->writeReport($report, $download->getSpreadSheet(), 'yyyy', 'MMMM', 'yyyy');
                } else {
                    $spreadsheet = $this->writeReport($report, $download->getSpreadSheet());
                }
            }
        }
        $this->writeLegend($spreadsheet);

        return $download->writeDownload($response);
    }

    public function writeReport(
        ReportEntity $report,
        Spreadsheet $spreadsheet,
        $datePatternTotals = 'MMMM',
        $datePatternCol1 = 'ccc',
        $datePatternCol2 = 'dd.MM.yy'
    ) {
        $sheet = $spreadsheet->getActiveSheet();
        $this->writeReportHeader($report, $sheet);
        $this->writeTotalsRow($report, $sheet, $datePatternTotals);
        $this->writeReportData($report, $sheet, $datePatternCol1, $datePatternCol2);

        return $spreadsheet;
    }

    public function writeReportHeader(ReportEntity $report, $sheet)
    {
        $reportHeader = [];
        if ('totals' == end($report->data)['date'] || 'month' == $report->period) {
            $reportHeader[] = null;
        }
        $columnIndex = 0;
        foreach (array_keys($report->data[0]) as $headline) {
            $columnIndex++;
            if ($columnIndex == 3 || $columnIndex == 4) {
                continue;
            }
    
            if (!in_array($headline, static::$ignoreColumns)) {
                $reportHeader[] = static::$headlines[$headline];
            }
        }
        
        // Placeholders for the new columns
        $end = array_splice($reportHeader, -1);  // get the last two elements
        $reportHeader[] = static::$headlines['noappointment'];
        $reportHeader[] = static::$headlines['missednoappointment'];
        $reportHeader = array_merge($reportHeader, $end);  // append them back after adding new headers
        
    
        $sheet->fromArray($reportHeader, null, 'A'. ($sheet->getHighestRow() + 2));
    }
    
    public function writeTotalsRow(ReportEntity $report, $sheet, $datePatternTotals)
    {
        if ('totals' == end($report->data)['date']) {
            $totals = array_pop($report->data);
            $dateString = $report->firstDay->year .'-'. $report->firstDay->month .'-'. $report->firstDay->day;
            $dateCol1 = ('MMMM' == $datePatternTotals) ?
                $this->getFormatedDates($this->setDateTime($dateString), $datePatternTotals) :
                null;
            $dateCol2 = $this->setDateTime($dateString)->format('Y');
            $reportTotal = [$dateCol1, $dateCol2];
    
            $columnIndex = 0;
            foreach ($totals as $key => $item) {
                $columnIndex++;
                if ($columnIndex == 3 || $columnIndex == 4) {
                    continue;
                }
                
                if (! in_array($key, static::$ignoreColumns) && 'date' != $key) {
                    $reportTotal[] = (string)($item);
                }
            }
    
            // Calculations for the new columns
            $end = array_splice($reportTotal, -1);
            
            $reportTotal[] = $this->calculateNoAppointment($totals);
            $reportTotal[] = $this->calculateMissedNoAppointment($totals);
    
            $reportTotal = array_merge($reportTotal, $end);
    
            $sheet->fromArray($reportTotal, null, 'A'. ($sheet->getHighestRow() + 1));
        }
    }
    
    public function writeReportData(ReportEntity $report, $sheet, $datePatternCol1, $datePatternCol2)
    {
        $reportData = [];
    
        foreach ($report->data as $row => $entry) {
            $processedRow = [];
            $columnIndex = 0;
    
            foreach ($entry as $key => $item) {
                $columnIndex++;
                if ($columnIndex == 3 || $columnIndex == 4) {
                    continue;
                }
    
                if (!in_array($key, static::$ignoreColumns)) {
                    if ('date' == $key) {
                        $dateCol1 = $this->getFormatedDates($this->setDateTime($item), $datePatternCol1);
                        $item = $this->getFormatedDates($this->setDateTime($item), $datePatternCol2);
                        $processedRow[] = $dateCol1;
                    }
                    $processedRow[] = (is_numeric($item)) ? (string)($item) : $item;
                }
            }
    
            // Calculations for the new columns
            $end = array_splice($processedRow, -1);
            
            $processedRow[] = $this->calculateNoAppointmentForRow($entry);
            $processedRow[] = $this->calculateMissedNoAppointmentForRow($entry);
    
            $processedRow = array_merge($processedRow, $end);
    
            $reportData[$row] = $processedRow;
        }
    
        $sheet->fromArray($reportData, null, 'A'. ($sheet->getHighestRow() + 1));
    }
    
    

    protected function writeLegend(Spreadsheet $spreadsheet)
    {
        $sheet = $spreadsheet->getActiveSheet();
        $legendData[] = '** in dieser Spalte sind nicht abschließend bearbeitete Kunden angegeben';
        $legendData = array_chunk($legendData, 1);
        $sheet->fromArray($legendData, null, 'A'. ($sheet->getHighestRow() + 1));

        return $spreadsheet;
    }

    private function calculateNoAppointment($totals)
    {
        if (isset($totals['clientscount']) && isset($totals['withappointment'])) {
            return (string) ($totals['clientscount'] - $totals['withappointment']);
        }
        return 'N/A';
    }
    
    private function calculateMissedNoAppointment($totals)
    {
        if (isset($totals['missed']) && isset($totals['missedwithappointment'])) {
            return (string) ($totals['missed'] - $totals['missedwithappointment']);
        }
        return 'N/A';
    }

    private function calculateNoAppointmentForRow($entry)
    {
        if (isset($entry['clientscount']) && isset($entry['withappointment'])) {
            return (string) ($entry['clientscount'] - $entry['withappointment']);
        }
        return 'N/A';
    }
    
    private function calculateMissedNoAppointmentForRow($entry)
    {
        if (isset($entry['missed']) && isset($entry['missedwithappointment'])) {
            return (string) ($entry['missed'] - $entry['missedwithappointment']);
        }
        return 'N/A';
    }
}
