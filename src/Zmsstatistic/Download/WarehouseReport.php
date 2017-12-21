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

class WarehouseReport extends \BO\Zmsstatistic\BaseController
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
        $title = 'statistic_'. $args['subject'] .'_'. $args['subjectid'] .'_'. $args['period'];
        $download = (new Download($request))->setSpreadSheet($title);
        $spreadsheet = $this->writeInfoHeader(
            $args['report'],
            $args['subject'],
            $args['subjectid'],
            $download->getSpreadSheet()
        );
        $spreadsheet = $this->writeDictionaryData($args['report'], $spreadsheet);
        $spreadsheet = $this->writeRawReport($args['report'], $spreadsheet);

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

    protected function writeInfoHeader(ReportEntity $report, $subject, $subjectid, Spreadsheet $spreadsheet)
    {
        $sheet = $spreadsheet->getActiveSheet();
        $data = $this->getOrganisationInfo($subject, $subjectid);
        $infoData[] = Report::$subjectTranslations[$subject];
        if (isset($data['organisation'])) {
            $infoData[] = $data['organisation']['name'];
        }
        if (isset($data['department'])) {
            $infoData[] = $data['department']['name'];
        }
        if (isset($data['scope'])) {
            $infoData[] = $data['scope']['contact']['name'] .' '. $data['scope']['shortname'];
        }
        $infoData = array_chunk($infoData, 1);
        $sheet->fromArray($infoData, null, 'A'. $sheet->getHighestRow());

        $firstDay = $report->firstDay->toDateTime()->format('d.m.Y');
        $lastDay = $report->lastDay->toDateTime()->format('d.m.Y');
        $range = array('Zeitraum:', $firstDay, 'bis', $lastDay);
        $sheet->fromArray($range, null, 'A'. ($sheet->getHighestRow() + 1));
        return $spreadsheet;
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
        $sheet->fromArray($reportData, null, 'A'. ($sheet->getHighestRow() + 2));
        return $spreadsheet;
    }

    protected function getOrganisationInfo($subject, $subjectId)
    {
        $info = [];
        if (false !== strpos($subject, 'scope')) {
            $info['scope'] = \App::$http->readGetResult('/scope/'. $subjectId .'/')->getEntity();
            $info['department'] = \App::$http->readGetResult('/scope/'. $info['scope']->id .'/department/')
              ->getEntity();
            $info['organisation'] = \App::$http
                ->readGetResult('/department/'. $info['department']->id .'/organisation/')->getEntity();
        }
        if (false !== strpos($subject, 'department')) {
            $info['department'] = \App::$http->readGetResult('/department/'. $subjectId .'/')->getEntity();
            $info['organisation'] = \App::$http
                ->readGetResult('/department/'. $info['department']->id .'/organisation/')->getEntity();
        }
        if (false !== strpos($subject, 'organisation')) {
            $info['organisation'] = \App::$http->readGetResult('/organisation/'. $subjectId .'/')->getEntity();
        }
        return $info;
    }
}
