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

    protected $period = '';

    protected $title = 'statistik';

    protected $type = 'xlsx';

    public function __construct($request)
    {
        $validator = $request->getAttribute('validator');
        $this->type = $validator->getParameter('type')->isString()->setDefault('xlsx')->getValue();
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

    public function getSpreadSheet()
    {
        return $this->spreadsheet;
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

    public function setSpreadSheet(
        $title = 'statistic',
        $creator = 'berlinonline',
        $subject = '',
        $description = 'statistic document',
        $keywords = 'statistic zms'
    ) {
        $this->title = $title;
        $this->spreadsheet = new Spreadsheet();
        $this->spreadsheet
            ->getProperties()
            ->setCreator($creator)
            ->setLastModifiedBy($creator)
            ->setTitle($title)
            ->setSubject($subject)
            ->setDescription($description)
            ->setKeywords($keywords)
            ->setCategory($this->subject);
        return $this;
    }
}
