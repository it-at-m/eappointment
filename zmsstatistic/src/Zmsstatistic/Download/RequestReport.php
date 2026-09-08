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
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

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
    #[\Override]
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
                $this->writeReport($report, $download->getSpreadSheet(), 'MMMM');
            } else {
                $this->writeReport($report, $download->getSpreadSheet());
            }
        }

        return $download->writeDownload($response);
    }

    public function writeReport(
        ReportEntity $report,
        Spreadsheet $spreadsheet,
        string $datePatternCol = 'dd.MM.yyyy'
    ): Spreadsheet {
        $sheet = $spreadsheet->getActiveSheet();

        $firstDay = $report->firstDay->year . '-' . $report->firstDay->month . '-' . $report->firstDay->day;
        $lastDay = $report->lastDay->year . '-' . $report->lastDay->month . '-' . $report->lastDay->day;
        $this->firstDayDate = $this->setDateTime($firstDay);
        $this->lastDayDate = $this->setDateTime($lastDay);

        $this->writeHeader($report, $sheet, $datePatternCol);
        $this->writeReportData($report, $sheet);

        return $spreadsheet;
    }

    private function getReportDates(ReportEntity $report): array
    {
        if ($report->period === 'day') {
            return $report->getDatesWithRequests();
        }

        $dates = [];
        $dateTime = clone $this->firstDayDate;

        do {
            $dates[] = $dateTime->format($this->dateFormatter[$report->period]);
            $dateTime->modify('+1 ' . $report->period);
        } while ($dateTime <= $this->lastDayDate);

        return $dates;
    }

    public function writeHeader(
        ReportEntity $report,
        \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet,
        string $datePatternCol
    ): void {
        $reportHeader = [];
        $reportHeader[] = 'Dienstleistung';
        $reportHeader[] = 'Ø Bearbeitungsdauer';
        $reportHeader[] = 'Summe';
        foreach ($this->getReportDates($report) as $date) {
            $reportHeader[] = $this->getFormatedDates(
                $this->setDateTime($date),
                $datePatternCol
            );
        }
        $sheet->fromArray($reportHeader, null, 'A' . ($sheet->getHighestRow() + 2));
    }

    /**
     * @SuppressWarnings(Unused)
     */
    public function writeReportData(ReportEntity $report, \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet): void
    {
        $reportData = [];
        $firstDataRow = $sheet->getHighestRow() + 1;
        $totalSum = 0;
        $reportDates = $this->getReportDates($report);
        $dateSums = array_fill(0, count($reportDates), 0);

        foreach ($report->data as $name => $entry) {
            if ($name !== 'sum' && $name !== 'average_processingtime' && $name !== 'average_processingtime_overall') {
                $rowData = [];
                if ($name === ReportEntity::REQUEST_STAT_NAME_UNCATEGORIZED) {
                    $rowData[] = 'Dienstleistung wurde nicht erfasst';
                } elseif ($name === ReportEntity::REQUEST_STAT_NAME_NONEXISTENT) {
                    $rowData[] = 'Dienstleistung konnte nicht erbracht werden';
                } else {
                    $rowData[] = $name;
                }
                $rowData[] = isset($report->data['average_processingtime'][$name])
                    && is_numeric($report->data['average_processingtime'][$name])
                    ? ReportHelper::formatTimeValue($report->data['average_processingtime'][$name])
                    : "0";
                $rowData[] = $report->data['sum'][$name];

                $includeInTotal = $name !== ReportEntity::REQUEST_STAT_NAME_UNCATEGORIZED
                    && $name !== ReportEntity::REQUEST_STAT_NAME_NONEXISTENT;
                if ($includeInTotal) {
                    $totalSum += (int)($report->data['sum'][$name] ?? 0);
                }

                foreach ($reportDates as $dateColumn => $dateString) {
                    $requestCount = isset($entry[$dateString])
                        ? (int) $entry[$dateString]['requestscount']
                        : 0;

                    $rowData[] = $requestCount;

                    if ($includeInTotal) {
                        $dateSums[$dateColumn] += $requestCount;
                    }
                }

                $reportData[] = $rowData;
            }
        }

        $sheet->fromArray($reportData, null, 'A' . $firstDataRow);
        $overallProcessingTime =
        isset(
            $report->data['average_processingtime_overall']
        )
             && is_numeric($report->data['average_processingtime_overall'])
             ? ReportHelper::formatTimeValue($report->data['average_processingtime_overall']) : '0';
        $sumRowIndex = $sheet->getHighestRow() + 2;
        $sumRow = array_merge(
            [
                'Ø Bearbeitungsdauer (unabhängig von DL) / Summe',
                $overallProcessingTime,
                $totalSum
            ],
            $dateSums
        );

        $sheet->fromArray($sumRow, null, 'A' . $sumRowIndex);
    }
}
