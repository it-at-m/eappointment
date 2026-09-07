<?php

/**
 *
 * @package zmsstatistic
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */

namespace BO\Zmsstatistic\Helper;

use Fig\Http\Message\StatusCodeInterface;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Psr\Http\Message\ResponseInterface;

class Download
{
    protected mixed $writer = null;

    protected mixed $spreadsheet = null;

    protected string $period = '';

    protected string $title = 'statistik';

    protected mixed $type = 'xlsx';

    public function __construct(mixed $request)
    {
        $validator = $request->getAttribute('validator');
        $this->type = $validator->getParameter('type')->isString()->setDefault('xlsx')->getValue();
        return $this;
    }

    public function writeDownload(ResponseInterface $response): mixed
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

    public function getSpreadSheet(): mixed
    {
        return $this->spreadsheet;
    }

    public function getWriter(): mixed
    {
        if ('xlsx' == $this->type) {
            $this->writer = IOFactory::createWriter($this->spreadsheet, 'Xlsx');
        }
        return $this->writer;
    }

    public function setSpreadSheet(
        string $title = 'statistic',
        mixed $creator = 'berlinonline',
        mixed $subject = '',
        mixed $description = 'statistic document',
        mixed $keywords = 'statistic zms'
    ): static {
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
