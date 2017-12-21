<?php
/**
 * @package zmsstatistic
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsstatistic\Download;

use \BO\Zmsentities\Exchange as ReportEntity;

use \BO\Zmsstatistic\Helper\Download;

use \BO\Zmsstatistic\Helper\Report;

use \BO\Zmsstatistic\Helper\OrganisationData;

use PhpOffice\PhpSpreadsheet\Spreadsheet;

class ClientScope extends \BO\Zmsstatistic\BaseController
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
        $title = 'statistic_'. $args['period'];
        $download = (new Download($request))->setSpreadSheet($title);
        $spreadsheet = $download->getSpreadSheet();
        $spreadsheet = $this->writeInfoHeader($args, $spreadsheet);
        foreach ($args['reports'] as $report) {
            $spreadsheet = $this->writeReport($report, $download->getSpreadSheet());
        }
        $spreadsheet = $this->writeLegend($spreadsheet);
        $response->getBody()->write($download->getWriter()->save('php://output'));
        return $response
            ->withHeader(
                'Content-Type',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
            )
            ->withHeader(
                'Content-Disposition',
                sprintf('attachment; filename="%s.%s"', $download->getTitle(), $download->getType())
            );
    }

    protected function writeInfoHeader(array $args, Spreadsheet $spreadsheet)
    {
        $sheet = $spreadsheet->getActiveSheet();
        $infoData[] = Report::$subjectTranslations[$args['category']];
        $infoData[] = $args['organisation']['name'];
        $infoData[] = $args['department']['name'];
        $infoData[] = $args['scope']['contact']['name'] .' '. $args['scope']['shortname'];
        $infoData = array_chunk($infoData, 1);
        $sheet->fromArray($infoData, null, 'A'. $sheet->getHighestRow());

        $firstDay = $args['reports'][0]->firstDay->toDateTime()->format('d.m.Y');
        $lastDay = $args['reports'][0]->lastDay->toDateTime()->format('d.m.Y');
        $range = array('Zeitraum:', $firstDay, 'bis', $lastDay);
        $sheet->fromArray($range, null, 'A'. ($sheet->getHighestRow() + 1));
        return $spreadsheet;
    }

    protected function writeReport(ReportEntity $report, Spreadsheet $spreadsheet)
    {
        $sheet = $spreadsheet->getActiveSheet();
        $this->writeReportHeader($report, $sheet);
        $this->writeTotalsRow($report, $sheet);
        $this->writeReportData($report, $sheet);
        return $spreadsheet;
    }

    protected function writeReportHeader(ReportEntity $report, $sheet)
    {
        $reportHeader = [];
        if ('totals' == end($report->data)['subjectid']) {
            $reportHeader[] = null;
        }
        foreach (array_keys($report->data[0]) as $headline) {
            if (! in_array($headline, Report::$ignoreColumns)) {
                $reportHeader[] = Report::$headlines[$headline];
            }
        }
        $sheet->fromArray($reportHeader, null, 'A'. ($sheet->getHighestRow() + 2));
    }

    protected function writeTotalsRow(ReportEntity $report, $sheet)
    {
        if ('totals' == end($report->data)['subjectid']) {
            $totals = array_pop($report->data);
            $dateString = $report->firstDay->year .'-'. $report->firstDay->month .'-'. $report->firstDay->day;
            $month = $this->getFormatedDates(new \DateTimeImmutable($dateString), 'MMMM');
            $year = $this->getFormatedDates(new \DateTimeImmutable($dateString), 'Y');
            $reportTotal = [$month, $year];
            foreach ($totals as $key => $item) {
                if (! in_array($key, Report::$ignoreColumns) && 'date' != $key) {
                    $reportTotal[] = (string)($item);
                }
            }
            $sheet->fromArray($reportTotal, null, 'A'. ($sheet->getHighestRow() + 1));
        }
    }

    protected function writeReportData(ReportEntity $report, $sheet)
    {
        $reportData = [];
        foreach ($report->data as $row => $entry) {
            foreach ($entry as $key => $item) {
                if (! in_array($key, Report::$ignoreColumns)) {
                    if ('date' == $key) {
                        $dayOfWeek = $this->getFormatedDates(new \DateTimeImmutable($item), 'ccc');
                        $item = (new \DateTimeImmutable($item))->format('d.m.y');
                        $reportData[$row][] = $dayOfWeek;
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

    protected function getFormatedDates($date, $pattern = 'MMMM')
    {
        $dateFormatter = new \IntlDateFormatter(
            'de-DE',
            \IntlDateFormatter::MEDIUM,
            \IntlDateFormatter::MEDIUM,
            'Europe/Berlin',
            \IntlDateFormatter::GREGORIAN,
            $pattern
        );
        return $dateFormatter->format($date->getTimestamp());
    }
}
