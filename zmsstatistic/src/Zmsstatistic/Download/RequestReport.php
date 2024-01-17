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
        $title = 'requeststatistic_'. $args['period'];
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

        $firstDay = $report->firstDay->year .'-'. $report->firstDay->month .'-'. $report->firstDay->day;
        $lastDay = $report->lastDay->year .'-'. $report->lastDay->month .'-'. $report->lastDay->day;
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
            $dateTime->modify('+1 '. $report->period);
        } while ($dateTime <= $this->lastDayDate);
        $sheet->fromArray($reportHeader, null, 'A'. ($sheet->getHighestRow() + 2));
    }

    /**
     * @SuppressWarnings(Unused)
     */
    public function writeReportData(ReportEntity $report, $sheet, $datePatternCol1, $datePatternCol2)
    {
        $reportData = [];
        foreach ($report->data as $name => $entry) {
            if ('sum' != $name) {
                $reportData[$name][] = $name;
                $reportData[$name][] = $report->data['sum'][$name];
                $dateTime = clone $this->firstDayDate;
                do {
                    $dateString = $dateTime->format($this->dateFormatter[$report->period]);
                    $reportData[$name][] = (isset($entry[$dateString])) ? $entry[$dateString]['requestscount'] : '0';
                    $dateTime->modify('+1 '. $report->period);
                } while ($dateTime <= $this->lastDayDate);
            }
        }
        $sheet->fromArray($reportData, null, 'A'. ($sheet->getHighestRow() + 1));
    }
}
