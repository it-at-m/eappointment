<?php

/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsstatistic;

use BO\Slim\Render;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class WarehousePeriod extends BaseController
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
        $periodList = \App::$http
          ->readGetResult('/warehouse/' . $args['subject'] . '/' . $args['subjectid'] . '/')
          ->getEntity();
        if (count($periodList['data']) == 1) {
            return Render::redirect("WarehouseReport", [
                'subject' => $args['subject'],
                'subjectid' => $args['subjectid'],
                'period' => $periodList['data'][0][0],
            ]);
        }

        return Render::withHtml(
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
