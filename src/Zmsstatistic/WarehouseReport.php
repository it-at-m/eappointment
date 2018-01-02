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
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 2])->getEntity();
        if (!$workstation->hasId()) {
            return \BO\Slim\Render::redirect(
                'index',
                array(
                    'error' => 'login_failed'
                )
            );
        }
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
              'workstation' => $workstation->getArrayCopy()
            )
        );
    }
}
