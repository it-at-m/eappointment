<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

class Pickup extends BaseController
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
        $workstation = \App::$http->readGetResult('/workstation/')->getEntity();
        $cluster = \App::$http->readGetResult('/scope/'. $workstation->scope['id'] .'/cluster/')->getEntity();
        $processList = \App::$http->readGetResult('/pickup/')->getCollection();
        $department = \App::$http->readGetResult('/scope/'. $workstation->scope['id'] .'/department/')->getEntity();

        \BO\Slim\Render::withHtml(
            $response,
            'page/pickup.twig',
            array(
              'title' => 'Abholer verwalten',
              'menuActive' => 'pickup',
              'workstation' => $workstation->getArrayCopy(),
              'department' => $department,
              'source' => $workstation->getRedirect(),
              'cluster' => ($cluster) ? $cluster : null,
              'processList' => $processList
            )
        );
    }
}
