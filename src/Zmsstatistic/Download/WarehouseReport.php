<?php
/**
 * @package zmsstatistic
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsstatistic\Download;

use \BO\Zmsentities\Exchange as ReportEntity;

use \BO\Zmsstatistic\Helper\Download;

use PhpOffice\PhpSpreadsheet\Spreadsheet;

class WarehouseReport extends Base
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
        $title = 'raw_statistic_'. $args['subject'] .'_'. $args['subjectid'] .'_'. $args['period'];
        $download = (new Download($request))->setSpreadSheet($title);
        $spreadsheet = $download->getSpreadSheet();
        $spreadsheet = $this->writeRawReport($args['report'], $spreadsheet);

        return $download->writeDownload($response);
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
