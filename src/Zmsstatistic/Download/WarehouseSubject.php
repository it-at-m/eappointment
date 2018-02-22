<?php
/**
 * @package zmsstatistic
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsstatistic\Download;

use \BO\Zmsentities\Exchange as ReportEntity;

use \BO\Zmsstatistic\Helper\Download;

use PhpOffice\PhpSpreadsheet\Spreadsheet;

class WarehouseSubject extends Base
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
        $title = 'raw_statistic_'. $args['subject'];
        $download = (new Download($request))->setSpreadSheet($title);
        $spreadsheet = $download->getSpreadSheet();
        //$spreadsheet = $this->writeInfoHeader($args, $spreadsheet);
        //$spreadsheet = $this->writeDictionaryData($args['reports'][0], $spreadsheet);
        $spreadsheet = $this->writeRawReport($args['reports'][0], $spreadsheet);

        return $download->writeDownload($response);
    }

    protected function writeDictionaryData(ReportEntity $report, Spreadsheet $spreadsheet)
    {
        $sheet = $spreadsheet->getActiveSheet();
        $reportData = [];
        foreach ($report->dictionary[0] as $key => $item) {
            $key = ('position' == $key) ? '#' : $key;
            if ('reference' != $key) {
                $reportData['header'][] = $key;
            }
        }
        foreach ($report->dictionary as $row => $entry) {
            foreach ($entry as $key => $item) {
                if ('position' == $key) {
                    $reportData[$row][] = $item + 1;
                } elseif ('reference' != $key) {
                    $reportData[$row][] = $item;
                }
            }
        }
        $sheet->fromArray($reportData, null, 'A'. ($sheet->getHighestRow() + 2));
        return $spreadsheet;
    }

    protected function writeRawReport(ReportEntity $report, Spreadsheet $spreadsheet)
    {
        $sheet = $spreadsheet->getActiveSheet();
        $reportData = [];
        foreach ($report->dictionary as $item) {
            $reportData['header'][] = $item['variable'];
        }
        foreach ($report->data as $row => $entry) {
            foreach ($entry as $item) {
                $reportData[$row][] = (is_numeric($item)) ? (string)($item) : $item;
            }
        }
        $sheet->fromArray($reportData, null, 'A'. ($sheet->getHighestRow()));
        return $spreadsheet;
    }
}
