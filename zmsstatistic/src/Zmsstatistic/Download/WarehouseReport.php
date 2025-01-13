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

class WarehouseReport extends Base
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
        $title = 'raw_statistic_' . $args['subject'] . '_' . $args['subjectid'] . '_' . $args['period'];
        $download = (new Download($request))->setSpreadSheet($title);

        $this->writeRawReport($args['report'], $download->getSpreadSheet());

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
        $sheet->fromArray($reportData, null, 'A' . ($sheet->getHighestRow()));
        return $spreadsheet;
    }
}
