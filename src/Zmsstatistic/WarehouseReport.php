<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsstatistic;

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

        return \BO\Slim\Render::withHtml(
            $response,
            'page/warehouseReport.twig',
            array(
              'title' => 'Kategorien',
              'menuActive' => 'warehouse',
              'report' => $report,
              'category' => $args['subject'],
              'subjectid' => $args['subjectid'],
              'period' => $args['period'],
              'workstation' => $workstation->getArrayCopy()
            )
        );
    }
}
