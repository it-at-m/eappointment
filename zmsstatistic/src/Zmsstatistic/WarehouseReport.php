<?php

/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsstatistic;

use BO\Slim\Render;
use BO\Zmsstatistic\Download\WarehouseReport as Download;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class WarehouseReport extends BaseController
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
        $validator = $request->getAttribute('validator');
        $report = \App::$http
          ->readGetResult('/warehouse/' . $args['subject'] . '/' . $args['subjectid'] . '/' . $args['period'] . '/')
          ->getEntity();

        $type = $validator->getParameter('type')->isString()->getValue();
        if ($type) {
            $args['category'] = 'raw-' . $args['subject'];
            $args['report'] = $report;
            return (new Download(\App::$slim->getContainer()))->readResponse($request, $response, $args);
        }

        return Render::withHtml(
            $response,
            'page/warehouseReport.twig',
            array(
              'title' => 'Kategorien',
              'menuActive' => 'warehouse',
              'report' => $report,
              'category' => $args['subject'],
              'subjectid' => $args['subjectid'],
              'period' => $args['period'],
              'workstation' => $this->workstation->getArrayCopy()
            )
        );
    }
}
