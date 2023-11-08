<?php
/**
 *
 * @package zmsstatistic
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmsstatistic\Helper;

use BO\Slim\Response;
use Fig\Http\Message\StatusCodeInterface;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;

class Download
{
    protected $writer = null;

    protected $spreadsheet = null;

    protected $period = '';

    protected $title = 'statistik';

    protected $type = 'xlsx';

    public function __construct($request)
    {
        $validator = $request->getAttribute('validator');
        $this->type = $validator->getParameter('type')->isString()->setDefault('xlsx')->getValue();
        return $this;
    }

    /**
     * @param Response $response
     * @return mixed
     */
    public function writeDownload($response)
    {
        $resource = fopen('php://temp', 'x+');

        try {
            $this->getWriter()->save($resource);
            rewind($resource);
            $response->getBody()->write(stream_get_contents($resource));
        } catch (\Exception $e) {
            fclose($resource);

            return $response->withStatus(StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR);
        }

        fclose($resource);

        return $response
            ->withHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->withHeader('Content-Disposition', sprintf('attachment; filename="%s.%s"', $this->title, $this->type));
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
              ->setDelimiter(';');
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
            ->setCategory($subject);
        return $this;
    }
}
