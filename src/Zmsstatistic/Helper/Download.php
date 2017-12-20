<?php
/**
 *
 * @package zmsstatistic
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmsstatistic\Helper;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;

use \BO\Mellon\Validator;

class Download
{
    protected $writer = null;

    protected $spreedsheet = null;

    protected $subject = '';

    protected $subjectId = '';

    protected $period = '';

    protected $title = 'statistik';

    protected $type = 'xlsx';

    protected $currentRow = 1;

    public function __construct($request, $args)
    {
        $validator = $request->getAttribute('validator');
        $this->type = $validator->getParameter('type')->isString()->setDefault('xlsx')->getValue();
        $this->setArguments($args);
        $this->setTitle();
        $this->setSpreadSheet();
        return $this;
    }

    protected function setTitle()
    {
        if ($this->subject) {
            $this->title = 'statistic_'. $this->subject .'_'. $this->subjectId .'_'. $this->period;
        } else {
            $this->title = 'statistic_'. $this->period;
        }
        return $this;
    }

    protected function setArguments($args)
    {
        $this->subject = (isset($args['subject'])) ? $args['subject'] : '';
        $this->subjectId = (isset($args['subjectid'])) ? $args['subjectid'] : '';
        $this->period = (isset($args['period'])) ? $args['period'] : '';
        $this->notificationReport = (isset($args['notificationReport'])) ? $args['notificationReport'] : null;
        $this->clientReport = (isset($args['clientReport'])) ? $args['clientReport'] : null;
        $this->waitingReport = (isset($args['waitingReport'])) ? $args['waitingReport'] : null;
        $this->requestReport = (isset($args['requestReport'])) ? $args['requestReport'] : null;
    }

    protected function setSpreadSheet()
    {
        $this->spreadsheet = new Spreadsheet();
        $this->spreadsheet
            ->getProperties()
            ->setCreator('BerlinOnline')
            ->setLastModifiedBy('BerlinOnline')
            ->setTitle($this->getTitle())
            ->setSubject($this->getTitle())
            ->setDescription('statistic document')
            ->setKeywords('statistic zms')
            ->setCategory($this->subject);
    }

    public function setReportWriter($scope, $department, $organisation)
    {
        $this->writeInfoHeader($scope, $department, $organisation);
        if ($this->clientReport) {
            $this->writeReport([
                $this->notificationReport, $this->clientReport
            ]);
        }

        return $this;
    }

    public function setRawReportWriter()
    {
        $report = \App::$http
          ->readGetResult('/warehouse/'. $this->subject .'/' . $this->subjectId . '/'. $this->period .'/')
          ->getEntity();
        $this->writeInfoHeader();
        $this->writeDictionaryData($report);
        $this->writeRawReport($report);
        return $this;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getWriter()
    {
        if ('csv' == $this->type) {
            $this->writer = IOFactory::createWriter($this->spreadsheet, 'Csv')
              ->setUseBOM(true)
              ->setSheetIndex(0)
              ->setExcelCompatibility(true);
        }
        if ('xlsx' == $this->type) {
            $this->writer = IOFactory::createWriter($this->spreadsheet, 'Xlsx');
        }
        return $this->writer;
    }

    protected function writeInfoHeader($scope = null, $department = null, $organisation = null)
    {
        $data = ($organisation) ?
            new OrganisationData($this->type, $scope, $department, $organisation) :
            (new OrganisationData($this->type))->setData($this->subject, $this->subjectId);
        $scopeName = $data->getScopeName();
        $departmentName = $data->getDepartmentName();
        $organisationName = $data->getOrganisationName();
        if ($organisationName) {
            $infoData[] = $organisationName;
        }
        if ($departmentName) {
            $infoData[] = $departmentName;
        }
        if ($scopeName) {
            $infoData[] = $scopeName;
        }
        $infoData = array_chunk($infoData, 1);

        $this->spreadsheet->getActiveSheet()->fromArray($infoData, null, 'A1');
        $this->currentRow = 5;
    }

    protected function writeDictionaryData($report)
    {
        $dictionaryData = [];
        foreach ($report->dictionary[0] as $key => $item) {
            $key = ('position' == $key) ? '#' : $key;
            if ('reference' != $key) {
                $dictionaryData['columns'][] = $key;
            }
        }
        foreach ($report->dictionary as $row => $entry) {
            foreach ($entry as $key => $item) {
                if ('position' == $key) {
                    $dictionaryData[$row][] = $item + 1;
                } elseif ('reference' != $key) {
                    $dictionaryData[$row][] = $item;
                }
            }
        }
        $this->spreadsheet->getActiveSheet()->fromArray($dictionaryData, null, 'A'. $this->currentRow);
        $this->currentRow += count($dictionaryData) + 1;
    }

    protected function writeReport($reports)
    {
        $rawData = [];
        foreach ($reports as $reportKey => $report) {
            foreach ($report->data as $row => $entry) {
                foreach ($entry as $key => $item) {
                    $rawData[$reportKey][$row][$key][] = (is_numeric($item)) ? (string)($item) : $item;
                }
            }
        }
        $this->spreadsheet->getActiveSheet()->fromArray($rawData, null, 'A'. $this->currentRow);
    }

    protected function writeRawReport($report)
    {
        $rawData = [];
        foreach ($report->dictionary as $item) {
            $rawData['colums'][] = $item['variable'];
        }
        foreach ($report->data as $row => $entry) {
            foreach ($entry as $item) {
                $rawData[$row][] = (is_numeric($item)) ? (string)($item) : $item;
            }
        }
        $this->spreadsheet->getActiveSheet()->fromArray($rawData, null, 'A'. $this->currentRow);
    }
}
