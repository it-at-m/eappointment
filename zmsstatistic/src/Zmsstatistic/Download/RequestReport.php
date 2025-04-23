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
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class RequestReport extends Base
{
    public $firstDayDate = null;

    public $lastDayDate = null;

    protected $dateFormatter = [
        'day' => 'Y-m-d',
        'month' => 'Y-m'
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
        $title = 'requeststatistic_' . $args['period'];
        $download = (new Download($request))->setSpreadSheet($title);

        $this->writeInfoHeader($args, $download->getSpreadSheet());
        foreach ($args['reports'] as $report) {
            if ('month' == $report->period) {
                $this->writeReport($report, $download->getSpreadSheet(), 'yyyy', 'MMMM');
            } else {
                $this->writeReport($report, $download->getSpreadSheet());
            }
        }

        return $download->writeDownload($response);
    }

    public function writeReport(
        ReportEntity $report,
        Spreadsheet $spreadsheet,
        $datePatternCol1 = 'MMMM',
        $datePatternCol2 = 'dd (ccc)'
    ) {
        $sheet = $spreadsheet->getActiveSheet();

        $firstDay = $report->firstDay->year . '-' . $report->firstDay->month . '-' . $report->firstDay->day;
        $lastDay = $report->lastDay->year . '-' . $report->lastDay->month . '-' . $report->lastDay->day;
        $this->firstDayDate = $this->setDateTime($firstDay);
        $this->lastDayDate = $this->setDateTime($lastDay);

        $this->writeHeader($report, $sheet, $datePatternCol1, $datePatternCol2);
        $this->writeReportData($report, $sheet, $datePatternCol1, $datePatternCol2);

        return $spreadsheet;
    }

    public function writeHeader(ReportEntity $report, $sheet, $datePatternCol1, $datePatternCol2)
    {
        $reportHeader = [];
        $reportHeader[] = 'Dienstleistung';
        $reportHeader[] = 'Ø Bearbeitungsdauer';
        $reportHeader[] = $this->getFormatedDates($this->firstDayDate, $datePatternCol1);
        $dateTime = clone $this->firstDayDate;
        do {
            $reportHeader[] = $this->getFormatedDates($dateTime, $datePatternCol2);
            $dateTime->modify('+1 ' . $report->period);
        } while ($dateTime <= $this->lastDayDate);
        $sheet->fromArray($reportHeader, null, 'A' . ($sheet->getHighestRow() + 2));
    }

    /**
     * @SuppressWarnings(Unused)
     */
    public function writeReportData(ReportEntity $report, $sheet, $datePatternCol1, $datePatternCol2)
    {
        $reportData = [];
        $rowIndex = $sheet->getHighestRow() + 1;
        $firstDataRow = $rowIndex;

        foreach ($report->data as $name => $entry) {
            if ($name !== 'sum' && $name !== 'average_processingtime') {
                $rowData = [];
                $rowData[] = $name;
                $rowData[] = isset($report->data['average_processingtime'][$name])
                    && is_numeric($report->data['average_processingtime'][$name])
                    ? (string)$report->data['average_processingtime'][$name]
                    : "0";
                $rowData[] = $report->data['sum'][$name];

                $dateTime = clone $this->firstDayDate;
                do {
                    $dateString = $dateTime->format($this->dateFormatter[$report->period]);
                    $rowData[] = isset($entry[$dateString]) ? (int)$entry[$dateString]['requestscount'] : '0';


                    $dateTime->modify('+1 ' . $report->period);
                } while ($dateTime <= $this->lastDayDate);

                $reportData[$name] = $rowData;
            }
        }

        $sheet->fromArray($reportData, null, 'A' . $rowIndex);
        $lastColumn = $sheet->getHighestColumn();
        $lastRow = $sheet->getHighestRow();
        $sumRowIndex = $lastRow + 2;
        $sumRow = ["Summe", "", ""];
        $sumRow[2] = "=SUM(C{$firstDataRow}:C{$lastRow})";
        $lastColumnIndex = Coordinate::columnIndexFromString($lastColumn);

        for ($colIndex = 4; $colIndex <= $lastColumnIndex; $colIndex++) {
            $colLetter = Coordinate::stringFromColumnIndex($colIndex);
            $sumRow[] = "=SUM({$colLetter}{$firstDataRow}:{$colLetter}{$lastRow})";
        }

        $sheet->fromArray($sumRow, null, 'A' . $sumRowIndex);
    }
}
