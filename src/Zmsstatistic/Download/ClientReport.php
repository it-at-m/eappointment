<?php
/**
 * @package zmsstatistic
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsstatistic\Download;

use \BO\Zmsentities\Exchange as ReportEntity;

use \BO\Zmsstatistic\Helper\Download;

use PhpOffice\PhpSpreadsheet\Spreadsheet;

class ClientReport extends Base
{
    /**
     * @SuppressWarnings(Param)
     * @return String
     */
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $title = 'clientstatistic_'. $args['period'];
        $download = (new Download($request))->setSpreadSheet($title);
        $spreadsheet = $download->getSpreadSheet();
        $spreadsheet = $this->writeInfoHeader($args, $spreadsheet);
        foreach ($args['reports'] as $report) {
            if ('month' == $report->period) {
                $spreadsheet = $this->writeReport($report, $download->getSpreadSheet(), 'yyyy', 'MMMM', 'yyyy');
            } else {
                $spreadsheet = $this->writeReport($report, $download->getSpreadSheet());
            }
        }
        $spreadsheet = $this->writeLegend($spreadsheet);
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
        if ('totals' == end($report->data)['subjectid']) {
            $reportHeader[] = null;
        }
        foreach (array_keys($report->data[0]) as $headline) {
            if (! in_array($headline, static::$ignoreColumns)) {
                $reportHeader[] = static::$headlines[$headline];
            }
        }
        $sheet->fromArray($reportHeader, null, 'A'. ($sheet->getHighestRow() + 2));
    }

    public function writeTotalsRow(ReportEntity $report, $sheet, $datePatternTotals)
    {
        if ('totals' == end($report->data)['subjectid']) {
            $totals = array_pop($report->data);
            $dateString = $report->firstDay->year .'-'. $report->firstDay->month .'-'. $report->firstDay->day;
            $dateCol1 = ('MMMM' == $datePatternTotals) ?
                $this->getFormatedDates($this->setDateTime($dateString), $datePatternTotals) :
                null;
            $dateCol2 = $this->setDateTime($dateString)->format('Y');
            $reportTotal = [$dateCol1, $dateCol2];
            foreach ($totals as $key => $item) {
                if (! in_array($key, static::$ignoreColumns) && 'date' != $key) {
                    $reportTotal[] = (string)($item);
                }
            }
            $sheet->fromArray($reportTotal, null, 'A'. ($sheet->getHighestRow() + 1));
        }
    }

    public function writeReportData(ReportEntity $report, $sheet, $datePatternCol1, $datePatternCol2)
    {
        $reportData = [];
        foreach ($report->data as $row => $entry) {
            foreach ($entry as $key => $item) {
                if (! in_array($key, static::$ignoreColumns)) {
                    if ('date' == $key) {
                        $dateCol1 = $this->getFormatedDates($this->setDateTime($item), $datePatternCol1);
                        $item = $this->getFormatedDates($this->setDateTime($item), $datePatternCol2);
                        $reportData[$row][] = $dateCol1;
                    }
                    $reportData[$row][] = (is_numeric($item)) ? (string)($item) : $item;
                }
            }
        }
        $sheet->fromArray($reportData, null, 'A'. ($sheet->getHighestRow() + 1));
    }

    protected function writeLegend(Spreadsheet $spreadsheet)
    {
        $sheet = $spreadsheet->getActiveSheet();
        $legendData[] = '* eine SMS kostet 0,15 EUR';
        $legendData[] = '** in dieser Spalte sind nicht abschließend bearbeitete Kunden angegeben';
        $legendData = array_chunk($legendData, 1);
        $sheet->fromArray($legendData, null, 'A'. ($sheet->getHighestRow() + 1));
        return $spreadsheet;
    }
}
