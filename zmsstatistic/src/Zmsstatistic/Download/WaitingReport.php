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

class WaitingReport extends Base
{
    protected $reportParts = [
        'waitingcalculated' => 'maximal berechnetet Wartezeit Spontankunde',
        'waitingcount' => 'maximal Wartende Spontankunde',
        'waitingtime' => 'maximal gemessene Wartezeit Spontankunde',
        'waitingcount_termin' => 'maximal Wartende Terminkunde',
        'waitingtime_termin' => 'maximal gemessene Wartezeit Terminkunde'
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
        $title = 'waitingstatistic_'. $args['period'];
        $download = (new Download($request))->setSpreadSheet($title);

        $this->writeInfoHeader($args, $download->getSpreadSheet());
        foreach ($args['reports'] as $report) {
            if ('month' == $report->period) {
                $this->writeWaitingReport($report, $download->getSpreadSheet(), 'yyyy', 'MMMM');
            } else {
                $this->writeWaitingReport($report, $download->getSpreadSheet());
            }
        }

        return $download->writeDownload($response);
    }

    public function writeWaitingReport(
        ReportEntity $report,
        Spreadsheet $spreadsheet,
        $datePatternCol1 = 'MMMM',
        $datePatternCol2 = 'dd (ccc)'
    ) {
        $sheet = $spreadsheet->getActiveSheet();
        $this->writeHeader($report, $sheet, $datePatternCol1, $datePatternCol2);
        $this->writeTotals($report, $sheet);
        foreach ($this->reportParts as $partName => $headline) {
            $this->writeReportPart($report, $sheet, $partName, $headline);
        }

        return $spreadsheet;
    }

    public function writeHeader(ReportEntity $report, $sheet, $datePatternCol1, $datePatternCol2)
    {
        $dateString = $report->firstDay->year .'-'. $report->firstDay->month .'-'. $report->firstDay->day;
        $reportHeader = [];
        $reportHeader[] = null;
        $reportHeader[] = $this->getFormatedDates($this->setDateTime($dateString), $datePatternCol1);
        foreach (array_keys($report->data) as $date) {
            if (! in_array($date, static::$ignoreColumns)) {
                $date = $this->getFormatedDates($this->setDateTime($date), $datePatternCol2);
                $reportHeader[] = $date;
            }
        }
        $sheet->fromArray($reportHeader, null, 'A'. ($sheet->getHighestRow() + 2));
    }

    public function writeTotals(ReportEntity $report, $sheet)
    {
        $entity = clone $report;
        $totals = array_pop($entity->data);
        $reportTotal['max'][] = 'Tagesmaximum Spontantkunde';
        $reportTotal['average'][] = 'Tagesdurchschnitt Spontantkunde';
        $reportTotal['max'][] = $totals['max_waitingtime'];
        $reportTotal['average'][] = $totals['average_waitingtime'];
        foreach ($entity->data as $entry) {
            $reportTotal['max'][] = $entry['max_waitingtime'];
            $reportTotal['average'][] = $entry['average_waitingtime'];
        }
        $sheet->fromArray($reportTotal, null, 'A'. ($sheet->getHighestRow() + 1));

        $reportTotal2['max'][] = 'Tagesmaximum Terminkunde';
        $reportTotal2['average'][] = 'Tagesdurchschnitt Terminkunde';
        $reportTotal2['max'][] = $totals['max_waitingtime_termin'];
        $reportTotal2['average'][] = $totals['average_waitingtime_termin'];
        foreach ($entity->data as $entry) {
            $reportTotal2['max'][] = $entry['max_waitingtime_termin'];
            $reportTotal2['average'][] = $entry['average_waitingtime_termin'];
        }
        $sheet->fromArray($reportTotal2, null, 'A'. ($sheet->getHighestRow() + 1));
    }

    public function writeReportPart(ReportEntity $report, $sheet, $rangeName, $headline)
    {
        $entity = clone $report;
        $totals = $entity->data['max'];
        unset($entity->data['max']);
        $reportData['headline'] = ['Zeitabschnitte',$headline];
        foreach ($entity->data as $entry) {
            foreach ($entry as $hour => $item) {
                if (5 < $hour && 22 > $hour) {
                    if (! isset($reportData[$hour])) {
                        $reportData[$hour] = [];
                    }
                    $range = $hour .'-'. ($hour + 1) .' Uhr';
                    if (! in_array($range, $reportData[$hour])) {
                        $reportData[$hour][] = $range;
                        $reportData[$hour][] = $totals[$hour][$rangeName];
                    }
                    $reportData[$hour][] = $item[$rangeName];
                }
            }
        }
        $sheet->fromArray($reportData, null, 'A'. ($sheet->getHighestRow() + 2));
    }
}
