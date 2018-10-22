<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsstatistic;

use BO\Zmsstatistic\Download\Base;

class WarehousePeriod extends BaseController
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
        $periodList = \App::$http
          ->readGetResult('/warehouse/'. $args['subject'] .'/'. $args['subjectid'] .'/')
          ->getEntity();
        if (count($periodList['data']) == 1) {
            return \BO\Slim\Render::redirect("WarehouseReport", [
                'subject' => $args['subject'],
                'subjectid' => $args['subjectid'],
                'period' => $periodList['data'][0][0],
            ]);
        }

        return \BO\Slim\Render::withHtml(
            $response,
            'page/warehousePeriod.twig',
            array(
                'title' => 'Kategorien',
                'menuActive' => 'warehouse',
                'periodList' => $periodList,
                'category' => $args['subject'],
                'subjectId' => $args['subjectid'],
                'workstation' => $this->workstation->getArrayCopy()
            )
        );
    }
}
