<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsstatistic;

use BO\Zmsstatistic\Download\WarehouseReport as Download;

class WarehouseReport extends BaseController
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
        $validator = $request->getAttribute('validator');
        $report = \App::$http
          ->readGetResult('/warehouse/'. $args['subject'] .'/'. $args['subjectid'] .'/'. $args['period'] .'/')
          ->getEntity();

        $type = $validator->getParameter('type')->isString()->getValue();
        if ($type) {
            $args['category'] = 'raw-'. $args['subject'];
            $args['report'] = $report;
            return (new Download(\App::$slim->getContainer()))->readResponse($request, $response, $args);
        }

        return \BO\Slim\Render::withHtml(
            $response,
            'page/warehouseReport.twig',
            array(
              'title' => 'Kategorien',
              'menuActive' => 'warehouse',
              'report' => $report,
              'category' => $args['subject'],
              'categoryName' => Download::$subjectTranslations[$args['subject']],
              'subjectid' => $args['subjectid'],
              'period' => $args['period'],
              'workstation' => $this->workstation->getArrayCopy()
            )
        );
    }
}
