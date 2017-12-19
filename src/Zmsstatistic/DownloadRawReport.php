<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsstatistic;

use \XLSXWriter;

use \BO\Mellon\Validator;

class DownloadRawReport extends BaseController
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
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 2])->getEntity();
        if (!$workstation->hasId()) {
            return \BO\Slim\Render::redirect(
                'index',
                array(
                    'error' => 'login_failed'
                )
            );
        }

        $category = $args['subject'];
        $subjectId = $args['subjectid'];
        $period = $args['period'];
        $xlsSheetTitle = 'statistik_'. $category .'_'. $subjectId .'_'. $period;
        $downloadType = Validator::param('type')->isString()->getValue();

        $organisationData = $this->getOrganisationData($category, $subjectId);
        $report = \App::$http
            ->readGetResult('/warehouse/'. $category .'/' . $subjectId . '/'. $period .'/')
            ->getEntity();

        $writer = new XLSXWriter();
        $writer = $this->setHeaderData($writer, $category, $organisationData, $xlsSheetTitle);
        $writer = $this->setDictionaryData($writer, $report, $xlsSheetTitle);
        $writer = $this->setReportData($writer, $report, $xlsSheetTitle);

        $response->getBody()->write($writer->writeToString());
        return $response
            ->withHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->withHeader(
                'Content-Disposition',
                sprintf('download; filename="%s.%s"', $xlsSheetTitle, $downloadType)
            );
    }

    protected function getOrganisationData($category, $subjectId)
    {
        $organisationData['scope'] = null;
        $organisationData['department'] = null;
        $organisationData['organisation'] = null;
        if (false !== strpos($category, 'scope')) {
            $organisationData['scope'] = \App::$http
                ->readGetResult('/scope/'. $subjectId .'/')->getEntity();
            $organisationData['department'] = \App::$http
                ->readGetResult('/scope/'. $organisationData['scope']->id .'/department/')->getEntity();
            $organisationData['organisation'] = \App::$http
                ->readGetResult('/department/'. $organisationData['department']->id .'/organisation/')->getEntity();
        }
        if (false !== strpos($category, 'department')) {
            $organisationData['department'] = \App::$http
                ->readGetResult('/department/'. $subjectId .'/')->getEntity();
            $organisationData['organisation'] = \App::$http
                ->readGetResult('/department/'. $organisationData['department']->id .'/organisation/')->getEntity();
        }
        if (false !== strpos($category, 'organisation')) {
            $organisationData['organisation'] = \App::$http
                ->readGetResult('/organisation/'. $subjectId .'/')->getEntity();
        }
        return $organisationData;
    }

    protected function setReportData($writer, $report, $xlsSheetTitle)
    {
        // raw report header
        $xlsHeaders = [];
        foreach ($report->dictionary as $item) {
            $xlsHeaders[] = $item['variable'];
        }
        $writer->writeSheetRow($xlsSheetTitle, $xlsHeaders);

        // raw report data
        foreach ($report->data as $entry) {
            $row = [];
            foreach ($entry as $item) {
                $row[] = $item;
            }
            $writer->writeSheetRow($xlsSheetTitle, $row);
        }
        return $writer;
    }

    protected function setHeaderData($writer, $category, $organisationData, $xlsSheetTitle)
    {
        $writer->writeSheetRow($xlsSheetTitle, [
            $organisationData['organisation']->name,
            '','','','','','','',''
        ]);
        if ($organisationData['department']) {
            $writer->writeSheetRow($xlsSheetTitle, [
                $organisationData['department']->name,
                '','','','','','','',''
            ]);
        }
        if ($organisationData['scope']) {
            $writer->writeSheetRow($xlsSheetTitle, [
                $organisationData['scope']['contact']['name'] .' '. $organisationData['scope']['shortname'],
                '','','','','','','',''
            ]);
        }
        $writer->writeSheetRow($xlsSheetTitle, [
            '','','','','','','',''
        ]);
        $writer->writeSheetRow($xlsSheetTitle, [
            'Kategorie: '. $category,
            '','','','','','','',''
        ]);
        return $writer;
    }

    protected function setDictionaryData($writer, $report, $xlsSheetTitle)
    {
        // dictionary header
        $xlsHeaders = [];
        foreach ($report->dictionary[0] as $key => $item) {
            $key = ('position' == $key) ? '#' : $key;
            if ('reference' != $key) {
                $xlsHeaders[] = $key;
            }
        }
        $writer->writeSheetRow($xlsSheetTitle, $xlsHeaders);

        // dictionary data
        foreach ($report->dictionary as $entry) {
            $row = [];
            foreach ($entry as $key => $item) {
                if ('position' == $key) {
                    $row[] = $item + 1;
                } elseif ('reference' != $key) {
                    $row[] = $item;
                }
            }
            $writer->writeSheetRow($xlsSheetTitle, $row);
        }
        $writer->writeSheetRow($xlsSheetTitle, ['','','','','','','','']);
        return $writer;
    }
}
